#!/usr/bin/env python3
from __future__ import annotations

import argparse
import base64
import json
import os
import warnings
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import httpx
from cryptography.hazmat.primitives.serialization import Encoding, pkcs12
from cryptography.utils import CryptographyDeprecationWarning
from lxml import etree


def _now_cr_iso() -> str:
    # Keep local Costa Rica style offset used across current fixtures/scripts.
    return datetime.now(timezone.utc).astimezone().replace(microsecond=0).isoformat()


def _mask(value: str, keep: int = 6) -> str:
    if len(value) <= keep:
        return "*" * len(value)
    return value[:keep] + "*" * (len(value) - keep)


def _envelope_resp(payload: dict[str, Any]) -> Any:
    if payload.get("status") != "ok":
        return None
    return payload.get("resp")


def _call_api(api_base_url: str, params: dict[str, str], timeout: float) -> tuple[int, dict[str, Any]]:
    with httpx.Client(timeout=timeout, verify=False) as client:
        response = client.post(f"{api_base_url.rstrip('/')}/api.php", data=params)

    try:
        body = response.json()
    except Exception:
        body = {"status": "error", "resp": response.text}
    return response.status_code, body


def _build_detalles() -> str:
    payload = [
        {
            "codigoCABYS": "8512000000000",
            "cantidad": "1.00",
            "unidadMedida": "Sp",
            "detalle": "Servicio profesional",
            "precioUnitario": "1000.00",
            "montoTotal": "1000.00",
            "subTotal": "1000.00",
            "baseImponible": "1000.00",
            "impuesto": [
                {
                    "codigo": "01",
                    "codigoTarifa": "08",
                    "tarifa": "13.00",
                    "monto": "130.00",
                }
            ],
            "impuestoAsumidoEmisorFabrica": "0.00",
            "impuestoNeto": "130.00",
            "montoTotalLinea": "1130.00",
        }
    ]
    return json.dumps(payload, ensure_ascii=False)


def _build_medios_pago() -> str:
    return json.dumps([{"tipoMedioPago": "06", "totalMedioPago": "1130.00"}], ensure_ascii=False)


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


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Flujo real-dev FE local: token -> clave -> genXML -> firmar -> check -> send -> consultar."
    )
    parser.add_argument("--api-base-url", default="http://127.0.0.1:8080", help="URL base del servicio Python local.")
    parser.add_argument("--timeout", type=float, default=30.0, help="Timeout HTTP en segundos.")

    parser.add_argument("--client-id", default=os.getenv("API_HACIENDA_CLIENT_ID", "api-stag"))
    parser.add_argument("--username", default=os.getenv("API_HACIENDA_USERNAME", ""))
    parser.add_argument("--password", default=os.getenv("API_HACIENDA_PASSWORD", ""))
    parser.add_argument("--client-secret", default=os.getenv("API_HACIENDA_CLIENT_SECRET", ""))
    parser.add_argument("--access-token", default=os.getenv("API_HACIENDA_ACCESS_TOKEN", ""))

    parser.add_argument("--p12-path", default=os.getenv("API_HACIENDA_P12_PATH", ""))
    parser.add_argument("--p12-pin", default=os.getenv("API_HACIENDA_P12_PIN", ""))

    parser.add_argument("--tipo-documento", default="FE", help="FE/NC/ND/TE/FEC/FEE (para clave).")
    parser.add_argument("--tipo-cedula", default=os.getenv("API_HACIENDA_TIPO_CEDULA", "02"))
    parser.add_argument("--cedula", default=os.getenv("API_HACIENDA_CEDULA", ""))
    parser.add_argument("--codigo-seguridad", default=os.getenv("API_HACIENDA_CODIGO_SEGURIDAD", "12345678"))
    parser.add_argument("--codigo-pais", default=os.getenv("API_HACIENDA_CODIGO_PAIS", "506"))
    parser.add_argument("--consecutivo", default=os.getenv("API_HACIENDA_CONSECUTIVO", "1"))
    parser.add_argument("--sucursal", default=os.getenv("API_HACIENDA_SUCURSAL", "1"))
    parser.add_argument("--terminal", default=os.getenv("API_HACIENDA_TERMINAL", "1"))

    parser.add_argument("--proveedor-sistemas", default=os.getenv("API_HACIENDA_PROVEEDOR_SISTEMAS", ""))
    parser.add_argument("--codigo-actividad-emisor", default=os.getenv("API_HACIENDA_CODIGO_ACTIVIDAD_EMISOR", "011101"))
    parser.add_argument("--emisor-nombre", default=os.getenv("API_HACIENDA_EMISOR_NOMBRE", "Emisor Dev"))
    parser.add_argument("--emisor-email", default=os.getenv("API_HACIENDA_EMISOR_EMAIL", "facturacion@example.com"))

    parser.add_argument("--receptor-tipo-identif", default=os.getenv("API_HACIENDA_RECEPTOR_TIPO_IDENTIF", "01"))
    parser.add_argument("--receptor-num-identif", default=os.getenv("API_HACIENDA_RECEPTOR_NUM_IDENTIF", "123456789"))
    parser.add_argument("--receptor-nombre", default=os.getenv("API_HACIENDA_RECEPTOR_NOMBRE", "Cliente Prueba"))
    parser.add_argument("--receptor-email", default=os.getenv("API_HACIENDA_RECEPTOR_EMAIL", "cliente@example.com"))

    parser.add_argument("--skip-send", action="store_true", help="No enviar a Hacienda.")
    parser.add_argument("--skip-consultar", action="store_true", help="No consultar estado en Hacienda.")
    return parser.parse_args()


