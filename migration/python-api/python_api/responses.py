from __future__ import annotations

from collections.abc import Mapping
from typing import Any

from fastapi.responses import JSONResponse

from python_api import constants as c


def _known_response_for_code(code: int) -> tuple[int, Any, bool] | None:
    if code == c.ERROR_USERS_NO_VALID:
        return 400, "Usuario no válido", True
    if code == c.ERROR_USERS_WRONG_LOGIN_INFO:
        return 401, "Información de acceso incorrecta", True
    if code == c.ERROR_USERS_NO_VALID_SESSION:
        return 440, "Sesión no válida o expirada", True
    if code == c.ERROR_USERS_ACCESS_DENIED:
        return 403, "Acceso denegado", True
    if code == c.ERROR_USERS_EXISTS:
        return 409, "El usuario ya existe", True
    return None


def tools_reply_compatible(payload: Any, kill_me: bool = False) -> JSONResponse:
    status_code = 200
    response_payload = payload

    if isinstance(payload, int):
        known = _known_response_for_code(payload)
        if known is not None:
            status_code, response_payload, kill_me = known

    if isinstance(response_payload, Mapping) and "Status" in response_payload:
        status = str(response_payload.get("Status", "")).lower()
        if status == "error":
            status_code = 500
            kill_me = True
        elif status == "ok":
            status_code = 200
        else:
            status_code = 400
            kill_me = True
        response_payload = response_payload.get("text", response_payload)

    if kill_me and isinstance(response_payload, str):
        response_payload = f"ERROR: {response_payload}"
    elif not kill_me:
        status_code = 200

    body = {
        "status": "error" if kill_me else "ok",
        "resp": response_payload,
    }
    return JSONResponse(status_code=status_code, content=body)
