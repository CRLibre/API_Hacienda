#!/usr/bin/env python3
from __future__ import annotations

import argparse
import asyncio
import json
import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
os.environ.setdefault("PYTHONPATH", str(ROOT / "python-api"))
if str(ROOT / "python-api") not in sys.path:
    sys.path.insert(0, str(ROOT / "python-api"))

from python_api.services.fe_async import process_sqs_batch, requeue_due_jobs


async def _run(max_messages: int, requeue_limit: int) -> dict:
    batch = await process_sqs_batch(max_messages=max_messages)
    requeue = requeue_due_jobs(limit=requeue_limit)
    return {"batch": batch, "requeue": requeue}


def main() -> int:
    parser = argparse.ArgumentParser(description="Run one FE async worker tick (SQS + retry requeue).")
    parser.add_argument("--max-messages", type=int, default=10, help="Max SQS messages to process.")
    parser.add_argument("--requeue-limit", type=int, default=100, help="Max due jobs to requeue.")
    args = parser.parse_args()

    result = asyncio.run(_run(max_messages=args.max_messages, requeue_limit=args.requeue_limit))
    print(json.dumps(result, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
