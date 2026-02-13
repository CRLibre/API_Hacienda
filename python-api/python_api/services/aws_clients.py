from __future__ import annotations

from functools import lru_cache
from typing import Any


@lru_cache(maxsize=8)
def get_boto3_client(service_name: str, region_name: str):
    try:
        import boto3
    except ImportError as exc:  # pragma: no cover - optional dependency path
        raise RuntimeError(
            "boto3 is required for AWS integration features. Install dependencies from pyproject.toml."
        ) from exc
    return boto3.client(service_name, region_name=region_name)


def get_sqs_client(region_name: str):
    return get_boto3_client("sqs", region_name)


def get_rds_data_client(region_name: str):
    return get_boto3_client("rds-data", region_name)


def read_sqs_messages(*, queue_url: str, region_name: str, max_messages: int) -> list[dict[str, Any]]:
    sqs = get_sqs_client(region_name)
    response = sqs.receive_message(
        QueueUrl=queue_url,
        MaxNumberOfMessages=max(1, min(10, max_messages)),
        WaitTimeSeconds=0,
        VisibilityTimeout=120,
        MessageAttributeNames=["All"],
    )
    return list(response.get("Messages", []))


def delete_sqs_message(*, queue_url: str, region_name: str, receipt_handle: str) -> None:
    sqs = get_sqs_client(region_name)
    sqs.delete_message(QueueUrl=queue_url, ReceiptHandle=receipt_handle)


def send_sqs_message(
    *,
    queue_url: str,
    region_name: str,
    body: str,
    delay_seconds: int = 0,
    deduplication_id: str | None = None,
    group_id: str | None = None,
) -> None:
    sqs = get_sqs_client(region_name)
    kwargs: dict[str, Any] = {
        "QueueUrl": queue_url,
        "MessageBody": body,
        "DelaySeconds": max(0, min(900, int(delay_seconds))),
    }
    if queue_url.endswith(".fifo"):
        kwargs["MessageGroupId"] = group_id or "api-hacienda-fe"
        kwargs["MessageDeduplicationId"] = deduplication_id or body
    sqs.send_message(**kwargs)
