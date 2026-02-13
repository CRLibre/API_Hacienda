from __future__ import annotations

import hashlib
import json
import random
import time
import uuid
from typing import Any

import httpx

from python_api.config import Settings, get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.aws_clients import (
    delete_sqs_message,
    read_sqs_messages,
    send_sqs_message,
)
from python_api.services.db_compat import execute, fetch_all, fetch_one

FINAL_STATES = {"ACCEPTED", "REJECTED", "FAILED", "DEAD_LETTER"}
RETRY_STATES = {"QUEUED", "RETRY_WAIT", "PENDING_HACIENDA"}


def _now() -> int:
    return int(time.time())


def _normalize_text(value: Any, *, max_len: int = 64000) -> str:
    if value is None:
        return ""
    text = str(value)
    if len(text) <= max_len:
        return text
    return text[:max_len]


def _recepcion_url(client_id: str) -> str | None:
    if client_id == "api-stag":
        return "https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    if client_id == "api-prod":
        return "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    return None


def _token_url(client_id: str) -> str | None:
    if client_id == "api-stag":
        return "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token"
    if client_id == "api-prod":
        return "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token"
    return None


def _safe_json_loads(raw: str | None) -> dict[str, Any]:
    if not raw:
        return {}
    try:
        data = json.loads(raw)
    except (TypeError, ValueError):
        return {}
    if not isinstance(data, dict):
        return {}
    return data


def _extract_estado(payload: dict[str, Any] | None) -> str:
    if not isinstance(payload, dict):
        return ""
    value = payload.get("ind-estado")
    if value is None:
        value = payload.get("ind_estado")
    if value is None:
        value = payload.get("estado")
    return str(value or "").strip().lower()


def _is_retryable_http(status_code: int) -> bool:
    if status_code <= 0:
        return True
    if status_code in {408, 409, 423, 425, 429}:
        return True
    return status_code >= 500


def _compute_retry_delay_seconds(*, attempt_count: int, settings: Settings) -> int:
    delay = max(1, int(settings.fe_retry_base_seconds))
    max_delay = max(delay, int(settings.fe_retry_max_seconds))
    loops = max(0, attempt_count - 1)
    for _ in range(min(loops, 20)):
        delay = min(max_delay, delay * 2)
        if delay >= max_delay:
            break
    jitter = max(0, int(settings.fe_retry_jitter_seconds))
    if jitter:
        delay += random.randint(0, jitter)
    return min(delay, max_delay)


def _normalize_job_row(row: dict[str, Any] | None) -> dict[str, Any] | None:
    if row is None:
        return None
    return {
        "job_id": str(row.get("job_id") or ""),
        "w_module": str(row.get("w_module") or ""),
        "r_route": str(row.get("r_route") or ""),
        "clave": str(row.get("clave") or ""),
        "client_id": str(row.get("client_id") or ""),
        "job_type": str(row.get("job_type") or "send"),
        "idempotency_key": str(row.get("idempotency_key") or ""),
        "status": str(row.get("status") or "QUEUED"),
        "attempt_count": int(row.get("attempt_count") or 0),
        "max_attempts": int(row.get("max_attempts") or 0),
        "next_retry_at": int(row.get("next_retry_at") or 0),
        "created_at": int(row.get("created_at") or 0),
        "updated_at": int(row.get("updated_at") or 0),
        "first_attempt_at": int(row.get("first_attempt_at") or 0),
        "last_attempt_at": int(row.get("last_attempt_at") or 0),
        "completed_at": int(row.get("completed_at") or 0),
        "last_error": str(row.get("last_error") or ""),
        "last_http_status": int(row.get("last_http_status") or 0),
        "last_http_body": str(row.get("last_http_body") or ""),
        "payload_json": str(row.get("payload_json") or "{}"),
    }


