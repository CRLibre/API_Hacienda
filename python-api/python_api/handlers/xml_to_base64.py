from __future__ import annotations

import base64
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.config import get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import fetch_one

settings = get_settings()


def _files_get_url(download_code: str) -> Path | None:
    row = fetch_one("SELECT * FROM files WHERE downloadCode = :downloadCode", {"downloadCode": download_code})
    if row is None:
        return None

    id_user = row.get("idUser")
    file_type = row.get("type") or ""
    file_name = row.get("name") or ""
    if not file_name:
        return None

    return Path(settings.files_base_path).expanduser() / str(id_user) / str(file_type) / str(file_name)


async def encode(_: Request, params: dict[str, str]) -> JSONResponse:
    path = _files_get_url(str(params.get("downloadCode", "")))
    if path is None or not path.is_file():
        return tools_reply_compatible("")

    try:
        payload = path.read_bytes()
    except OSError:
        return tools_reply_compatible("")

    return tools_reply_compatible(base64.b64encode(payload).decode("utf-8"))

