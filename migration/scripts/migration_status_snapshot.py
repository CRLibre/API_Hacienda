#!/usr/bin/env python3
from __future__ import annotations

import argparse
import csv
from collections import Counter, defaultdict
from pathlib import Path


def _load_tsv(path: Path) -> list[dict[str, str]]:
    with path.open("r", encoding="utf-8", newline="") as fh:
        return list(csv.DictReader(fh, delimiter="\t"))


def _route_key(row: dict[str, str]) -> tuple[str, str]:
    return ((row.get("w") or "").strip(), (row.get("r") or "").strip())


def _is_true(value: str) -> bool:
    return value.strip().lower() == "true"


def parse_args() -> argparse.Namespace:
    default_root = Path(__file__).resolve().parents[1]
    parser = argparse.ArgumentParser(description="Print a concise migration status snapshot.")
    parser.add_argument("--migration-root", default=str(default_root), help="Path to migration root.")
    parser.add_argument(
        "--tracker",
        default="docs/contracts/migration-tracker.tsv",
        help="Relative path (from migration-root) to tracker TSV.",
    )
    parser.add_argument(
        "--report",
        default="reports/global-parity-live.tsv",
        help="Relative path (from migration-root) to global parity live report TSV.",
    )
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    migration_root = Path(args.migration_root).resolve()
    tracker_path = (migration_root / args.tracker).resolve()
    report_path = (migration_root / args.report).resolve()

    if not tracker_path.exists():
        print(f"ERROR: tracker missing: {tracker_path}")
        return 1

    tracker_rows = _load_tsv(tracker_path)
    if not tracker_rows:
        print(f"ERROR: tracker empty: {tracker_path}")
        return 1

    status_counter = Counter((r.get("status") or "").strip() or "<empty>" for r in tracker_rows)
    parity_counter = Counter((r.get("parity_status") or "").strip() or "<empty>" for r in tracker_rows)
    cutover_counter = Counter((r.get("cutover_status") or "").strip() or "<empty>" for r in tracker_rows)

    grouped: dict[tuple[str, str], list[dict[str, str]]] = defaultdict(list)
    for row in tracker_rows:
        grouped[_route_key(row)].append(row)

    unique_routes = set(grouped.keys())
    additive_routes = {
        key for key, entries in grouped.items() if any((e.get("parity_status") or "").strip() == "python_only_v44_captured" for e in entries)
    }
    legacy_routes = unique_routes - additive_routes

    print("Tracker")
    print(f"- Rows: {len(tracker_rows)}")
    print(f"- Unique routes: {len(unique_routes)}")
    print(f"- Legacy routes: {len(legacy_routes)}")
    print(f"- Additive routes: {len(additive_routes)}")

    additive_list = sorted(additive_routes)
    if additive_list:
        print(f"- Additive route keys: {', '.join(f'{w}/{r}' for w, r in additive_list)}")

    print("- status counts: " + ", ".join(f"{k}={v}" for k, v in sorted(status_counter.items())))
    print("- parity counts: " + ", ".join(f"{k}={v}" for k, v in sorted(parity_counter.items())))
    print("- cutover counts: " + ", ".join(f"{k}={v}" for k, v in sorted(cutover_counter.items())))

    duplicates = sorted((key, len(entries)) for key, entries in grouped.items() if len(entries) > 1)
    if duplicates:
        print("- Duplicate route keys in tracker rows:")
        for (w, r), count in duplicates:
            print(f"  - {w}/{r}: {count} rows")

    if report_path.exists():
        report_rows = _load_tsv(report_path)
        report_routes = {_route_key(row) for row in report_rows}
        pass_count = sum(1 for row in report_rows if _is_true(row.get("pass", "")))
        fail_count = len(report_rows) - pass_count
        missing = sorted(legacy_routes - report_routes)
        unexpected = sorted(report_routes - legacy_routes)

        print("Global parity live")
        print(f"- Report rows: {len(report_rows)}")
        print(f"- Passing scenarios: {pass_count}")
        print(f"- Failing scenarios: {fail_count}")
        print(f"- Unique routes in report: {len(report_routes)}")
        print(f"- Legacy coverage by report routes: {len(report_routes & legacy_routes)}/{len(legacy_routes)}")
        if missing:
            print("- Legacy routes missing in report:")
            for w, r in missing:
                print(f"  - {w}/{r}")
        if unexpected:
            print("- Report routes not present in legacy tracker:")
            for w, r in unexpected:
                print(f"  - {w}/{r}")
    else:
        print(f"Global parity live\n- Report missing: {report_path}")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