def _build_idempotency_key(*, route_r: str, client_id: str, clave: str, message: str) -> str:
    raw = "\n".join(
        [
            "v1",
            route_r.strip().lower(),
            client_id.strip().lower(),
            clave.strip(),
            message,
        ]
    ).encode("utf-8")
    return hashlib.sha256(raw).hexdigest()


def ensure_async_tables() -> None:
    execute(
        """
        CREATE TABLE IF NOT EXISTS fe_async_jobs (
            id BIGINT NOT NULL AUTO_INCREMENT,
            job_id VARCHAR(64) NOT NULL,
            w_module VARCHAR(64) NOT NULL,
            r_route VARCHAR(128) NOT NULL,
            clave VARCHAR(64) NOT NULL DEFAULT '',
            client_id VARCHAR(32) NOT NULL DEFAULT '',
            job_type VARCHAR(16) NOT NULL DEFAULT 'send',
            idempotency_key VARCHAR(64) NOT NULL DEFAULT '',
            payload_json LONGTEXT NOT NULL,
            status VARCHAR(32) NOT NULL,
            attempt_count INT NOT NULL DEFAULT 0,
            max_attempts INT NOT NULL DEFAULT 256,
            next_retry_at BIGINT NOT NULL DEFAULT 0,
            created_at BIGINT NOT NULL,
            updated_at BIGINT NOT NULL,
            first_attempt_at BIGINT NULL,
            last_attempt_at BIGINT NULL,
            completed_at BIGINT NULL,
            last_error TEXT NULL,
            last_http_status INT NULL,
            last_http_body LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_fe_async_jobs_job_id (job_id),
            UNIQUE KEY uq_fe_async_jobs_idempotency_key (idempotency_key),
            KEY idx_fe_async_jobs_status_retry (status, next_retry_at),
            KEY idx_fe_async_jobs_clave (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        """
    )

    column_exists = fetch_one(
        """
        SELECT 1 AS ok
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fe_async_jobs'
          AND COLUMN_NAME = 'idempotency_key'
        LIMIT 1
        """
    )
    if not column_exists:
        execute(
            """
            ALTER TABLE fe_async_jobs
            ADD COLUMN idempotency_key VARCHAR(64) NOT NULL DEFAULT '' AFTER job_type
            """
        )
        execute(
            """
            UPDATE fe_async_jobs
            SET idempotency_key = job_id
            WHERE idempotency_key = ''
            """
        )

    idx_exists = fetch_one(
        """
        SELECT 1 AS ok
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'fe_async_jobs'
          AND INDEX_NAME = 'uq_fe_async_jobs_idempotency_key'
        LIMIT 1
        """
    )
    if not idx_exists:
        execute(
            """
            ALTER TABLE fe_async_jobs
            ADD UNIQUE KEY uq_fe_async_jobs_idempotency_key (idempotency_key)
            """
        )


def _load_job(job_id: str) -> dict[str, Any] | None:
    row = fetch_one(
        """
        SELECT
            job_id, w_module, r_route, clave, client_id, job_type, idempotency_key, payload_json,
            status, attempt_count, max_attempts, next_retry_at, created_at, updated_at,
            first_attempt_at, last_attempt_at, completed_at, last_error, last_http_status, last_http_body
        FROM fe_async_jobs
        WHERE job_id = :job_id
        """,
        {"job_id": job_id},
    )
    return _normalize_job_row(row)


def _load_job_by_idempotency_key(idempotency_key: str) -> dict[str, Any] | None:
    row = fetch_one(
        """
        SELECT
            job_id, w_module, r_route, clave, client_id, job_type, idempotency_key, payload_json,
            status, attempt_count, max_attempts, next_retry_at, created_at, updated_at,
            first_attempt_at, last_attempt_at, completed_at, last_error, last_http_status, last_http_body
        FROM fe_async_jobs
        WHERE idempotency_key = :idempotency_key
        ORDER BY id DESC
        LIMIT 1
        """,
        {"idempotency_key": idempotency_key},
    )
    return _normalize_job_row(row)


