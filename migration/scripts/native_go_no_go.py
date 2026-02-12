#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
import json
import re
import urllib.error
import urllib.request
from dataclasses import dataclass
from pathlib import Path
from typing import Any


@dataclass(frozen=True)
class CheckResult:
    name: str
    ok: bool
    detail: str


def parse_args() -> argparse.Namespace:
    default_root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(
        description="Validate native cutover readiness before decommissioning PHP baseline."
    )
    parser.add_argument("--migration-root", default=str(default_root), help="Path to migration root.")
    parser.add_argument(
        "--tracker",
        default="docs/contracts/migration-tracker.tsv",
        help="Relative path (from migration-root) to tracker TSV.",
    )
    parser.add_argument(
        "--report",
        default="reports/global-parity-live.tsv",
        help="Relative path (from migration-root) to latest global parity live report TSV.",
    )
    parser.add_argument(
        "--candidate-url",
        default="",
        help="Optional candidate base URL (e.g. http://127.0.0.1:8080) for live readiness checks.",
    )
    parser.add_argument("--timeout", type=float, default=10.0, help="HTTP timeout in seconds for live checks.")
    return parser.parse_args()


def _load_tsv(path: Path) -> list[dict[str, str]]:
    with path.open("r", encoding="utf-8", newline="") as fh:
        return list(csv.DictReader(fh, delimiter="\t"))


def check_tracker(tracker_path: Path) -> CheckResult:
    if not tracker_path.exists():
        return CheckResult("tracker_state", False, f"missing tracker file: {tracker_path}")

    rows = _load_tsv(tracker_path)
    if not rows:
        return CheckResult("tracker_state", False, "tracker has no rows")

    bad_status = [r for r in rows if (r.get("status") or "").strip() != "completed"]
    allowed_parity = {"passed", "python_only_v44_captured"}
    bad_parity = [r for r in rows if (r.get("parity_status") or "").strip() not in allowed_parity]
    bad_cutover = [r for r in rows if (r.get("cutover_status") or "").strip() != "cutover"]

    if bad_status or bad_parity or bad_cutover:
        return CheckResult(
            "tracker_state",
            False,
            (
                f"tracker not fully promoted "
                f"(status!=completed: {len(bad_status)}, "
                f"parity!=passed: {len(bad_parity)}, "
                f"cutover!=cutover: {len(bad_cutover)})"
            ),
        )

    unique_routes = len({((r.get("w") or "").strip(), (r.get("r") or "").strip()) for r in rows})
    python_only = sum(1 for r in rows if (r.get("parity_status") or "").strip() == "python_only_v44_captured")
    detail = (
        "all rows completed with accepted parity/cutover "
        f"({len(rows)} rows, {unique_routes} unique routes, python_only_v44={python_only})"
    )
    return CheckResult("tracker_state", True, detail)


def check_global_report(report_path: Path) -> CheckResult:
    if not report_path.exists():
        return CheckResult("global_parity_report", False, f"missing report file: {report_path}")

    rows = _load_tsv(report_path)
    if not rows:
        return CheckResult("global_parity_report", False, "report has no rows")

    failed = [r for r in rows if (r.get("pass") or "").strip().lower() != "true"]
    if failed:
        return CheckResult(
            "global_parity_report",
            False,
            f"parity report has failures ({len(failed)}/{len(rows)} scenarios failing)",
        )

    unique_routes = len({((r.get("w") or "").strip(), (r.get("r") or "").strip()) for r in rows})
    return CheckResult(
        "global_parity_report",
        True,
        f"all scenarios passing ({len(rows)} scenarios, {unique_routes} unique routes)",
    )


def check_no_proxy_routes(migration_root: Path) -> CheckResult:
    handler_path = migration_root / "python-api" / "python_api" / "handlers" / "__init__.py"
    if not handler_path.exists():
        return CheckResult("no_proxy_routes", False, f"missing handlers file: {handler_path}")

    text = handler_path.read_text(encoding="utf-8")
    matches = re.findall(r"\(\"([^\"]+)\",\s*\"([^\"]+)\"\):\s*proxy_modules\.proxy", text)
    if matches:
        sample = ", ".join(f"{w}/{r}" for w, r in matches[:5])
        suffix = "" if len(matches) <= 5 else f", ... (+{len(matches) - 5} more)"
        return CheckResult("no_proxy_routes", False, f"found {len(matches)} proxy route mappings: {sample}{suffix}")
    return CheckResult("no_proxy_routes", True, "no HANDLERS routes mapped to proxy_modules.proxy")


