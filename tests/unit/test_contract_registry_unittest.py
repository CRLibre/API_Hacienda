from __future__ import annotations

import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

from python_api import contract, registry


class ContractRegistryTest(unittest.TestCase):
    def test_registry_has_expected_routes(self) -> None:
        self.assertGreater(registry.total_routes(), 100)
        self.assertIsNotNone(registry.get_route("version", "version"))
        self.assertIsNotNone(registry.get_route("users", "users_log_me_in"))

    def test_apply_defaults_missing_required_respects_override_order(self) -> None:
        enriched, missing = contract.apply_defaults_and_validate({}, "check", "checkxml")
        self.assertEqual(missing, "tipoDocumento")
        self.assertEqual(enriched, {})

    def test_apply_defaults_unknown_route_does_not_fail(self) -> None:
        params = {"w": "x", "r": "y", "a": "1"}
        enriched, missing = contract.apply_defaults_and_validate(params, "x", "y")
        self.assertIsNone(missing)
        self.assertEqual(enriched, params)

    def test_ordered_specs_honors_override_and_keeps_remaining(self) -> None:
        specs = [
            contract.ParamSpec(key="consecutivo", default="", required=True),
            contract.ParamSpec(key="tipoDocumento", default="", required=True),
            contract.ParamSpec(key="tipoCedula", default="", required=True),
            contract.ParamSpec(key="extra", default="x", required=False),
        ]
        ordered = contract._ordered_specs("clave", "clave", specs)  # noqa: SLF001
        ordered_keys = [spec.key for spec in ordered]
        self.assertEqual(ordered_keys[:3], ["tipoDocumento", "tipoCedula", "consecutivo"])
        self.assertIn("extra", ordered_keys)

    def test_load_params_returns_empty_when_file_missing(self) -> None:
        with patch("python_api.contract._params_data_file", return_value=Path("/tmp/not_found_params.tsv")):
            loaded = contract._load_params()  # noqa: SLF001
        self.assertEqual(loaded, {})

    def test_load_params_reads_valid_rows(self) -> None:
        with tempfile.TemporaryDirectory() as td:
            params_file = Path(td) / "params.tsv"
            params_file.write_text(
                "w\tr\tparam_key\tdefault\trequired\tmodule_file\n"
                "mod\troute\tid\t\ttrue\t./m.php\n"
                "mod\troute\topt\tabc\tfalse\t./m.php\n",
                encoding="utf-8",
            )
            with patch("python_api.contract._params_data_file", return_value=params_file):
                loaded = contract._load_params()  # noqa: SLF001

        self.assertIn(("mod", "route"), loaded)
        self.assertEqual(loaded[("mod", "route")][0].key, "id")
        self.assertTrue(loaded[("mod", "route")][0].required)
        self.assertEqual(loaded[("mod", "route")][1].default, "abc")

    def test_load_routes_returns_empty_when_file_missing(self) -> None:
        with patch("python_api.registry._routes_data_file", return_value=Path("/tmp/not_found_routes.tsv")):
            loaded = registry._load_routes()  # noqa: SLF001
        self.assertEqual(loaded, ())

    def test_load_routes_reads_valid_rows(self) -> None:
        with tempfile.TemporaryDirectory() as td:
            routes_file = Path(td) / "routes.tsv"
            routes_file.write_text(
                "w\tr\tmodule_file\n"
                "mod\troute\t./api/mod.php\n",
                encoding="utf-8",
            )
            with patch("python_api.registry._routes_data_file", return_value=routes_file):
                loaded = registry._load_routes()  # noqa: SLF001
        self.assertEqual(len(loaded), 1)
        self.assertEqual(loaded[0].w, "mod")
        self.assertEqual(loaded[0].r, "route")


if __name__ == "__main__":
    unittest.main()
