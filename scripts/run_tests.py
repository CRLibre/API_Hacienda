#!/usr/bin/env python3
from __future__ import annotations

import argparse
import importlib
import os
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TESTS_ROOT = ROOT / "tests"


def _discover_suite(suite_name: str) -> unittest.TestSuite:
    suite_dir = TESTS_ROOT / suite_name
    if not suite_dir.is_dir():
        raise FileNotFoundError(f"test suite directory not found: {suite_dir}")
    loader = unittest.TestLoader()
    return loader.discover(str(suite_dir), pattern="test_*.py", top_level_dir=str(ROOT))


def _parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Run unittest suites for API_Hacienda.")
    parser.add_argument(
        "--suite",
        action="append",
        choices=("unit", "functional", "smoke"),
        help="Suite to run (can be repeated). Default: unit + functional.",
    )
    parser.add_argument("--coverage", dest="coverage", action="store_true", help="Enable coverage.")
    parser.add_argument("--no-coverage", dest="coverage", action="store_false", help="Disable coverage.")
    parser.set_defaults(coverage=True)
    parser.add_argument(
        "--min-coverage",
        type=float,
        default=80.0,
        help="Minimum accepted coverage percentage when coverage is enabled.",
    )
    parser.add_argument("--verbosity", type=int, default=2, choices=(1, 2), help="unittest verbosity.")
    return parser.parse_args()


def main() -> int:
    args = _parse_args()
    suites = args.suite or ["unit", "functional"]

    # Ensure tests can import app code.
    os.environ.setdefault("PYTHONPATH", str(ROOT / "python-api"))
    if str(ROOT) not in sys.path:
        sys.path.insert(0, str(ROOT))
    if str(ROOT / "python-api") not in sys.path:
        sys.path.insert(0, str(ROOT / "python-api"))

    coverage_obj = None
    if args.coverage:
        try:
            coverage_mod = importlib.import_module("coverage")
        except ModuleNotFoundError:
            print("coverage is not installed. Install it and rerun, or use --no-coverage.")
            return 2
        coverage_obj = coverage_mod.Coverage(config_file=str(ROOT / ".coveragerc"))
        coverage_obj.start()

    full_suite = unittest.TestSuite()
    for suite_name in suites:
        full_suite.addTests(_discover_suite(suite_name))

    result = unittest.TextTestRunner(verbosity=args.verbosity).run(full_suite)

    if coverage_obj is not None:
        coverage_obj.stop()
        coverage_obj.save()
        percent = coverage_obj.report()
        if percent < args.min_coverage:
            print(
                f"\nCoverage gate failed: {percent:.2f}% < {args.min_coverage:.2f}%"
            )
            return 1

    return 0 if result.wasSuccessful() else 1


if __name__ == "__main__":
    raise SystemExit(main())
