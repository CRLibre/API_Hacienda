from __future__ import annotations

import base64
import json
import re
from typing import Any
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
    "gen_xml_rep": (
        "ReciboElectronicoPago",
        "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/reciboElectronicoPago",
    ),
}

ROUTES_WITH_CODIGO_ACTIVIDAD_RECEPTOR = {"gen_xml_fe", "gen_xml_nc", "gen_xml_nd", "gen_xml_fec"}

SUMMARY_TAGS: dict[str, str] = {
    "total_serv_gravados": "TotalServGravados",
    "total_serv_exentos": "TotalServExentos",
    "total_serv_exonerados": "TotalServExonerado",
    "total_serv_no_sujeto": "TotalServNoSujeto",
    "total_merc_gravada": "TotalMercanciasGravadas",
    "total_merc_exenta": "TotalMercanciasExentas",
    "total_merc_exonerada": "TotalMercExonerada",
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

SUMMARY_FIELDS_BY_ROUTE: dict[str, tuple[str, ...]] = {
    "gen_xml_fe": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_serv_exonerados",
        "total_serv_no_sujeto",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_merc_exonerada",
        "total_merc_no_sujeta",
        "total_gravados",
        "total_exento",
        "total_exonerado",
        "total_no_sujeto",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalIVADevuelto",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_nc": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_serv_exonerados",
        "total_serv_no_sujeto",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_merc_exonerada",
        "total_merc_no_sujeta",
        "total_gravados",
        "total_exento",
        "total_exonerado",
        "total_no_sujeto",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalIVADevuelto",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_nd": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_serv_exonerados",
        "total_serv_no_sujeto",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_merc_exonerada",
        "total_merc_no_sujeta",
        "total_gravados",
        "total_exento",
        "total_exonerado",
        "total_no_sujeto",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalIVADevuelto",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_te": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_serv_exonerados",
        "total_serv_no_sujeto",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_merc_exonerada",
        "total_merc_no_sujeta",
        "total_gravados",
        "total_exento",
        "total_exonerado",
        "total_no_sujeto",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalIVADevuelto",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_fec": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_serv_exonerados",
        "total_serv_no_sujeto",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_merc_exonerada",
        "total_merc_no_sujeta",
        "total_gravados",
        "total_exento",
        "total_exonerado",
        "total_no_sujeto",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_fee": (
        "total_serv_gravados",
        "total_serv_exentos",
        "total_merc_gravada",
        "total_merc_exenta",
        "total_gravados",
        "total_exento",
        "total_ventas",
        "total_descuentos",
        "total_ventas_neta",
        "total_impuestos",
        "total_impuestos_asumidos_fabrica",
        "totalOtrosCargos",
        "total_comprobante",
    ),
    "gen_xml_rep": (
        "total_ventas",
        "total_ventas_neta",
        "total_impuestos",
        "total_comprobante",
    ),
}


def _safe_text(value: Any) -> str:
    if value is None:
        return ""
    return str(value).strip()


def _parse_json(raw: Any) -> Any | None:
    if raw is None:
        return None
    if isinstance(raw, (dict, list, int, float, bool)):
        return raw
    text = _safe_text(raw)
    if text == "":
        return None
    try:
        return json.loads(text)
    except json.JSONDecodeError:
        return None


def _coerce_sequence(raw: Any, *, nested_keys: tuple[str, ...] = ()) -> list[Any]:
    parsed = _parse_json(raw)
    if parsed is None:
        return []
    if isinstance(parsed, list):
        return parsed
    if isinstance(parsed, dict):
        for key in nested_keys:
            nested = parsed.get(key)
            if isinstance(nested, list):
                return nested
        return [parsed]
    return []


def _pick(data: dict[str, Any], *keys: str) -> Any:
    for key in keys:
        if key in data:
            return data.get(key)
    return None


def _add_if(parent: ET.Element, tag: str, value: Any) -> None:
    text = _safe_text(value)
    if text == "":
        return
    ET.SubElement(parent, tag).text = text


def _add_from(data: dict[str, Any], parent: ET.Element, tag: str, *keys: str) -> None:
    _add_if(parent, tag, _pick(data, *keys))


def _pad_left(value: Any, size: int) -> str:
    raw = _safe_text(value)
    if raw == "":
        return raw
    return raw.zfill(size)


def _parse_decimal_2(value: Any) -> str:
    text = _safe_text(value)
    if text == "":
        return ""
    try:
        return f"{float(text):.2f}"
    except (TypeError, ValueError):
        return text


