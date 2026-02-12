from __future__ import annotations

import random
import sqlite3
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible

REPO_ROOT = Path(__file__).resolve().parents[4]
GEOLOC_DIR = REPO_ROOT / "api/modules/geoloc"
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
    ip_address = str(params.get("key", "")).strip() or str(params.get("ipAddress", "")).strip()
    if not ip_address:
        ip_address = request.client.host if request.client and request.client.host else ""

    if ip_address in {"127.0.0.1", "0.0.0.0", ""}:
        return tools_reply_compatible(_random_details())

    return tools_reply_compatible(_get_details_for_ip(ip_address))


async def geoloc_create_tables(_: Request, __: dict[str, str]) -> JSONResponse:
    GEOLOC_DIR.mkdir(parents=True, exist_ok=True)
    with _open(BLOCKS_DB) as db_b:
        db_b.execute('DROP TABLE IF EXISTS "blocks"')
        db_b.execute('CREATE TABLE "blocks" ("startIpNum" INTEGER NOT NULL, "endIpNum" INTEGER NOT NULL, "locId" INTEGER)')
        db_b.commit()
    with _open(LOCATIONS_DB) as db_l:
        db_l.execute('DROP TABLE IF EXISTS "locations"')
        db_l.execute(
            'CREATE TABLE "locations" ("locId" INTEGER PRIMARY KEY NOT NULL, "country" VARCHAR, "region" VARCHAR, "city" VARCHAR, "postalCode" VARCHAR, "latitude" VARCHAR, "longitude" VARCHAR, "metroCode" VARCHAR, "areaCode" VARCHAR)'
        )
        db_l.commit()
    return tools_reply_compatible("Tables created!")


async def geoloc_load_locations(_: Request, __: dict[str, str]) -> JSONResponse:
    cities_path = GEOLOC_DIR / "GeoLiteCity-Location.csv"
    if not cities_path.is_file():
        # Compatibility with legacy typo ($blocksPath undefined).
        return tools_reply_compatible("Missing file: ")

    if not LOCATIONS_DB.is_file():
        await geoloc_create_tables(_, {})

    lines = cities_path.read_text(encoding="utf-8", errors="ignore").splitlines()
    count = 0
    row_count = 0
    batch: list[tuple[str, str, str, str, str, str, str, str, str]] = []

    with _open(LOCATIONS_DB) as db_l:
        for line in lines:
            if row_count > 1:
                values = line.split(",")
                while len(values) < 9:
                    values.append("0")
                values = [value if value not in {"", "\n"} else "0" for value in values[:9]]

                batch.append(tuple(values))  # type: ignore[arg-type]
                count += 1
                if count == 500:
                    db_l.executemany(
                        "INSERT INTO locations (locId,country,region,city,postalCode,latitude,longitude,metroCode,areaCode) VALUES (?,?,?,?,?,?,?,?,?)",
                        batch,
                    )
                    db_l.commit()
                    count = 0
                    batch = []
            row_count += 1
    return tools_reply_compatible(None)


async def geoloc_load_blocks(_: Request, __: dict[str, str]) -> JSONResponse:
    blocks_path = GEOLOC_DIR / "GeoLiteCity-Blocks.csv"
    if not blocks_path.is_file():
        return tools_reply_compatible(f"Missing file GeoLiteCity-Blocks.csv{blocks_path}")

    if not BLOCKS_DB.is_file():
        await geoloc_create_tables(_, {})

    lines = blocks_path.read_text(encoding="utf-8", errors="ignore").splitlines()
    count = 0
    batch: list[tuple[str, str, str]] = []

    with _open(BLOCKS_DB) as db_b:
        for line in lines:
            values = line.split(",")
            while len(values) < 3:
                values.append("0")
            batch.append((values[0], values[1], values[2]))
            count += 1
            if count == 500:
                db_b.executemany("INSERT INTO blocks (startIpNum,endIpNum,locId) VALUES (?,?,?)", batch)
                db_b.commit()
                count = 0
                batch = []
    return tools_reply_compatible("Blocks loaded!")

