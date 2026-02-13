from __future__ import annotations

import unittest
from unittest.mock import patch

from tests.support import bootstrap  # noqa: F401

from python_api.config import Settings
from python_api.db import models
from python_api.services import db_compat


class _DummyRow:
    def __init__(self, mapping):
        self._mapping = mapping


class _DummyResult:
    def __init__(self, one=None, many=None, rowcount=0):
        self._one = one
        self._many = many or []
        self.rowcount = rowcount

    def fetchone(self):
        return self._one

    def fetchall(self):
        return self._many


class _DummySession:
    def __init__(self, results):
        self._results = list(results)
        self.committed = False

    def execute(self, *_args, **_kwargs):
        return self._results.pop(0)

    def commit(self):
        self.committed = True

    def __enter__(self):
        return self

    def __exit__(self, *_exc):
        return False


class DBAndConfigTest(unittest.TestCase):
    def test_settings_defaults_and_overrides(self) -> None:
        settings = Settings(database_url="sqlite+pysqlite:///:memory:")
        self.assertEqual(settings.env, "test")
        self.assertEqual(settings.log_level, "WARNING")
        self.assertIn("sqlite", settings.database_url)

    def test_sqlalchemy_models_metadata(self) -> None:
        self.assertEqual(models.User.__tablename__, "users")
        self.assertEqual(models.Session.__tablename__, "sessions")

    def test_row_to_dict_and_fetch_one(self) -> None:
        row = _DummyRow({"idUser": 1, "userName": "juandi"})
        self.assertEqual(db_compat._row_to_dict(row), {"idUser": 1, "userName": "juandi"})  # noqa: SLF001

        session = _DummySession([_DummyResult(one=row)])
        with patch("python_api.services.db_compat.SessionLocal", return_value=session):
            loaded = db_compat.fetch_one("SELECT 1", {})
        self.assertEqual(loaded["idUser"], 1)

    def test_fetch_all_execute_and_insert_return_id(self) -> None:
        rows = [_DummyRow({"idMsg": 1}), _DummyRow({"idMsg": 2})]
        session_fetch_all = _DummySession([_DummyResult(many=rows)])
        with patch("python_api.services.db_compat.SessionLocal", return_value=session_fetch_all):
            loaded = db_compat.fetch_all("SELECT * FROM msgs", {})
        self.assertEqual(len(loaded), 2)

        session_execute = _DummySession([_DummyResult(rowcount=3)])
        with patch("python_api.services.db_compat.SessionLocal", return_value=session_execute):
            affected = db_compat.execute("UPDATE x SET y=1", {})
        self.assertEqual(affected, 3)
        self.assertTrue(session_execute.committed)

        session_insert = _DummySession([_DummyResult(rowcount=1), _DummyResult(one=_DummyRow({"idFile": 55}))])
        with patch("python_api.services.db_compat.SessionLocal", return_value=session_insert):
            new_id = db_compat.insert_and_return_id(
                "INSERT INTO files VALUES(1)",
                "SELECT idFile FROM files",
                {"name": "x"},
                "idFile",
            )
        self.assertEqual(new_id, 55)
        self.assertTrue(session_insert.committed)


if __name__ == "__main__":
    unittest.main()