def _coerce_email_list(raw: Any, *, max_items: int) -> list[str]:
    parsed = _parse_json(raw)
    if isinstance(parsed, list):
        candidates: list[Any] = parsed
    elif parsed is None:
        text = _safe_text(raw)
        if text == "":
            return []
        candidates = [text]
    else:
        candidates = [parsed]

    emails: list[str] = []
    seen: set[str] = set()
    for candidate in candidates:
        if isinstance(candidate, dict):
            value = _pick(candidate, "email", "correo", "CorreoElectronico")
        else:
            value = candidate

        for part in re.split(r"[,\n;]+", _safe_text(value)):
            email = part.strip()
            if email == "" or email in seen:
                continue
            seen.add(email)
            emails.append(email)
            if len(emails) >= max_items:
                return emails
    return emails


def _build_emisor(root: ET.Element, params: dict[str, str], route: str) -> None:
    emisor = ET.SubElement(root, "Emisor")
    _add_if(emisor, "Nombre", params.get("emisor_nombre"))

    tipo = params.get("emisor_tipo_identif")
    numero = params.get("emisor_num_identif")
    if _safe_text(tipo) or _safe_text(numero):
        ident = ET.SubElement(emisor, "Identificacion")
        _add_if(ident, "Tipo", tipo)
        _add_if(ident, "Numero", numero)

    if route != "gen_xml_rep":
        _add_if(emisor, "Registrofiscal8707", params.get("registrofiscal8707"))
        _add_if(emisor, "NombreComercial", params.get("emisor_nombre_comercial"))

        if (
            _safe_text(params.get("emisor_provincia"))
            and _safe_text(params.get("emisor_canton"))
            and _safe_text(params.get("emisor_distrito"))
            and _safe_text(params.get("emisor_otras_senas"))
        ):
            ubic = ET.SubElement(emisor, "Ubicacion")
            _add_if(ubic, "Provincia", params.get("emisor_provincia"))
            _add_if(ubic, "Canton", params.get("emisor_canton"))
            _add_if(ubic, "Distrito", params.get("emisor_distrito"))
            _add_if(ubic, "Barrio", params.get("emisor_barrio"))
            _add_if(ubic, "OtrasSenas", params.get("emisor_otras_senas"))

        _add_if(emisor, "OtrasSenasExtranjero", params.get("emisor_otras_senas_extranjero"))

        if _safe_text(params.get("emisor_cod_pais_tel")) and _safe_text(params.get("emisor_tel")):
            tel = ET.SubElement(emisor, "Telefono")
            _add_if(tel, "CodigoPais", params.get("emisor_cod_pais_tel"))
            _add_if(tel, "NumTelefono", params.get("emisor_tel"))

    for email in _coerce_email_list(params.get("emisor_email"), max_items=4):
        ET.SubElement(emisor, "CorreoElectronico").text = email


def _build_receptor(root: ET.Element, params: dict[str, str], route: str) -> None:
    if route != "gen_xml_rep" and _safe_text(params.get("omitir_receptor")).lower() == "true":
        return

    has_name = _safe_text(params.get("receptor_nombre")) != ""
    has_ident = _safe_text(params.get("receptor_tipo_identif")) != "" and _safe_text(params.get("receptor_num_identif")) != ""
    has_any = has_name or has_ident
    if not has_any:
        return

    receptor = ET.SubElement(root, "Receptor")
    _add_if(receptor, "Nombre", params.get("receptor_nombre"))

    if has_ident:
        ident = ET.SubElement(receptor, "Identificacion")
        _add_if(ident, "Tipo", params.get("receptor_tipo_identif"))
        _add_if(ident, "Numero", params.get("receptor_num_identif"))

    if route != "gen_xml_rep":
        _add_if(receptor, "NombreComercial", params.get("receptor_nombre_comercial"))

        if (
            _safe_text(params.get("receptor_provincia"))
            and _safe_text(params.get("receptor_canton"))
            and _safe_text(params.get("receptor_distrito"))
            and _safe_text(params.get("receptor_otras_senas"))
        ):
            ubic = ET.SubElement(receptor, "Ubicacion")
            _add_if(ubic, "Provincia", params.get("receptor_provincia"))
            _add_if(ubic, "Canton", params.get("receptor_canton"))
            _add_if(ubic, "Distrito", params.get("receptor_distrito"))
            _add_if(ubic, "Barrio", params.get("receptor_barrio"))
            _add_if(ubic, "OtrasSenas", params.get("receptor_otras_senas"))

        _add_if(receptor, "OtrasSenasExtranjero", params.get("receptor_otras_senas_extranjero"))

        if _safe_text(params.get("receptor_cod_pais_tel")) and _safe_text(params.get("receptor_tel")):
            tel = ET.SubElement(receptor, "Telefono")
            _add_if(tel, "CodigoPais", params.get("receptor_cod_pais_tel"))
            _add_if(tel, "NumTelefono", params.get("receptor_tel"))

    # Current v4.4 XSDs in-repo still allow a single receptor email.
    for email in _coerce_email_list(params.get("receptor_email"), max_items=1):
        ET.SubElement(receptor, "CorreoElectronico").text = email


