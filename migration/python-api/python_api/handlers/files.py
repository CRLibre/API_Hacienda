from __future__ import annotations

import hashlib
import time
from pathlib import Path
from typing import Any

from fastapi import Request
from fastapi.responses import JSONResponse, Response
from starlette.datastructures import UploadFile

from python_api import constants as c
from python_api.config import get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import execute, fetch_one

settings = get_settings()

MIME_TYPES: dict[str, str] = {
    "pdf": "application/pdf",
    "exe": "application/octet-stream",
    "zip": "application/zip",
    "docx": "application/msword",
    "doc": "application/msword",
    "xls": "application/vnd.ms-excel",
    "ppt": "application/vnd.ms-powerpoint",
    "gif": "image/gif",
    "png": "image/png",
    "jpeg": "image/jpg",
    "jpg": "image/jpg",
    "mp3": "audio/mpeg",
    "wav": "audio/x-wav",
    "mpeg": "video/mpeg",
    "mpg": "video/mpeg",
    "mpe": "video/mpeg",
    "mov": "video/quicktime",
    "avi": "video/x-msvideo",
    "3gp": "video/3gpp",
    "css": "text/css",
    "jsc": "application/javascript",
    "js": "application/javascript",
    "php": "text/html",
    "htm": "text/html",
    "html": "text/html",
    "svg": "image/svg+xml",
}


def _as_int(value: Any, default: int) -> int:
    try:
        return int(value)
    except (TypeError, ValueError):
        return default


def _files_base_path() -> Path:
    return Path(settings.files_base_path).expanduser()


def _files_create_path(id_user: int, file_type: str) -> Path:
    return _files_base_path() / str(id_user) / file_type


def _files_create_download_code(name: str, id_user: int) -> str:
    raw = f"{name}//{int(time.time())}{id_user}"
    return hashlib.md5(raw.encode("utf-8")).hexdigest()


def _mime_type(file_name: str) -> str:
    ext = file_name.rsplit(".", 1)[-1].lower() if "." in file_name else ""
    return MIME_TYPES.get(ext, "application/octet-stream")


def _load_user_id_from_iam(iam: str) -> int:
    if not iam:
        return 0
    row = fetch_one("SELECT idUser FROM users WHERE userName = :userName", {"userName": iam})
    if row is None:
        return 0
    return _as_int(row.get("idUser"), 0)


def _load_file_by_code(download_code: str) -> dict[str, Any] | None:
    row = fetch_one("SELECT * FROM files WHERE downloadCode = :downloadCode", {"downloadCode": download_code})
    if row is None:
        return None
    return dict(row)


def _file_path_from_row(row: dict[str, Any], size: str = "") -> Path:
    path = _files_create_path(_as_int(row.get("idUser"), 0), str(row.get("type", ""))) / str(row.get("name", ""))
    if size and size != "0":
        stem = path.stem
        path = path.with_name(f"{stem}_{size}{path.suffix}")
    return path


async def filesGetUrl(_: Request, params: dict[str, str]) -> JSONResponse:
    download_code = str(params.get("downloadCode", ""))
    row = _load_file_by_code(download_code)
    if row is None:
        return tools_reply_compatible(False)
    file_path = _file_path_from_row(row)
    return tools_reply_compatible(str(file_path))


async def files_view_file(_: Request, params: dict[str, str]) -> Response:
    code = str(params.get("code", ""))
    size = str(params.get("size", "0"))
    row = _load_file_by_code(code)
    if row is None:
        return tools_reply_compatible(c.ERROR_FILES_NOT_FOUND)

    path = _file_path_from_row(row, size=size)
    if not path.is_file():
        return tools_reply_compatible(c.ERROR_FILES_NOT_FOUND)

    return Response(content=path.read_bytes(), media_type=_mime_type(path.name), status_code=200)


async def upload(request: Request, params: dict[str, str]) -> JSONResponse:
    file_type = str(params.get("type", "attach"))
    final_name_param = str(params.get("finalName", ""))
    allowed_ext = str(
        params.get(
            "ext",
            "jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF,p12,P12,pfx,PFX,xml,XML,Xml",
        )
    )
    max_size_mb = _as_int(params.get("maxSize", 2), 2)
    delete_existing = str(params.get("del", "1")).lower() not in {"0", "false", "no"}

    id_user = _load_user_id_from_iam(str(params.get("iam", "")))

    form = await request.form()
    upload_file = form.get("fileToUpload")
    if not isinstance(upload_file, UploadFile):
        return tools_reply_compatible(c.ERROR_FILES_UPLOAD_ERROR)

    original_name = upload_file.filename or "upload.bin"
    extension = Path(original_name).suffix
    extension_only = extension[1:] if extension.startswith(".") else extension

    final_name = (
        Path(original_name).name
        if not final_name_param
        else f"{final_name_param}.{extension_only}" if extension_only else final_name_param
    )

    target_dir = _files_create_path(id_user, file_type)
    target_dir.mkdir(parents=True, exist_ok=True)
    target_file = target_dir / final_name

    content = await upload_file.read()
    if len(content) > max_size_mb * 1_000_000:
        return tools_reply_compatible(c.ERROR_FILES_TOO_BIG)

    if allowed_ext != "*":
        allowed = [item.strip() for item in allowed_ext.split(",") if item.strip()]
        if extension_only not in allowed:
            return tools_reply_compatible(c.ERROR_FILES_EXT_NOT_ALLOWED)

    try:
        if target_file.exists():
            if delete_existing:
                target_file.unlink()
            else:
                return tools_reply_compatible(c.ERROR_FILES_UPLOAD_ERROR)
        target_file.write_bytes(content)
    except OSError:
        return tools_reply_compatible(c.ERROR_FILES_UPLOAD_ERROR)

    download_code = _files_create_download_code(final_name, id_user)
    source_for_md5 = str(getattr(upload_file.file, "name", original_name))
    file_md5 = hashlib.md5(source_for_md5.encode("utf-8")).hexdigest()
    now = int(time.time())

    execute(
        """
        INSERT INTO files (md5, name, timestamp, size, idUser, downloadCode, fileType, type)
        VALUES (:md5, :name, :timestamp, :size, :idUser, :downloadCode, :fileType, :type)
        """,
        {
            "md5": file_md5,
            "name": final_name,
            "timestamp": now,
            "size": len(content),
            "idUser": id_user,
            "downloadCode": download_code,
            "fileType": "",
            "type": file_type,
        },
    )

    row = fetch_one("SELECT idFile FROM files WHERE downloadCode = :downloadCode", {"downloadCode": download_code})
    id_file = _as_int(row.get("idFile"), 0) if row is not None else 0
    if id_file == 0:
        return tools_reply_compatible(c.ERROR_FILES_UPLOAD_ERROR)

    return tools_reply_compatible({"idFile": id_file, "name": final_name, "downloadCode": download_code})
