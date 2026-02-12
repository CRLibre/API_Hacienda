from __future__ import annotations

import base64
import os

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api import constants as c
from python_api.responses import tools_reply_compatible


async def makeKey(_: Request, __: dict[str, str]) -> JSONResponse:
    return tools_reply_compatible(base64.b64encode(os.urandom(32)).decode("utf-8"))


async def encrypt(_: Request, __: dict[str, str]) -> JSONResponse:
    # Route compatibility: legacy module marks this endpoint with users_noAccess.
    return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)


async def desencrypt(_: Request, __: dict[str, str]) -> JSONResponse:
    # Route compatibility: legacy module marks this endpoint with users_noAccess.
    return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
