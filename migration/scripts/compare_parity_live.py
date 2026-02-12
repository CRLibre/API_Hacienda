#!/usr/bin/env python3
from __future__ import annotations

import argparse
import base64
import csv
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from collections import defaultdict
from pathlib import Path
from typing import Any


DEFAULT_SCENARIOS = ("success", "validation_error", "auth_error", "malformed")
BASE64_RE = re.compile(r"^[A-Za-z0-9+/]+={0,2}$")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Compare live parity between PHP baseline and Python candidate using route fixtures."
    )
    parser.add_argument("--migration-root", default=".", help="Path to migration root.")
    parser.add_argument(
        "--routes-tsv",
        default="docs/contracts/w1-factura-priority-routes.tsv",
        help="Route list TSV with at least columns w and r.",
    )
    parser.add_argument("--manifest", default="tests/fixtures/parity/manifest.json", help="Fixture manifest JSON path.")
    parser.add_argument("--baseline-url", required=True, help="Baseline API base URL, e.g. http://127.0.0.1:8080")
    parser.add_argument("--candidate-url", required=True, help="Candidate API base URL, e.g. http://127.0.0.1:8090")
    parser.add_argument("--report", default="reports/parity-live-report.tsv", help="Output report path.")
    parser.add_argument(
        "--update-tracker",
        action="store_true",
        help="If set, update tracker parity_status to passed for fully passing routes.",
    )
    parser.add_argument(
        "--tracker",
        default="docs/contracts/migration-tracker.tsv",
        help="Tracker TSV to update when --update-tracker is set.",
    )
    parser.add_argument(
        "--scenarios",
        default=",".join(DEFAULT_SCENARIOS),
        help="Comma-separated scenarios to evaluate.",
    )
    parser.add_argument("--timeout", type=float, default=30.0, help="Request timeout in seconds.")
    return parser.parse_args()


def canonicalize_json(value: Any) -> Any:
    if isinstance(value, dict):
        return {k: canonicalize_json(value[k]) for k in sorted(value)}
    if isinstance(value, list):
        return [canonicalize_json(item) for item in value]
    return value


def load_routes(routes_tsv: Path) -> list[tuple[str, str]]:
    rows: list[tuple[str, str]] = []
    with routes_tsv.open("r", encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh, delimiter="\t")
        for row in reader:
            w = (row.get("w") or "").strip()
            r = (row.get("r") or "").strip()
            if w and r:
                rows.append((w, r))
    return rows


def load_manifest(manifest_path: Path) -> dict[tuple[str, str], dict[str, Any]]:
    raw = json.loads(manifest_path.read_text(encoding="utf-8"))
    out: dict[tuple[str, str], dict[str, Any]] = {}
    for route in raw:
        w = str(route.get("w", "")).strip()
        r = str(route.get("r", "")).strip()
        if not w or not r:
            continue
        out[(w, r)] = route
    return out


def send_request(base_url: str, request_fixture: dict[str, Any], timeout: float) -> tuple[int, str]:
    method = str(request_fixture.get("method", "POST")).upper()
    path = str(request_fixture.get("path", "/api.php"))
    params = request_fixture.get("params", {}) or {}
    if not isinstance(params, dict):
        params = {}

    encoded = urllib.parse.urlencode({k: "" if v is None else str(v) for k, v in params.items()}).encode("utf-8")
    url = urllib.parse.urljoin(base_url.rstrip("/") + "/", path.lstrip("/"))
    data = encoded if method != "GET" else None

    if method == "GET":
        qs = encoded.decode("utf-8")
        if qs:
            sep = "&" if "?" in url else "?"
            url = f"{url}{sep}{qs}"

    req = urllib.request.Request(url, data=data, method=method)
    req.add_header("Content-Type", "application/x-www-form-urlencoded")

    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            status = int(resp.status)
            body = resp.read().decode("utf-8", errors="replace")
            return status, body
    except urllib.error.HTTPError as exc:
        body = exc.read().decode("utf-8", errors="replace")
        return int(exc.code), body
    except Exception as exc:  # pragma: no cover
        return 0, f"__request_error__:{exc}"


def compare_bodies(left: str, right: str) -> tuple[bool, str]:
    left_s = left.strip()
    right_s = right.strip()
    if left_s == right_s:
        return True, "exact"

    try:
        left_j = canonicalize_json(json.loads(left_s))
        right_j = canonicalize_json(json.loads(right_s))
        if left_j == right_j:
            return True, "json_equivalent"
        return False, "json_mismatch"
    except Exception:
        return False, "body_mismatch"


def compare_route_specific(
    w: str,
    r: str,
    baseline_status: int,
    baseline_body: str,
    candidate_status: int,
    candidate_body: str,
) -> tuple[bool, str] | None:
    # crypto/makeKey is non-deterministic by design on both sides.
    if (w, r) != ("crypto", "makeKey"):
        return None
    if baseline_status != candidate_status:
        return False, "status_mismatch"
    if baseline_status != 200:
        return False, "status_unexpected"

    def parse_resp(body: str) -> str | None:
        try:
            obj = json.loads(body)
        except Exception:
            return None
        if not isinstance(obj, dict):
            return None
        if obj.get("status") != "ok":
            return None
        resp = obj.get("resp")
        if not isinstance(resp, str):
            return None
        if not BASE64_RE.fullmatch(resp):
            return None
        try:
            raw = base64.b64decode(resp, validate=True)
        except Exception:
            return None
        if len(raw) != 32:
            return None
        return resp

    left = parse_resp(baseline_body.strip())
    right = parse_resp(candidate_body.strip())
    if left is None or right is None:
        return False, "makekey_format_mismatch"
    return True, "makekey_equivalent"


def safe_read_json(path: Path) -> dict[str, Any]:
    return json.loads(path.read_text(encoding="utf-8"))


