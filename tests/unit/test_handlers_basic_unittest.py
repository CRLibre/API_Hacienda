from __future__ import annotations

import base64
import json
import tempfile
import time
import unittest
from pathlib import Path
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

import httpx
from starlette.requests import Request

from python_api.handlers import callback, clave, crypto, ejemplo, geoloc, make_json, make_qr, send, sign_xml_legacy
from python_api.handlers import token as token_handler
from python_api.handlers import version as version_handler


def _request_with_body(body: bytes, content_type: str = "application/json") -> Request:
    scope = {
        "type": "http",
        "http_version": "1.1",
        "method": "POST",
        "scheme": "http",
        "path": "/api.php",
        "raw_path": b"/api.php",
        "query_string": b"",
        "headers": [
            (b"content-type", content_type.encode("utf-8")),
        ],
        "client": ("127.0.0.1", 9999),
        "server": ("testserver", 80),
    }
    sent = False

    async def receive():
        nonlocal sent
        if sent:
            return {"type": "http.request", "body": b"", "more_body": False}
        sent = True
        return {"type": "http.request", "body": body, "more_body": False}

    return Request(scope, receive)


class HandlerBasicsAsyncTest(unittest.IsolatedAsyncioTestCase):
    async def test_ejemplo_routes(self) -> None:
        hola = await ejemplo.hola(None, {})
        self.assertEqual(hola.status_code, 200)
        self.assertIn("hola", hola.body.decode("utf-8"))

        un_usuario = await ejemplo.un_usuario(None, {"nombre": "Ana", "apellido": "Lopez"})
        self.assertEqual(un_usuario.status_code, 200)
        self.assertIn("Ana, Lopez", un_usuario.body.decode("utf-8"))

    async def test_crypto_route_access_denied(self) -> None:
        result = await crypto.encrypt(None, {})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(result.status_code, 403)
        self.assertEqual(payload["status"], "error")

    async def test_make_json_payload_shape(self) -> None:
        result = await make_json.makeJson(
            None,
            {
                "clave": "k1",
                "fecha": "2026-02-12",
                "emi_tipoIdentificacion": "01",
                "emi_numeroIdentificacion": "123",
                "recp_tipoIdentificacion": "01",
                "recp_numeroIdentificacion": "456",
                "comprobanteXml": "<xml/>",
            },
        )
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"]["clave"], "k1")
        self.assertEqual(payload["resp"]["emisor"]["numeroIdentificacion"], "123")

    async def test_make_qr_fallback_to_base64_text(self) -> None:
        with patch("python_api.handlers.make_qr._make_qr_png", return_value=None):
            result = await make_qr.makeQR(None, {"string": "abc"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], base64.b64encode(b"abc").decode("utf-8"))

    async def test_clave_happy_path(self) -> None:
        fake_now = time.struct_time((2026, 2, 12, 11, 0, 0, 3, 43, -1))
        with patch("python_api.handlers.clave.time.localtime", return_value=fake_now):
            result = await clave.clave(
                None,
                {
                    "tipoDocumento": "FE",
                    "tipoCedula": "01",
                    "cedula": "115970791",
                    "situacion": "normal",
                    "codigoPais": "506",
                    "consecutivo": "1",
                    "codigoSeguridad": "12345678",
                    "sucursal": "1",
                    "terminal": "1",
                },
            )
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("clave", payload["resp"])
        self.assertEqual(payload["resp"]["length"], 50)

    async def test_clave_invalid_cedula(self) -> None:
        result = await clave.clave(
            None,
            {
                "tipoDocumento": "FE",
                "tipoCedula": "01",
                "cedula": "abc",
                "situacion": "normal",
                "consecutivo": "1",
                "codigoSeguridad": "12345678",
            },
        )
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("cedula no es numeral", payload["resp"])

    async def test_callback_accepts_invalid_json_and_returns_202_payload(self) -> None:
        request = _request_with_body(b"{broken", "application/json")
        result = await callback.callback(request, {"idUser": "1"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(result.status_code, 200)
        self.assertEqual(payload["resp"], 202)

    async def test_sign_xml_invalid_doc_type(self) -> None:
        result = await sign_xml_legacy.signFE(None, {"tipodoc": "XYZ"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("No se encuentra tipo de documento", payload["resp"])

    async def test_version_file_fallback(self) -> None:
        with tempfile.TemporaryDirectory() as td:
            version_file = Path(td) / "VERSION"
            version_file.write_text("abc1234", encoding="utf-8")
            with patch("python_api.handlers.version._git_version", return_value=None):
                with patch("python_api.handlers.version.VERSION_FILE", version_file):
                    result = await version_handler.version(None, {})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("abc1234", payload["resp"])


class HandlerBasicsSyncTest(unittest.TestCase):
    def test_token_url_mapping(self) -> None:
        self.assertIn("rut-stag", token_handler._token_url("api-stag"))  # noqa: SLF001
        self.assertIn("realms/rut", token_handler._token_url("api-prod"))  # noqa: SLF001
        self.assertIsNone(token_handler._token_url("x"))  # noqa: SLF001

    def test_token_payload_validation_missing_values(self) -> None:
        payload, error = token_handler._payload_for_grant(  # noqa: SLF001
            {"grant_type": "password", "client_id": "", "username": "u", "password": "p"}
        )
        self.assertIsNone(payload)
        self.assertIn("Client ID", str(error))

    def test_send_recepcion_url_and_raw_http_lines(self) -> None:
        self.assertIsNone(send._recepcion_url("x"))  # noqa: SLF001
        self.assertIn("sandbox", send._recepcion_url("api-stag"))  # noqa: SLF001
        request = httpx.Request("POST", "https://example.test")
        response = httpx.Response(202, request=request, text="ok", headers={"x-one": "1"})
        lines = send._raw_http_lines(response)  # noqa: SLF001
        self.assertTrue(lines[0].startswith("HTTP/"))
        self.assertIn("ok", "\n".join(lines))

    def test_geoloc_ip_conversion_helpers(self) -> None:
        value = geoloc._ip_to_int("10.0.0.1")  # noqa: SLF001
        self.assertEqual(value, 167772161)
        self.assertEqual(geoloc._int_to_ip(value), "10.0.0.1")  # noqa: SLF001
        self.assertIsNone(geoloc._ip_to_int("999.1.1.1"))  # noqa: SLF001


if __name__ == "__main__":
    unittest.main()
