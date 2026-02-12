from __future__ import annotations

from collections.abc import Awaitable, Callable

from fastapi import Request
from fastapi.responses import Response

from python_api import constants as c
from python_api.compat import ParsedLegacyRequest
from python_api.config import get_settings
from python_api.fallback import FallbackProxy
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import execute, fetch_all, fetch_one

settings = get_settings()
fallback_proxy = FallbackProxy(settings)
MODULE_FACTURADOR_VERSION = "V.0.0"

ROUTES: set[str] = {
    "addInventaryProduct",
    "addSucursales",
    "add_companny_reciver",
    "add_terminal",
    "backup_user",
    "compannyUpdateInformation",
    "compannyUpdateLocation",
    "compannyUpdateTipoCambio",
    "companny_add_master_Consecutive",
    "companny_add_voucher",
    "companny_getMyConsecutive",
    "companny_getMyInfo",
    "companny_updateConsecutive",
    "companny_users_getMyDetails",
    "companny_users_get_my_details",
    "companny_users_logMeIn",
    "companny_users_recover_pwd",
    "companny_users_register",
    "companny_users_update_profile",
    "company_change_env",
    "company_get_env",
    "company_prod_users",
    "company_stag_users",
    "copy_master_tables",
    "delete_reciver",
    "getCompannyLocationInformation",
    "getProductByCode",
    "getSucursales",
    "getTerminales",
    "getUnid",
    "getUserPermissionById",
    "getUsersCompanny",
    "get_active_receiver",
    "get_all_privinces",
    "get_cantons",
    "get_companny_information",
    "get_companny_information_admin",
    "get_district",
    "get_inventory",
    "get_neighborhood",
    "get_prod_companny_credentials",
    "get_prod_credentials",
    "get_receiver_by_id",
    "get_stag_companny_credentials",
    "get_stag_credentials",
    "get_tipo_impuesto",
    "get_type_of_id",
    "get_vouchers",
    "info",
    "inser_to_log_table",
    "users_log_me_out",
}


async def proxy(request: Request, params: dict[str, str]) -> Response:
    try:
        parsed = ParsedLegacyRequest(params=params, source="query")
        return await fallback_proxy.proxy(request, parsed)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": f"Fallback proxy failed: {exc}"})


def _to_int(value: object) -> int:
    try:
        return int(value)  # type: ignore[arg-type]
    except (TypeError, ValueError):
        return 0


def _safe_numeric_id(raw: str) -> int | None:
    value = raw.strip()
    if not value or not value.isdigit():
        return None
    parsed = _to_int(value)
    return parsed if parsed > 0 else None


def _session_user_id(params: dict[str, str]) -> int | None:
    session_key = str(params.get("sessionKey", "")).strip()
    if not session_key:
        return None
    row = fetch_one("SELECT idUser FROM sessions WHERE sessionKey = :sessionKey", {"sessionKey": session_key})
    if row is None:
        return None
    parsed = _to_int(row.get("idUser"))
    return parsed if parsed > 0 else None


async def info(_: Request, __: dict[str, str]) -> Response:
    return tools_reply_compatible(
        {
            "info": "Modulo de interface de facturacion",
            "version": MODULE_FACTURADOR_VERSION,
        }
    )


async def get_all_privinces(_: Request, __: dict[str, str]) -> Response:
    rows = fetch_all(
        """
        SELECT DISTINCT idProvincia AS idProvincia, nombreProvincia AS nombreProvincia
        FROM codificacion_mh
        """
    )
    return tools_reply_compatible(rows)


async def get_type_of_id(_: Request, __: dict[str, str]) -> Response:
    rows = fetch_all("SELECT codigo, descripcion FROM tipo_cedula")
    return tools_reply_compatible(rows)


async def get_cantons(_: Request, params: dict[str, str]) -> Response:
    id_province = str(params.get("idProvince", ""))
    rows = fetch_all(
        """
        SELECT DISTINCT nombreCanton AS nombreCanton, idCanton AS idCanton
        FROM codificacion_mh
        WHERE idProvincia = :idProvince
        """,
        {"idProvince": id_province},
    )
    return tools_reply_compatible(rows)


async def get_district(_: Request, params: dict[str, str]) -> Response:
    id_province = str(params.get("idProvince", ""))
    id_canton = str(params.get("idCanton", ""))
    rows = fetch_all(
        """
        SELECT DISTINCT nombreDistrito AS nombreDistrito, idDistrito AS idDistrito
        FROM codificacion_mh
        WHERE idProvincia = :idProvince AND idCanton = :idCanton
        """,
        {"idProvince": id_province, "idCanton": id_canton},
    )
    return tools_reply_compatible(rows)


async def get_neighborhood(_: Request, params: dict[str, str]) -> Response:
    id_province = str(params.get("idProvince", ""))
    id_canton = str(params.get("idCanton", ""))
    id_district = str(params.get("idDistrito", ""))
    rows = fetch_all(
        """
        SELECT DISTINCT nombreBarrio AS nombreBarrio, idBarrio AS idBarrio
        FROM codificacion_mh
        WHERE idProvincia = :idProvince AND idCanton = :idCanton AND idDistrito = :idDistrito
        """,
        {"idProvince": id_province, "idCanton": id_canton, "idDistrito": id_district},
    )
    return tools_reply_compatible(rows)


async def inser_to_log_table(_: Request, params: dict[str, str]) -> Response:
    id_user = _session_user_id(params)
    if id_user is None:
        id_user = _safe_numeric_id(str(params.get("idUser", "")))
    if id_user is None:
        id_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    payload = str(params.get("json", ""))
    table = f"{id_user}_master_logs"
    sql = f"INSERT INTO `{table}` (`idUser`, `json`) VALUES (:idUser, :json)"
    try:
        rows = execute(sql, {"idUser": id_user, "json": payload})
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


HandlerFn = Callable[[Request, dict[str, str]], Awaitable[Response] | Response]
NATIVE_HANDLERS: dict[str, HandlerFn] = {
    "info": info,
    "get_all_privinces": get_all_privinces,
    "get_type_of_id": get_type_of_id,
    "get_cantons": get_cantons,
    "get_district": get_district,
    "get_neighborhood": get_neighborhood,
    "inser_to_log_table": inser_to_log_table,
}


def get_handler(route: str) -> HandlerFn:
    return NATIVE_HANDLERS.get(route, proxy)