def _build_condiciones(root: ET.Element, params: dict[str, str], route: str) -> None:
    _add_if(root, "CondicionVenta", params.get("condicion_venta"))
    if route != "gen_xml_rep":
        _add_if(root, "CondicionVentaOtros", params.get("condicion_venta_otros"))
        _add_if(root, "PlazoCredito", params.get("plazo_credito"))


def _build_codigo_comercial(parent: ET.Element, raw: Any, *, tipo_key: str, codigo_key: str) -> None:
    items = _coerce_sequence(raw)
    for item in items[:5]:
        if not isinstance(item, dict):
            continue
        tipo = _safe_text(_pick(item, tipo_key))
        codigo = _safe_text(_pick(item, codigo_key))
        if tipo == "" or codigo == "":
            continue
        codigo_el = ET.SubElement(parent, "CodigoComercial")
        _add_if(codigo_el, "Tipo", tipo)
        _add_if(codigo_el, "Codigo", codigo)


def _build_descuento(parent: ET.Element, raw: Any) -> None:
    for item in _coerce_sequence(raw)[:5]:
        if not isinstance(item, dict):
            continue
        monto = _safe_text(_pick(item, "montoDescuento", "MontoDescuento"))
        codigo = _safe_text(_pick(item, "codigoDescuento", "CodigoDescuento"))
        if monto == "" or codigo == "":
            continue

        desc = ET.SubElement(parent, "Descuento")
        _add_if(desc, "MontoDescuento", monto)
        _add_if(desc, "CodigoDescuento", codigo)

        if codigo == "99":
            _add_from(item, desc, "CodigoDescuentoOTRO", "codigoDescuentoOTRO", "CodigoDescuentoOTRO")

        naturaleza = _safe_text(_pick(item, "naturalezaDescuento", "NaturalezaDescuento"))
        if naturaleza:
            _add_if(desc, "NaturalezaDescuento", naturaleza)


def _build_datos_impuesto_especifico(parent: ET.Element, raw: Any) -> None:
    if not isinstance(raw, dict):
        return
    data = ET.SubElement(parent, "DatosImpuestoEspecifico")
    _add_from(raw, data, "CantidadUnidadMedida", "cantidadUnidadMedida", "CantidadUnidadMedida")
    _add_from(raw, data, "Porcentaje", "porcentaje", "Porcentaje")
    _add_from(raw, data, "Proporcion", "proporcion", "Proporcion")
    _add_from(raw, data, "VolumenUnidadConsumo", "volumenUnidadConsumo", "VolumenUnidadConsumo")
    _add_from(raw, data, "ImpuestoUnidad", "impuestoUnidad", "ImpuestoUnidad")
    if len(data) == 0:
        parent.remove(data)


def _build_exoneracion(parent: ET.Element, raw: Any) -> None:
    if not isinstance(raw, dict):
        return

    ex = ET.SubElement(parent, "Exoneracion")
    _add_from(raw, ex, "TipoDocumentoEX1", "tipoDocumento", "TipoDocumentoEX1", "TipoDocumento")
    _add_from(raw, ex, "TipoDocumentoOTRO", "tipoDocumentoOtro", "TipoDocumentoOTRO")
    _add_from(raw, ex, "NumeroDocumento", "numeroDocumento", "NumeroDocumento")
    _add_from(raw, ex, "Articulo", "numeroArticulo", "Articulo")
    _add_from(raw, ex, "Inciso", "numeroInciso", "Inciso")
    _add_from(raw, ex, "NombreInstitucion", "nombreInstitucion", "NombreInstitucion")
    _add_from(raw, ex, "NombreInstitucionOtros", "nombreInstitucionOtros", "NombreInstitucionOtros")
    _add_from(raw, ex, "FechaEmisionEX", "fechaEmision", "FechaEmisionEX")
    _add_from(raw, ex, "TarifaExonerada", "tarifaExoneracion", "TarifaExonerada")
    _add_from(raw, ex, "MontoExoneracion", "montoExoneracion", "MontoExoneracion")

    if len(ex) == 0:
        parent.remove(ex)


