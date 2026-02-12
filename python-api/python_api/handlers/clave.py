from __future__ import annotations

import time

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _is_digits(value: str) -> bool:
    return value.isdigit()


async def clave(_: Request, params: dict[str, str]) -> JSONResponse:
    tipo_doc = str(params.get("tipoDocumento", ""))
    tipo_cedula = str(params.get("tipoCedula", ""))
    cedula = str(params.get("cedula", ""))
    situacion = str(params.get("situacion", ""))
    codigo_pais = str(params.get("codigoPais", "506"))
    consecutivo = str(params.get("consecutivo", ""))
    codigo_seguridad = str(params.get("codigoSeguridad", ""))
    sucursal = str(params.get("sucursal", "001"))
    terminal = str(params.get("terminal", "00001"))

    if not _is_digits(cedula):
        return tools_reply_compatible("El parametro cedula no es numeral")

    if not _is_digits(codigo_pais):
        return tools_reply_compatible("El parametro codigoPais no es numeral")
    if len(codigo_pais) != 3:
        return tools_reply_compatible("El parametro codigoPais debe ser de 3 digitos")

    if not _is_digits(sucursal):
        return tools_reply_compatible("El parametro sucursal no es numeral")
    if len(sucursal) < 3:
        sucursal = sucursal.zfill(3)
    elif len(sucursal) > 3:
        return tools_reply_compatible("El parametro sucursal debe ser de 3 digitos")

    if not _is_digits(terminal):
        return tools_reply_compatible("El parametro terminal no es numeral")
    if len(terminal) < 5:
        terminal = terminal.zfill(5)
    elif len(terminal) > 5:
        return tools_reply_compatible("El parametro terminal debe ser de 5 digitos")

    if not _is_digits(consecutivo):
        return tools_reply_compatible("El parametro consecutivo no es numeral")
    if len(consecutivo) < 10:
        consecutivo = consecutivo.zfill(10)
    elif len(consecutivo) > 10:
        return tools_reply_compatible("El parametro consecutivo debe ser de 10 digitos")

    if not _is_digits(codigo_seguridad):
        return tools_reply_compatible("El parametro codigoSeguridad no es numeral")
    if len(codigo_seguridad) < 8:
        codigo_seguridad = codigo_seguridad.zfill(8)
    elif len(codigo_seguridad) > 8:
        return tools_reply_compatible("El parametro codigoSeguridad debe ser de 8 digitos")

    tipos = {
        "FE": "01",
        "ND": "02",
        "NC": "03",
        "TE": "04",
        "CCE": "05",
        "CPCE": "06",
        "RCE": "07",
        "FEC": "08",
        "FEE": "09",
    }
    tipo_documento = tipos.get(tipo_doc)
    if tipo_documento is None:
        return tools_reply_compatible(f"No se encuentra el tipo de documento [{tipo_doc}]")

    consecutivo_final = f"{sucursal}{terminal}{tipo_documento}{consecutivo}"

    identificacion: str | None = None
    if tipo_cedula in {"fisico", "01"}:
        identificacion = cedula.zfill(12)
    elif tipo_cedula in {"juridico", "02"}:
        if len(cedula) < 12:
            identificacion = cedula.zfill(12)
        elif len(cedula) == 12:
            identificacion = cedula
        else:
            return tools_reply_compatible("cedula juridico incorrecto")
    elif tipo_cedula in {"dimex", "03"}:
        if len(cedula) < 12:
            identificacion = cedula.zfill(12)
        elif len(cedula) == 12:
            identificacion = cedula
        else:
            return tools_reply_compatible("dimex incorrecto")
    elif tipo_cedula in {"nite", "04"}:
        identificacion = cedula.zfill(12)
    else:
        return tools_reply_compatible("No se encuentra tipo de cedula")

    situaciones = {"normal": 1, "contingencia": 2, "sininternet": 3}
    cod_situacion = situaciones.get(situacion.lower())
    if cod_situacion is None:
        return tools_reply_compatible(f"No se encuentra el tipo de situacion [{situacion}]")

    now = time.localtime()
    dia = f"{now.tm_mday:02d}"
    mes = f"{now.tm_mon:02d}"
    ano = f"{now.tm_year % 100:02d}"
    clave_valor = f"{codigo_pais}{dia}{mes}{ano}{identificacion}{consecutivo_final}{cod_situacion}{codigo_seguridad}"

    return tools_reply_compatible(
        {
            "clave": clave_valor,
            "consecutivo": consecutivo_final,
            "length": len(clave_valor),
        }
    )

