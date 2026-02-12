from __future__ import annotations

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api import constants as c
from python_api.responses import tools_reply_compatible
from . import files
from . import users


async def test(_: Request, __: dict[str, str]) -> JSONResponse:
    return tools_reply_compatible("Test :)")


async def _upload_legacy(request: Request, params: dict[str, str], *, ext: str) -> JSONResponse:
    user = users._require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    upload_params = dict(params)
    upload_params["iam"] = str(user.get("userName", ""))
    upload_params["type"] = "hacienda"
    upload_params["ext"] = ext
    return await files.upload(request, upload_params)


async def subir_certif(request: Request, params: dict[str, str]) -> JSONResponse:
    return await _upload_legacy(request, params, ext="p12")


async def subir_xml(request: Request, params: dict[str, str]) -> JSONResponse:
    return await _upload_legacy(request, params, ext="xml")