def get_job_status(job_id: str) -> dict[str, Any] | None:
    ensure_async_tables()
    row = _load_job(job_id)
    if row is None:
        return None
    payload = row.pop("payload_json", "{}")
    row["payload"] = _safe_json_loads(payload)
    return row


def _send_job_message(*, job_id: str, delay_seconds: int, settings: Settings) -> None:
    if not settings.fe_sqs_queue_url:
        raise RuntimeError("API_HACIENDA_FE_SQS_QUEUE_URL is required when async mode is enabled.")
    send_sqs_message(
        queue_url=settings.fe_sqs_queue_url,
        region_name=settings.aws_region,
        body=json.dumps({"job_id": job_id}),
        delay_seconds=delay_seconds,
        deduplication_id=f"{job_id}:{uuid.uuid4().hex}",
        group_id=job_id,
    )


def enqueue_send_job(*, route_r: str, params: dict[str, str], message: str) -> dict[str, Any]:
    settings = get_settings()
    if not settings.fe_async_enabled:
        raise RuntimeError("Async FE mode is disabled.")
    ensure_async_tables()

    now = _now()
    job_id = uuid.uuid4().hex
    payload = {
        "api_to": str(params.get("client_id", "")),
        "clave": str(params.get("clave", "")),
        "message": message,
        "token": str(params.get("token", "")),
        "client_id": str(params.get("client_id", "")),
        "client_secret": str(params.get("client_secret", "")),
        "username": str(params.get("username", "")),
        "password": str(params.get("password", "")),
        "refresh_token": str(params.get("refresh_token", "")),
        "grant_type": str(params.get("grant_type", "")),
    }
    idempotency_key = _build_idempotency_key(
        route_r=route_r,
        client_id=payload["api_to"],
        clave=payload["clave"],
        message=message,
    )

    existing = _load_job_by_idempotency_key(idempotency_key)
    if existing is not None:
        if existing["status"] in RETRY_STATES:
            _send_job_message(job_id=existing["job_id"], delay_seconds=0, settings=settings)
        return existing

    try:
        execute(
            """
            INSERT INTO fe_async_jobs (
                job_id, w_module, r_route, clave, client_id, job_type, idempotency_key, payload_json, status,
                attempt_count, max_attempts, next_retry_at, created_at, updated_at
            ) VALUES (
                :job_id, :w_module, :r_route, :clave, :client_id, :job_type, :idempotency_key, :payload_json, :status,
                :attempt_count, :max_attempts, :next_retry_at, :created_at, :updated_at
            )
            """,
            {
                "job_id": job_id,
                "w_module": "send",
                "r_route": route_r,
                "clave": payload["clave"],
                "client_id": payload["api_to"],
                "job_type": "send",
                "idempotency_key": idempotency_key,
                "payload_json": json.dumps(payload, ensure_ascii=False),
                "status": "QUEUED",
                "attempt_count": 0,
                "max_attempts": int(settings.fe_retry_max_attempts),
                "next_retry_at": now,
                "created_at": now,
                "updated_at": now,
            },
        )
    except Exception:
        existing = _load_job_by_idempotency_key(idempotency_key)
        if existing is not None:
            if existing["status"] in RETRY_STATES:
                _send_job_message(job_id=existing["job_id"], delay_seconds=0, settings=settings)
            return existing
        raise
    _send_job_message(job_id=job_id, delay_seconds=0, settings=settings)
    row = _load_job(job_id)
    if row is None:
        raise RuntimeError("Could not load queued FE async job.")
    return row


