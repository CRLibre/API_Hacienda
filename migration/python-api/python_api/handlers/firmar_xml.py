from __future__ import annotations

import base64
import warnings
from pathlib import Path

from cryptography.hazmat.primitives.serialization import Encoding, pkcs12
from cryptography.utils import CryptographyDeprecationWarning
from fastapi import Request
from fastapi.responses import JSONResponse
from lxml import etree

from python_api.config import get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import fetch_one

settings = get_settings()


def _files_get_url(download_code: str) -> Path | None:
    row = fetch_one("SELECT * FROM files WHERE downloadCode = :downloadCode", {"downloadCode": download_code})
    if row is None:
        return None

    id_user = row.get("idUser")
    file_type = row.get("type") or ""
    file_name = row.get("name") or ""

    if not file_name:
        return None

    return Path(settings.files_base_path).expanduser() / str(id_user) / str(file_type) / str(file_name)


def _sign_xml_with_p12(p12_bytes: bytes, pin: str, xml_bytes: bytes) -> bytes:
    private_key, certificate, _extra = pkcs12.load_key_and_certificates(
        p12_bytes,
        pin.encode("utf-8") if pin else b"",
    )
    if private_key is None or certificate is None:
        raise ValueError("No se pudo cargar la llave/certificado desde el P12")

    with warnings.catch_warnings():
        warnings.filterwarnings(
            "ignore",
            category=CryptographyDeprecationWarning,
            message=r".*SECT.*will be removed in the next release\.",
        )
        from signxml import XMLSigner, methods

    root = etree.fromstring(xml_bytes)
    cert_pem = certificate.public_bytes(Encoding.PEM)
    signer = XMLSigner(
        method=methods.enveloped,
        signature_algorithm="rsa-sha256",
        digest_algorithm="sha256",
        c14n_algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315",
    )
    signed_root = signer.sign(root, key=private_key, cert=cert_pem)
    return etree.tostring(signed_root, encoding="UTF-8", xml_declaration=True)


async def firmar(_: Request, params: dict[str, str]) -> JSONResponse:
    p12_url = str(params.get("p12Url", ""))
    pin = str(params.get("pinP12", ""))
    in_xml = str(params.get("inXml", ""))

    p12_path = _files_get_url(p12_url)
    if p12_path is None or not p12_path.is_file():
        return tools_reply_compatible("No se encontró el archivo p12")

    try:
        p12_bytes = p12_path.read_bytes()
    except OSError as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})

    try:
        xml_bytes = base64.b64decode(in_xml)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})

    try:
        signed_xml = _sign_xml_with_p12(p12_bytes=p12_bytes, pin=pin, xml_bytes=xml_bytes)
    except Exception as exc:
        return tools_reply_compatible({"Status": "Error occurred", "text": str(exc)})

    xml_firmado = base64.b64encode(signed_xml).decode("utf-8")
    return tools_reply_compatible({"xmlFirmado": xml_firmado})
