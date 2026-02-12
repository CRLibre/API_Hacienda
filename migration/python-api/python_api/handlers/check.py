from __future__ import annotations

import base64
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse
from lxml import etree

from python_api.responses import tools_reply_compatible

REPO_ROOT = Path(__file__).resolve().parents[4]
XSD_ROOT = REPO_ROOT / "www/xsd"

DOC_XSD_CANDIDATES: dict[str, tuple[str, ...]] = {
    "FE": ("FacturaElectronica_V4.4-noSign.xsd", "FacturaElectronica_V4.4.xsd", "FacturaElectronica_V.4.2.xsd"),
    "ND": ("NotaDebitoElectronica_V4.4.xsd", "NotaDebitoElectronica.xsd"),
    "NC": ("NotaCreditoElectronica_V4.4.xsd", "NotaCreditoElectronica_V4.2.xsd"),
    "TE": ("TiqueteElectronico_V4.4.xsd", "TiqueteElectronico_V4.2.xsd"),
    "CCE": ("MensajeReceptor_V4.4.xsd", "MensajeReceptor_4.2.xsd"),
    "CPCE": ("MensajeReceptor_V4.4.xsd", "MensajeReceptor_4.2.xsd"),
    "RCE": ("MensajeReceptor_V4.4.xsd", "MensajeReceptor_4.2.xsd"),
    "FEC": ("FacturaElectronicaCompra_V4.4.xsd",),
    "FEE": ("FacturaElectronicaExportacion_V4.4.xsd",),
    "REP": ("ReciboElectronicoPago_V4.4.xsd",),
}


def _libxml_message(error: etree._LogEntry) -> str:
    if error.level == etree.ErrorLevels.WARNING:
        level = "Warning"
    elif error.level == etree.ErrorLevels.ERROR:
        level = "Error"
    elif error.level == etree.ErrorLevels.FATAL:
        level = "Fatal Error"
    else:
        level = "Error"

    message = f"<br/>\n<b>{level} {error.type}</b>: {str(error.message).strip()}"
    if getattr(error, "filename", None):
        message += f" in <b>{error.filename}</b>"
    message += f" on line <b>{error.line}</b>\n"
    return message


def _resolve_xsd_path(tipo_doc: str) -> Path | None:
    for filename in DOC_XSD_CANDIDATES.get(tipo_doc, ()):
        candidate = XSD_ROOT / filename
        if candidate.is_file():
            return candidate
    return None


def _decode_b64(value: str) -> bytes | None:
    try:
        return base64.b64decode(value, validate=True)
    except Exception:
        padding = "=" * ((4 - len(value) % 4) % 4)
        try:
            return base64.b64decode(value + padding, validate=False)
        except Exception:
            return None


def _load_xml_doc(params: dict[str, str]) -> etree._ElementTree | None:
    xml_param = str(params.get("xml", "")).strip()
    if xml_param:
        candidates: list[bytes] = []
        if xml_param.startswith("<"):
            candidates.append(xml_param.encode("utf-8"))
        else:
            decoded = _decode_b64(xml_param)
            if decoded:
                candidates.append(decoded)
            candidates.append(xml_param.encode("utf-8"))

        for raw in candidates:
            try:
                return etree.ElementTree(etree.fromstring(raw))
            except Exception:
                continue

    xml_path = REPO_ROOT / "fac.xml"
    if not xml_path.is_file():
        return None
    try:
        return etree.parse(str(xml_path))
    except Exception:
        return None


def _validate_document(tipo_doc: str, params: dict[str, str]) -> str | None:
    xsd_path = _resolve_xsd_path(tipo_doc)
    xml_doc = _load_xml_doc(params)
    if xsd_path is None or xml_doc is None:
        return None
    try:
        schema_doc = etree.parse(str(xsd_path))
        schema = etree.XMLSchema(schema_doc)
    except Exception:
        return None

    if schema.validate(xml_doc):
        return "validated"

    errors = [_libxml_message(error) for error in schema.error_log]
    return "".join(errors) if errors else None


async def checkxml(_: Request, params: dict[str, str]) -> JSONResponse:
    tipo_doc = str(params.get("tipoDocumento", "")).strip().upper()
    if tipo_doc not in DOC_XSD_CANDIDATES:
        return tools_reply_compatible("No se encuentra tipo de documento")

    return tools_reply_compatible(_validate_document(tipo_doc, params))
