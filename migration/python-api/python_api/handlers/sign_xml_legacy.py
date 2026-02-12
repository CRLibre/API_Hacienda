from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible
from . import firmar_xml

TIPOS: dict[str, str | None] = {
    "FE": "01",
    "ND": "02",
    "NC": "03",
    "TE": "04",
    "CCE": "05",
    "CPCE": "06",
    "RCE": "07",
}


async def signFE(request: Request, params: dict[str, str]) -> JSONResponse:
    tipo_doc = str(params.get("tipodoc", ""))
    if tipo_doc not in TIPOS:
        return tools_reply_compatible("No se encuentra tipo de documento")

    tipo_documento = TIPOS.get(tipo_doc)
    if tipo_documento is None:
        return tools_reply_compatible("El tipo de documento es nulo")

    return await firmar_xml.firmar(request, params)
