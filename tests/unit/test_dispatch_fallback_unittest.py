from __future__ import annotations

import json
import unittest
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

import httpx
from starlette.requests import Request

from python_api.compat import ParsedLegacyRequest
from python_api.config import Settings
from python_api.fallback import FallbackProxy
from python_api.handlers import get_handler, is_implemented, module_exists, proxy_modules, should_skip_local_validation


def _request(method: str = "POST", headers: dict[str, str] | None = None) -> Request:
    header_items = []
    for key, value in (headers or {}).items():
        header_items.append((key.lower().encode("utf-8"), value.encode("utf-8")))
    scope = {
        "type": "http",
        "http_version": "1.1",
        "method": method,
        "scheme": "http",
        "path": "/api.php",
        "raw_path": b"/api.php",
        "query_string": b"",
        "headers": header_items,
        "client": ("127.0.0.1", 9999),
        "server": ("testserver", 80),
    }

    async def receive():
        return {"type": "http.request", "body": b"", "more_body": False}

    return Request(scope, receive)


class DispatchTest(unittest.TestCase):
    def test_handler_dispatch_known_and_unknown(self) -> None:
        self.assertIsNotNone(get_handler("version", "version"))
        self.assertIsNone(get_handler("unknown", "unknown"))

    def test_implemented_and_module_exists(self) -> None:
        self.assertTrue(is_implemented("version", "version"))
        self.assertFalse(is_implemented("unknown", "x"))
        self.assertTrue(module_exists("version"))
        self.assertFalse(module_exists("does-not-exist"))

    def test_proxy_module_support_map(self) -> None:
        self.assertTrue(proxy_modules.supports("version", "version"))
        self.assertFalse(proxy_modules.supports("version", "missing"))
        self.assertFalse(proxy_modules.supports("missing", "missing"))

    def test_skip_local_validation_default_false(self) -> None:
        self.assertFalse(should_skip_local_validation("version", "version"))
        self.assertFalse(should_skip_local_validation("unknown", "route"))


class FallbackProxyAsyncTest(unittest.IsolatedAsyncioTestCase):
    async def test_fallback_proxy_requires_url(self) -> None:
        proxy = FallbackProxy(Settings(php_fallback_url=None))
        req = _request()
        with self.assertRaises(RuntimeError):
            await proxy.proxy(req, ParsedLegacyRequest(params={"w": "x", "r": "y"}, source="query"))

    async def test_fallback_proxy_query_passthrough(self) -> None:
        class DummyClient:
            last_request: dict[str, object] | None = None

            def __init__(self, **kwargs):
                self.kwargs = kwargs

            async def __aenter__(self):
                return self

            async def __aexit__(self, exc_type, exc, tb):
                return False

            async def request(self, method, url, **kwargs):
                DummyClient.last_request = {"method": method, "url": url, "kwargs": kwargs}
                request = httpx.Request(method, url)
                return httpx.Response(
                    207,
                    request=request,
                    headers={"content-type": "application/json"},
                    content=b'{"status":"ok"}',
                )

        proxy = FallbackProxy(Settings(php_fallback_url="https://legacy.example/api.php", request_timeout_seconds=5))
        req = _request(headers={"x-request-id": "req-123"})
        parsed = ParsedLegacyRequest(params={"w": "version", "r": "version"}, source="query")

        with patch("python_api.fallback.httpx.AsyncClient", DummyClient):
            response = await proxy.proxy(req, parsed)

        self.assertEqual(response.status_code, 207)
        self.assertEqual(response.headers.get("content-type"), "application/json")
        self.assertEqual(response.body.decode("utf-8"), '{"status":"ok"}')

        self.assertIsNotNone(DummyClient.last_request)
        kwargs = DummyClient.last_request["kwargs"]
        self.assertEqual(kwargs["params"], {"w": "version", "r": "version"})
        self.assertEqual(kwargs["headers"]["x-request-id"], "req-123")

    async def test_proxy_module_fallback_error_path(self) -> None:
        request = _request()
        with patch("python_api.handlers.proxy_modules.fallback_proxy.proxy", side_effect=RuntimeError("boom")):
            response = await proxy_modules.proxy(request, {"w": "version", "r": "version"})
        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual(payload["status"], "error")
        self.assertIn("Fallback proxy failed", payload["resp"])


if __name__ == "__main__":
    unittest.main()
