from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def FE(_: Request, __: dict[str, str]) -> JSONResponse:
    # Legacy implementation only calls modules_loader("clave") and returns no payload.
    return tools_reply_compatible(None)


async def NC(_: Request, __: dict[str, str]) -> JSONResponse:
    # Legacy implementation is empty.
    return tools_reply_compatible(None)


async def ND(_: Request, __: dict[str, str]) -> JSONResponse:
    # Legacy implementation is empty.
    return tools_reply_compatible(None)


async def gen_xml_nc(_: Request, __: dict[str, str]) -> JSONResponse:
    # Legacy route declares action genXMLNC but no function body exists in contrib code.
    return tools_reply_compatible(None)

