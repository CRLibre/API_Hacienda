from __future__ import annotations

import json
from dataclasses import dataclass
from urllib.parse import parse_qs

from fastapi import Request
from fastapi.responses import JSONResponse


@dataclass(slots=True)
class ParsedLegacyRequest:
    params: dict[str, str]
    source: str


def envelope_ok(resp: object, status_code: int = 200) -> JSONResponse:
    return JSONResponse(status_code=status_code, content={"status": "ok", "resp": resp})


def envelope_error(resp: object, status_code: int = 400) -> JSONResponse:
    return JSONResponse(status_code=status_code, content={"status": "error", "resp": resp})


async def parse_legacy_params(request: Request) -> ParsedLegacyRequest:
    query_params = {k: v for k, v in request.query_params.items()}
    if "w" in query_params:
        return ParsedLegacyRequest(params=query_params, source="query")

    content_type = request.headers.get("content-type", "")
    method = request.method.upper()

    if method in {"POST", "PUT", "PATCH"}:
        if "application/json" in content_type:
            try:
                payload = await request.json()
            except json.JSONDecodeError:
                return ParsedLegacyRequest(params={}, source="invalid_json")

            if isinstance(payload, dict) and "w" in payload:
                normalized = {str(k): str(v) for k, v in payload.items()}
                return ParsedLegacyRequest(params=normalized, source="json")

        if "application/x-www-form-urlencoded" in content_type:
            body_text = (await request.body()).decode("utf-8", errors="replace")
            form_data = {k: v[-1] for k, v in parse_qs(body_text, keep_blank_values=True).items()}
            if "w" in form_data:
                return ParsedLegacyRequest(params=form_data, source="form")

        if "multipart/form-data" in content_type:
            form = await request.form()
            form_data = {k: str(v) for k, v in form.items()}
            if "w" in form_data:
                return ParsedLegacyRequest(params=form_data, source="form")

    return ParsedLegacyRequest(params={}, source="none")

