from __future__ import annotations

from collections.abc import Awaitable, Callable
import time

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


def _safe_numeric_token(raw: str) -> str | None:
    value = raw.strip()
    if not value or not value.isdigit():
        return None
    return value


def _session_user_id(params: dict[str, str]) -> int | None:
    session_key = str(params.get("sessionKey", "")).strip()
    if not session_key:
        return None
    row = fetch_one("SELECT idUser FROM sessions WHERE sessionKey = :sessionKey", {"sessionKey": session_key})
    if row is None:
        return None
    parsed = _to_int(row.get("idUser"))
    return parsed if parsed > 0 else None


def _require_users_logged_in(params: dict[str, str]) -> bool:
    return _session_user_id(params) is not None


def _confirm_master_session(id_master_user: int, session_key: str) -> int | None:
    if id_master_user <= 0 or not session_key:
        return None

    table = f"{id_master_user}_master_sessions"
    sql = f"SELECT idUser, lastAccess FROM `{table}` WHERE sessionKey = :sessionKey"
    try:
        row = fetch_one(sql, {"sessionKey": session_key})
    except Exception:
        return None

    if row is None:
        return None

    lifetime = settings.users_session_lifetime
    if lifetime != -1:
        last_access = _to_int(row.get("lastAccess"))
        if int(time.time()) - last_access > lifetime:
            return None

    parsed = _to_int(row.get("idUser"))
    return parsed if parsed > 0 else None


def _require_companny_logged_in(params: dict[str, str]) -> int | None:
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return None
    session_key = str(params.get("sessionKey", "")).strip()
    if _confirm_master_session(id_master_user, session_key) is None:
        return None
    return id_master_user


def _config_values_sql(id_master_user: int, names: list[str], with_name: bool = False) -> tuple[str, dict[str, str]]:
    where_parts: list[str] = []
    params: dict[str, str] = {}
    for idx, name in enumerate(names):
        key = f"name{idx}"
        where_parts.append(f"`name` = :{key}")
        params[key] = name
    where_clause = " OR ".join(where_parts) if where_parts else "1=0"
    select_fields = "`name`, `value`" if with_name else "`value`"
    sql = f"SELECT {select_fields} FROM `{id_master_user}_master_config_companny` WHERE {where_clause}"
    return sql, params


def _try_insert_master_log(id_master_user: int, payload: str) -> None:
    table = f"{id_master_user}_master_logs"
    sql = f"INSERT INTO `{table}` (`idUser`, `json`) VALUES (:idUser, :json)"
    try:
        execute(sql, {"idUser": id_master_user, "json": payload})
    except Exception:
        pass


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