async def _fetch_token_if_possible(payload: dict[str, Any], settings: Settings) -> tuple[str, str]:
    token = str(payload.get("token") or "")
    client_id = str(payload.get("client_id") or payload.get("api_to") or "")
    token_url = _token_url(client_id)
    if not token_url:
        return token, ""

    username = str(payload.get("username") or "")
    password = str(payload.get("password") or "")
    client_secret = str(payload.get("client_secret") or "")
    refresh_token = str(payload.get("refresh_token") or "")

    grant_type = str(payload.get("grant_type") or "").strip()
    if grant_type == "":
        if username and password:
            grant_type = "password"
        elif refresh_token:
            grant_type = "refresh_token"

    token_payload: dict[str, str] = {
        "client_id": client_id,
        "client_secret": client_secret,
        "grant_type": grant_type,
    }
    if grant_type == "password" and username and password:
        token_payload["username"] = username
        token_payload["password"] = password
    elif grant_type == "refresh_token" and refresh_token:
        token_payload["refresh_token"] = refresh_token
    else:
        return token, ""

    try:
        async with httpx.AsyncClient(timeout=settings.request_timeout_seconds, verify=False) as client:
            response = await client.post(
                token_url,
                headers={"Content-Type": "application/x-www-form-urlencoded"},
                data=token_payload,
            )
    except Exception as exc:  # pragma: no cover - network dependent
        return token, f"token_fetch_error: {exc}"

    try:
        data = response.json()
    except ValueError:
        data = {}
    new_token = str(data.get("access_token") or "")
    if not new_token:
        return token, f"token_fetch_empty: http={response.status_code}"
    payload["token"] = new_token
    return new_token, ""


async def _post_hacienda(
    *,
    api_to: str,
    token: str,
    message: str,
    settings: Settings,
) -> tuple[int, str, dict[str, Any] | None, str]:
    url = _recepcion_url(api_to)
    if url is None:
        return 0, "", None, "No URL set for client_id"
    headers = {"Authorization": f"bearer {token}", "Content-Type": "application/json"}
    try:
        async with httpx.AsyncClient(timeout=settings.request_timeout_seconds, verify=False) as client:
            response = await client.post(url, headers=headers, content=message.encode("utf-8"))
    except Exception as exc:  # pragma: no cover - network dependent
        return 0, "", None, str(exc)
    try:
        data = response.json()
    except ValueError:
        data = None
    return response.status_code, response.text, data, ""


async def _consult_hacienda(
    *,
    api_to: str,
    token: str,
    clave: str,
    settings: Settings,
) -> tuple[int, str, dict[str, Any] | None, str]:
    url_base = _recepcion_url(api_to)
    if url_base is None:
        return 0, "", None, "No URL set for client_id"
    headers = {
        "Authorization": f"Bearer {token}",
        "Cache-Control": "no-cache",
        "Content-Type": "application/x-www-form-urlencoded",
    }
    try:
        async with httpx.AsyncClient(timeout=settings.request_timeout_seconds, verify=False) as client:
            response = await client.get(f"{url_base}{clave}", headers=headers)
    except Exception as exc:  # pragma: no cover - network dependent
        return 0, "", None, str(exc)
    try:
        data = response.json()
    except ValueError:
        data = None
    return response.status_code, response.text, data, ""


def _mark_completed(
    *,
    job_id: str,
    status: str,
    http_status: int,
    body: str,
    error: str,
) -> None:
    now = _now()
    execute(
        """
        UPDATE fe_async_jobs
        SET
            status = :status,
            completed_at = :completed_at,
            updated_at = :updated_at,
            last_http_status = :last_http_status,
            last_http_body = :last_http_body,
            last_error = :last_error
        WHERE job_id = :job_id
        """,
        {
            "status": status,
            "completed_at": now,
            "updated_at": now,
            "last_http_status": http_status,
            "last_http_body": _normalize_text(body),
            "last_error": _normalize_text(error),
            "job_id": job_id,
        },
    )


