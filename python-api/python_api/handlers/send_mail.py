from __future__ import annotations

import base64
from email.message import EmailMessage
import smtplib

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.config import get_settings
from python_api.responses import tools_reply_compatible

settings = get_settings()


def _safe_b64_decode(value: str) -> bytes:
    if not value:
        return b""
    try:
        return base64.b64decode(value)
    except Exception:
        return b""


def _send_best_effort(msg: EmailMessage) -> None:
    host = settings.mail_host.strip()
    if not host:
        return

    try:
        with smtplib.SMTP(host=host, port=settings.mail_port, timeout=8) as server:
            if settings.mail_secure.lower() == "tls":
                try:
                    server.starttls()
                except Exception:
                    pass

            username = settings.mail_username.strip()
            password = settings.mail_password
            if username and password:
                server.login(username, password)
            server.send_message(msg)
    except Exception:
        # Legacy flow does not fail the API call if mailing fails.
        return


async def sendmail(_: Request, params: dict[str, str]) -> JSONResponse:
    clave = str(params.get("clave", ""))
    xml_envia = _safe_b64_decode(str(params.get("xmlEnvia", "")))
    xml_hacienda = _safe_b64_decode(str(params.get("xmlHacienda", "")))
    factura_pdf = _safe_b64_decode(str(params.get("facturaPDF", "")))

    mail = EmailMessage()
    mail["Subject"] = f"Documentos de Factura electronica #{clave}"
    mail["From"] = "info@api-hacienda.local"
    mail["To"] = "walner1borbon@gmail.com"
    mail.set_content("Se adjuntan las facturas electronicas.")

    if xml_envia:
        mail.add_attachment(xml_envia, maintype="application", subtype="xml", filename=f"Comprobante_{clave}.xml")
    if xml_hacienda:
        mail.add_attachment(xml_hacienda, maintype="application", subtype="xml", filename=f"MH_{clave}.xml")
    if factura_pdf:
        mail.add_attachment(factura_pdf, maintype="application", subtype="pdf", filename="name file.pdf")

    _send_best_effort(mail)
    return tools_reply_compatible("test")