def _build_impuestos(parent: ET.Element, raw: Any, *, include_extended: bool = True) -> None:
    for item in _coerce_sequence(raw):
        if not isinstance(item, dict):
            continue
        codigo = _safe_text(_pick(item, "codigo", "Codigo"))
        monto = _safe_text(_pick(item, "monto", "Monto"))
        if codigo == "" or monto == "":
            continue

        imp = ET.SubElement(parent, "Impuesto")
        _add_if(imp, "Codigo", codigo)

        if codigo == "99":
            _add_from(item, imp, "CodigoImpuestoOTRO", "codigoImpuestoOtro", "codigoImpuestoOTRO", "CodigoImpuestoOTRO")

        _add_from(item, imp, "CodigoTarifaIVA", "codigoTarifa", "CodigoTarifaIVA")
        _add_from(item, imp, "Tarifa", "tarifa", "Tarifa")
        _add_from(item, imp, "FactorCalculoIVA", "factorIVA", "FactorCalculoIVA")

        if include_extended:
            _build_datos_impuesto_especifico(imp, _pick(item, "datosImpuestoEspecifico", "DatosImpuestoEspecifico"))

        _add_if(imp, "Monto", monto)

        if include_extended:
            exoneracion = _pick(item, "exoneracion", "Exoneracion")
            _build_exoneracion(imp, exoneracion)


def _build_detalle_surtido(parent: ET.Element, raw: Any) -> None:
    surtido_items = _coerce_sequence(raw)
    if not surtido_items:
        return

    detalle_surtido = ET.SubElement(parent, "DetalleSurtido")
    for item in surtido_items[:20]:
        if not isinstance(item, dict):
            continue

        linea = ET.SubElement(detalle_surtido, "LineaDetalleSurtido")
        _add_from(item, linea, "CodigoCABYSSurtido", "codigoCABYSSurtido", "CodigoCABYSSurtido")

        for codigo_item in _coerce_sequence(_pick(item, "codigoComercialSurtido", "CodigoComercialSurtido"))[:5]:
            if not isinstance(codigo_item, dict):
                continue
            tipo = _safe_text(_pick(codigo_item, "tipoSurtido", "TipoSurtido"))
            codigo = _safe_text(_pick(codigo_item, "codigoSurtido", "CodigoSurtido"))
            if tipo == "" or codigo == "":
                continue
            codigo_el = ET.SubElement(linea, "CodigoComercialSurtido")
            _add_if(codigo_el, "TipoSurtido", tipo)
            _add_if(codigo_el, "CodigoSurtido", codigo)

        _add_from(item, linea, "CantidadSurtido", "cantidadSurtido", "CantidadSurtido")
        _add_from(item, linea, "UnidadMedidaSurtido", "unidadMedidaSurtido", "UnidadMedidaSurtido")
        _add_from(item, linea, "UnidadMedidaComercialSurtido", "unidadMedidaComercialSurtido", "UnidadMedidaComercialSurtido")
        _add_from(item, linea, "DetalleSurtido", "detalleSurtido", "DetalleSurtido")
        _add_from(item, linea, "PrecioUnitarioSurtido", "precioUnitarioSurtido", "PrecioUnitarioSurtido")
        _add_from(item, linea, "MontoTotalSurtido", "montoTotalSurtido", "MontoTotalSurtido")

        for desc in _coerce_sequence(_pick(item, "descuentoSurtido", "DescuentoSurtido"))[:5]:
            if not isinstance(desc, dict):
                continue
            monto_desc = _safe_text(_pick(desc, "montoDescuentoSurtido", "MontoDescuentoSurtido"))
            codigo_desc = _safe_text(_pick(desc, "codigoDescuentoSurtido", "CodigoDescuentoSurtido"))
            if monto_desc == "" or codigo_desc == "":
                continue
            desc_el = ET.SubElement(linea, "DescuentoSurtido")
            _add_if(desc_el, "MontoDescuentoSurtido", monto_desc)
            _add_if(desc_el, "CodigoDescuentoSurtido", codigo_desc)
            _add_from(desc, desc_el, "DescuentoSurtidoOtros", "descuentoSurtidoOtros", "DescuentoSurtidoOtros")

        _add_from(item, linea, "SubTotalSurtido", "subTotalSurtido", "SubTotalSurtido")
        _add_from(item, linea, "IVACobradoFabricaSurtido", "ivaCobradoFabricaSurtido", "IVACobradoFabricaSurtido")
        _add_from(item, linea, "BaseImponibleSurtido", "baseImponibleSurtido", "BaseImponibleSurtido")

        for imp in _coerce_sequence(_pick(item, "impuestoSurtido", "ImpuestoSurtido"))[:1000]:
            if not isinstance(imp, dict):
                continue
            cod_imp = _safe_text(_pick(imp, "codigoImpuestoSurtido", "CodigoImpuestoSurtido"))
            monto_imp = _safe_text(_pick(imp, "montoImpuestoSurtido", "MontoImpuestoSurtido"))
            if cod_imp == "" or monto_imp == "":
                continue

            imp_el = ET.SubElement(linea, "ImpuestoSurtido")
            _add_if(imp_el, "CodigoImpuestoSurtido", cod_imp)
            _add_from(imp, imp_el, "CodigoImpuestoOTROSurtido", "codigoImpuestoOTROSurtido", "CodigoImpuestoOTROSurtido")
            _add_from(imp, imp_el, "CodigoTarifaIVASurtido", "codigoTarifaIVASurtido", "CodigoTarifaIVASurtido")
            _add_from(imp, imp_el, "TarifaSurtido", "tarifaSurtido", "TarifaSurtido")

            datos = _pick(imp, "datosImpuestoEspecificoSurtido", "DatosImpuestoEspecificoSurtido")
            if isinstance(datos, dict):
                data_el = ET.SubElement(imp_el, "DatosImpuestoEspecificoSurtido")
                _add_from(datos, data_el, "CantidadUnidadMedidaSurtido", "cantidadUnidadMedidaSurtido", "CantidadUnidadMedidaSurtido")
                _add_from(datos, data_el, "PorcentajeSurtido", "porcentajeSurtido", "PorcentajeSurtido")
                _add_from(datos, data_el, "ProporcionSurtido", "proporcionSurtido", "ProporcionSurtido")
                _add_from(datos, data_el, "VolumenUnidadConsumoSurtido", "volumenUnidadConsumoSurtido", "VolumenUnidadConsumoSurtido")
                _add_from(datos, data_el, "ImpuestoUnidadSurtido", "impuestoUnidadSurtido", "ImpuestoUnidadSurtido")
                if len(data_el) == 0:
                    imp_el.remove(data_el)

            _add_if(imp_el, "MontoImpuestoSurtido", monto_imp)

    if len(detalle_surtido) == 0:
        parent.remove(detalle_surtido)


