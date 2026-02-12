from __future__ import annotations

import httpx
from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _consultar_url(client_id: str) -> str | None:
    if client_id == "api-stag":
        return "https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    if client_id == "api-prod":
        return "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/"
    return None


async def consultarCom(_: Request, params: dict[str, str]) -> JSONResponse:
    clave = str(params.get("clave", ""))
    token = str(params.get("token", ""))
    client_id = str(params.get("client_id", ""))

    if clave == "":
        return tools_reply_compatible("La clave no puede ser en blanco")

    url_base = _consultar_url(client_id)
    if url_base is None:
        return tools_reply_compatible("Ha ocurrido un error en el client_id.")

    headers = {
        "Authorization": f"Bearer {token}",
        "Cache-Control": "no-cache",
        "Content-Type": "application/x-www-form-urlencoded",
        "Postman-Token": "bf8dc171-5bb7-fa54-7416-56c5cda9bf5c",
    }

    try:
        async with httpx.AsyncClient(timeout=30, verify=False) as client:
            response = await client.get(f"{url_base}{clave}", headers=headers)
    except Exception as exc:
        return tools_reply_compatible({"Status": 0, "to": None, "text": str(exc)})

    try:
        data = response.json()
    except ValueError:
        data = None
    return tools_reply_compatible(data)

