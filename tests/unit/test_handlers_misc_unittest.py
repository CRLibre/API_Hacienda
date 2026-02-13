from __future__ import annotations

import json
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

from python_api.handlers import legacyall, geoloc
from python_api.handlers import version as version_handler


class MiscHandlersAsyncTest(unittest.IsolatedAsyncioTestCase):
    async def test_legacyall_noop_routes(self) -> None:
        for fn in (legacyall.FE, legacyall.NC, legacyall.ND, legacyall.gen_xml_nc):
            response = await fn(None, {})
            payload = json.loads(response.body.decode("utf-8"))
            self.assertEqual(payload["status"], "ok")
            self.assertIsNone(payload["resp"])

    async def test_geoloc_route_behavior(self) -> None:
        get_ip = await geoloc.geoloc_get_by_ip(None, {})
        payload = json.loads(get_ip.body.decode("utf-8"))
        self.assertEqual(payload["status"], "error")
        self.assertIn("Falta el parametro requerido", payload["resp"])

        create_tables = await geoloc.geoloc_create_tables(None, {})
        self.assertEqual(create_tables.status_code, 200)
        self.assertEqual(create_tables.body, b"")

        load_locations = await geoloc.geoloc_load_locations(None, {})
        self.assertEqual(load_locations.status_code, 200)
        self.assertEqual(load_locations.body, b"")

        load_blocks = await geoloc.geoloc_load_blocks(None, {})
        self.assertEqual(load_blocks.status_code, 200)
        self.assertEqual(load_blocks.body, b"")


class VersionHelpersSyncTest(unittest.TestCase):
    def test_git_version_parsing(self) -> None:
        class _Result:
            returncode = 0
            stdout = "init-123-gabc1234\n"

        with patch("python_api.handlers.version.subprocess.run", return_value=_Result()):
            self.assertEqual(version_handler._git_version(), "abc1234")  # noqa: SLF001

    def test_file_version_validation(self) -> None:
        with tempfile.TemporaryDirectory() as td:
            valid_file = Path(td) / "VERSION"
            valid_file.write_text("1234567", encoding="utf-8")
            with patch("python_api.handlers.version.VERSION_FILE", valid_file):
                self.assertEqual(version_handler._file_version(), "1234567")  # noqa: SLF001

            invalid_file = Path(td) / "VERSION_BAD"
            invalid_file.write_text("12", encoding="utf-8")
            with patch("python_api.handlers.version.VERSION_FILE", invalid_file):
                self.assertIsNone(version_handler._file_version())  # noqa: SLF001


if __name__ == "__main__":
    unittest.main()
