from __future__ import annotations

import re
from collections.abc import Mapping
from typing import Any
from urllib.parse import urlparse

from python_api.config import Settings, get_settings
from python_api.services.aws_clients import get_rds_data_client

PARAM_RE = re.compile(r":([A-Za-z_][A-Za-z0-9_]*)")


def _ensure_settings(settings: Settings) -> tuple[str, str, str]:
    resource_arn = settings.rds_data_resource_arn.strip()
    secret_arn = settings.rds_data_secret_arn.strip()
    database = settings.rds_data_database.strip()

    if not database:
        parsed = urlparse(settings.database_url)
        database = parsed.path.lstrip("/")

    if not resource_arn or not secret_arn or not database:
        raise RuntimeError(
            "RDS Data API backend requires API_HACIENDA_RDS_DATA_RESOURCE_ARN, "
            "API_HACIENDA_RDS_DATA_SECRET_ARN and database name "
            "(API_HACIENDA_RDS_DATA_DATABASE or parsable API_HACIENDA_DATABASE_URL)."
        )
    return resource_arn, secret_arn, database


def _to_field(value: Any) -> dict[str, Any]:
    if value is None:
        return {"isNull": True}
    if isinstance(value, bool):
        return {"booleanValue": value}
    if isinstance(value, int):
        return {"longValue": value}
    if isinstance(value, float):
        return {"doubleValue": value}
    if isinstance(value, (bytes, bytearray)):
        return {"blobValue": bytes(value)}
    return {"stringValue": str(value)}


def _extract_field_value(field: Mapping[str, Any]) -> Any:
    if field.get("isNull"):
        return None
    for key in ("stringValue", "longValue", "doubleValue", "booleanValue", "blobValue"):
        if key in field:
            return field[key]
    if "arrayValue" in field:
        array_value = field["arrayValue"]
        return array_value.get("stringValues") or array_value.get("longValues") or []
    return None


def _build_parameters(sql: str, params: Mapping[str, Any]) -> list[dict[str, Any]]:
    names = []
    seen: set[str] = set()
    for name in PARAM_RE.findall(sql):
        if name in seen:
            continue
        seen.add(name)
        names.append(name)
    return [{"name": name, "value": _to_field(params.get(name))} for name in names]


def _execute_statement(
    sql: str,
    params: Mapping[str, Any] | None = None,
    *,
    include_result_metadata: bool = False,
    transaction_id: str | None = None,
    settings: Settings | None = None,
) -> dict[str, Any]:
    settings = settings or get_settings()
    resource_arn, secret_arn, database = _ensure_settings(settings)
    client = get_rds_data_client(settings.aws_region)
    payload: dict[str, Any] = {
        "resourceArn": resource_arn,
        "secretArn": secret_arn,
        "database": database,
        "sql": sql,
        "parameters": _build_parameters(sql, dict(params or {})),
        "includeResultMetadata": include_result_metadata,
    }
    if transaction_id:
        payload["transactionId"] = transaction_id
    return client.execute_statement(**payload)


def _records_to_rows(response: Mapping[str, Any]) -> list[dict[str, Any]]:
    metadata = response.get("columnMetadata") or []
    columns = [str(meta.get("name", "")) for meta in metadata]
    rows: list[dict[str, Any]] = []
    for record in response.get("records") or []:
        row: dict[str, Any] = {}
        for idx, field in enumerate(record):
            key = columns[idx] if idx < len(columns) and columns[idx] else f"c{idx}"
            row[key] = _extract_field_value(field)
        rows.append(row)
    return rows


def fetch_one(sql: str, params: Mapping[str, Any] | None = None) -> dict[str, Any] | None:
    response = _execute_statement(sql, params, include_result_metadata=True)
    rows = _records_to_rows(response)
    if not rows:
        return None
    return rows[0]


def fetch_all(sql: str, params: Mapping[str, Any] | None = None) -> list[dict[str, Any]]:
    response = _execute_statement(sql, params, include_result_metadata=True)
    return _records_to_rows(response)


def execute(sql: str, params: Mapping[str, Any] | None = None) -> int:
    response = _execute_statement(sql, params, include_result_metadata=False)
    return int(response.get("numberOfRecordsUpdated") or 0)


def insert_and_return_id(
    insert_sql: str,
    select_sql: str,
    params: Mapping[str, Any],
    id_key: str,
) -> int | None:
    settings = get_settings()
    resource_arn, secret_arn, database = _ensure_settings(settings)
    client = get_rds_data_client(settings.aws_region)
    tx = client.begin_transaction(resourceArn=resource_arn, secretArn=secret_arn, database=database)
    tx_id = tx["transactionId"]
    try:
        _execute_statement(insert_sql, params, transaction_id=tx_id, settings=settings)
        response = _execute_statement(
            select_sql,
            params,
            include_result_metadata=True,
            transaction_id=tx_id,
            settings=settings,
        )
        rows = _records_to_rows(response)
        client.commit_transaction(
            resourceArn=resource_arn,
            secretArn=secret_arn,
            transactionId=tx_id,
        )
    except Exception:
        client.rollback_transaction(
            resourceArn=resource_arn,
            secretArn=secret_arn,
            transactionId=tx_id,
        )
        raise

    if not rows:
        return None
    value = rows[0].get(id_key)
    if value is None:
        return None
    return int(value)
