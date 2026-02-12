from __future__ import annotations

import base64
from xml.etree import ElementTree as ET

from fastapi import Request
from fastapi.responses import Response

from python_api.responses import tools_reply_compatible

XML_SCHEMA = "http://www.w3.org/2001/XMLSchema"
XML_SCHEMA_INSTANCE = "http://www.w3.org/2001/XMLSchema-instance"

ROUTE_META: dict[str, tuple[str, str]] = {
    "gen_xml_fe": (
        "FacturaElectronica",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica",
    ),
    "gen_xml_nc": (
        "NotaCreditoElectronica",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaCreditoElectronica",
    ),
    "gen_xml_nd": (
        "NotaDebitoElectronica",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaDebitoElectronica",
    ),
    "gen_xml_te": (
        "TiqueteElectronico",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico",
    ),
    "gen_xml_mr": (
        "MensajeReceptor",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/mensajeReceptor",
    ),
    "gen_xml_fec": (
        "FacturaElectronicaCompra",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra",
    ),
    "gen_xml_fee": (
        "FacturaElectronicaExportacion",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaExportacion",
    ),
}


def _add_if(parent: ET.Element, tag: str, value: str | None) -> None:
    if value is None:
        return
    text = str(value).strip()
    if text == "":
        return
    ET.SubElement(parent, tag).text = text


def _pad_left(value: str, size: int) -> str:
    raw = str(value or "").strip()
    if raw == "":
        return raw
    return raw.zfill(size)


def _build_emisor(root: ET.Element, params: dict[str, str]) -> None:
    emisor = ET.SubElement(root, "Emisor")
    _add_if(emisor, "Nombre", params.get("emisor_nombre"))

    if params.get("emisor_tipo_identif", "").strip() or params.get("emisor_num_identif", "").strip():
        ident = ET.SubElement(emisor, "Identificacion")
        _add_if(ident, "Tipo", params.get("emisor_tipo_identif"))
        _add_if(ident, "Numero", params.get("emisor_num_identif"))

    _add_if(emisor, "NombreComercial", params.get("emisor_nombre_comercial"))
    _add_if(emisor, "Registrofiscal8707", params.get("registrofiscal8707"))

    if (
        params.get("emisor_provincia", "").strip()
        and params.get("emisor_canton", "").strip()
        and params.get("emisor_distrito", "").strip()
        and params.get("emisor_otras_senas", "").strip()
    ):
        ubic = ET.SubElement(emisor, "Ubicacion")
        _add_if(ubic, "Provincia", params.get("emisor_provincia"))
        _add_if(ubic, "Canton", params.get("emisor_canton"))
        _add_if(ubic, "Distrito", params.get("emisor_distrito"))
        _add_if(ubic, "Barrio", params.get("emisor_barrio"))
        _add_if(ubic, "OtrasSenas", params.get("emisor_otras_senas"))

    if params.get("emisor_cod_pais_tel", "").strip() and params.get("emisor_tel", "").strip():
        tel = ET.SubElement(emisor, "Telefono")
        _add_if(tel, "CodigoPais", params.get("emisor_cod_pais_tel"))
        _add_if(tel, "NumTelefono", params.get("emisor_tel"))

    _add_if(emisor, "CorreoElectronico", params.get("emisor_email"))


def _build_receptor(root: ET.Element, params: dict[str, str]) -> None:
    if str(params.get("omitir_receptor", "")).lower() == "true":
        return

    if not params.get("receptor_nombre", "").strip():
        return

    receptor = ET.SubElement(root, "Receptor")
    _add_if(receptor, "Nombre", params.get("receptor_nombre"))
    if params.get("receptor_tipo_identif", "").strip() or params.get("receptor_num_identif", "").strip():
        ident = ET.SubElement(receptor, "Identificacion")
        _add_if(ident, "Tipo", params.get("receptor_tipo_identif"))
        _add_if(ident, "Numero", params.get("receptor_num_identif"))

    _add_if(receptor, "IdentificacionExtranjero", params.get("receptor_identif_extranjero"))
    _add_if(receptor, "NombreComercial", params.get("receptor_nombre_comercial"))

    if (
        params.get("receptor_provincia", "").strip()
        and params.get("receptor_canton", "").strip()
        and params.get("receptor_distrito", "").strip()
        and params.get("receptor_otras_senas", "").strip()
    ):
        ubic = ET.SubElement(receptor, "Ubicacion")
        _add_if(ubic, "Provincia", params.get("receptor_provincia"))
        _add_if(ubic, "Canton", params.get("receptor_canton"))
        _add_if(ubic, "Distrito", params.get("receptor_distrito"))
        _add_if(ubic, "Barrio", params.get("receptor_barrio"))
        _add_if(ubic, "OtrasSenas", params.get("receptor_otras_senas"))

    _add_if(receptor, "OtrasSenasExtranjero", params.get("receptor_otras_senas_extranjero"))

    if params.get("receptor_cod_pais_tel", "").strip() and params.get("receptor_tel", "").strip():
        tel = ET.SubElement(receptor, "Telefono")
        _add_if(tel, "CodigoPais", params.get("receptor_cod_pais_tel"))
        _add_if(tel, "NumTelefono", params.get("receptor_tel"))

    _add_if(receptor, "CorreoElectronico", params.get("receptor_email"))


