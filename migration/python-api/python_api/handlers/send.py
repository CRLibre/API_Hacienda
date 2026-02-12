from __future__ import annotations

import json as jsonlib

import httpx
from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _recepcion_url(client_id: str) -> str | None:
    if client_id == "api-stag":
        return "https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    if client_id == "api-prod":
        return "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    return None


def _raw_http_lines(response: httpx.Response) -> list[str]:
    version = response.http_version or "1.1"
    status_line = f"HTTP/{version} {response.status_code} {response.reason_phrase}\r"
    header_lines = [f"{k}: {v}\r" for k, v in response.headers.items()]
    return [status_line, *header_lines, "\r", response.text]


async def _post_to_hacienda(
    *,
    api_to: str,
    token: str,
    message: str,
) -> JSONResponse:
    url = _recepcion_url(api_to)
    if url is None:
        return tools_reply_compatible({"Status": 0, "to": api_to, "text": "No URL set!"})

    headers = {
        "Authorization": f"bearer {token}",
        "Content-Type": "application/json",
    }

    try:
        async with httpx.AsyncClient(timeout=30, verify=False) as client:
            response = await client.post(url, headers=headers, content=message.encode("utf-8"))
    except Exception as exc:
        return tools_reply_compatible({"Status": 0, "to": api_to, "text": str(exc)})

    return tools_reply_compatible({"Status": response.status_code, "text": _raw_http_lines(response)})


async def json(_: Request, params: dict[str, str]) -> JSONResponse:
    payload: dict[str, object] = {
        "clave": params.get("clave", ""),
        "fecha": params.get("fecha", ""),
        "emisor": {
            "tipoIdentificacion": params.get("emi_tipoIdentificacion", ""),
            "numeroIdentificacion": params.get("emi_numeroIdentificacion", ""),
        },
        "receptor": {
            "tipoIdentificacion": params.get("recp_tipoIdentificacion", ""),
            "numeroIdentificacion": params.get("recp_numeroIdentificacion", ""),
        },
        "comprobanteXml": params.get("comprobanteXml", ""),
        "callbackUrl": params.get("callbackUrl", ""),
    }

    if str(params.get("callbackUrl", "")) == "":
        payload.pop("callbackUrl", None)
    if str(params.get("recp_tipoIdentificacion", "")) == "" or str(params.get("recp_numeroIdentificacion", "")) == "":
        payload.pop("receptor", None)

    message = jsonlib.dumps(payload, ensure_ascii=False)
    return await _post_to_hacienda(
        api_to=str(params.get("client_id", "")),
        token=str(params.get("token", "")),
        message=message,
    )


async def sendMensaje(_: Request, params: dict[str, str]) -> JSONResponse:
    consecutivo_receptor = str(params.get("consecutivoReceptor", "")).zfill(20)

    payload = {
        "clave": params.get("clave", ""),
        "fecha": params.get("fecha", ""),
        "emisor": {
            "tipoIdentificacion": params.get("emi_tipoIdentificacion", ""),
            "numeroIdentificacion": params.get("emi_numeroIdentificacion", ""),
        },
        "receptor": {
            "tipoIdentificacion": params.get("recp_tipoIdentificacion", ""),
            "numeroIdentificacion": params.get("recp_numeroIdentificacion", ""),
        },
        "consecutivoReceptor": consecutivo_receptor,
        "comprobanteXml": params.get("comprobanteXml", ""),
    }

    message = jsonlib.dumps(payload, ensure_ascii=False)
    return await _post_to_hacienda(
        api_to=str(params.get("client_id", "")),
        token=str(params.get("token", "")),
        message=message,
    )


async def sendTE(_: Request, params: dict[str, str]) -> JSONResponse:
    payload = {
        "clave": params.get("clave", ""),
        "fecha": params.get("fecha", ""),
        "emisor": {
            "tipoIdentificacion": params.get("emi_tipoIdentificacion", ""),
            "numeroIdentificacion": params.get("emi_numeroIdentificacion", ""),
        },
        "comprobanteXml": params.get("comprobanteXml", ""),
    }

    message = jsonlib.dumps(payload, ensure_ascii=False)
    return await _post_to_hacienda(
        api_to=str(params.get("client_id", "")),
        token=str(params.get("token", "")),
        message=message,
    )