def write_report(report_path: Path, rows: list[dict[str, Any]]) -> None:
    report_path.parent.mkdir(parents=True, exist_ok=True)
    fields = [
        "w",
        "r",
        "scenario",
        "pass",
        "baseline_status",
        "candidate_status",
        "match_type",
        "details",
    ]
    with report_path.open("w", encoding="utf-8", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=fields, delimiter="\t")
        writer.writeheader()
        writer.writerows(rows)


def update_tracker_status(tracker_path: Path, route_pass: dict[tuple[str, str], bool]) -> tuple[int, int]:
    with tracker_path.open("r", encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh, delimiter="\t")
        fields = reader.fieldnames or []
        rows = list(reader)

    updated = 0
    touched = 0
    for row in rows:
        key = ((row.get("w") or "").strip(), (row.get("r") or "").strip())
        if key not in route_pass:
            continue
        touched += 1
        if route_pass[key]:
            if row.get("parity_status") != "passed":
                row["parity_status"] = "passed"
                updated += 1
        else:
            if row.get("parity_status") == "passed":
                row["parity_status"] = "captured"

    with tracker_path.open("w", encoding="utf-8", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=fields, delimiter="\t", lineterminator="\n")
        writer.writeheader()
        writer.writerows(rows)
    return touched, updated


def main() -> int:
    args = parse_args()
    migration_root = Path(args.migration_root).resolve()
    routes_tsv = (migration_root / args.routes_tsv).resolve()
    manifest_path = (migration_root / args.manifest).resolve()
    report_path = (migration_root / args.report).resolve()
    tracker_path = (migration_root / args.tracker).resolve()
    scenarios = tuple(s.strip() for s in args.scenarios.split(",") if s.strip())

    if not routes_tsv.exists():
        print(f"Routes TSV not found: {routes_tsv}", file=sys.stderr)
        return 1
    if not manifest_path.exists():
        print(f"Manifest not found: {manifest_path}", file=sys.stderr)
        return 1

    routes = load_routes(routes_tsv)
    manifest = load_manifest(manifest_path)
    report_rows: list[dict[str, Any]] = []
    route_results: dict[tuple[str, str], list[bool]] = defaultdict(list)

    for w, r in routes:
        route = manifest.get((w, r))
        if route is None:
            for scenario in scenarios:
                report_rows.append(
                    {
                        "w": w,
                        "r": r,
                        "scenario": scenario,
                        "pass": "false",
                        "baseline_status": "0",
                        "candidate_status": "0",
                        "match_type": "missing_manifest_route",
                        "details": "Route missing in manifest",
                    }
                )
                route_results[(w, r)].append(False)
            continue

        scenario_map = route.get("scenarios", {}) or {}
        for scenario in scenarios:
            scenario_cfg = scenario_map.get(scenario)
            if not isinstance(scenario_cfg, dict):
                report_rows.append(
                    {
                        "w": w,
                        "r": r,
                        "scenario": scenario,
                        "pass": "false",
                        "baseline_status": "0",
                        "candidate_status": "0",
                        "match_type": "missing_scenario",
                        "details": "Scenario missing in manifest route entry",
                    }
                )
                route_results[(w, r)].append(False)
                continue

            req_file = Path(str(scenario_cfg.get("request", "")))
            if not req_file.is_absolute():
                req_file = (migration_root / req_file).resolve()
            if not req_file.exists():
                report_rows.append(
                    {
                        "w": w,
                        "r": r,
                        "scenario": scenario,
                        "pass": "false",
                        "baseline_status": "0",
                        "candidate_status": "0",
                        "match_type": "missing_request_fixture",
                        "details": f"Missing request fixture: {req_file}",
                    }
                )
                route_results[(w, r)].append(False)
                continue

            req_payload = safe_read_json(req_file)
            baseline_status, baseline_body = send_request(args.baseline_url, req_payload, args.timeout)
            candidate_status, candidate_body = send_request(args.candidate_url, req_payload, args.timeout)

            route_specific = compare_route_specific(w, r, baseline_status, baseline_body, candidate_status, candidate_body)
            if route_specific is not None:
                body_match, match_type = route_specific
                status_match = True
                ok = body_match
            else:
                status_match = baseline_status == candidate_status
                body_match, match_type = compare_bodies(baseline_body, candidate_body)
                ok = status_match and body_match

            details = ""
            if not status_match:
                details = f"status mismatch {baseline_status}!={candidate_status}"
            elif not body_match:
                details = "body mismatch"

            report_rows.append(
                {
                    "w": w,
                    "r": r,
                    "scenario": scenario,
                    "pass": "true" if ok else "false",
                    "baseline_status": str(baseline_status),
                    "candidate_status": str(candidate_status),
                    "match_type": match_type if status_match else "status_mismatch",
                    "details": details,
                }
            )
            route_results[(w, r)].append(ok)

    write_report(report_path, report_rows)

    route_pass = {key: all(vals) and bool(vals) for key, vals in route_results.items()}
    route_total = len(route_pass)
    route_ok = sum(1 for v in route_pass.values() if v)
    scenario_total = len(report_rows)
    scenario_ok = sum(1 for row in report_rows if row["pass"] == "true")

    print(f"Report written: {report_path}")
    print(f"Scenario parity: {scenario_ok}/{scenario_total}")
    print(f"Route parity: {route_ok}/{route_total}")

    if args.update_tracker:
        if not tracker_path.exists():
            print(f"Tracker not found: {tracker_path}", file=sys.stderr)
            return 1
        touched, updated = update_tracker_status(tracker_path, route_pass)
        print(f"Tracker updated: {tracker_path}")
        print(f"Routes touched: {touched}")
        print(f"Routes marked passed: {updated}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