def check_no_facturador_fallback(migration_root: Path) -> CheckResult:
    facturador_path = migration_root / "python-api" / "python_api" / "handlers" / "facturador.py"
    if not facturador_path.exists():
        return CheckResult("no_facturador_fallback", False, f"missing file: {facturador_path}")

    text = facturador_path.read_text(encoding="utf-8")
    line_match = re.search(r"PARITY_FALLBACK_ROUTES:\s*set\[str\]\s*=\s*([^\n]+)", text)
    if line_match is None:
        return CheckResult("no_facturador_fallback", False, "PARITY_FALLBACK_ROUTES assignment not found")

    rhs = line_match.group(1).strip()
    if rhs == "set()":
        return CheckResult("no_facturador_fallback", True, "PARITY_FALLBACK_ROUTES is empty (set())")

    block = re.search(r"PARITY_FALLBACK_ROUTES:\s*set\[str\]\s*=\s*\{([^}]*)\}", text, re.S)
    if block is None:
        return CheckResult(
            "no_facturador_fallback",
            False,
            f"unsupported PARITY_FALLBACK_ROUTES format: {rhs}",
        )

    routes = [token.strip().strip('"') for token in block.group(1).split(",") if token.strip()]
    if routes:
        sample = ", ".join(routes[:5])
        suffix = "" if len(routes) <= 5 else f", ... (+{len(routes) - 5} more)"
        return CheckResult(
            "no_facturador_fallback",
            False,
            f"PARITY_FALLBACK_ROUTES still has {len(routes)} routes: {sample}{suffix}",
        )
    return CheckResult("no_facturador_fallback", True, "PARITY_FALLBACK_ROUTES is empty ({})")


def _http_get_json(url: str, timeout: float) -> tuple[int, dict[str, Any] | None, str]:
    req = urllib.request.Request(url, method="GET")
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            raw = resp.read().decode("utf-8", errors="replace")
            try:
                payload = json.loads(raw)
            except Exception:
                payload = None
            return int(resp.status), payload, raw
    except urllib.error.HTTPError as exc:
        raw = exc.read().decode("utf-8", errors="replace")
        try:
            payload = json.loads(raw)
        except Exception:
            payload = None
        return int(exc.code), payload, raw
    except Exception as exc:
        return 0, None, f"request_error:{exc}"


def check_live_native(candidate_url: str, timeout: float) -> CheckResult:
    if not candidate_url:
        return CheckResult("live_native_mode", True, "skipped (no --candidate-url provided)")

    base = candidate_url.rstrip("/")
    status, ready, ready_raw = _http_get_json(f"{base}/readyz", timeout=timeout)
    if status != 200 or not isinstance(ready, dict):
        return CheckResult("live_native_mode", False, f"/readyz invalid response: status={status}, body={ready_raw}")
    if ready.get("status") != "ok":
        return CheckResult("live_native_mode", False, f"/readyz status != ok: {ready_raw}")
    if ready.get("fallback_enabled") not in (False, 0):
        return CheckResult("live_native_mode", False, f"/readyz fallback_enabled is not false: {ready_raw}")

    status_root, root, root_raw = _http_get_json(f"{base}/", timeout=timeout)
    if status_root != 200 or not isinstance(root, dict):
        return CheckResult("live_native_mode", False, f"/ invalid response: status={status_root}, body={root_raw}")
    resp = root.get("resp")
    if not isinstance(resp, dict):
        return CheckResult("live_native_mode", False, f"/ missing resp object: {root_raw}")
    if resp.get("mode") != "native":
        return CheckResult("live_native_mode", False, f"/ mode is not native: {root_raw}")

    return CheckResult("live_native_mode", True, "live checks passed (/readyz fallback disabled, / mode=native)")


def main() -> int:
    args = parse_args()
    migration_root = Path(args.migration_root).resolve()
    tracker_path = (migration_root / args.tracker).resolve()
    report_path = (migration_root / args.report).resolve()

    checks = [
        check_tracker(tracker_path),
        check_global_report(report_path),
        check_no_proxy_routes(migration_root),
        check_no_facturador_fallback(migration_root),
        check_live_native(args.candidate_url, args.timeout),
    ]

    ok = True
    for item in checks:
        prefix = "PASS" if item.ok else "FAIL"
        print(f"[{prefix}] {item.name}: {item.detail}")
        if not item.ok:
            ok = False

    if ok:
        print("\nGO: native deployment is ready. PHP baseline can be decommissioned.")
        return 0

    print("\nNO-GO: keep PHP baseline until failed checks are resolved.")
    return 1


if __name__ == "__main__":
    raise SystemExit(main())