def _mark_retry(
    *,
    row: dict[str, Any],
    attempt_count: int,
    error: str,
    http_status: int,
    body: str,
    settings: Settings,
) -> dict[str, Any]:
    now = _now()
    age = max(0, now - int(row.get("created_at") or now))
    max_attempts = int(row.get("max_attempts") or settings.fe_retry_max_attempts)
    too_many_attempts = attempt_count >= max_attempts
    too_old = age >= int(settings.fe_retry_max_age_seconds)

    if too_many_attempts or too_old:
        final_status = "DEAD_LETTER"
        _mark_completed(
            job_id=row["job_id"],
            status=final_status,
            http_status=http_status,
            body=body,
            error=error,
        )
        return {"status": final_status, "retry_scheduled": False, "age_seconds": age}

    delay = _compute_retry_delay_seconds(attempt_count=attempt_count, settings=settings)
    next_retry_at = now + delay
    execute(
        """
        UPDATE fe_async_jobs
        SET
            status = :status,
            next_retry_at = :next_retry_at,
            updated_at = :updated_at,
            last_error = :last_error,
            last_http_status = :last_http_status,
            last_http_body = :last_http_body
        WHERE job_id = :job_id
        """,
        {
            "status": "RETRY_WAIT",
            "next_retry_at": next_retry_at,
            "updated_at": now,
            "last_error": _normalize_text(error),
            "last_http_status": http_status,
            "last_http_body": _normalize_text(body),
            "job_id": row["job_id"],
        },
    )
    _send_job_message(
        job_id=row["job_id"],
        delay_seconds=min(900, max(0, next_retry_at - now)),
        settings=settings,
    )
    return {"status": "RETRY_WAIT", "retry_scheduled": True, "next_retry_at": next_retry_at}


def _mark_for_consult(*, row: dict[str, Any], settings: Settings, http_status: int, body: str) -> dict[str, Any]:
    now = _now()
    next_retry_at = now + max(1, int(settings.fe_consult_delay_seconds))
    execute(
        """
        UPDATE fe_async_jobs
        SET
            status = :status,
            job_type = :job_type,
            next_retry_at = :next_retry_at,
            updated_at = :updated_at,
            last_http_status = :last_http_status,
            last_http_body = :last_http_body,
            last_error = ''
        WHERE job_id = :job_id
        """,
        {
            "status": "PENDING_HACIENDA",
            "job_type": "consult",
            "next_retry_at": next_retry_at,
            "updated_at": now,
            "last_http_status": http_status,
            "last_http_body": _normalize_text(body),
            "job_id": row["job_id"],
        },
    )
    _send_job_message(
        job_id=row["job_id"],
        delay_seconds=min(900, max(0, next_retry_at - now)),
        settings=settings,
    )
    return {"status": "PENDING_HACIENDA", "next_retry_at": next_retry_at}


