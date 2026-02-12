from __future__ import annotations

import httpx
from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _token_url(client_id: str) -> str | None:
    if client_id == "api-stag":
        return "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token"
    if client_id == "api-prod":
        return "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token"
    return None


def _payload_for_grant(params: dict[str, str]) -> tuple[dict[str, str] | None, str | None]:
    client_id = str(params.get("client_id", ""))
    grant_type = str(params.get("grant_type", ""))
    client_secret = str(params.get("client_secret", ""))

    if grant_type == "password":
        username = str(params.get("username", ""))
        password = str(params.get("password", ""))
        if client_id == "":
            return None, "El parametro Client ID es requerido"
        if grant_type == "":
            return None, "El parametro Grant Type es requerido"
        if username == "":
            return None, "El parametro Username es requerido"
        if password == "":
            return None, "El parametro Password es requerido"
        return {
            "client_id": client_id,
            "client_secret": client_secret,
            "grant_type": grant_type,
            "username": username,
            "password": password,
        }, None

    if grant_type == "refresh_token":
        refresh_token = str(params.get("refresh_token", ""))
        if client_id == "":
            return None, "El parametro Client ID es requerido"
        if grant_type == "":
            return None, "El parametro Grant Type es requerido"
        if refresh_token == "":
            return None, "El parametro Refresh Token es requerido"
        return {
            "client_id": client_id,
            "client_secret": client_secret,
            "grant_type": grant_type,
            "refresh_token": refresh_token,
        }, None

    return {}, None


async def _token_call(params: dict[str, str]) -> JSONResponse:
    client_id = str(params.get("client_id", ""))
    url = _token_url(client_id)
    if not url:
        return tools_reply_compatible(None)

    payload, validation_error = _payload_for_grant(params)
    if validation_error:
        return tools_reply_compatible(validation_error)
    if payload is None:
        payload = {}

    headers = {"Content-Type": "application/x-www-form-urlencoded"}
    try:
        async with httpx.AsyncClient(timeout=30, verify=False) as client:
            response = await client.post(url, headers=headers, data=payload)
    except Exception as exc:
        return tools_reply_compatible({"Status": 0, "text": str(exc)})

    try:
        data = response.json()
    except ValueError:
        data = None
    return tools_reply_compatible(data)


async def gettoken(_: Request, params: dict[str, str]) -> JSONResponse:
    return await _token_call(params)


async def refresh(_: Request, params: dict[str, str]) -> JSONResponse:
    return await _token_call(params)

