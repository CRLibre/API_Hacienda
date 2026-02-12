from __future__ import annotations

import base64

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


async def makeQR(_: Request, params: dict[str, str]) -> JSONResponse:
    value = str(params.get("string", ""))
    payload = base64.b64encode(value.encode("utf-8")).decode("utf-8")
    return tools_reply_compatible(payload)

