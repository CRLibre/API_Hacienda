from __future__ import annotations

import json
import unittest

from tests.support import bootstrap  # noqa: F401

from python_api.handlers import cala


class CalaHandlerTest(unittest.IsolatedAsyncioTestCase):
    async def test_cala_core(self) -> None:
        response = await cala.cala_core(None, {})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("actualyl do anything", payload["resp"])

    async def test_cala_default(self) -> None:
        response = await cala.cala_default(None, {})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(response.status_code, 200)
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], -1)

    async def test_cala_test_install(self) -> None:
        response = await cala.cala_test_install(None, {})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "ok")
        self.assertIn("Cala Installation check proccess", payload["resp"])


if __name__ == "__main__":
    unittest.main()