async def process_job(job_id: str) -> dict[str, Any]:
    settings = get_settings()
    ensure_async_tables()
    row = _load_job(job_id)
    if row is None:
        return {"job_id": job_id, "result": "missing"}

    if row["status"] in FINAL_STATES:
        return {"job_id": job_id, "result": "already_final", "status": row["status"]}
    if row["status"] == "PROCESSING":
        return {"job_id": job_id, "result": "already_processing", "status": row["status"]}

    now = _now()
    if int(row.get("next_retry_at") or 0) > now:
        wait_seconds = int(row["next_retry_at"]) - now
        _send_job_message(
            job_id=row["job_id"],
            delay_seconds=min(900, max(0, wait_seconds)),
            settings=settings,
        )
        return {"job_id": row["job_id"], "result": "not_due", "wait_seconds": wait_seconds}

    attempt_count = int(row.get("attempt_count") or 0) + 1
    first_attempt_at = int(row.get("first_attempt_at") or 0) or now
    execute(
        """
        UPDATE fe_async_jobs
        SET
            status = :status,
            attempt_count = :attempt_count,
            first_attempt_at = :first_attempt_at,
            last_attempt_at = :last_attempt_at,
            updated_at = :updated_at
        WHERE job_id = :job_id
        """,
        {
            "status": "PROCESSING",
            "attempt_count": attempt_count,
            "first_attempt_at": first_attempt_at,
            "last_attempt_at": now,
            "updated_at": now,
            "job_id": row["job_id"],
        },
    )

    payload = _safe_json_loads(row["payload_json"])
    token, token_error = await _fetch_token_if_possible(payload, settings)
    if token_error and not token:
        retry = _mark_retry(
            row=row,
            attempt_count=attempt_count,
            error=token_error,
            http_status=0,
            body="",
            settings=settings,
        )
        return {"job_id": row["job_id"], "result": "retry_token", **retry}

    api_to = str(payload.get("api_to") or row.get("client_id") or "")
    clave = str(payload.get("clave") or row.get("clave") or "")
    job_type = str(row.get("job_type") or "send")

    if job_type == "send":
        http_status, body, data, error = await _post_hacienda(
            api_to=api_to,
            token=token,
            message=str(payload.get("message") or ""),
            settings=settings,
        )
        if error or _is_retryable_http(http_status):
            retry = _mark_retry(
                row=row,
                attempt_count=attempt_count,
                error=error or f"http_status={http_status}",
                http_status=http_status,
                body=body,
                settings=settings,
            )
            return {"job_id": row["job_id"], "result": "retry_send", **retry}

        estado = _extract_estado(data)
        if estado == "aceptado":
            _mark_completed(
                job_id=row["job_id"],
                status="ACCEPTED",
                http_status=http_status,
                body=body,
                error="",
            )
            return {"job_id": row["job_id"], "result": "accepted", "status": "ACCEPTED"}
        if estado == "rechazado":
            _mark_completed(
                job_id=row["job_id"],
                status="REJECTED",
                http_status=http_status,
                body=body,
                error="",
            )
            return {"job_id": row["job_id"], "result": "rejected", "status": "REJECTED"}

        pending = _mark_for_consult(row=row, settings=settings, http_status=http_status, body=body)
        return {"job_id": row["job_id"], "result": "pending_consult", **pending}

    http_status, body, data, error = await _consult_hacienda(
        api_to=api_to,
        token=token,
        clave=clave,
        settings=settings,
    )
    if error or _is_retryable_http(http_status):
        retry = _mark_retry(
            row=row,
            attempt_count=attempt_count,
            error=error or f"http_status={http_status}",
            http_status=http_status,
            body=body,
            settings=settings,
        )
        return {"job_id": row["job_id"], "result": "retry_consult", **retry}

    estado = _extract_estado(data)
    if estado == "aceptado":
        _mark_completed(
            job_id=row["job_id"],
            status="ACCEPTED",
            http_status=http_status,
            body=body,
            error="",
        )
        return {"job_id": row["job_id"], "result": "accepted", "status": "ACCEPTED"}
    if estado == "rechazado":
        _mark_completed(
            job_id=row["job_id"],
            status="REJECTED",
            http_status=http_status,
            body=body,
            error="",
        )
        return {"job_id": row["job_id"], "result": "rejected", "status": "REJECTED"}

    retry = _mark_retry(
        row=row,
        attempt_count=attempt_count,
        error=f"estado_no_final={estado or 'desconocido'}",
        http_status=http_status,
        body=body,
        settings=settings,
    )
    return {"job_id": row["job_id"], "result": "retry_estado_no_final", **retry}


