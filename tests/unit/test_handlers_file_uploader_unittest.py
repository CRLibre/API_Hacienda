from __future__ import annotations

import json
import unittest
from unittest.mock import AsyncMock, patch

from tests.support import bootstrap  # noqa: F401

from python_api.handlers import file_uploader


class FileUploaderHandlerTest(unittest.IsolatedAsyncioTestCase):
    async def test_test_endpoint(self) -> None:
        response = await file_uploader.test(None, {})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], "Test :)")

    async def test_upload_legacy_denied_when_no_session(self) -> None:
        with patch("python_api.handlers.file_uploader.users._require_logged_in", return_value=None):
            response = await file_uploader.subir_certif(None, {})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "error")

    async def test_upload_legacy_forwards_to_files_upload(self) -> None:
        fake_user = {"userName": "juandi"}
        fake_response = file_uploader.tools_reply_compatible({"ok": True})

        with patch("python_api.handlers.file_uploader.users._require_logged_in", return_value=fake_user):
            with patch(
                "python_api.handlers.file_uploader.files.upload",
                new=AsyncMock(return_value=fake_response),
            ) as mocked_upload:
                response = await file_uploader.subir_xml(None, {"x": "1"})

        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertTrue(payload["resp"]["ok"])
        kwargs = mocked_upload.await_args.args[1]
        self.assertEqual(kwargs["iam"], "juandi")
        self.assertEqual(kwargs["type"], "hacienda")
        self.assertEqual(kwargs["ext"], "xml")


if __name__ == "__main__":
    unittest.main()