def _build_detalle_servicio(root: ET.Element, params: dict[str, str]) -> None:
    detalle_servicio = ET.SubElement(root, "DetalleServicio")
    detalles = _coerce_sequence(params.get("detalles"), nested_keys=("detalles",))

    if not detalles:
        raw = _safe_text(params.get("detalles"))
        if raw:
            linea = ET.SubElement(detalle_servicio, "LineaDetalle")
            _add_if(linea, "NumeroLinea", "1")
            _add_if(linea, "Detalle", raw)
        return

    for index, item in enumerate(detalles, start=1):
        linea = ET.SubElement(detalle_servicio, "LineaDetalle")
        _add_if(linea, "NumeroLinea", index)

        if not isinstance(item, dict):
            _add_if(linea, "Detalle", item)
            continue

        _add_from(item, linea, "CodigoCABYS", "codigoCABYS", "CodigoCABYS")
        _build_codigo_comercial(linea, _pick(item, "codigoComercial", "CodigoComercial"), tipo_key="tipo", codigo_key="codigo")

        _add_from(item, linea, "Cantidad", "cantidad", "Cantidad")
        _add_from(item, linea, "UnidadMedida", "unidadMedida", "UnidadMedida")
        _add_from(item, linea, "TipoTransaccion", "tipoTransaccion", "TipoTransaccion")
        _add_from(item, linea, "UnidadMedidaComercial", "unidadMedidaComercial", "UnidadMedidaComercial")
        _add_from(item, linea, "Detalle", "detalle", "Detalle")
        _add_from(item, linea, "NumeroVINoSerie", "numeroVINoSerie", "NumeroVINoSerie")
        _add_from(item, linea, "RegistroMedicamento", "registroMedicamento", "RegistroMedicamento")
        _add_from(item, linea, "FormaFarmaceutica", "formaFarmaceutica", "FormaFarmaceutica")

        _build_detalle_surtido(linea, _pick(item, "detalleSurtido", "DetalleSurtido"))

        _add_from(item, linea, "PrecioUnitario", "precioUnitario", "PrecioUnitario")
        _add_from(item, linea, "MontoTotal", "montoTotal", "MontoTotal")

        _build_descuento(linea, _pick(item, "descuento", "Descuento"))

        _add_from(item, linea, "SubTotal", "subTotal", "SubTotal")
        _add_from(item, linea, "IVACobradoFabrica", "IVACobradoFabrica")
        _add_from(item, linea, "BaseImponible", "baseImponible", "BaseImponible")

        _build_impuestos(linea, _pick(item, "impuesto", "Impuesto"))

        _add_from(item, linea, "ImpuestoAsumidoEmisorFabrica", "impuestoAsumidoEmisorFabrica", "ImpuestoAsumidoEmisorFabrica")
        _add_from(item, linea, "ImpuestoNeto", "impuestoNeto", "ImpuestoNeto")
        _add_from(item, linea, "MontoTotalLinea", "montoTotalLinea", "MontoTotalLinea")


