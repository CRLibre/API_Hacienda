from __future__ import annotations

from collections.abc import Awaitable, Callable

from fastapi import Request
from fastapi.responses import Response

from . import cala
from . import callback
from . import check
from . import clave
from . import consultar
from . import crlibreall
from . import crypto
from . import ejemplo
from . import files
from . import facturador
from . import file_uploader
from . import firmar_xml
from . import geoloc
from . import genxml
from . import make_json
from . import make_qr
from . import proxy_modules
from . import send_mail
from . import send
from . import sign_xml_legacy
from . import token
from . import users
from . import version
from . import wirez
from . import xml_to_base64

HandlerFn = Callable[[Request, dict[str, str]], Awaitable[Response] | Response]

HANDLERS: dict[tuple[str, str], HandlerFn] = {
    ("XmlToBase64", "encode"): xml_to_base64.encode,
    ("callback", "callback"): callback.callback,
    ("clave", "clave"): clave.clave,
    ("check", "checkxml"): check.checkxml,
    ("consultar", "consultarCom"): consultar.consultarCom,
    ("crlibreall", "FE"): crlibreall.FE,
    ("crlibreall", "NC"): crlibreall.NC,
    ("crlibreall", "ND"): crlibreall.ND,
    ("crlibreall", "gen_xml_nc"): crlibreall.gen_xml_nc,
    ("cala", "cala_core"): cala.cala_core,
    ("cala", "cala_default"): cala.cala_default,
    ("cala", "cala_test_install"): cala.cala_test_install,
    ("crypto", "desencrypt"): crypto.desencrypt,
    ("crypto", "encrypt"): crypto.encrypt,
    ("crypto", "makeKey"): crypto.makeKey,
    ("ejemplo", "hola"): ejemplo.hola,
    ("ejemplo", "un_usuario"): ejemplo.un_usuario,
    ("fileUploader", "subir_certif"): file_uploader.subir_certif,
    ("fileUploader", "subir_xml"): file_uploader.subir_xml,
    ("fileUploader", "test"): file_uploader.test,
    ("files", "filesGetUrl"): files.filesGetUrl,
    ("files", "files_view_file"): files.files_view_file,
    ("files", "upload"): files.upload,
    ("firmarXML", "firmar"): firmar_xml.firmar,
    ("geoloc", "geoloc_create_tables"): geoloc.geoloc_create_tables,
    ("geoloc", "geoloc_get_by_ip"): geoloc.geoloc_get_by_ip,
    ("geoloc", "geoloc_load_blocks"): geoloc.geoloc_load_blocks,
    ("geoloc", "geoloc_load_locations"): geoloc.geoloc_load_locations,
    ("genXML", "gen_xml_fe"): genxml.gen_xml_fe,
    ("genXML", "gen_xml_fec"): genxml.gen_xml_fec,
    ("genXML", "gen_xml_fee"): genxml.gen_xml_fee,
    ("genXML", "gen_xml_mr"): genxml.gen_xml_mr,
    ("genXML", "gen_xml_nc"): genxml.gen_xml_nc,
    ("genXML", "gen_xml_nd"): genxml.gen_xml_nd,
    ("genXML", "gen_xml_te"): genxml.gen_xml_te,
    ("genXML", "test"): genxml.test,
    ("makeJson", "makeJson"): make_json.makeJson,
    ("makeQR", "makeQR"): make_qr.makeQR,
    ("sendMail", "sendmail"): send_mail.sendmail,
    ("send", "json"): send.json,
    ("send", "sendMensaje"): send.sendMensaje,
    ("send", "sendTE"): send.sendTE,
    ("signXML", "signFE"): sign_xml_legacy.signFE,
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
    ("version", "version"): version.version,
    ("wirez", "conversations_get_details"): wirez.conversations_get_details,
    ("wirez", "messages_get_in_conversation"): wirez.messages_get_in_conversation,
    ("wirez", "messages_get_recent"): wirez.messages_get_recent,
    ("wirez", "messages_send"): wirez.messages_send,
}


def get_handler(w: str, r: str) -> HandlerFn | None:
    handler = HANDLERS.get((w, r))
    if handler is not None:
        return handler
    if w == "facturador" and r in facturador.ROUTES:
        return facturador.get_handler(r)
    if proxy_modules.supports(w, r):
        return proxy_modules.proxy
    return None


def is_implemented(w: str, r: str) -> bool:
    return (w, r) in HANDLERS or (w == "facturador" and r in facturador.ROUTES) or proxy_modules.supports(w, r)