async def getCompannyLocationInformation(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    rows = fetch_all(
        """
        SELECT nombreProvincia, nombreCanton, nombreDistrito, nombreBarrio
        FROM codificacion_mh
        WHERE idProvincia = :idProvincia
          AND idCanton = :idCanton
          AND idDistrito = :idDistrito
          AND idBarrio = :idBarrio
        """,
        {
            "idProvincia": str(params.get("idProvincia", "")),
            "idCanton": str(params.get("idCanton", "")),
            "idDistrito": str(params.get("idDistrito", "")),
            "idBarrio": str(params.get("idBarrio", "")),
        },
    )
    return tools_reply_compatible(rows)


async def get_companny_information_admin(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    names = [
        "NOMBRE",
        "NCODPAIS",
        "TIPOCAMBIO",
        "situacion",
        "TIPOCED",
        "CEDULA",
        "NOMCOMER",
        "PROVINCIA",
        "CANTON",
        "DISTRITO",
        "BARRIO",
        "SENNAS",
        "NNUMER",
        "FCODPAIS",
        "EMAIL",
        "FNUMER",
    ]
    sql, sql_params = _config_values_sql(id_master_user, names, with_name=True)
    sql = f"{sql} ORDER BY `value` ASC"
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_companny_information(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    names = [
        "NOMBRE",
        "NCODPAIS",
        "TIPOCAMBIO",
        "situacion",
        "TIPOCED",
        "CEDULA",
        "NOMCOMER",
        "PROVINCIA",
        "CANTON",
        "DISTRITO",
        "BARRIO",
        "SENNAS",
        "NNUMER",
        "FCODPAIS",
        "EMAIL",
        "FNUMER",
    ]
    sql, sql_params = _config_values_sql(id_master_user, names, with_name=True)
    sql = f"{sql} ORDER BY `value` ASC"
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def company_get_env(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"SELECT `value` AS env FROM `{id_master_user}_master_config_companny` WHERE `name` = 'ENV'"
    try:
        rows = fetch_all(sql)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_prod_companny_credentials(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    names = ["prodUserName", "prodPassword", "prodP12Code", "prodPin"]
    sql, sql_params = _config_values_sql(id_master_user, names, with_name=False)
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_stag_companny_credentials(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    names = ["stagUserName", "stagPassword", "stagP12Code", "stagPin"]
    sql, sql_params = _config_values_sql(id_master_user, names, with_name=False)
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_prod_credentials(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    names = ["prodUserName", "prodPassword", "prodP12Code", "prodPin"]
    sql, sql_params = _config_values_sql(id_user, names, with_name=False)
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_stag_credentials(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    names = ["stagUserName", "stagPassword", "stagP12Code", "stagPin"]
    sql, sql_params = _config_values_sql(id_user, names, with_name=False)
    try:
        rows = fetch_all(sql, sql_params)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})
    return tools_reply_compatible(rows)


async def get_tipo_impuesto(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    rows = fetch_all("SELECT * FROM `tipo_impuestos`")
    return tools_reply_compatible(rows)


async def getUnid(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    rows = fetch_all("SELECT * FROM `unidad_medida` ORDER BY `id` ASC")
    return tools_reply_compatible(rows)


async def get_active_receiver(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"""
    SELECT DISTINCT
        `nombreCliente`,
        `idReceptor`,
        `correoPrincipal`,
        C.`nombreProvincia`,
        `telefono`,
        `tipoCedula`,
        `numeroCedula`
    FROM `{id_master_user}_master_receiver` AS R
    INNER JOIN codificacion_mh AS C ON R.`idProvincia` = C.`idProvincia`
    WHERE `estadoCliente` = '1'
    """
    rows = fetch_all(sql)
    return tools_reply_compatible(rows)


async def get_receiver_by_id(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_receptor = str(params.get("idReceptor", ""))
    sql = f"""
    SELECT *
    FROM `{id_master_user}_master_receiver`
    WHERE `estadoCliente` = '1' AND `idReceptor` = :idReceptor
    """
    rows = fetch_all(sql, {"idReceptor": id_receptor})
    return tools_reply_compatible(rows)


async def get_vouchers(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    env = str(params.get("env", ""))
    sql = f"""
    SELECT `idComprobante`, `consecutivo`, `clave`, `tipoDocumento`, `estado`, `fechaCreacion`
    FROM `{id_master_user}_master_vouchers`
    WHERE `env` = :env
    """
    rows = fetch_all(sql, {"env": env})
    return tools_reply_compatible(rows)


async def getProductByCode(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sucursal = _safe_numeric_token(str(params.get("sucursal", "")))
    if sucursal is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    codigo = str(params.get("codigo", ""))
    sql = f"""
    SELECT
        I.`idProducto`,
        I.`descripcion`,
        I.`unidadMedida`,
        I.`precioVenta`,
        I.`cantidadImpuesto`,
        IV.`codigo`
    FROM `{id_master_user}_master_inventary_sucursal_{sucursal}` AS I
    INNER JOIN tipo_impuestos AS IV ON I.`idImpuesto` = IV.`idImpuesto`
    WHERE `codigoBarras` = :codigo
    """
    rows = fetch_all(sql, {"codigo": codigo})
    return tools_reply_compatible(rows)


async def get_inventory(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sucursal = _safe_numeric_token(str(params.get("sucursal", "")))
    if sucursal is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    sql = f"""
    SELECT `idProducto`, `codigoBarras`, `nombre`, `unidadMedida`, `precioVenta`, `disponible`
    FROM `{id_master_user}_master_inventary_sucursal_{sucursal}`
    """
    _try_insert_master_log(id_master_user, sql)
    rows = fetch_all(sql)
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
    "getCompannyLocationInformation": getCompannyLocationInformation,
    "get_companny_information_admin": get_companny_information_admin,
    "get_companny_information": get_companny_information,
    "company_get_env": company_get_env,
    "get_prod_companny_credentials": get_prod_companny_credentials,
    "get_stag_companny_credentials": get_stag_companny_credentials,
    "get_prod_credentials": get_prod_credentials,
    "get_stag_credentials": get_stag_credentials,
    "get_tipo_impuesto": get_tipo_impuesto,
    "getUnid": getUnid,
    "get_active_receiver": get_active_receiver,
    "get_receiver_by_id": get_receiver_by_id,
    "get_vouchers": get_vouchers,
    "getProductByCode": getProductByCode,
    "get_inventory": get_inventory,
}


def get_handler(route: str) -> HandlerFn:
    return NATIVE_HANDLERS.get(route, proxy)
