from __future__ import annotations

import base64
import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

from python_api.handlers import files, firmar_xml, send_mail, xml_to_base64


class HandlerIOAsyncTest(unittest.IsolatedAsyncioTestCase):
    async def test_xml_to_base64_empty_when_file_missing(self) -> None:
        with patch("python_api.handlers.xml_to_base64._files_get_url", return_value=None):
            result = await xml_to_base64.encode(None, {"downloadCode": "x"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], "")

    async def test_xml_to_base64_success(self) -> None:
        with tempfile.TemporaryDirectory() as td:
            path = Path(td) / "a.xml"
            path.write_bytes(b"<a/>")
            with patch("python_api.handlers.xml_to_base64._files_get_url", return_value=path):
                result = await xml_to_base64.encode(None, {"downloadCode": "x"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], base64.b64encode(b"<a/>").decode("utf-8"))

    async def test_firmar_xml_missing_p12(self) -> None:
        with patch("python_api.handlers.firmar_xml._files_get_url", return_value=None):
            result = await firmar_xml.firmar(None, {"p12Url": "code", "pinP12": "1234", "inXml": ""})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("No se encontró el archivo p12", payload["resp"])

    async def test_send_mail_builds_message_and_returns_test(self) -> None:
        captured: dict[str, object] = {}

        def _fake_send(msg):
            captured["message"] = msg

        params = {
            "clave": "123",
            "xmlEnvia": base64.b64encode(b"<xml/>").decode("utf-8"),
            "xmlHacienda": base64.b64encode(b"<mh/>").decode("utf-8"),
            "facturaPDF": base64.b64encode(b"%PDF").decode("utf-8"),
        }
        with patch("python_api.handlers.send_mail._send_best_effort", side_effect=_fake_send):
            result = await send_mail.sendmail(None, params)

        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], "test")
        message = captured["message"]
        self.assertEqual(message["Subject"], "Documentos de Factura electronica #123")
        self.assertGreaterEqual(len(list(message.iter_attachments())), 2)

    async def test_files_get_url_paths(self) -> None:
        with patch("python_api.handlers.files._load_file_by_code", return_value=None):
            missing = await files.filesGetUrl(None, {"downloadCode": "x"})
        payload_missing = json.loads(missing.body.decode("utf-8"))
        self.assertFalse(payload_missing["resp"])

        fake_row = {"idUser": 1, "type": "hacienda", "name": "a.xml"}
        with patch("python_api.handlers.files._load_file_by_code", return_value=fake_row):
            with patch("python_api.handlers.files._file_path_from_row", return_value=Path("/tmp/a.xml")):
                ok = await files.filesGetUrl(None, {"downloadCode": "x"})
        payload_ok = json.loads(ok.body.decode("utf-8"))
        self.assertEqual(payload_ok["resp"], "/tmp/a.xml")

    async def test_files_upload_without_file_form_field(self) -> None:
        class _Req:
            async def form(self):
                return {}

        result = await files.upload(_Req(), {"iam": "", "ext": "*"})
        payload = json.loads(result.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], "-404")


class HandlerIOSyncTest(unittest.TestCase):
    def test_safe_b64_decode_invalid_returns_empty(self) -> None:
        self.assertEqual(send_mail._safe_b64_decode("%%%"), b"")  # noqa: SLF001
        self.assertEqual(send_mail._safe_b64_decode(""), b"")  # noqa: SLF001

    def test_files_helpers(self) -> None:
        self.assertEqual(files._as_int("3", 0), 3)  # noqa: SLF001
        self.assertEqual(files._as_int("x", 7), 7)  # noqa: SLF001
        self.assertEqual(files._mime_type("a.svg"), "image/svg+xml")  # noqa: SLF001
        self.assertEqual(files._mime_type("a.unknown"), "application/octet-stream")  # noqa: SLF001

        row = {"idUser": 9, "type": "hacienda", "name": "invoice.xml"}
        path = files._file_path_from_row(row)  # noqa: SLF001
        self.assertTrue(str(path).endswith("/9/hacienda/invoice.xml"))
        resized = files._file_path_from_row(row, size="120")  # noqa: SLF001
        self.assertTrue(str(resized).endswith("/9/hacienda/invoice_120.xml"))

    def test_download_code_is_md5_hex(self) -> None:
        code = files._files_create_download_code("a.xml", 1)  # noqa: SLF001
        self.assertEqual(len(code), 32)
        self.assertTrue(all(ch in "0123456789abcdef" for ch in code))

    def test_load_user_id_from_iam_empty(self) -> None:
        self.assertEqual(files._load_user_id_from_iam(""), 0)  # noqa: SLF001


if __name__ == "__main__":
    unittest.main()
