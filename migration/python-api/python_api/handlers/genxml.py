from __future__ import annotations

import json
import shutil
import subprocess
from pathlib import Path
from typing import Any

from fastapi import Request
from fastapi.responses import Response

from python_api.compat import ParsedLegacyRequest
from python_api.config import get_settings
from python_api.fallback import FallbackProxy
from python_api.responses import tools_reply_compatible

settings = get_settings()
fallback_proxy = FallbackProxy(settings)
BRIDGE_PATH = Path(__file__).resolve().parents[1] / "legacy_bridge" / "genxml_bridge.php"


def _decode_json_output(raw_text: str) -> dict[str, Any] | None:
    text = raw_text.strip()
    if not text:
        return None

    try:
        loaded = json.loads(text)
        return loaded if isinstance(loaded, dict) else None
    except json.JSONDecodeError:
        pass

    lines = [line.strip() for line in text.splitlines() if line.strip()]
    for line in reversed(lines):
        try:
            loaded = json.loads(line)
            if isinstance(loaded, dict):
                return loaded
        except json.JSONDecodeError:
            continue
    return None


def _call_local_php_bridge(route: str, params: dict[str, str]) -> tuple[Any | None, str | None]:
    php_binary = shutil.which("php")
    if php_binary is None:
        return None, "php binary not found"

    if not BRIDGE_PATH.is_file():
        return None, f"bridge script not found: {BRIDGE_PATH}"

    payload = json.dumps({"route": route, "params": params}, ensure_ascii=False)
    try:
        proc = subprocess.run(
            [php_binary, str(BRIDGE_PATH)],
            input=payload.encode("utf-8"),
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            timeout=180,
            check=False,
        )
    except Exception as exc:
        return None, str(exc)

    stdout = proc.stdout.decode("utf-8", errors="replace")
    stderr = proc.stderr.decode("utf-8", errors="replace").strip()
    decoded = _decode_json_output(stdout)
    if decoded is None:
        err = stderr or stdout.strip() or f"bridge exited with code {proc.returncode}"
        return None, err

    if not bool(decoded.get("ok")):
        err = decoded.get("message") or decoded.get("error") or "unknown bridge error"
        if stderr:
            err = f"{err} | {stderr}"
        return None, str(err)

    return decoded.get("result"), None


async def _dispatch(request: Request, params: dict[str, str], route: str) -> Response:
    result, bridge_error = _call_local_php_bridge(route, params)
    if bridge_error is None:
        return tools_reply_compatible(result)

    try:
        parsed = ParsedLegacyRequest(params=params, source="query")
        return await fallback_proxy.proxy(request, parsed)
    except Exception:
        return tools_reply_compatible({"Status": "Error occurred", "text": bridge_error})


async def gen_xml_fe(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_fe")


async def gen_xml_nc(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_nc")


async def gen_xml_nd(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_nd")


async def gen_xml_te(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_te")


async def gen_xml_mr(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_mr")


async def gen_xml_fec(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_fec")


async def gen_xml_fee(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "gen_xml_fee")


async def test(request: Request, params: dict[str, str]) -> Response:
    return await _dispatch(request, params, "test")

