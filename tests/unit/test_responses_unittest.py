from __future__ import annotations

import json
import unittest

from tests.support import bootstrap  # noqa: F401

from python_api import constants as c
from python_api.responses import tools_reply_compatible


class ResponsesCompatibilityTest(unittest.TestCase):
    def _payload(self, response) -> dict:
        return json.loads(response.body.decode("utf-8"))

    def test_known_user_error_code_maps_to_http_and_message(self) -> None:
        response = tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
        payload = self._payload(response)
        self.assertEqual(response.status_code, 403)
        self.assertEqual(payload["status"], "error")
        self.assertTrue(str(payload["resp"]).startswith("ERROR: "))
        self.assertIn("Acceso denegado", payload["resp"])

    def test_kill_me_prefixes_error_for_strings(self) -> None:
        response = tools_reply_compatible("Algo malo", kill_me=True)
        payload = self._payload(response)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(payload["status"], "error")
        self.assertEqual(payload["resp"], "ERROR: Algo malo")

    def test_mapping_status_ok_keeps_success(self) -> None:
        response = tools_reply_compatible({"Status": "ok", "text": "fine"})
        payload = self._payload(response)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"], "fine")

    def test_mapping_status_error_forces_error(self) -> None:
        response = tools_reply_compatible({"Status": "Error", "text": "boom"})
        payload = self._payload(response)
        self.assertEqual(response.status_code, 500)
        self.assertEqual(payload["status"], "error")
        self.assertEqual(payload["resp"], "ERROR: boom")

    def test_mapping_status_unknown_forces_bad_request(self) -> None:
        response = tools_reply_compatible({"Status": "what", "text": "??"})
        payload = self._payload(response)
        self.assertEqual(response.status_code, 400)
        self.assertEqual(payload["status"], "error")
        self.assertEqual(payload["resp"], "ERROR: ??")

    def test_non_error_plain_payload_stays_ok(self) -> None:
        response = tools_reply_compatible({"a": 1})
        payload = self._payload(response)
        self.assertEqual(response.status_code, 200)
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"]["a"], 1)


if __name__ == "__main__":
    unittest.main()
