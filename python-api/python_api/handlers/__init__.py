from __future__ import annotations

from collections.abc import Awaitable, Callable

from fastapi import Request
from fastapi.responses import Response

from . import clave
from . import consultar
from . import token
from . import users

HandlerFn = Callable[[Request, dict[str, str]], Awaitable[Response] | Response]

HANDLERS: dict[tuple[str, str], HandlerFn] = {
    ("clave", "clave"): clave.clave,
    ("consultar", "consultarCom"): consultar.consultarCom,
    ("token", "gettoken"): token.gettoken,
    ("token", "refresh"): token.refresh,
    ("users", "login_auto"): users.login_auto,
    ("users", "users_avatar_get"): users.users_avatar_get,
    ("users", "users_avatar_upload"): users.users_avatar_upload,
    ("users", "users_confirm_session_vilidity"): users.users_confirm_session_vilidity,
    ("users", "users_get_list"): users.users_get_list,
    ("users", "users_get_my_details"): users.users_get_my_details,
    ("users", "users_log_me_in"): users.users_log_me_in,
    ("users", "users_log_me_out"): users.users_log_me_out,
    ("users", "users_personal_bg_get"): users.users_personal_bg_get,
    ("users", "users_personal_bg_upload"): users.users_personal_bg_upload,
    ("users", "users_recover_pwd"): users.users_recover_pwd,
    ("users", "users_register"): users.users_register,
    ("users", "users_update_profile"): users.users_update_profile,
}


def get_handler(w: str, r: str) -> HandlerFn | None:
    return HANDLERS.get((w, r))


def is_implemented(w: str, r: str) -> bool:
    return (w, r) in HANDLERS
