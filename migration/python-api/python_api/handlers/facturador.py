from __future__ import annotations

from collections.abc import Awaitable, Callable
from email.message import EmailMessage
import hashlib
import random
import smtplib
import ssl
import time

import bcrypt
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


def _require_companny_logged_in_user(params: dict[str, str]) -> tuple[int, int] | None:
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return None
    session_key = str(params.get("sessionKey", "")).strip()
    companny_user_id = _confirm_master_session(id_master_user, session_key)
    if companny_user_id is None:
        return None
    return id_master_user, companny_user_id


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


def _load_companny_user(id_master_user: int, *, field: str, value: str) -> dict[str, object] | None:
    if field not in {"idUser", "userName", "email"}:
        return None
    sql = f"SELECT * FROM `{id_master_user}_master_users` WHERE `{field}` = :value"
    row = fetch_one(sql, {"value": value})
    return row


def _generate_companny_session_key(id_master_user: int, id_user: int, ip: str) -> str:
    table = f"{id_master_user}_master_sessions"
    execute(f"DELETE FROM `{table}` WHERE `idUser` = :idUser", {"idUser": id_user})

    random_seed = f"{int(time.time()) * random.randint(0, 1000)}"
    session_key = bcrypt.hashpw(random_seed.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    execute(
        f"INSERT INTO `{table}` (`idUser`, `sessionKey`, `ip`, `lastAccess`) VALUES (:idUser, :sessionKey, :ip, :lastAccess)",
        {"idUser": id_user, "sessionKey": session_key, "ip": ip, "lastAccess": int(time.time())},
    )
    return session_key


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


async def getSucursales(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"SELECT `idSucursal`, `nombreSucursal`, `sucursal` FROM `{id_user}_master_sucursales`"
    rows = fetch_all(sql)
    return tools_reply_compatible(rows)


async def getTerminales(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_sucursal = str(params.get("idSucursal", "")).strip()
    if id_sucursal:
        sql = f"""
        SELECT `idTerminal`, `nombreTerminal`, `terminal`, S.`nombreSucursal`, S.`sucursal`
        FROM `{id_user}_master_terminales` AS T
        INNER JOIN `{id_user}_master_sucursales` AS S ON T.`idSucursal` = S.`idSucursal`
        WHERE S.`idSucursal` = :idSucursal
        """
        rows = fetch_all(sql, {"idSucursal": id_sucursal})
    else:
        sql = f"""
        SELECT `idTerminal`, `nombreTerminal`, `terminal`, S.`nombreSucursal`, S.`sucursal`
        FROM `{id_user}_master_terminales` AS T
        INNER JOIN `{id_user}_master_sucursales` AS S ON T.`idSucursal` = S.`idSucursal`
        """
        rows = fetch_all(sql)
    return tools_reply_compatible(rows)


async def getUsersCompanny(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"""
    SELECT
        `idUser`,
        `fullName`,
        `email`,
        `country`,
        T.`nombreTerminal`,
        T.`terminal`,
        S.`nombreSucursal`,
        S.`sucursal`
    FROM `{id_user}_master_users` AS U
    INNER JOIN `{id_user}_master_terminales` AS T ON U.`settings` = T.`idTerminal`
    INNER JOIN `{id_user}_master_sucursales` AS S ON T.`idSucursal` = S.`idSucursal`
    """
    rows = fetch_all(sql)
    return tools_reply_compatible(rows)


async def getUserPermissionById(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _session_user_id(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"""
    SELECT
        R.`rolCode`,
        R.`descripcion`,
        U.`userName`,
        U.`FullName`,
        `idUser`
    FROM `{id_master_user}_master_permission` AS P
    INNER JOIN `{id_master_user}_master_rol` AS R ON P.`idRol` = R.`idRol`
    INNER JOIN `{id_master_user}_master_users` AS U ON P.`idCompanyUser` = U.`idUser`
    WHERE U.`status` = '1'
    """
    rows = fetch_all(sql)
    return tools_reply_compatible(rows)


async def companny_getMyInfo(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    iam = str(params.get("iam", ""))
    sql = f"""
    SELECT
        `idUser`,
        `fullName`,
        `email`,
        `country`,
        T.`nombreTerminal`,
        T.`terminal`,
        S.`nombreSucursal`,
        S.`sucursal`
    FROM `{id_master_user}_master_users` AS U
    INNER JOIN `{id_master_user}_master_terminales` AS T ON U.`settings` = T.`idTerminal`
    INNER JOIN `{id_master_user}_master_sucursales` AS S ON T.`idSucursal` = S.`idSucursal`
    WHERE U.`userName` = :iam
    """
    rows = fetch_all(sql, {"iam": iam})
    return tools_reply_compatible(rows)


async def companny_getMyConsecutive(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _require_companny_logged_in(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    iam = str(params.get("iam", ""))
    env = str(params.get("env", ""))
    tipo = str(params.get("tipoComprobante", ""))
    sql = f"""
    SELECT COALESCE(MAX(`numeroConsecutivo`), 0) AS consecutivo
    FROM `{id_master_user}_master_consecutive` AS C
    INNER JOIN `{id_master_user}_master_users` AS U ON C.`idUser` = U.`idUser`
    WHERE U.`userName` = :iam AND C.`ENV` = :env AND C.`tipoComprobante` = :tipo
    """
    rows = fetch_all(sql, {"iam": iam, "env": env, "tipo": tipo})
    return tools_reply_compatible(rows)


async def compannyUpdateTipoCambio(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    sql = f"UPDATE `{id_master_user}_master_config_companny` SET `value` = :value WHERE `name` = 'TIPOCAMBIO'"
    rows = execute(sql, {"value": str(params.get("tipoCambio", ""))})
    return tools_reply_compatible(rows)


async def compannyUpdateLocation(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    table = f"{id_master_user}_master_config_companny"
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'PROVINCIA'", {"value": str(params.get("idProvincia", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'CANTON'", {"value": str(params.get("idCanton", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'DISTRITO'", {"value": str(params.get("idDistrito", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'BARRIO'", {"value": str(params.get("idBarrio", ""))})
    rows = execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'SENNAS'", {"value": str(params.get("sennas", ""))})
    return tools_reply_compatible(rows)


async def compannyUpdateInformation(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    table = f"{id_master_user}_master_config_companny"
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'NOMBRE'", {"value": str(params.get("nombre", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'NOMCOMER'", {"value": str(params.get("nombreComercial", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'EMAIL'", {"value": str(params.get("email", ""))})
    execute(
        f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'NCODPAIS' OR `name` = 'FCODPAIS'",
        {"value": str(params.get("codigoPais", ""))},
    )
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'NNUMER'", {"value": str(params.get("telefono", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'FNUMER'", {"value": str(params.get("fax", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'TIPOCED'", {"value": str(params.get("tipoCedula", ""))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'CEDULA'", {"value": str(params.get("cedula", ""))})
    return tools_reply_compatible(1)


async def company_stag_users(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    table = f"{id_user}_master_config_companny"
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'stagUserName'", {"value": str(params.get('userName', ''))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'stagPassword'", {"value": str(params.get('password', ''))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'stagP12Code'", {"value": str(params.get('downloadCode', ''))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'stagPin'", {"value": str(params.get('pinCerti', ''))})
    return tools_reply_compatible("ok")


async def company_prod_users(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    table = f"{id_user}_master_config_companny"
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'prodUserName'", {"value": str(params.get('userName', ''))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'prodPassword'", {"value": str(params.get('password', ''))})
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'prodP12Code'", {"value": str(params.get('downloadCode', ''))})
    # Compatibility: legacy code updates stagPin here (not prodPin).
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'stagPin'", {"value": str(params.get('pinCerti', ''))})
    return tools_reply_compatible("ok")


async def company_change_env(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    env = "api-prod" if str(params.get("envProduccion", "")).lower() == "true" else "api-stag"
    table = f"{id_user}_master_config_companny"
    execute(f"UPDATE `{table}` SET `value` = :value WHERE `name` = 'ENV'", {"value": env})
    return tools_reply_compatible("ok")


async def delete_reciver(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_receptor = str(params.get("idReceptor", ""))
    sql = f"UPDATE `{id_user}_master_receiver` SET `estadoCliente` = '0' WHERE `idReceptor` = :idReceptor"
    rows = execute(sql, {"idReceptor": id_receptor})
    return tools_reply_compatible(rows)


async def add_terminal(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sql = f"""
    INSERT INTO `{id_user}_master_terminales` (`idTerminal`, `nombreTerminal`, `terminal`, `idSucursal`)
    VALUES (NULL, :nombreTerminal, :numeroTerminal, :idSucursal)
    """
    rows = execute(
        sql,
        {
            "nombreTerminal": str(params.get("nombreTerminal", "")),
            "numeroTerminal": str(params.get("numeroTerminal", "")),
            "idSucursal": str(params.get("idSucursal", "")),
        },
    )
    return tools_reply_compatible(rows)


def _copy_master_inventory_table(id_user: int, numero_sucursal: str) -> None:
    templates = fetch_all(
        """
        SELECT table_name
        FROM information_schema.tables
        WHERE table_name LIKE 'master_inventary_sucursal_%'
        """
    )
    for row in templates:
        template = str(row.get("table_name", ""))
        if not template:
            continue
        sql = f"CREATE TABLE IF NOT EXISTS `{id_user}_master_inventary_sucursal_{numero_sucursal}` LIKE `{template}`"
        execute(sql)


async def addSucursales(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    numero_sucursal = str(params.get("numeroSucursal", ""))
    sql = f"""
    INSERT INTO `{id_user}_master_sucursales` (`idSucursal`, `nombreSucursal`, `sucursal`)
    VALUES (NULL, :nombreSucursal, :numeroSucursal)
    """
    rows = execute(
        sql,
        {
            "nombreSucursal": str(params.get("nombreSucursal", "")),
            "numeroSucursal": numero_sucursal,
        },
    )
    _copy_master_inventory_table(id_user, numero_sucursal)
    return tools_reply_compatible(rows)


async def addInventaryProduct(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, _companny_user_id = session

    sucursal = _safe_numeric_token(str(params.get("sucursal", "")))
    if sucursal is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    sql = f"""
    INSERT INTO `{id_master_user}_master_inventary_sucursal_{sucursal}`
    (`idProducto`, `nombre`, `descripcion`, `unidadMedida`, `precioVenta`, `idImpuesto`, `cantidadImpuesto`, `codigoBarras`, `disponible`)
    VALUES
    (NULL, :nombre, :descripcion, :unidadMedida, :precioVenta, :idImpuesto, :cantidadImpuesto, :codigoBarras, :disponible)
    """
    rows = execute(
        sql,
        {
            "nombre": str(params.get("nombre", "")),
            "descripcion": str(params.get("descripcion", "")),
            "unidadMedida": str(params.get("unidadMedida", "")),
            "precioVenta": str(params.get("precioVenta", "")),
            "idImpuesto": str(params.get("idImpuesto", "")),
            "cantidadImpuesto": str(params.get("cantidadImpuesto", "")),
            "codigoBarras": str(params.get("codigoBarras", "")),
            "disponible": str(params.get("disponible", "")),
        },
    )
    return tools_reply_compatible(rows)


async def add_companny_reciver(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, companny_user_id = session

    sql = f"""
    INSERT INTO `{id_master_user}_master_receiver`
    (`idReceptor`, `idUser`, `nombreCliente`, `numeroCedula`, `tipoCedula`, `telefono`, `idProvincia`, `idCanton`, `idDistrito`, `idBarrio`, `otrasSenas`, `nombreComercial`, `correoPrincipal`, `copiasCorreo`, `codigoPais`, `numeroFax`, `estadoCliente`)
    VALUES
    (NULL, :idUser, :nombreCliente, :numeroCedula, :tipoCedula, :telefono, :idProvincia, :idCanton, :idDistrito, :idBarrio, :otrasSenas, :nombreComercial, :correoPrincipal, :copiasCorreo, '506', :numeroFax, '1')
    """
    rows = execute(
        sql,
        {
            "idUser": companny_user_id,
            "nombreCliente": str(params.get("nombreCliente", "")),
            "numeroCedula": str(params.get("numeroCedula", "")),
            "tipoCedula": str(params.get("tipoCedula", "")),
            "telefono": str(params.get("telefono", "")),
            "idProvincia": str(params.get("idProvincia", "")),
            "idCanton": str(params.get("idCanton", "")),
            "idDistrito": str(params.get("idDistrito", "")),
            "idBarrio": str(params.get("idBarrio", "")),
            "otrasSenas": str(params.get("otrasSenas", "")),
            "nombreComercial": str(params.get("nombreComercial", "")),
            "correoPrincipal": str(params.get("correoPrincipal", "")),
            "copiasCorreo": str(params.get("copiasCorreo", "")),
            "numeroFax": str(params.get("numeroFax", "")),
        },
    )
    return tools_reply_compatible(rows)


async def companny_add_master_Consecutive(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    id_user = _safe_numeric_id(str(params.get("idUser", "")))
    if id_master_user is None or id_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    envs = ["api-stag", "api-prod"]
    tipos = ["FE", "NC", "ND", "TE", "CCE", "CPCE", "RCEFE"]
    table = f"{id_master_user}_master_consecutive"
    total_rows = 0
    for env in envs:
        for tipo in tipos:
            rows = execute(
                f"INSERT INTO `{table}` (`idConsecutivo`, `ENV`, `companyName`, `numeroConsecutivo`, `tipoComprobante`, `idUser`) VALUES (NULL, :env, '', '0', :tipo, :idUser)",
                {"env": env, "tipo": tipo, "idUser": id_user},
            )
            total_rows += rows
    return tools_reply_compatible(total_rows)


async def companny_add_voucher(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, companny_user_id = session

    id_receptor_raw = str(params.get("idReceptor", "")).strip()
    id_receptor = _safe_numeric_id(id_receptor_raw) if id_receptor_raw else None
    id_ref_raw = str(params.get("idComprobanteReferencia", "")).strip()
    id_ref = _safe_numeric_id(id_ref_raw) if id_ref_raw else None

    sql = f"""
    INSERT INTO `{id_master_user}_master_vouchers`
    (`idComprobante`, `consecutivo`, `clave`, `idComprobanteReferencia`, `idUser`, `tipoDocumento`, `estado`, `xmlEnviadoBase64`, `respuestaMHBase64`, `idReceptor`, `env`)
    VALUES
    (NULL, :consecutivo, :clave, :idComprobanteReferencia, :idUser, :tipoDocumento, :estado, :xmlEnviadoBase64, :respuestaMHBase64, :idReceptor, :env)
    """
    rows = execute(
        sql,
        {
            "consecutivo": str(params.get("consecutivo", "")),
            "clave": str(params.get("clave", "")),
            "idComprobanteReferencia": id_ref,
            "idUser": companny_user_id,
            "tipoDocumento": str(params.get("tipoDocumento", "")),
            "estado": str(params.get("estado", "")),
            "xmlEnviadoBase64": str(params.get("xmlEnviadoBase64", "")),
            "respuestaMHBase64": str(params.get("respuestaMHBase64", "")),
            "idReceptor": id_receptor,
            "env": str(params.get("env", "")),
        },
    )
    return tools_reply_compatible(rows)


async def companny_updateConsecutive(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, companny_user_id = session

    sql = f"""
    UPDATE `{id_master_user}_master_consecutive`
    SET `numeroConsecutivo` = `numeroConsecutivo` + 1
    WHERE `idUser` = :idUser AND `ENV` = :env AND `tipoComprobante` = :tipoDocumento
    """
    rows = execute(
        sql,
        {
            "idUser": companny_user_id,
            "env": str(params.get("env", "")),
            "tipoDocumento": str(params.get("tipoDocumento", "")),
        },
    )
    return tools_reply_compatible(rows)


async def copy_master_tables(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    masters = fetch_all(
        """
        SELECT table_name
        FROM information_schema.tables
        WHERE table_name LIKE 'master_%'
        """
    )
    table_names = [str(row.get("table_name", "")) for row in masters if row.get("table_name")]

    for table_name in table_names:
        execute(f"CREATE TABLE IF NOT EXISTS `{id_user}_{table_name}` LIKE `{table_name}`")

    defaults = [
        (1, "NOMBRE", "CRLibre.org", "CRLibre.org"),
        (2, "TIPOCED", "01", "CRLibre.org"),
        (3, "CEDULA", "702320717", "CRLibre.org"),
        (4, "NOMCOMER", "CRLibre.org", "CRLibre.org"),
        (5, "EMAIL", "info@crlibre.org", "CRLibre.org"),
        (6, "PROVINCIA", "1", "CRLibre.org"),
        (7, "CANTON", "01", "CRLibre.org"),
        (8, "DISTRITO", "08", "CRLibre.org"),
        (9, "BARRIO", "01", "CRLibre.org"),
        (10, "SENNAS", "250 mts oeste Scotiabank, Rhormoser", "CRLibre.org"),
        (11, "NCODPAIS", "506", "CRLibre.org"),
        (12, "NNUMER", "64206205", "CRLibre.org"),
        (13, "FCODPAIS", "506", "CRLibre.org"),
        (14, "FNUMER", "", "CRLibre.org"),
        (15, "ENV", "api-stag", "CRLibre.org"),
        (16, "situacion", "normal", ""),
        (17, "stagUserName", "cpf-07-0232-0717@stag.comprobanteselectronicos.go.cr", ""),
        (18, "stagPassword", "1PdeUpreble", ""),
        (19, "prodUserName", "cpf-07-0232-0717@stag.comprobanteselectronicos.go.cr", ""),
        (20, "prodPassword", "N&@4+p[H-e[+#$DcOP@9", ""),
        (21, "stagP12Code", "", ""),
        (22, "prodP12Code", "", ""),
        (23, "stagPin", "1994", ""),
        (24, "prodPin", "1994", ""),
        (25, "TIPOCAMBIO", "564.48", ""),
    ]
    cfg_table = f"{id_user}_master_config_companny"
    for id_config, name, value, comp_name in defaults:
        execute(
            f"INSERT INTO `{cfg_table}` (`idConfig`, `name`, `value`, `compannyName`) VALUES (:idConfig, :name, :value, :compannyName)",
            {"idConfig": id_config, "name": name, "value": value, "compannyName": comp_name},
        )

    return tools_reply_compatible(table_names)


async def backup_user(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    id_user = _session_user_id(params)
    if id_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    masters = fetch_all(
        """
        SELECT table_name
        FROM information_schema.tables
        WHERE table_name LIKE 'master_%'
        """
    )
    table_names = [str(row.get("table_name", "")) for row in masters if row.get("table_name")]

    # Keep legacy behavior close to PHP backUpUser(): builds CSV list with trailing comma bug (substr(..., 0, -1)).
    tables = "".join(f"{table_name}, " for table_name in table_names)
    tables = tables[:-1] if tables else ""
    backup_file = f"C:\\{id_user}_{int(time.time())}_Respaldo.sql"
    backup_file_sql = backup_file.replace("\\", "\\\\")

    try:
        if tables:
            execute(f"SELECT * INTO OUTFILE '{backup_file_sql}' FROM {tables}")
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})

    return tools_reply_compatible(table_names)


async def companny_users_getMyDetails(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, _companny_user_id = session
    iam = str(params.get("iam", "")).strip()
    user = _load_companny_user(id_master_user, field="userName", value=iam) if iam else None
    if user is None:
        return tools_reply_compatible({"idUser": 0, "pwd": ""})
    return tools_reply_compatible(user)


async def companny_users_get_my_details(request: Request, params: dict[str, str]) -> Response:
    return await companny_users_getMyDetails(request, params)


async def companny_users_logMeIn(request: Request, params: dict[str, str]) -> Response:
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    companny_user_name = str(params.get("userName", ""))
    raw_pwd = str(params.get("pwd", ""))
    if "@" in companny_user_name:
        user = _load_companny_user(id_master_user, field="email", value=companny_user_name)
    else:
        user = _load_companny_user(id_master_user, field="userName", value=companny_user_name)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_WRONG_LOGIN_INFO)

    stored_pwd = str(user.get("pwd", ""))
    valid = False
    if stored_pwd.startswith("$2"):
        try:
            valid = bcrypt.checkpw(raw_pwd.encode("utf-8"), stored_pwd.encode("utf-8"))
        except ValueError:
            valid = False
    if not valid:
        valid = stored_pwd == hashlib.md5(raw_pwd.encode("utf-8")).hexdigest()
    if not valid:
        return tools_reply_compatible(c.ERROR_USERS_WRONG_LOGIN_INFO)

    ip = request.client.host if request.client and request.client.host else ""
    session_key = _generate_companny_session_key(id_master_user, _to_int(user.get("idUser")), ip)
    return tools_reply_compatible(
        {
            "sessionKey": session_key,
            "userName": str(user.get("userName", "")),
            "idUser": _to_int(user.get("idUser")),
        }
    )


async def companny_users_register(_: Request, params: dict[str, str]) -> Response:
    if not _require_users_logged_in(params):
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user = _session_user_id(params)
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    user_name = str(params.get("userName", ""))
    email = str(params.get("email", ""))
    if _load_companny_user(id_master_user, field="userName", value=user_name) is not None:
        return tools_reply_compatible({"code": c.ERROR_USERS_EXISTS, "status": "usuario ya existe"})
    if _load_companny_user(id_master_user, field="email", value=email) is not None:
        return tools_reply_compatible({"code": c.ERROR_USERS_EXISTS, "status": "usuario ya existe"})

    now = int(time.time())
    pwd_hash = bcrypt.hashpw(str(params.get("pwd", "")).encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    sql = f"""
    INSERT INTO `{id_master_user}_master_users`
    (`idMasterUser`, `fullName`, `userName`, `email`, `about`, `country`, `status`, `timestamp`, `lastAccess`, `pwd`, `avatar`, `settings`)
    VALUES
    (:idMasterUser, :fullName, :userName, :email, :about, :country, :status, :timestamp, :lastAccess, :pwd, :avatar, :settings)
    """
    execute(
        sql,
        {
            "idMasterUser": id_master_user,
            "fullName": str(params.get("fullName", "")),
            "userName": user_name,
            "email": email,
            "about": str(params.get("about", "May all beings be at ease")),
            "country": str(params.get("country", "crc")),
            "status": 1,
            "timestamp": now,
            "lastAccess": now,
            "pwd": pwd_hash,
            "avatar": 0,
            "settings": str(params.get("settings", "")),
        },
    )
    # Keep compatibility with legacy behavior: registration auto-login.
    user = _load_companny_user(id_master_user, field="userName", value=user_name)
    if user is None:
        return tools_reply_compatible(c.ERROR_ERROR)
    session_key = _generate_companny_session_key(id_master_user, _to_int(user.get("idUser")), "")
    return tools_reply_compatible({"sessionKey": session_key, "userName": user_name, "idUser": _to_int(user.get("idUser"))})


async def companny_users_recover_pwd(_: Request, params: dict[str, str]) -> Response:
    id_master_user = _safe_numeric_id(str(params.get("idMasterUser", "")))
    if id_master_user is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    user_name = str(params.get("userName", ""))
    if "@" in user_name:
        user = _load_companny_user(id_master_user, field="email", value=user_name)
    else:
        user = _load_companny_user(id_master_user, field="userName", value=user_name)
    if user is None or _to_int(user.get("idUser")) == 0:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    temp_pwd = str(random.randint(0, 1000) + int(time.time()))
    pwd_hash = bcrypt.hashpw(temp_pwd.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    execute(
        f"UPDATE `{id_master_user}_master_users` SET `pwd` = :pwd WHERE `idUser` = :idUser",
        {"pwd": pwd_hash, "idUser": _to_int(user.get("idUser"))},
    )

    subject = f"Recuperación de Clave {settings.core_site_name}"
    reply_to = f"no-repy@{getattr(settings, 'mail_noreply', 'crlibre.org')}"
    sent = _send_recover_email(
        to=str(user.get("email", "")),
        subject=subject,
        reply_to=reply_to,
        message=f"Su nueva clave es: {temp_pwd}",
    )
    if sent:
        return tools_reply_compatible(c.SUCCESS_ALL_GOOD)
    return tools_reply_compatible(c.ERROR_ERROR)


async def companny_users_update_profile(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, companny_user_id = session

    current = _load_companny_user(id_master_user, field="idUser", value=str(companny_user_id))
    if current is None:
        return tools_reply_compatible(c.ERROR_BAD_REQUEST)

    requested_user_name = str(params.get("userName", current.get("userName", "")))
    requested_email = str(params.get("email", current.get("email", "")))
    if requested_user_name != str(current.get("userName", "")):
        exists = _load_companny_user(id_master_user, field="userName", value=requested_user_name)
        if exists is not None and _to_int(exists.get("idUser")) != companny_user_id:
            return tools_reply_compatible({"code": c.ERROR_USERS_EXISTS, "status": "usuario ya existe"})
    if requested_email != str(current.get("email", "")):
        exists = _load_companny_user(id_master_user, field="email", value=requested_email)
        if exists is not None and _to_int(exists.get("idUser")) != companny_user_id:
            return tools_reply_compatible({"code": c.ERROR_USERS_EXISTS, "status": "usuario ya existe"})

    pwd_raw = str(params.get("pwd", "")).strip()
    if pwd_raw:
        pwd_value = bcrypt.hashpw(pwd_raw.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    else:
        pwd_value = str(current.get("pwd", ""))

    sql = f"""
    UPDATE `{id_master_user}_master_users`
    SET
      `fullName` = :fullName,
      `userName` = :userName,
      `email` = :email,
      `about` = :about,
      `country` = :country,
      `status` = :status,
      `timestamp` = :timestamp,
      `lastAccess` = :lastAccess,
      `pwd` = :pwd,
      `avatar` = :avatar
    WHERE `idUser` = :idUser
    """
    rows = execute(
        sql,
        {
            "fullName": str(params.get("fullName", current.get("fullName", ""))),
            "userName": requested_user_name,
            "email": requested_email,
            "about": str(params.get("about", current.get("about", ""))),
            "country": str(params.get("country", current.get("country", ""))),
            "status": str(params.get("status", current.get("status", "1"))),
            "timestamp": str(params.get("timestamp", current.get("timestamp", int(time.time())))),
            "lastAccess": str(params.get("lastAccess", current.get("lastAccess", int(time.time())))),
            "pwd": pwd_value,
            "avatar": str(params.get("avatar", current.get("avatar", "0"))),
            "idUser": companny_user_id,
        },
    )
    if rows == 0:
        return tools_reply_compatible({"code": c.ERROR_ERROR, "status": "error registrando"})
    return tools_reply_compatible({"code": c.SUCCESS_ALL_GOOD, "status": "registrado con exito"})


async def users_log_me_out(_: Request, params: dict[str, str]) -> Response:
    session = _require_companny_logged_in_user(params)
    if session is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    id_master_user, _companny_user_id = session

    session_key = str(params.get("sessionKey", ""))
    table = f"{id_master_user}_master_sessions"
    execute(f"DELETE FROM `{table}` WHERE `sessionKey` = :sessionKey", {"sessionKey": session_key})
    return tools_reply_compatible("good bye")


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
    "getSucursales": getSucursales,
    "getTerminales": getTerminales,
    "getUsersCompanny": getUsersCompanny,
    "getUserPermissionById": getUserPermissionById,
    "companny_getMyInfo": companny_getMyInfo,
    "companny_getMyConsecutive": companny_getMyConsecutive,
    "compannyUpdateTipoCambio": compannyUpdateTipoCambio,
    "compannyUpdateLocation": compannyUpdateLocation,
    "compannyUpdateInformation": compannyUpdateInformation,
    "company_stag_users": company_stag_users,
    "company_prod_users": company_prod_users,
    "company_change_env": company_change_env,
    "delete_reciver": delete_reciver,
    "add_terminal": add_terminal,
    "addSucursales": addSucursales,
    "addInventaryProduct": addInventaryProduct,
    "add_companny_reciver": add_companny_reciver,
    "companny_add_master_Consecutive": companny_add_master_Consecutive,
    "companny_add_voucher": companny_add_voucher,
    "companny_updateConsecutive": companny_updateConsecutive,
    "copy_master_tables": copy_master_tables,
    "backup_user": backup_user,
    "companny_users_getMyDetails": companny_users_getMyDetails,
    "companny_users_get_my_details": companny_users_get_my_details,
    "companny_users_logMeIn": companny_users_logMeIn,
    "companny_users_register": companny_users_register,
    "companny_users_recover_pwd": companny_users_recover_pwd,
    "companny_users_update_profile": companny_users_update_profile,
    "users_log_me_out": users_log_me_out,
}


def get_handler(route: str) -> HandlerFn:
    return NATIVE_HANDLERS.get(route, proxy)
