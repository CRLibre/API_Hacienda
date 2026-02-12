from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _legacy_noop_response() -> JSONResponse:
    # In upstream PHP, allFE/allNC/allND do not implement a full workflow and effectively return no payload.
    return tools_reply_compatible(None)


async def FE(_: Request, __: dict[str, str]) -> JSONResponse:
    return _legacy_noop_response()


async def NC(_: Request, __: dict[str, str]) -> JSONResponse:
    return _legacy_noop_response()


async def ND(_: Request, __: dict[str, str]) -> JSONResponse:
    return _legacy_noop_response()


async def gen_xml_nc(_: Request, __: dict[str, str]) -> JSONResponse:
    return _legacy_noop_response()
