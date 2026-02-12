from __future__ import annotations

import base64
import hashlib
import random
import shutil
import smtplib
import ssl
import time
from collections.abc import Mapping
from email.message import EmailMessage
from pathlib import Path
from typing import Any

import bcrypt
from fastapi import Request
from fastapi.responses import JSONResponse, Response
from starlette.datastructures import UploadFile

from python_api import constants as c
from python_api.config import get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.crypto_compat import php_compat_decrypt, php_compat_encrypt
from python_api.services.db_compat import execute, fetch_one

settings = get_settings()
TRANSPARENT_GIF = base64.b64decode("R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7")

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


def _client_ip(request: Request) -> str:
    if request.client and request.client.host:
        return request.client.host
    return ""


def _as_int(value: Any, default: int) -> int:
    try:
        return int(value)
    except (TypeError, ValueError):
        return default


def _serialize_row(row: Mapping[str, Any]) -> dict[str, Any]:
    serialized: dict[str, Any] = {}
    for key, value in row.items():
        if isinstance(value, (bytes, bytearray)):
            serialized[key] = value.decode("utf-8", errors="replace")
        else:
            serialized[key] = value
    return serialized


def _load_user(field: str, value: str | int) -> dict[str, Any] | None:
    if field not in {"idUser", "userName", "email"}:
        return None
    row = fetch_one(f"SELECT * FROM users WHERE {field} = :value", {"value": value})
    if row is None:
        return None
    return _serialize_row(row)


