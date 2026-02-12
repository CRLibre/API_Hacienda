from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def sendmail(_: Request, params: dict[str, str]) -> JSONResponse:
    _ = params.get("xmlEnvia")
    _ = params.get("facturaPDF")
    _ = params.get("xmlHacienda")
    _ = params.get("clave")
    return tools_reply_compatible("test")

