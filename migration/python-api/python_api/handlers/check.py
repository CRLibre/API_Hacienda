from __future__ import annotations

from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse
from lxml import etree

from python_api.responses import tools_reply_compatible

REPO_ROOT = Path(__file__).resolve().parents[4]


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


def _validate_fe() -> str | None:
    xml_path = REPO_ROOT / "fac.xml"
    xsd_path = REPO_ROOT / "www/xsd/FacturaElectronica_V.4.2.xsd"
    if not xml_path.is_file() or not xsd_path.is_file():
        return None

    try:
        schema_doc = etree.parse(str(xsd_path))
        schema = etree.XMLSchema(schema_doc)
        xml_doc = etree.parse(str(xml_path))
    except Exception:
        return None

    if schema.validate(xml_doc):
        return "validated"

    errors = [_libxml_message(error) for error in schema.error_log]
    return "".join(errors) if errors else None


async def checkxml(_: Request, params: dict[str, str]) -> JSONResponse:
    tipo_doc = str(params.get("tipoDocumento", ""))
    tipos = {"FE", "ND", "NC", "TE", "CCE", "CPCE", "RCE", "FEC, FEE"}

    if tipo_doc not in tipos:
        return tools_reply_compatible("No se encuentra tipo de documento")

    if tipo_doc == "FE":
        return tools_reply_compatible(_validate_fe())

    return tools_reply_compatible(None)