def _build_detalle_servicio_rep(root: ET.Element, params: dict[str, str]) -> None:
    detalle_servicio = ET.SubElement(root, "DetalleServicio")
    detalles = _coerce_sequence(params.get("detalles"), nested_keys=("detalles",))

    if not detalles:
        raw = _safe_text(params.get("detalles"))
        if raw:
            linea = ET.SubElement(detalle_servicio, "LineaDetalle")
            _add_if(linea, "NumeroLinea", "1")
            _add_if(linea, "Detalle", raw)
        return

    for index, item in enumerate(detalles, start=1):
        linea = ET.SubElement(detalle_servicio, "LineaDetalle")
        _add_if(linea, "NumeroLinea", index)

        if not isinstance(item, dict):
            _add_if(linea, "Detalle", item)
            continue

        _add_from(item, linea, "Detalle", "detalle", "Detalle")
        _add_from(item, linea, "MontoTotal", "montoTotal", "MontoTotal")
        _add_from(item, linea, "SubTotal", "subTotal", "SubTotal")
        _build_impuestos(linea, _pick(item, "impuesto", "Impuesto"), include_extended=False)
        _add_from(item, linea, "ImpuestoNeto", "impuestoNeto", "ImpuestoNeto")
        _add_from(item, linea, "MontoTotalLinea", "montoTotalLinea", "MontoTotalLinea")


def _build_otros_cargos(root: ET.Element, params: dict[str, str]) -> None:
    cargos = _coerce_sequence(params.get("otrosCargos"), nested_keys=("otrosCargos",))
    for item in cargos[:15]:
        if not isinstance(item, dict):
            continue

        cargo = ET.Element("OtrosCargos")
        _add_from(item, cargo, "TipoDocumentoOC", "tipoDocumentoOC", "TipoDocumentoOC")
        _add_from(item, cargo, "TipoDocumentoOTROS", "tipoDocumentoOTROS", "TipoDocumentoOTROS")

        tipo_tercero = _safe_text(_pick(item, "tipoIdentidadTercero", "tipoIdentificacionTercero", "TipoIdentidadTercero"))
        numero_tercero = _safe_text(_pick(item, "numeroIdentidadTercero", "numeroIdentificacionTercero", "NumeroIdentidadTercero"))
        if tipo_tercero and numero_tercero:
            tercero = ET.SubElement(cargo, "IdentificacionTercero")
            _add_if(tercero, "Tipo", tipo_tercero)
            _add_if(tercero, "Numero", numero_tercero)

        _add_from(item, cargo, "NombreTercero", "nombreTercero", "NombreTercero")
        _add_from(item, cargo, "Detalle", "detalle", "Detalle")
        _add_from(item, cargo, "PorcentajeOC", "porcentajeOC", "PorcentajeOC")
        _add_from(item, cargo, "MontoCargo", "montoCargo", "MontoCargo")

        if len(cargo) > 0:
            root.append(cargo)


def _build_total_desglose_impuesto(resumen: ET.Element, params: dict[str, str]) -> None:
    desglose = _coerce_sequence(params.get("totalDesgloseImpuesto"), nested_keys=("totalDesgloseImpuesto",))
    for item in desglose:
        if not isinstance(item, dict):
            continue
        node = ET.SubElement(resumen, "TotalDesgloseImpuesto")
        _add_from(item, node, "Codigo", "Codigo", "codigo")
        _add_from(item, node, "CodigoTarifaIVA", "CodigoTarifaIVA", "codigoTarifaIVA")
        _add_from(item, node, "TotalMontoImpuesto", "TotalMontoImpuesto", "totalMontoImpuesto")
        if len(node) == 0:
            resumen.remove(node)


