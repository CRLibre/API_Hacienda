from __future__ import annotations

import json
import os
import unittest
import urllib.request

BASE_URL = os.getenv("SMOKE_API_BASE_URL", "").rstrip("/")


@unittest.skipUnless(BASE_URL, "Set SMOKE_API_BASE_URL to run live smoke tests.")
class LiveSmokeTest(unittest.TestCase):
    def _get_json(self, path: str) -> tuple[int, dict]:
        request = urllib.request.Request(f"{BASE_URL}{path}", method="GET")
        with urllib.request.urlopen(request, timeout=15) as response:
            body = response.read().decode("utf-8")
            return response.status, json.loads(body)

    def test_healthz_live(self) -> None:
        status, payload = self._get_json("/healthz")
        self.assertEqual(status, 200)
        self.assertEqual(payload["status"], "ok")

    def test_version_live(self) -> None:
        status, payload = self._get_json("/api.php?w=version&r=version")
        self.assertEqual(status, 200)
        self.assertEqual(payload["status"], "ok")


if __name__ == "__main__":
    unittest.main()
