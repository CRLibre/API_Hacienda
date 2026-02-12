from __future__ import annotations

from fastapi import Request
from fastapi.responses import Response

from python_api.compat import ParsedLegacyRequest
from python_api.config import get_settings
from python_api.fallback import FallbackProxy
from python_api.responses import tools_reply_compatible

settings = get_settings()
fallback_proxy = FallbackProxy(settings)

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
