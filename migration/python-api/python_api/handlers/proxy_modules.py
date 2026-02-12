from __future__ import annotations

from fastapi import Request
from fastapi.responses import Response

from python_api.compat import ParsedLegacyRequest
from python_api.config import get_settings
from python_api.fallback import FallbackProxy
from python_api.responses import tools_reply_compatible

settings = get_settings()
fallback_proxy = FallbackProxy(settings)

MODULE_ROUTES: dict[str, set[str]] = {
    "XmlToBase64": {"encode"},
    "callback": {"callback"},
    "check": {"checkxml"},
    "crlibreall": {"FE", "NC", "ND", "gen_xml_nc"},
    "ejemplo": {"hola", "un_usuario"},
    "fileUploader": {"subir_certif", "subir_xml", "test"},
    "geoloc": {"geoloc_create_tables", "geoloc_get_by_ip", "geoloc_load_blocks", "geoloc_load_locations"},
    "makeJson": {"makeJson"},
    "makeQR": {"makeQR"},
    "sendMail": {"sendmail"},
    "signXML": {"signFE"},
    "version": {"version"},
    "wirez": {"conversations_get_details", "messages_get_in_conversation", "messages_get_recent", "messages_send"},
}


def supports(w: str, r: str) -> bool:
    return r in MODULE_ROUTES.get(w, set())


async def proxy(request: Request, params: dict[str, str]) -> Response:
    try:
        parsed = ParsedLegacyRequest(params=params, source="query")
        return await fallback_proxy.proxy(request, parsed)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": f"Fallback proxy failed: {exc}"})
