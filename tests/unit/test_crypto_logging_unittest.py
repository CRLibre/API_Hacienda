from __future__ import annotations

import json
import logging
import unittest

from tests.support import bootstrap  # noqa: F401

from python_api.logging_config import JsonFormatter, configure_logging
from python_api.services import crypto_compat


class CryptoLoggingTest(unittest.TestCase):
    def test_normalize_key_is_32_bytes(self) -> None:
        short = crypto_compat._normalize_key("abc")  # noqa: SLF001
        long = crypto_compat._normalize_key("x" * 40)  # noqa: SLF001
        self.assertEqual(len(short), 32)
        self.assertEqual(len(long), 32)
        self.assertEqual(short[:3], b"abc")

    def test_php_compat_encrypt_decrypt_roundtrip(self) -> None:
        secret = "hello world ñ"
        passphrase = "my-passphrase"
        encrypted = crypto_compat.php_compat_encrypt(secret, passphrase)
        decrypted = crypto_compat.php_compat_decrypt(encrypted, passphrase)
        self.assertEqual(decrypted, secret)

    def test_json_formatter_includes_known_extra_fields(self) -> None:
        formatter = JsonFormatter()
        record = logging.LogRecord(
            name="api.test",
            level=logging.INFO,
            pathname=__file__,
            lineno=10,
            msg="request_complete",
            args=(),
            exc_info=None,
        )
        record.request_id = "req-1"
        record.method = "GET"
        record.path = "/healthz"
        record.status_code = 200
        raw = formatter.format(record)
        payload = json.loads(raw)
        self.assertEqual(payload["message"], "request_complete")
        self.assertEqual(payload["request_id"], "req-1")
        self.assertEqual(payload["method"], "GET")
        self.assertEqual(payload["path"], "/healthz")
        self.assertEqual(payload["status_code"], 200)
        self.assertIn("ts", payload)

    def test_configure_logging_sets_root_handler(self) -> None:
        configure_logging("warning")
        root = logging.getLogger()
        self.assertEqual(root.level, logging.WARNING)
        self.assertEqual(len(root.handlers), 1)
        self.assertIsInstance(root.handlers[0].formatter, JsonFormatter)


if __name__ == "__main__":
    unittest.main()
