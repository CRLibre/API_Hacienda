from __future__ import annotations

import base64
import json
import unittest
from unittest.mock import AsyncMock, patch

from tests.support import bootstrap  # noqa: F401

import httpx

from python_api.handlers import check, consultar, send
from python_api.handlers import token as token_handler


class HandlerNetworkAndCheckAsyncTest(unittest.IsolatedAsyncioTestCase):
    async def test_checkxml_unknown_tipo_documento(self) -> None:
        response = await check.checkxml(None, {"tipoDocumento": "XXX"})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("No se encuentra tipo de documento", payload["resp"])

    async def test_token_call_validation_and_unknown_client(self) -> None:
        validation = await token_handler._token_call(  # noqa: SLF001
            {"grant_type": "password", "client_id": "", "username": "u", "password": "p"}
        )
        validation_payload = json.loads(validation.body.decode("utf-8"))
        self.assertIn("Client ID", validation_payload["resp"])

        unknown_client = await token_handler._token_call(  # noqa: SLF001
            {"grant_type": "password", "client_id": "x", "username": "u", "password": "p"}
        )
        unknown_payload = json.loads(unknown_client.body.decode("utf-8"))
        self.assertIsNone(unknown_payload["resp"])

    async def test_token_call_http_success_and_json_error(self) -> None:
        class _Client:
            def __init__(self, **_kwargs):
                pass

            async def __aenter__(self):
                return self

            async def __aexit__(self, *_exc):
                return False

            async def post(self, *_args, **_kwargs):
                request = httpx.Request("POST", "https://x")
                return httpx.Response(200, request=request, json={"access_token": "abc"})

        params = {
            "grant_type": "password",
            "client_id": "api-stag",
            "client_secret": "s",
            "username": "u",
            "password": "p",
        }
        with patch("python_api.handlers.token.httpx.AsyncClient", _Client):
            response = await token_handler._token_call(params)  # noqa: SLF001
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["resp"]["access_token"], "abc")

    async def test_consultar_error_paths(self) -> None:
        no_clave = await consultar.consultarCom(None, {"clave": "", "client_id": "api-stag"})
        self.assertIn("clave no puede ser en blanco", no_clave.body.decode("utf-8"))

        bad_client = await consultar.consultarCom(None, {"clave": "123", "client_id": "x"})
        self.assertIn("client_id", bad_client.body.decode("utf-8"))

    async def test_send_post_to_hacienda_paths(self) -> None:
        no_url = await send._post_to_hacienda(api_to="x", token="t", message="{}")  # noqa: SLF001
        self.assertIn("No URL set", no_url.body.decode("utf-8"))

        class _BrokenClient:
            def __init__(self, **_kwargs):
                pass

            async def __aenter__(self):
                return self

            async def __aexit__(self, *_exc):
                return False

            async def post(self, *_args, **_kwargs):
                raise RuntimeError("boom")

        with patch("python_api.handlers.send.httpx.AsyncClient", _BrokenClient):
            broken = await send._post_to_hacienda(api_to="api-stag", token="t", message="{}")  # noqa: SLF001
        self.assertIn("boom", broken.body.decode("utf-8"))

    async def test_send_json_payload_building(self) -> None:
        captured: dict[str, str] = {}

        async def _fake_post_to_hacienda(*, api_to: str, token: str, message: str):
            captured["api_to"] = api_to
            captured["token"] = token
            captured["message"] = message
            return send.tools_reply_compatible({"ok": True})

        params = {
            "client_id": "api-stag",
            "token": "abc",
            "clave": "k",
            "fecha": "2026-02-12",
            "emi_tipoIdentificacion": "01",
            "emi_numeroIdentificacion": "123",
            "recp_tipoIdentificacion": "",
            "recp_numeroIdentificacion": "",
            "comprobanteXml": "<xml/>",
            "callbackUrl": "",
        }
        with patch("python_api.handlers.send._post_to_hacienda", new=AsyncMock(side_effect=_fake_post_to_hacienda)):
            response = await send.json(None, params)

        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        sent_payload = json.loads(captured["message"])
        self.assertNotIn("callbackUrl", sent_payload)
        self.assertNotIn("receptor", sent_payload)


class HandlerNetworkAndCheckSyncTest(unittest.TestCase):
    def test_check_decode_helpers(self) -> None:
        valid = check._decode_b64(base64.b64encode(b"abc").decode("utf-8"))  # noqa: SLF001
        self.assertEqual(valid, b"abc")
        no_padding = check._decode_b64("YWJj")  # noqa: SLF001
        self.assertEqual(no_padding, b"abc")
        self.assertIsNotNone(check._resolve_xsd_path("FE"))  # noqa: SLF001


if __name__ == "__main__":
    unittest.main()