def _build_medios_pago(resumen: ET.Element, params: dict[str, str]) -> None:
    medios = _coerce_sequence(params.get("medios_pago"), nested_keys=("mediosPago", "medioPago"))
    for item in medios[:4]:
        medio = ET.Element("MedioPago")

        if isinstance(item, dict):
            tipo = _safe_text(_pick(item, "tipoMedioPago", "TipoMedioPago", "tipo"))
            _add_if(medio, "TipoMedioPago", tipo)
            if tipo == "99":
                _add_from(item, medio, "MedioPagoOtros", "medioPagoOtros", "MedioPagoOtros")
            total = _parse_decimal_2(_pick(item, "totalMedioPago", "TotalMedioPago"))
            _add_if(medio, "TotalMedioPago", total)
        else:
            _add_if(medio, "TipoMedioPago", item)

        if len(medio) > 0:
            resumen.append(medio)


def _build_resumen(root: ET.Element, params: dict[str, str], route: str) -> None:
    resumen = ET.SubElement(root, "ResumenFactura")

    cod_moneda = _safe_text(params.get("cod_moneda"))
    tipo_cambio = _safe_text(params.get("tipo_cambio"))

    moneda = ET.SubElement(resumen, "CodigoTipoMoneda")
    if cod_moneda and cod_moneda != "CRC" and tipo_cambio and tipo_cambio != "0":
        _add_if(moneda, "CodigoMoneda", cod_moneda)
        _add_if(moneda, "TipoCambio", tipo_cambio)
    else:
        _add_if(moneda, "CodigoMoneda", "CRC")
        _add_if(moneda, "TipoCambio", "1")

    fields = SUMMARY_FIELDS_BY_ROUTE.get(route, tuple(SUMMARY_TAGS.keys()))
    for field in fields:
        if field in {"total_impuestos", "total_impuestos_asumidos_fabrica", "totalIVADevuelto", "totalOtrosCargos", "total_comprobante"}:
            continue
        tag = SUMMARY_TAGS.get(field)
        if tag is not None:
            _add_if(resumen, tag, params.get(field))

    _build_total_desglose_impuesto(resumen, params)

    for field in fields:
        if field not in {"total_impuestos", "total_impuestos_asumidos_fabrica", "totalIVADevuelto", "totalOtrosCargos"}:
            continue
        tag = SUMMARY_TAGS.get(field)
        if tag is not None:
            _add_if(resumen, tag, params.get(field))

    _build_medios_pago(resumen, params)

    for field in fields:
        if field != "total_comprobante":
            continue
        tag = SUMMARY_TAGS.get(field)
        if tag is not None:
            _add_if(resumen, tag, params.get(field))


def _build_informacion_referencia(root: ET.Element, params: dict[str, str]) -> None:
    referencias = _coerce_sequence(
        params.get("informacion_referencia"),
        nested_keys=("informacion_referencia", "informacionReferencia"),
    )
    for item in referencias[:10]:
        if not isinstance(item, dict):
            continue

        tipo_doc = _safe_text(_pick(item, "tipoDoc", "TipoDocIR", "tipo_doc"))
        fecha = _safe_text(_pick(item, "fechaEmision", "FechaEmisionIR", "fecha_emision"))
        if tipo_doc == "" or fecha == "":
            continue

        info = ET.SubElement(root, "InformacionReferencia")
        _add_if(info, "TipoDocIR", tipo_doc)

        if tipo_doc == "99":
            _add_from(item, info, "TipoDocRefOTRO", "tipoDocOtro", "TipoDocRefOTRO", "tipoDocRefOtro")

        _add_from(item, info, "Numero", "numero", "Numero")
        _add_if(info, "FechaEmisionIR", fecha)

        codigo_ref = _safe_text(_pick(item, "codigo", "Codigo"))
        if codigo_ref:
            _add_if(info, "Codigo", codigo_ref)
            if codigo_ref == "99":
                _add_from(item, info, "CodigoReferenciaOTRO", "codigoOtro", "CodigoReferenciaOTRO")

        _add_from(item, info, "Razon", "razon", "Razon")


