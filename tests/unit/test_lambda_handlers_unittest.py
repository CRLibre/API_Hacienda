from __future__ import annotations

import unittest
from unittest.mock import patch

from python_api import aws_lambda_handlers


class LambdaHandlersTest(unittest.TestCase):
    def test_scheduler_requeue_handler_shapes_response(self) -> None:
        with patch.object(aws_lambda_handlers, "requeue_due_jobs", return_value={"enabled": True, "requeued": 2}):
            with patch.object(
                aws_lambda_handlers, "process_sqs_batch", autospec=True
            ) as mocked_batch:
                mocked_batch.return_value = {"enabled": True, "processed": 2}
                response = aws_lambda_handlers.scheduler_requeue_handler({}, None)

        self.assertIn("requeue", response)
        self.assertIn("batch", response)
        self.assertEqual(2, response["requeue"]["requeued"])

    def test_sqs_worker_handler_ignores_invalid_messages(self) -> None:
        event = {"Records": [{"messageId": "m1", "body": "{}"}]}
        response = aws_lambda_handlers.sqs_worker_handler(event, None)
        self.assertIn("batchItemFailures", response)
        self.assertEqual([], response["batchItemFailures"])
