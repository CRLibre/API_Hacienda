from __future__ import annotations

import json
import unittest
from types import SimpleNamespace
from unittest.mock import patch

from python_api.handlers import send as send_handler
from python_api.services import db_compat, fe_async


class DataApiBackendSwitchTest(unittest.TestCase):
    def test_fetch_one_uses_rds_data_api_backend(self) -> None:
        settings = SimpleNamespace(db_backend="rds_data_api")
        with patch.object(db_compat, "get_settings", return_value=settings):
            with patch.object(db_compat.rds_data_backend, "fetch_one", return_value={"ok": 1}) as mocked:
                row = db_compat.fetch_one("SELECT 1 AS ok")
        self.assertEqual({"ok": 1}, row)
        mocked.assert_called_once_with("SELECT 1 AS ok", None)


class FeAsyncHelpersTest(unittest.TestCase):
    def test_retry_delay_respects_cap(self) -> None:
        settings = SimpleNamespace(
            fe_retry_base_seconds=60,
            fe_retry_max_seconds=600,
            fe_retry_jitter_seconds=0,
        )
        delay = fe_async._compute_retry_delay_seconds(attempt_count=30, settings=settings)
        self.assertEqual(600, delay)

    def test_extract_estado_variants(self) -> None:
        self.assertEqual("aceptado", fe_async._extract_estado({"ind-estado": "Aceptado"}))
        self.assertEqual("rechazado", fe_async._extract_estado({"ind_estado": "Rechazado"}))
        self.assertEqual("", fe_async._extract_estado(None))

    def test_idempotency_key_is_stable_and_sensitive(self) -> None:
        key1 = fe_async._build_idempotency_key(
            route_r="json",
            client_id="api-stag",
            clave="506123",
            message='{"a":1}',
        )
        key2 = fe_async._build_idempotency_key(
            route_r="json",
            client_id="api-stag",
            clave="506123",
            message='{"a":1}',
        )
        key3 = fe_async._build_idempotency_key(
            route_r="json",
            client_id="api-stag",
            clave="506123",
            message='{"a":2}',
        )
        self.assertEqual(key1, key2)
        self.assertNotEqual(key1, key3)


class SendQueueModeTest(unittest.IsolatedAsyncioTestCase):
    async def test_send_json_uses_queue_when_enabled(self) -> None:
        settings = SimpleNamespace(fe_async_enabled=True, fe_sqs_queue_url="https://sqs.local/q")
        params = {
            "client_id": "api-stag",
            "token": "abc",
            "clave": "506....",
        }
        with patch.object(send_handler, "get_settings", return_value=settings):
            with patch.object(
                send_handler,
                "enqueue_send_job",
                return_value={
                    "job_id": "job-1",
                    "status": "QUEUED",
                    "next_retry_at": 123456,
                },
            ):
                response = await send_handler.json(None, params)

        payload = json.loads(response.body.decode("utf-8"))
        self.assertEqual("error", payload["status"])
        self.assertEqual("job-1", payload["resp"]["job_id"])
        self.assertTrue(payload["resp"]["queued"])
        self.assertEqual(202, payload["resp"]["Status"])
