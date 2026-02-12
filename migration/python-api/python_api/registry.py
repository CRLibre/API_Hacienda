from __future__ import annotations

import csv
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True, slots=True)
class RouteSpec:
    w: str
    r: str
    module_file: str
    implemented: bool = False


def _routes_data_file() -> Path:
    return Path(__file__).resolve().parent / "data" / "routes.tsv"


def _load_routes() -> tuple[RouteSpec, ...]:
    data_file = _routes_data_file()
    if not data_file.exists():
        return ()

    rows: list[RouteSpec] = []
    with data_file.open("r", encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh, delimiter="\t")
        for row in reader:
            w = (row.get("w") or "").strip()
            r = (row.get("r") or "").strip()
            module_file = (row.get("module_file") or "").strip()
            if not w or not r:
                continue
            rows.append(RouteSpec(w=w, r=r, module_file=module_file, implemented=False))
    return tuple(rows)


ALL_ROUTES: tuple[RouteSpec, ...] = _load_routes()
ROUTE_INDEX = {(route.w, route.r): route for route in ALL_ROUTES}


def get_route(w: str, r: str) -> RouteSpec | None:
    return ROUTE_INDEX.get((w, r))


def total_routes() -> int:
    return len(ALL_ROUTES)

