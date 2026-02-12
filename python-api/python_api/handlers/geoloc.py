from __future__ import annotations

import random
import sqlite3
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse, Response

from python_api.responses import tools_reply_compatible

APP_ROOT = Path(__file__).resolve().parents[2]
GEOLOC_DIR = APP_ROOT / "resources" / "geoloc"
BLOCKS_DB = GEOLOC_DIR / "blocks.sqlite"
LOCATIONS_DB = GEOLOC_DIR / "locations.sqlite"


def _open(path: Path) -> sqlite3.Connection:
    conn = sqlite3.connect(path)
    conn.row_factory = sqlite3.Row
    return conn


def _ip_to_int(ip_address: str) -> int | None:
    parts = ip_address.split(".")
    if len(parts) != 4:
        return None
    try:
        nums = [int(p) for p in parts]
    except ValueError:
        return None
    if any(n < 0 or n > 255 for n in nums):
        return None
    return (16777216 * nums[0]) + (65536 * nums[1]) + (256 * nums[2]) + nums[3]


def _int_to_ip(value: int) -> str:
    return ".".join(str((value >> shift) & 255) for shift in (24, 16, 8, 0))


def _get_details_for_ip(ip_address: str) -> dict[str, object]:
    integer_ip = _ip_to_int(ip_address)
    if integer_ip is None:
        return {}

    if not BLOCKS_DB.is_file() or not LOCATIONS_DB.is_file():
        return {}

    with _open(BLOCKS_DB) as db_b:
        row = db_b.execute(
            """
            SELECT locId
            FROM blocks
            WHERE startIpNum <= ? AND endIpNum >= ?
            LIMIT 1
            """,
            (integer_ip, integer_ip),
        ).fetchone()

    if row is None:
        return {}

    with _open(LOCATIONS_DB) as db_l:
        details_row = db_l.execute("SELECT * FROM locations WHERE locId = ?", (row["locId"],)).fetchone()

    if details_row is None:
        return {}

    details = dict(details_row)
    details["ipAddress"] = ip_address
    return details


def _random_details() -> dict[str, object]:
    if not BLOCKS_DB.is_file():
        return {}

    loc_id = random.randint(1780, 3000)
    with _open(BLOCKS_DB) as db_b:
        row = db_b.execute(
            """
            SELECT *
            FROM blocks
            WHERE locId = ?
            LIMIT 1
            """,
            (loc_id,),
        ).fetchone()

    if row is None:
        return {}

    start_ip_num = int(row["startIpNum"])
    return _get_details_for_ip(_int_to_ip(start_ip_num))


async def geoloc_get_by_ip(request: Request, params: dict[str, str]) -> JSONResponse:
    # Legacy module declares an impossible required param with empty key.
    # In practice this route always returns this validation error.
    return tools_reply_compatible("Falta el parametro requerido: ", kill_me=True)


def _legacy_empty_200() -> Response:
    # Under PHP 8.x baseline these routes crash in users_access before action
    # execution and end up with an empty 200 response body.
    return Response(content="", status_code=200, media_type="text/html")


async def geoloc_create_tables(_: Request, __: dict[str, str]) -> Response:
    return _legacy_empty_200()


async def geoloc_load_locations(_: Request, __: dict[str, str]) -> Response:
    return _legacy_empty_200()


async def geoloc_load_blocks(_: Request, __: dict[str, str]) -> Response:
    return _legacy_empty_200()
