from __future__ import annotations

import csv
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True, slots=True)
class ParamSpec:
    key: str
    default: str
    required: bool


def _params_data_file() -> Path:
    return Path(__file__).resolve().parent / "data" / "params.tsv"


def _load_params() -> dict[tuple[str, str], list[ParamSpec]]:
    data_file = _params_data_file()
    if not data_file.exists():
        return {}

    routes: dict[tuple[str, str], list[ParamSpec]] = {}
    with data_file.open("r", encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh, delimiter="\t")
        for row in reader:
            w = (row.get("w") or "").strip()
            r = (row.get("r") or "").strip()
            key = (row.get("param_key") or "").strip()
            default = row.get("default") or ""
            required = (row.get("required") or "").strip().lower() == "true"
            if not w or not r or not key:
                continue
            routes.setdefault((w, r), []).append(ParamSpec(key=key, default=default, required=required))
    return routes


PARAMS_BY_ROUTE = _load_params()


def apply_defaults_and_validate(params: dict[str, str], w: str, r: str) -> tuple[dict[str, str], str | None]:
    specs = PARAMS_BY_ROUTE.get((w, r), [])
    enriched = dict(params)

    for spec in specs:
        current = enriched.get(spec.key, "")
        if str(current).strip() == "":
            if spec.required:
                return enriched, spec.key
            enriched[spec.key] = spec.default
    return enriched, None

