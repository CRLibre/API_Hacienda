from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def makeJson(_: Request, params: dict[str, str]) -> JSONResponse:
    response = {
        "clave": str(params.get("clave", "")),
        "fecha": str(params.get("fecha", "")),
        "emisor": {
            "tipoIdentificacion": str(params.get("emi_tipoIdentificacion", "")),
            "numeroIdentificacion": str(params.get("emi_numeroIdentificacion", "")),
        },
        "receptor": {
            "tipoIdentificacion": str(params.get("recp_tipoIdentificacion", "")),
            "numeroIdentificacion": str(params.get("recp_numeroIdentificacion", "")),
        },
        "comprobanteXml": str(params.get("comprobanteXml", "")),
    }
    return tools_reply_compatible(response)