def _build_resumen(root: ET.Element, params: dict[str, str]) -> None:
    resumen = ET.SubElement(root, "ResumenFactura")
    moneda = ET.SubElement(resumen, "CodigoTipoMoneda")
    _add_if(moneda, "CodigoMoneda", params.get("cod_moneda"))
    _add_if(moneda, "TipoCambio", params.get("tipo_cambio"))

    totals = {
        "total_serv_gravados": "TotalServGravados",
        "total_serv_exentos": "TotalServExentos",
        "total_serv_exonerados": "TotalServExonerados",
        "total_serv_no_sujeto": "TotalServNoSujeto",
        "total_merc_gravada": "TotalMercanciasGravadas",
        "total_merc_exenta": "TotalMercanciasExentas",
        "total_merc_exonerada": "TotalMercanciasExoneradas",
        "total_merc_no_sujeta": "TotalMercNoSujeta",
        "total_gravados": "TotalGravado",
        "total_exento": "TotalExento",
        "total_exonerado": "TotalExonerado",
        "total_no_sujeto": "TotalNoSujeto",
        "total_ventas": "TotalVenta",
        "total_descuentos": "TotalDescuentos",
        "total_ventas_neta": "TotalVentaNeta",
        "total_impuestos": "TotalImpuesto",
        "total_impuestos_asumidos_fabrica": "TotalImpAsumEmisorFabrica",
        "totalIVADevuelto": "TotalIVADevuelto",
        "totalOtrosCargos": "TotalOtrosCargos",
        "total_comprobante": "TotalComprobante",
    }
    for source, target in totals.items():
        _add_if(resumen, target, params.get(source))

    _add_if(resumen, "TotalDesgloseImpuestoCompat", params.get("totalDesgloseImpuesto"))


def _build_detalle(root: ET.Element, params: dict[str, str]) -> None:
    detalle = ET.SubElement(root, "DetalleServicio")
    linea = ET.SubElement(detalle, "LineaDetalle")
    _add_if(linea, "DetalleCompat", params.get("detalles"))


def _build_otros(root: ET.Element, params: dict[str, str]) -> None:
    otros = ET.SubElement(root, "Otros")
    _add_if(otros, "OtroTextoCompat", params.get("otros"))
    _add_if(otros, "InformacionReferenciaCompat", params.get("informacion_referencia"))
    _add_if(otros, "OtrosCargosCompat", params.get("otrosCargos"))
    _add_if(otros, "MediosPagoCompat", params.get("medios_pago"))


def _build_mr(root: ET.Element, params: dict[str, str]) -> None:
    _add_if(root, "Clave", params.get("clave"))
    _add_if(root, "NumeroCedulaEmisor", _pad_left(str(params.get("numero_cedula_emisor", "")), 12))
    _add_if(root, "FechaEmisionDoc", params.get("fecha_emision_doc"))
    _add_if(root, "Mensaje", params.get("mensaje"))
    _add_if(root, "DetalleMensaje", params.get("detalle_mensaje"))
    _add_if(root, "MontoTotalImpuesto", params.get("monto_total_impuesto"))
    _add_if(root, "CodigoActividad", _pad_left(str(params.get("codigo_actividad", "")), 6))
    _add_if(root, "TotalFactura", params.get("total_factura"))
    _add_if(root, "NumeroCedulaReceptor", _pad_left(str(params.get("numero_cedula_receptor", "")), 12))
    _add_if(root, "NumeroConsecutivoReceptor", params.get("numero_consecutivo_receptor"))


def _build_document(route: str, params: dict[str, str]) -> str:
    root_tag, namespace = ROUTE_META[route]
    root = ET.Element(
        root_tag,
        {
            "xmlns": namespace,
            "xmlns:xsd": XML_SCHEMA,
            "xmlns:xsi": XML_SCHEMA_INSTANCE,
        },
    )

    if route == "gen_xml_mr":
        _build_mr(root, params)
    else:
        _add_if(root, "Clave", params.get("clave"))
        _add_if(root, "ProveedorSistemas", params.get("proveedor_sistemas"))
        _add_if(root, "CodigoActividadEmisor", _pad_left(str(params.get("codigo_actividad_emisor", "")), 6))
        _add_if(root, "CodigoActividadReceptor", _pad_left(str(params.get("codigo_actividad_receptor", "")), 6))
        _add_if(root, "NumeroConsecutivo", params.get("consecutivo"))
        _add_if(root, "FechaEmision", params.get("fecha_emision"))
        _build_emisor(root, params)
        _build_receptor(root, params)
        _build_resumen(root, params)
        _build_detalle(root, params)
        _build_otros(root, params)

    xml_bytes = ET.tostring(root, encoding="utf-8", xml_declaration=True)
    return base64.b64encode(xml_bytes).decode("utf-8")


def _build_response(route: str, params: dict[str, str]) -> dict[str, str]:
    clave = str(params.get("clave", ""))
    xml_b64 = _build_document(route, params)
    return {"clave": clave, "xml": xml_b64}


async def gen_xml_fe(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_fe", params))


async def gen_xml_nc(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_nc", params))


async def gen_xml_nd(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_nd", params))


async def gen_xml_te(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_te", params))


async def gen_xml_mr(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_mr", params))


async def gen_xml_fec(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_fec", params))


async def gen_xml_fee(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_fee", params))


async def test(request: Request, params: dict[str, str]) -> Response:
    _ = request
    _ = params
    return tools_reply_compatible("Esto es un test")