def main() -> int:
    args = parse_args()

    if not args.cedula:
        print("ERROR: falta cedula. Definí --cedula o env API_HACIENDA_CEDULA.")
        return 2
    if not args.proveedor_sistemas:
        print("ERROR: falta proveedor de sistemas. Definí --proveedor-sistemas o env API_HACIENDA_PROVEEDOR_SISTEMAS.")
        return 2
    if not args.p12_path or not args.p12_pin:
        print("ERROR: faltan datos de firma. Definí --p12-path/--p12-pin (o env API_HACIENDA_P12_PATH/API_HACIENDA_P12_PIN).")
        return 2

    p12_path = Path(args.p12_path).expanduser().resolve()
    if not p12_path.is_file():
        print(f"ERROR: no existe p12: {p12_path}")
        return 2

    access_token = str(args.access_token or "")
    if not args.skip_send:
        if access_token:
            print(f"1) token: usando token provisto ({_mask(access_token)})")
        else:
            if not args.username or not args.password:
                print(
                    "ERROR: faltan credenciales para token. Definí --username/--password o --access-token."
                )
                return 2
            print("1) token/gettoken")
            token_params = {
                "w": "token",
                "r": "gettoken",
                "client_id": args.client_id,
                "grant_type": "password",
                "username": args.username,
                "password": args.password,
            }
            if args.client_secret:
                token_params["client_secret"] = args.client_secret

            code, body = _call_api(args.api_base_url, token_params, args.timeout)
            token_resp = _envelope_resp(body)
            if code != 200 or not isinstance(token_resp, dict) or not token_resp.get("access_token"):
                print("ERROR token/gettoken:", json.dumps(body, ensure_ascii=False, indent=2))
                return 1
            access_token = str(token_resp["access_token"])
            print(f"   OK token: { _mask(access_token) }")
    else:
        print("1) token: omitido (--skip-send)")

    print("2) clave/clave")
    clave_params = {
        "w": "clave",
        "r": "clave",
        "tipoDocumento": args.tipo_documento,
        "tipoCedula": args.tipo_cedula,
        "cedula": args.cedula,
        "codigoPais": args.codigo_pais,
        "situacion": "normal",
        "codigoSeguridad": args.codigo_seguridad,
        "consecutivo": args.consecutivo,
        "sucursal": args.sucursal,
        "terminal": args.terminal,
    }
    code, body = _call_api(args.api_base_url, clave_params, args.timeout)
    clave_resp = _envelope_resp(body)
    if code != 200 or not isinstance(clave_resp, dict) or not clave_resp.get("clave") or not clave_resp.get("consecutivo"):
        print("ERROR clave/clave:", json.dumps(body, ensure_ascii=False, indent=2))
        return 1
    clave = str(clave_resp["clave"])
    consecutivo = str(clave_resp["consecutivo"])
    print(f"   OK clave: {clave}")

    print("3) genXML/gen_xml_fe")
    now_iso = _now_cr_iso()
    genxml_params = {
        "w": "genXML",
        "r": "gen_xml_fe",
        "clave": clave,
        "proveedor_sistemas": args.proveedor_sistemas,
        "codigo_actividad_emisor": args.codigo_actividad_emisor,
        "consecutivo": consecutivo,
        "fecha_emision": now_iso,
        "emisor_nombre": args.emisor_nombre,
        "emisor_tipo_identif": args.tipo_cedula,
        "emisor_num_identif": args.cedula,
        "emisor_email": args.emisor_email,
        "emisor_provincia": "1",
        "emisor_canton": "01",
        "emisor_distrito": "01",
        "emisor_otras_senas": "San Jose",
        "receptor_nombre": args.receptor_nombre,
        "receptor_tipo_identif": args.receptor_tipo_identif,
        "receptor_num_identif": args.receptor_num_identif,
        "receptor_email": args.receptor_email,
        "condicion_venta": "01",
        "detalles": _build_detalles(),
        "medios_pago": _build_medios_pago(),
        "cod_moneda": "CRC",
        "tipo_cambio": "1",
        "total_ventas": "1000.00",
        "total_ventas_neta": "1000.00",
        "total_impuestos": "130.00",
        "total_comprobante": "1130.00",
    }
    code, body = _call_api(args.api_base_url, genxml_params, args.timeout)
    genxml_resp = _envelope_resp(body)
    if code != 200 or not isinstance(genxml_resp, dict) or not genxml_resp.get("xml"):
        print("ERROR genXML/gen_xml_fe:", json.dumps(body, ensure_ascii=False, indent=2))
        return 1
    xml_unsigned_b64 = str(genxml_resp["xml"])
    print("   OK XML FE generado")

    print("4) firmar XML (local p12)")
    xml_unsigned = base64.b64decode(xml_unsigned_b64)
    p12_bytes = p12_path.read_bytes()
    xml_signed = _sign_xml_with_p12(p12_bytes=p12_bytes, pin=args.p12_pin, xml_bytes=xml_unsigned)
    xml_signed_b64 = base64.b64encode(xml_signed).decode("utf-8")
    print("   OK XML firmado")

    print("5) check/checkxml (XSD local)")
    check_params = {
        "w": "check",
        "r": "checkxml",
        "tipoDocumento": "FE",
        # FE check route validates against no-sign schema first.
        "xml": xml_unsigned_b64,
    }
    code, body = _call_api(args.api_base_url, check_params, args.timeout)
    check_resp = _envelope_resp(body)
    if code != 200 or check_resp != "validated":
        print("ERROR check/checkxml:", json.dumps(body, ensure_ascii=False, indent=2))
        return 1
    print("   OK XML validado")

    if args.skip_send:
        print("6) send/json omitido (--skip-send)")
        print("Flujo local completado hasta validación XSD.")
        return 0

    print("6) send/json (Hacienda)")
    send_params = {
        "w": "send",
        "r": "json",
        "client_id": args.client_id,
        "token": access_token,
        "clave": clave,
        "fecha": now_iso,
        "emi_tipoIdentificacion": args.tipo_cedula,
        "emi_numeroIdentificacion": args.cedula,
        "recp_tipoIdentificacion": args.receptor_tipo_identif,
        "recp_numeroIdentificacion": args.receptor_num_identif,
        "comprobanteXml": xml_signed_b64,
    }
    code, body = _call_api(args.api_base_url, send_params, args.timeout)
    send_resp = _envelope_resp(body)
    if code != 200:
        print("ERROR send/json:", json.dumps(body, ensure_ascii=False, indent=2))
        return 1
    print("   send response:", json.dumps(send_resp, ensure_ascii=False))

    if args.skip_consultar:
        print("7) consultar/consultarCom omitido (--skip-consultar)")
        return 0

    print("7) consultar/consultarCom")
    consultar_params = {
        "w": "consultar",
        "r": "consultarCom",
        "client_id": args.client_id,
        "token": access_token,
        "clave": clave,
    }
    code, body = _call_api(args.api_base_url, consultar_params, args.timeout)
    consultar_resp = _envelope_resp(body)
    if code != 200:
        print("ERROR consultar/consultarCom:", json.dumps(body, ensure_ascii=False, indent=2))
        return 1
    print("   consultar response:", json.dumps(consultar_resp, ensure_ascii=False))

    print("OK: flujo real-dev FE ejecutado.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