async def process_sqs_batch(max_messages: int | None = None) -> dict[str, Any]:
    settings = get_settings()
    if not settings.fe_async_enabled:
        return {"enabled": False, "reason": "API_HACIENDA_FE_ASYNC_ENABLED is false"}
    if not settings.fe_sqs_queue_url:
        return {"enabled": False, "reason": "API_HACIENDA_FE_SQS_QUEUE_URL is empty"}

    ensure_async_tables()
    max_messages = max_messages or settings.fe_worker_batch_size
    messages = read_sqs_messages(
        queue_url=settings.fe_sqs_queue_url,
        region_name=settings.aws_region,
        max_messages=max_messages,
    )

    summary = {
        "enabled": True,
        "fetched_messages": len(messages),
        "processed": 0,
        "deleted": 0,
        "errors": 0,
        "results": [],
    }

    for msg in messages:
        receipt = str(msg.get("ReceiptHandle") or "")
        body = _safe_json_loads(msg.get("Body"))
        job_id = str(body.get("job_id") or "")
        if not job_id:
            if receipt:
                delete_sqs_message(
                    queue_url=settings.fe_sqs_queue_url,
                    region_name=settings.aws_region,
                    receipt_handle=receipt,
                )
                summary["deleted"] += 1
            summary["errors"] += 1
            summary["results"].append({"result": "invalid_message", "body": body})
            continue
        try:
            outcome = await process_job(job_id)
            summary["processed"] += 1
            summary["results"].append(outcome)
            if receipt:
                delete_sqs_message(
                    queue_url=settings.fe_sqs_queue_url,
                    region_name=settings.aws_region,
                    receipt_handle=receipt,
                )
                summary["deleted"] += 1
        except Exception as exc:  # pragma: no cover - defensive
            summary["errors"] += 1
            summary["results"].append({"job_id": job_id, "result": "exception", "error": str(exc)})

    return summary


def requeue_due_jobs(limit: int = 100) -> dict[str, Any]:
    settings = get_settings()
    if not settings.fe_async_enabled:
        return {"enabled": False, "reason": "API_HACIENDA_FE_ASYNC_ENABLED is false"}
    if not settings.fe_sqs_queue_url:
        return {"enabled": False, "reason": "API_HACIENDA_FE_SQS_QUEUE_URL is empty"}
    ensure_async_tables()

    now = _now()
    safe_limit = max(1, min(1000, int(limit)))
    stuck_before = now - max(60, int(settings.fe_stuck_processing_seconds))

    stuck_rows = fetch_all(
        f"""
        SELECT job_id
        FROM fe_async_jobs
        WHERE status = 'PROCESSING'
          AND COALESCE(last_attempt_at, updated_at, created_at) <= :stuck_before
        ORDER BY updated_at ASC
        LIMIT {safe_limit}
        """,
        {"stuck_before": stuck_before},
    )
    recovered = 0
    for row in stuck_rows:
        job_id = str(row.get("job_id") or "")
        if not job_id:
            continue
        execute(
            """
            UPDATE fe_async_jobs
            SET
                status = 'RETRY_WAIT',
                next_retry_at = :next_retry_at,
                updated_at = :updated_at,
                last_error = :last_error
            WHERE job_id = :job_id
            """,
            {
                "next_retry_at": now,
                "updated_at": now,
                "last_error": "Recovered stuck PROCESSING job by scheduler",
                "job_id": job_id,
            },
        )
        _send_job_message(job_id=job_id, delay_seconds=0, settings=settings)
        recovered += 1

    rows = fetch_all(
        f"""
        SELECT job_id
        FROM fe_async_jobs
        WHERE status IN ('QUEUED', 'RETRY_WAIT', 'PENDING_HACIENDA')
          AND next_retry_at <= :now
        ORDER BY next_retry_at ASC
        LIMIT {safe_limit}
        """,
        {"now": now},
    )

    pushed = 0
    for row in rows:
        job_id = str(row.get("job_id") or "")
        if not job_id:
            continue
        _send_job_message(job_id=job_id, delay_seconds=0, settings=settings)
        pushed += 1
    return {
        "enabled": True,
        "stuck_found": len(stuck_rows),
        "stuck_recovered": recovered,
        "due_found": len(rows),
        "requeued": pushed,
    }


def enqueue_response(job_row: dict[str, Any]) -> Any:
    return tools_reply_compatible(
        {
            "Status": 202,
            "queued": True,
            "job_id": job_row["job_id"],
            "job_status": job_row["status"],
            "next_retry_at": job_row["next_retry_at"],
        }
    )