def _users_hash(raw_password: str) -> str:
    bcrypt_hash = bcrypt.hashpw(raw_password.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    encrypted = php_compat_encrypt(bcrypt_hash, settings.crypto_key)
    return base64.b64encode(encrypted.encode("utf-8")).decode("utf-8")


def _users_deshash(stored_password: str) -> str:
    decoded = base64.b64decode(stored_password).decode("utf-8")
    return php_compat_decrypt(decoded, settings.crypto_key)


def _verify_bcrypt(raw_password: str, stored_password: str) -> bool:
    try:
        decrypted = _users_deshash(stored_password)
    except Exception:
        return False

    if not isinstance(decrypted, str) or not decrypted.startswith("$2"):
        return False

    if decrypted.startswith("$2y$"):
        decrypted = "$2b$" + decrypted[4:]

    try:
        return bcrypt.checkpw(raw_password.encode("utf-8"), decrypted.encode("utf-8"))
    except ValueError:
        return False


def _md5_hash(raw_password: str) -> str:
    return hashlib.md5(raw_password.encode("utf-8")).hexdigest()


def _generate_session_key(id_user: int, ip: str) -> str:
    execute("DELETE FROM sessions WHERE idUser = :idUser", {"idUser": id_user})

    random_seed = f"{int(time.time()) * random.randint(0, 1000)}"
    seed_hash = bcrypt.hashpw(random_seed.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    session_key = php_compat_encrypt(seed_hash, settings.crypto_key)

    execute(
        """
        INSERT INTO sessions (idUser, sessionKey, ip, lastAccess)
        VALUES (:idUser, :sessionKey, :ip, :lastAccess)
        """,
        {"idUser": id_user, "sessionKey": session_key, "ip": ip, "lastAccess": int(time.time())},
    )
    return session_key


def _confirm_session_key(user: Mapping[str, Any], session_key: str, ip: str) -> bool:
    if not session_key:
        return False

    row = fetch_one(
        """
        SELECT *
        FROM sessions
        WHERE sessionKey = :sessionKey
          AND ip = :ip
          AND idUser = :idUser
        """,
        {"sessionKey": session_key, "ip": ip, "idUser": _as_int(user.get("idUser"), 0)},
    )
    if row is None:
        return False

    lifetime = settings.users_session_lifetime
    if lifetime != -1:
        last_access = _as_int(row.get("lastAccess"), 0)
        if int(time.time()) - last_access > lifetime:
            return False

    return True


def _touch_access(user: Mapping[str, Any], session_key: str) -> None:
    now = int(time.time())
    execute(
        "UPDATE sessions SET lastAccess = :lastAccess WHERE sessionKey = :sessionKey",
        {"lastAccess": now, "sessionKey": session_key},
    )
    execute(
        "UPDATE users SET lastAccess = :lastAccess WHERE idUser = :idUser",
        {"lastAccess": now, "idUser": _as_int(user.get("idUser"), 0)},
    )


def _require_logged_in(request: Request, params: dict[str, str]) -> dict[str, Any] | None:
    iam = str(params.get("iam", "")).strip()
    if not iam:
        return None

    user = _load_user("userName", iam)
    if user is None or _as_int(user.get("idUser"), 0) == 0:
        return None

    session_key = str(params.get("sessionKey", ""))
    if not _confirm_session_key(user, session_key, _client_ip(request)):
        return None

    _touch_access(user, session_key)
    return user


def _users_exists_response() -> JSONResponse:
    return tools_reply_compatible({"code": c.ERROR_USERS_EXISTS, "status": "usuario ya existe"})


def _files_base_path() -> Path:
    return Path(str(settings.files_base_path)).expanduser()


def _files_create_path(id_user: int, file_type: str) -> Path:
    return _files_base_path() / str(id_user) / file_type


def _files_create_download_code(name: str, id_user: int) -> str:
    raw = f"{name}//{int(time.time())}{id_user}"
    return hashlib.md5(raw.encode("utf-8")).hexdigest()


def _files_mime_type(file_name: str) -> str:
    ext = file_name.rsplit(".", 1)[-1].lower() if "." in file_name else ""
    return MIME_TYPES.get(ext, "application/octet-stream")


def _files_store_record(
    *,
    file_md5: str,
    name: str,
    timestamp: int,
    size: int,
    id_user: int,
    download_code: str,
    file_type: str,
    file_class: str,
) -> int | None:
    execute(
        """
        INSERT INTO files (md5, name, timestamp, size, idUser, downloadCode, fileType, type)
        VALUES (:md5, :name, :timestamp, :size, :idUser, :downloadCode, :fileType, :type)
        """,
        {
            "md5": file_md5,
            "name": name,
            "timestamp": timestamp,
            "size": size,
            "idUser": id_user,
            "downloadCode": download_code,
            "fileType": file_type,
            "type": file_class,
        },
    )

    row = fetch_one("SELECT idFile FROM files WHERE downloadCode = :downloadCode", {"downloadCode": download_code})
    if row is None:
        return None
    return _as_int(row.get("idFile"), 0) or None


async def _files_upload(
    request: Request,
    *,
    id_user: int,
    file_class: str,
    final_name: str | None = None,
    ext: str | None = None,
    max_size_mb: int = 0,
    delete_existing: bool = True,
) -> dict[str, Any] | int:
    form = await request.form()
    upload = form.get("fileToUpload")
    if not isinstance(upload, UploadFile):
        return c.ERROR_FILES_UPLOAD_ERROR

    original_name = upload.filename or "upload.bin"
    target_dir = _files_create_path(id_user, file_class)
    target_dir.mkdir(parents=True, exist_ok=True)

    extension = Path(original_name).suffix
    if extension.startswith("."):
        extension_only = extension[1:]
    else:
        extension_only = extension

    if final_name:
        target_name = f"{final_name}{extension}" if extension else final_name
    else:
        target_name = Path(original_name).name
    target_file = target_dir / target_name

    if ext and ext != "*":
        allowed = {item.strip().lower() for item in ext.split(",") if item.strip()}
        if extension_only.lower() not in allowed:
            return c.ERROR_FILES_EXT_NOT_ALLOWED

    content = await upload.read()
    if max_size_mb and len(content) > max_size_mb * 1_000_000:
        return c.ERROR_FILES_TOO_BIG

    try:
        if delete_existing and target_file.exists():
            target_file.unlink()
        target_file.write_bytes(content)
    except OSError:
        return c.ERROR_FILES_UPLOAD_ERROR

    download_code = _files_create_download_code(target_name, id_user)
    source_for_md5 = str(getattr(upload.file, "name", original_name))
    file_md5 = hashlib.md5(source_for_md5.encode("utf-8")).hexdigest()
    file_id = _files_store_record(
        file_md5=file_md5,
        name=target_name,
        timestamp=int(time.time()),
        size=len(content),
        id_user=id_user,
        download_code=download_code,
        file_type="",
        file_class=file_class,
    )
    if file_id is None:
        return c.ERROR_FILES_UPLOAD_ERROR

    return {
        "idFile": file_id,
        "name": target_name,
        "downloadCode": download_code,
        "fullPath": str(target_file),
    }


def _file_response_from_path(path: Path) -> Response:
    if not path.exists():
        return Response(content=TRANSPARENT_GIF, media_type="image/gif", status_code=200)
    return Response(content=path.read_bytes(), media_type=_files_mime_type(path.name), status_code=200)


def _send_recover_email(to: str, subject: str, reply_to: str, message: str) -> bool:
    if not to:
        return False

    msg = EmailMessage()
    msg["Subject"] = subject
    msg["From"] = settings.mail_address or reply_to
    msg["To"] = to
    if reply_to:
        msg["Reply-To"] = reply_to
    msg.set_content(message)

    try:
        if settings.mail_type.lower() == "smtp" and settings.mail_host:
            secure_mode = settings.mail_secure.lower()
            if secure_mode == "ssl":
                context = ssl.create_default_context()
                with smtplib.SMTP_SSL(settings.mail_host, settings.mail_port, context=context, timeout=20) as server:
                    if settings.mail_username:
                        server.login(settings.mail_username, settings.mail_password)
                    server.send_message(msg)
            else:
                with smtplib.SMTP(settings.mail_host, settings.mail_port, timeout=20) as server:
                    if secure_mode == "tls":
                        server.starttls(context=ssl.create_default_context())
                    if settings.mail_username:
                        server.login(settings.mail_username, settings.mail_password)
                    server.send_message(msg)
            return True

        with smtplib.SMTP("localhost", timeout=10) as server:
            server.send_message(msg)
        return True
    except Exception:
        return False


async def users_register(request: Request, params: dict[str, str]) -> JSONResponse:
    user_name = str(params.get("userName", ""))
    email = str(params.get("email", ""))

    if _load_user("userName", user_name) is not None:
        return _users_exists_response()
    if _load_user("email", email) is not None:
        return _users_exists_response()

    now = int(time.time())
    execute(
        """
        INSERT INTO users (
            fullName, userName, email, about, country, status, timestamp, lastAccess, pwd, avatar, settings
        ) VALUES (
            :fullName, :userName, :email, :about, :country, :status, :timestamp, :lastAccess, :pwd, :avatar, :settings
        )
        """,
        {
            "fullName": str(params.get("fullName", "")),
            "userName": user_name,
            "email": email,
            "about": str(params.get("about", "May all beings be at ease")),
            "country": str(params.get("country", "crc")),
            "status": "1",
            "timestamp": now,
            "lastAccess": now,
            "pwd": _users_hash(str(params.get("pwd", ""))),
            "avatar": "0",
            "settings": "NULL",
        },
    )

    user = _load_user("userName", user_name)
    if user is None:
        return tools_reply_compatible({"code": c.ERROR_ERROR, "status": "error registrando"})

    session_key = _generate_session_key(_as_int(user.get("idUser"), 0), _client_ip(request))
    return tools_reply_compatible(
        {
            "sessionKey": session_key,
            "userName": str(user.get("userName", "")),
            "idUser": _as_int(user.get("idUser"), 0),
        }
    )


async def users_get_list(_: Request, __: dict[str, str]) -> JSONResponse:
    # Compatibility: PHP route is intentionally configured with users_noAccess.
    return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)


async def users_avatar_get(_: Request, params: dict[str, str]) -> Response:
    user = _load_user("userName", str(params.get("userName", "")))
    if user is None:
        return Response(content=TRANSPARENT_GIF, media_type="image/gif", status_code=200)

    avatar_id = str(user.get("avatar", "")).strip()
    if not avatar_id:
        return Response(content=TRANSPARENT_GIF, media_type="image/gif", status_code=200)

    file_row = fetch_one("SELECT * FROM files WHERE idFile = :idFile", {"idFile": avatar_id})
    if file_row is None:
        return Response(content=TRANSPARENT_GIF, media_type="image/gif", status_code=200)

    avatar_name = str(file_row.get("name", ""))
    requested_size = str(params.get("size", "25"))
    avatar_name = avatar_name.replace("avatar_def", f"avatar_def_{requested_size}")
    avatar_path = _files_create_path(_as_int(user.get("idUser"), 0), "avatar") / avatar_name
    return _file_response_from_path(avatar_path)


async def users_avatar_upload(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _as_int(user.get("idUser"), 0)
    result = await _files_upload(
        request,
        id_user=id_user,
        file_class="avatar",
        final_name="avatar_def",
        ext="jpg,png,gif",
        max_size_mb=0,
        delete_existing=True,
    )
    if isinstance(result, int) and result < 0:
        return tools_reply_compatible(result)

    full_path = Path(str(result["fullPath"]))
    if full_path.exists():
        base_name = full_path.stem
        extension = full_path.suffix
        for size in ("250", "100", "50", "25"):
            version = full_path.with_name(f"{base_name}_{size}{extension}")
            if not version.exists():
                shutil.copyfile(full_path, version)

    execute(
        "UPDATE users SET avatar = :avatar WHERE idUser = :idUser",
        {"avatar": str(result["idFile"]), "idUser": id_user},
    )
    return tools_reply_compatible(c.SUCCESS_ALL_GOOD)


async def users_log_me_in(request: Request, params: dict[str, str]) -> JSONResponse:
    user_name = str(params.get("userName", ""))
    raw_password = str(params.get("pwd", ""))

    if "@" in user_name and user_name.find("@") > 0:
        user = _load_user("email", user_name)
    else:
        user = _load_user("userName", user_name)

    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_WRONG_LOGIN_INFO)

    stored_password = str(user.get("pwd", ""))
    valid_bcrypt = _verify_bcrypt(raw_password, stored_password)
    valid_md5 = stored_password == _md5_hash(raw_password)

    if not valid_bcrypt and not valid_md5:
        return tools_reply_compatible(c.ERROR_USERS_WRONG_LOGIN_INFO)

    if valid_md5:
        execute(
            "UPDATE users SET pwd = :pwd WHERE idUser = :idUser",
            {"pwd": _users_hash(raw_password), "idUser": _as_int(user.get("idUser"), 0)},
        )

    session_key = _generate_session_key(_as_int(user.get("idUser"), 0), _client_ip(request))
    return tools_reply_compatible(
        {
            "sessionKey": session_key,
            "userName": str(user.get("userName", "")),
            "idUser": _as_int(user.get("idUser"), 0),
        }
    )


async def users_log_me_out(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    execute(
        "DELETE FROM sessions WHERE sessionKey = :sessionKey AND ip = :ip",
        {"sessionKey": str(params.get("sessionKey", "")), "ip": _client_ip(request)},
    )
    return tools_reply_compatible("good bye")


async def users_get_my_details(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    current = _load_user("userName", str(params.get("iam", "")))
    if current is None:
        current = {"idUser": 0, "pwd": ""}
    return tools_reply_compatible(current)


async def users_update_profile(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _as_int(user.get("idUser"), 0)
    requested_user_name = str(params.get("userName", user.get("userName", "")))
    requested_email = str(params.get("email", user.get("email", "")))

    current_user_name = str(user.get("userName", ""))
    current_email = str(user.get("email", ""))

    if requested_user_name != current_user_name:
        existing = _load_user("userName", requested_user_name)
        if existing is not None and _as_int(existing.get("idUser"), 0) != id_user:
            return _users_exists_response()

    if requested_email != current_email:
        existing = _load_user("email", requested_email)
        if existing is not None and _as_int(existing.get("idUser"), 0) != id_user:
            return _users_exists_response()

    new_password = str(params.get("pwd", "")).strip()
    timestamp_default = int(time.time())
    update_params: dict[str, Any] = {
        "idUser": id_user,
        "fullName": str(params.get("fullName", user.get("fullName", ""))),
        "userName": requested_user_name,
        "email": requested_email,
        "about": str(params.get("about", user.get("about", ""))),
        "country": str(params.get("country", user.get("country", ""))),
        "status": str(params.get("status", user.get("status", "1"))),
        "timestamp": _as_int(params.get("timestamp", user.get("timestamp")), timestamp_default),
        "lastAccess": _as_int(params.get("lastAccess", user.get("lastAccess")), timestamp_default),
        "avatar": str(params.get("avatar", user.get("avatar", "0"))),
    }

    if new_password:
        update_params["pwd"] = _users_hash(new_password)
        execute(
            """
            UPDATE users
            SET fullName = :fullName,
                userName = :userName,
                email = :email,
                about = :about,
                country = :country,
                status = :status,
                timestamp = :timestamp,
                lastAccess = :lastAccess,
                pwd = :pwd,
                avatar = :avatar
            WHERE idUser = :idUser
            """,
            update_params,
        )
    else:
        execute(
            """
            UPDATE users
            SET fullName = :fullName,
                userName = :userName,
                email = :email,
                about = :about,
                country = :country,
                status = :status,
                timestamp = :timestamp,
                lastAccess = :lastAccess,
                avatar = :avatar
            WHERE idUser = :idUser
            """,
            update_params,
        )

    return tools_reply_compatible({"code": c.SUCCESS_ALL_GOOD, "status": "registrado con exito"})


async def users_confirm_session_vilidity(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    return tools_reply_compatible(c.SUCCESS_ALL_GOOD)


async def users_personal_bg_upload(request: Request, params: dict[str, str]) -> JSONResponse:
    user = _require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _as_int(user.get("idUser"), 0)
    target_dir = _files_create_path(id_user, "background")
    if target_dir.exists() and target_dir.is_dir():
        for file in target_dir.iterdir():
            if file.is_file():
                file.unlink()

    result = await _files_upload(
        request,
        id_user=id_user,
        file_class="background",
        final_name="personalBackground",
        ext=None,
        max_size_mb=0,
        delete_existing=True,
    )
    if isinstance(result, int) and result < 0:
        return tools_reply_compatible(result)
    return tools_reply_compatible(c.SUCCESS_ALL_GOOD)


async def users_personal_bg_get(_: Request, params: dict[str, str]) -> Response | JSONResponse:
    fallback_path = str(params.get("fallBack", ""))
    user = _load_user("userName", str(params.get("userName", "")))
    if user is None or _as_int(user.get("idUser"), 0) == 0:
        return tools_reply_compatible(fallback_path)

    base_path = _files_create_path(_as_int(user.get("idUser"), 0), "background") / "personalBackground"
    candidates = [base_path.with_suffix(".jpg"), base_path.with_suffix(".png"), base_path.with_suffix(".gif")]
    for candidate in candidates:
        if candidate.exists():
            return _file_response_from_path(candidate)

    if fallback_path:
        fallback = Path(fallback_path)
        if fallback.exists() and fallback.is_file():
            return _file_response_from_path(fallback)

    return Response(content=b"", media_type="application/octet-stream", status_code=200)


async def users_recover_pwd(_: Request, params: dict[str, str]) -> JSONResponse:
    user_name = str(params.get("userName", ""))

    if "@" in user_name and user_name.find("@") > 0:
        user = _load_user("email", user_name)
    else:
        user = _load_user("userName", user_name)

    if user is None or _as_int(user.get("idUser"), 0) == 0:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    temp_password = str(random.randint(0, 1000) + int(time.time()))
    new_hash = _users_hash(temp_password)
    changed = execute(
        "UPDATE users SET pwd = :pwd WHERE idUser = :idUser",
        {"pwd": new_hash, "idUser": _as_int(user.get("idUser"), 0)},
    )
    if changed <= 0:
        return tools_reply_compatible(c.ERROR_ERROR)

    sent = _send_recover_email(
        to=str(user.get("email", "")),
        subject=f"Recuperación de Clave {settings.core_site_name}",
        reply_to=settings.mail_noreply,
        message=f"Su nueva clave es: {temp_password}",
    )
    return tools_reply_compatible(c.SUCCESS_ALL_GOOD if sent else c.ERROR_ERROR)


async def login_auto(_: Request, __: dict[str, str]) -> JSONResponse:
    # Compatibility: module route exists but the referenced function is missing in PHP.
    return tools_reply_compatible(c.ERROR_BAD_REQUEST)
