#!/usr/bin/env python3
from __future__ import annotations

import argparse
import asyncio
import base64
import importlib.util
import json
import os
import sys
from datetime import datetime, timedelta, timezone
from pathlib import Path

from cryptography import x509
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.hazmat.primitives.serialization import pkcs12
from cryptography.x509.oid import NameOID


def _load_module(name: str, module_path: Path):
    spec = importlib.util.spec_from_file_location(name, str(module_path))
    if spec is None or spec.loader is None:
        raise RuntimeError(f"No se pudo cargar módulo: {module_path}")
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


def _build_rep_params() -> dict[str, str]:
    now = datetime.now(timezone(timedelta(hours=-6))).replace(microsecond=0)
    ref = (now - timedelta(days=1)).replace(microsecond=0)
    return {
        "clave": "50612022600310112345600100001010000000001123456789",
        "proveedor_sistemas": "3101123456",
        "consecutivo": "00100001010000000001",
        "fecha_emision": now.isoformat(),
        "emisor_nombre": "Mi Empresa de Prueba",
        "emisor_tipo_identif": "02",
        "emisor_num_identif": "3101123456",
        "emisor_email": "facturacion@empresa.cr",
        "receptor_nombre": "Cliente Demo",
        "receptor_tipo_identif": "01",
        "receptor_num_identif": "123456789",
        "receptor_email": "cliente@example.com",
        "condicion_venta": "09",
        "detalles": json.dumps(
            [
                {
                    "detalle": "Pago parcial comprobante",
                    "montoTotal": "113.00",
                    "subTotal": "100.00",
                    "impuesto": [{"codigo": "01", "codigoTarifa": "08", "tarifa": "13.00", "monto": "13.00"}],
                    "impuestoNeto": "13.00",
                    "montoTotalLinea": "113.00",
                }
            ]
        ),
        "medios_pago": json.dumps([{"tipoMedioPago": "06", "totalMedioPago": "113.00"}]),
        "cod_moneda": "CRC",
        "tipo_cambio": "1",
        "total_ventas": "113.00",
        "total_ventas_neta": "113.00",
        "total_impuestos": "13.00",
        "total_comprobante": "126.00",
        "informacion_referencia": json.dumps(
            [
                {
                    "tipoDoc": "01",
                    "numero": "50611022600310112345600100001010000000001123456789",
                    "fechaEmision": ref.isoformat(),
                    "codigo": "04",
                    "razon": "Pago de comprobante previo",
                }
            ]
        ),
    }


def _make_test_p12(pin: str) -> bytes:
    key = rsa.generate_private_key(public_exponent=65537, key_size=2048)
    subject = issuer = x509.Name(
        [
            x509.NameAttribute(NameOID.COUNTRY_NAME, "CR"),
            x509.NameAttribute(NameOID.ORGANIZATION_NAME, "API Hacienda Python Migration"),
            x509.NameAttribute(NameOID.COMMON_NAME, "rep-flow-smoke.local"),
        ]
    )
    now = datetime.now(timezone.utc)
    cert = (
        x509.CertificateBuilder()
        .subject_name(subject)
        .issuer_name(issuer)
        .public_key(key.public_key())
        .serial_number(x509.random_serial_number())
        .not_valid_before(now - timedelta(days=1))
        .not_valid_after(now + timedelta(days=30))
        .add_extension(x509.BasicConstraints(ca=False, path_length=None), critical=True)
        .sign(key, hashes.SHA256())
    )
    return pkcs12.serialize_key_and_certificates(
        name=b"rep-flow-smoke",
        key=key,
        cert=cert,
        cas=None,
        encryption_algorithm=serialization.BestAvailableEncryption(pin.encode("utf-8")),
    )


def _parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Smoke de flujo REP v4.4 (genXML -> firmar -> check -> send dry-run).")
    parser.add_argument(
        "--python-api-root",
        default="/Users/juandi/Documents/github/API_Hacienda/migration/python-api",
        help="Ruta absoluta al folder migration/python-api",
    )
    parser.add_argument(
        "--out-signed-xml",
        default="",
        help="Ruta opcional para guardar el XML firmado (debug).",
    )
    return parser.parse_args()


def main() -> int:
    args = _parse_args()
    python_api_root = Path(args.python_api_root).resolve()
    handlers_root = python_api_root / "python_api" / "handlers"

    if not handlers_root.is_dir():
        print(f"ERROR: no existe handlers root: {handlers_root}")
        return 1

    os.environ.setdefault("API_HACIENDA_DATABASE_URL", "sqlite:///:memory:")
    os.environ.setdefault("API_HACIENDA_PHP_FALLBACK_URL", "")
    sys.path.insert(0, str(python_api_root))

    genxml = _load_module("genxml_rep_smoke", handlers_root / "genxml.py")
    firmar = _load_module("firmar_rep_smoke", handlers_root / "firmar_xml.py")
    check = _load_module("check_rep_smoke", handlers_root / "check.py")
    send = _load_module("send_rep_smoke", handlers_root / "send.py")

    rep_params = _build_rep_params()
    xml_unsigned_b64 = genxml._build_document("gen_xml_rep", rep_params)
    xml_unsigned = base64.b64decode(xml_unsigned_b64)

    pin = "1234"
    p12 = _make_test_p12(pin)
    xml_signed = firmar._sign_xml_with_p12(p12_bytes=p12, pin=pin, xml_bytes=xml_unsigned)
    xml_signed_b64 = base64.b64encode(xml_signed).decode("utf-8")

    validation_direct = check._validate_document("REP", {"xml": xml_signed_b64})
    if validation_direct != "validated":
        print("ERROR: validación directa REP falló")
        print(str(validation_direct))
        return 1

    check_response = asyncio.run(check.checkxml(None, {"tipoDocumento": "REP", "xml": xml_signed_b64}))
    check_payload = json.loads(check_response.body.decode("utf-8"))
    if check_payload.get("status") != "ok" or check_payload.get("resp") != "validated":
        print("ERROR: checkxml REP no devolvió validated")
        print(json.dumps(check_payload, ensure_ascii=False, indent=2))
        return 1

    send_response = asyncio.run(
        send.json(
            None,
            {
                "token": "dry-run-token",
                "client_id": "dry-run",
                "clave": rep_params["clave"],
                "fecha": rep_params["fecha_emision"],
                "emi_tipoIdentificacion": rep_params["emisor_tipo_identif"],
                "emi_numeroIdentificacion": rep_params["emisor_num_identif"],
                "recp_tipoIdentificacion": rep_params["receptor_tipo_identif"],
                "recp_numeroIdentificacion": rep_params["receptor_num_identif"],
                "comprobanteXml": xml_signed_b64,
            },
        )
    )
    send_payload = json.loads(send_response.body.decode("utf-8"))
    if "No URL set!" not in json.dumps(send_payload, ensure_ascii=False):
        print("ERROR: send/json dry-run no devolvió respuesta esperada de URL faltante")
        print(json.dumps(send_payload, ensure_ascii=False, indent=2))
        return 1

    if args.out_signed_xml:
        out_path = Path(args.out_signed_xml).expanduser().resolve()
        out_path.parent.mkdir(parents=True, exist_ok=True)
        out_path.write_bytes(xml_signed)
        print(f"XML firmado guardado en: {out_path}")

    print("OK: flujo REP v4.4 verificado")
    print("- genXML REP: generado")
    print("- firmar XML: generado con P12 temporal")
    print("- checkxml REP: validated")
    print("- send/json: dry-run sin red (No URL set!)")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
