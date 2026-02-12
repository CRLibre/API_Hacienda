from __future__ import annotations

import json

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def callback(request: Request, params: dict[str, str]) -> JSONResponse:
    _ = params.get("idUser")
    raw = (await request.body()).decode("utf-8", errors="replace")
    normalized = raw.replace("ind-estado", "ind_estado").replace("respuesta-xml", "respuesta_xml")

    try:
        payload = json.loads(normalized)
    except Exception:
        return tools_reply_compatible(202)

    _ = json.dumps(payload.get("clave"))
    _ = json.dumps(payload.get("ind_estado"))
    _ = json.dumps(payload.get("fecha"))
    _ = json.dumps(payload.get("respuesta_xml"))
    return tools_reply_compatible(202)