def _append_otro_texto(otros: ET.Element, item: Any) -> None:
    if isinstance(item, dict):
        text = _safe_text(_pick(item, "texto", "Texto"))
        codigo = _safe_text(_pick(item, "codigo", "Codigo"))
    else:
        text = _safe_text(item)
        codigo = ""

    if text == "":
        return

    node = ET.SubElement(otros, "OtroTexto")
    if codigo:
        node.set("codigo", codigo)
    node.text = text


def _append_otro_contenido(otros: ET.Element, item: Any) -> None:
    if not isinstance(item, dict):
        text = _safe_text(item)
        if text == "":
            return
        ET.SubElement(otros, "OtroContenido").text = text
        return

    codigo = _safe_text(_pick(item, "codigo", "Codigo"))
    contenido = _pick(item, "contenidoEstructurado", "contenido", "Contenido")
    if isinstance(contenido, (dict, list)):
        text = json.dumps(contenido, ensure_ascii=False)
    else:
        text = _safe_text(contenido)
    if text == "":
        return

    node = ET.SubElement(otros, "OtroContenido")
    if codigo:
        node.set("codigo", codigo)
    node.text = text


def _build_otros(root: ET.Element, params: dict[str, str]) -> None:
    raw = params.get("otros")
    parsed = _parse_json(raw)
    otros = ET.Element("Otros")

    if isinstance(parsed, dict):
        otro_texto = parsed.get("otroTexto")
        if isinstance(otro_texto, list):
            for item in otro_texto:
                _append_otro_texto(otros, item)
        elif otro_texto is not None:
            _append_otro_texto(otros, otro_texto)

        otro_contenido = parsed.get("otroContenido")
        if isinstance(otro_contenido, list):
            for item in otro_contenido:
                _append_otro_contenido(otros, item)
        elif otro_contenido is not None:
            _append_otro_contenido(otros, otro_contenido)

    elif parsed is not None:
        _append_otro_texto(otros, parsed)
    else:
        raw_text = _safe_text(raw)
        if raw_text:
            _append_otro_texto(otros, raw_text)

    root.append(otros)


def _build_mr(root: ET.Element, params: dict[str, str]) -> None:
    _add_if(root, "Clave", params.get("clave"))
    _add_if(root, "NumeroCedulaEmisor", _pad_left(params.get("numero_cedula_emisor"), 12))
    _add_if(root, "FechaEmisionDoc", params.get("fecha_emision_doc"))
    _add_if(root, "Mensaje", params.get("mensaje"))
    _add_if(root, "DetalleMensaje", params.get("detalle_mensaje"))
    _add_if(root, "MontoTotalImpuesto", params.get("monto_total_impuesto"))
    _add_if(root, "CodigoActividad", _pad_left(params.get("codigo_actividad"), 6))
    _add_if(root, "TotalFactura", params.get("total_factura"))
    _add_if(root, "NumeroCedulaReceptor", _pad_left(params.get("numero_cedula_receptor"), 12))
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
        if route != "gen_xml_rep":
            _add_if(root, "CodigoActividadEmisor", _pad_left(params.get("codigo_actividad_emisor"), 6))

        if route in ROUTES_WITH_CODIGO_ACTIVIDAD_RECEPTOR:
            _add_if(root, "CodigoActividadReceptor", _pad_left(params.get("codigo_actividad_receptor"), 6))

        _add_if(root, "NumeroConsecutivo", params.get("consecutivo"))
        _add_if(root, "FechaEmision", params.get("fecha_emision"))

        _build_emisor(root, params, route)
        _build_receptor(root, params, route)
        _build_condiciones(root, params, route)
        if route == "gen_xml_rep":
            _build_detalle_servicio_rep(root, params)
        else:
            _build_detalle_servicio(root, params)
            _build_otros_cargos(root, params)
        _build_resumen(root, params, route)
        _build_informacion_referencia(root, params)
        if route != "gen_xml_rep":
            _build_otros(root, params)

    xml_bytes = ET.tostring(root, encoding="utf-8", xml_declaration=True)
    return base64.b64encode(xml_bytes).decode("utf-8")


def _build_response(route: str, params: dict[str, str]) -> dict[str, str]:
    clave = _safe_text(params.get("clave"))
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


async def gen_xml_rep(request: Request, params: dict[str, str]) -> Response:
    _ = request
    return tools_reply_compatible(_build_response("gen_xml_rep", params))


async def test(request: Request, params: dict[str, str]) -> Response:
    _ = request
    _ = params
    return tools_reply_compatible("Esto es un test")
