from __future__ import annotations

import unittest
from unittest.mock import AsyncMock, patch

from tests.support import bootstrap  # noqa: F401

from fastapi.responses import JSONResponse
from fastapi.testclient import TestClient

from python_api import main


class MainBranchesTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.client = TestClient(main.app)

    def test_missing_dispatch_with_fallback_proxy(self) -> None:
        async_proxy = AsyncMock(return_value=JSONResponse(status_code=299, content={"proxied": True}))
        with patch.object(main.settings, "php_fallback_url", "http://legacy.local/api.php"):
            with patch.object(main.fallback_proxy, "proxy", async_proxy):
                response = self.client.get("/api.php?r=version")
        self.assertEqual(response.status_code, 299)
        self.assertTrue(response.json()["proxied"])
        self.assertTrue(async_proxy.await_count >= 1)

    def test_should_skip_validation_without_fallback(self) -> None:
        with patch.object(main.settings, "php_fallback_url", ""):
            with patch("python_api.main.should_skip_local_validation", return_value=True):
                with patch("python_api.main.module_exists", return_value=True):
                    response = self.client.get("/api.php?w=version&r=version")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Function not found", response.json()["resp"])

    def test_handler_exception_returns_500(self) -> None:
        def _boom(*_args, **_kwargs):
            raise RuntimeError("handler crashed")

        with patch("python_api.main.get_handler", return_value=_boom):
            response = self.client.get("/api.php?w=version&r=version")
        self.assertEqual(response.status_code, 500)
        self.assertEqual(response.json()["status"], "error")
        self.assertIn("Python handler failed", response.json()["resp"])

    def test_fallback_exception_returns_502(self) -> None:
        with patch.object(main.settings, "php_fallback_url", "http://legacy.local/api.php"):
            with patch("python_api.main.get_handler", return_value=None):
                with patch.object(main.fallback_proxy, "proxy", AsyncMock(side_effect=RuntimeError("fallback down"))):
                    response = self.client.get("/api.php?w=unknown&r=unknown")
        self.assertEqual(response.status_code, 502)
        self.assertEqual(response.json()["status"], "error")
        self.assertIn("Fallback proxy failed", response.json()["resp"])


if __name__ == "__main__":
    unittest.main()
