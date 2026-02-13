from __future__ import annotations

import asyncio
import json
import os
from typing import Any

from python_api.main import app
from python_api.services.fe_async import process_job, process_sqs_batch, requeue_due_jobs

try:
    from mangum import Mangum
except ImportError:  # pragma: no cover - optional at runtime for non-lambda usage
    Mangum = None

_api_handler = Mangum(app, lifespan="off") if Mangum is not None else None


def api_gateway_handler(event: dict[str, Any], context: Any) -> dict[str, Any]:
    if _api_handler is None:
        raise RuntimeError("Mangum is required for api_gateway_handler. Install dependency `mangum`.")
    return _api_handler(event, context)


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


async def _process_sqs_event(records: list[dict[str, Any]]) -> dict[str, Any]:
    failures: list[dict[str, str]] = []
    results: list[dict[str, Any]] = []

    for record in records:
        message_id = str(record.get("messageId") or "")
        body = _safe_json_loads(record.get("body"))
        job_id = str(body.get("job_id") or "")
        if not job_id:
            results.append({"message_id": message_id, "result": "skipped_no_job_id"})
            continue
        try:
            outcome = await process_job(job_id)
            results.append({"message_id": message_id, "job_id": job_id, "outcome": outcome})
        except Exception as exc:  # pragma: no cover - defensive path for AWS runtime
            if message_id:
                failures.append({"itemIdentifier": message_id})
            results.append({"message_id": message_id, "job_id": job_id, "error": str(exc)})

    return {"batchItemFailures": failures, "results": results}


def sqs_worker_handler(event: dict[str, Any], context: Any) -> dict[str, Any]:
    records = event.get("Records") or []
    return asyncio.run(_process_sqs_event(records))


def scheduler_requeue_handler(event: dict[str, Any], context: Any) -> dict[str, Any]:
    requeue_limit = int(os.getenv("API_HACIENDA_FE_REQUEUE_LIMIT", "200"))
    max_messages = int(os.getenv("API_HACIENDA_FE_WORKER_BATCH_SIZE", "10"))
    requeue_result = requeue_due_jobs(limit=requeue_limit)
    batch_result = asyncio.run(process_sqs_batch(max_messages=max_messages))
    return {"requeue": requeue_result, "batch": batch_result}
