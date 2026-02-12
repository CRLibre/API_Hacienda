from __future__ import annotations

from collections.abc import Mapping
from typing import Any

from sqlalchemy import text

from python_api.db.session import SessionLocal


def _row_to_dict(row: Any) -> dict[str, Any]:
    if row is None:
        return {}
    mapping: Mapping[str, Any] = row._mapping
    return {k: mapping[k] for k in mapping.keys()}


def fetch_one(sql: str, params: Mapping[str, Any] | None = None) -> dict[str, Any] | None:
    with SessionLocal() as db:
        row = db.execute(text(sql), dict(params or {})).fetchone()
        if row is None:
            return None
        return _row_to_dict(row)


def fetch_all(sql: str, params: Mapping[str, Any] | None = None) -> list[dict[str, Any]]:
    with SessionLocal() as db:
        rows = db.execute(text(sql), dict(params or {})).fetchall()
        return [_row_to_dict(row) for row in rows]


def execute(sql: str, params: Mapping[str, Any] | None = None) -> int:
    with SessionLocal() as db:
        result = db.execute(text(sql), dict(params or {}))
        db.commit()
        return result.rowcount or 0


def insert_and_return_id(
    insert_sql: str,
    select_sql: str,
    params: Mapping[str, Any],
    id_key: str,
) -> int | None:
    with SessionLocal() as db:
        db.execute(text(insert_sql), dict(params))
        row = db.execute(text(select_sql), dict(params)).fetchone()
        db.commit()
        if row is None:
            return None
        mapping = row._mapping
        return int(mapping[id_key])

