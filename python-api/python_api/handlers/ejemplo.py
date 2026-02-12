from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def hola(_: Request, __: dict[str, str]) -> JSONResponse:
    return tools_reply_compatible("hola :)")


async def un_usuario(_: Request, params: dict[str, str]) -> JSONResponse:
    nombre = str(params.get("nombre", ""))
    apellido = str(params.get("apellido", ""))
    return tools_reply_compatible(f"{nombre}, {apellido}")

