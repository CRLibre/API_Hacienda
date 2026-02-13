from __future__ import annotations

import unittest

from tests.support import bootstrap  # noqa: F401

from fastapi import FastAPI, Request
from fastapi.testclient import TestClient

from python_api.compat import envelope_error, envelope_ok, parse_legacy_params


class CompatParserTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        app = FastAPI()

        @app.api_route("/parse", methods=["GET", "POST", "PUT", "PATCH"])
        async def _parse(request: Request):
            parsed = await parse_legacy_params(request)
            return {"source": parsed.source, "params": parsed.params}

        cls.client = TestClient(app)

    def test_parse_query_params(self) -> None:
        response = self.client.get("/parse?w=ejemplo&r=hola&x=1")
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "query")
        self.assertEqual(payload["params"]["w"], "ejemplo")
        self.assertEqual(payload["params"]["r"], "hola")
        self.assertEqual(payload["params"]["x"], "1")

    def test_parse_json_params(self) -> None:
        response = self.client.post("/parse", json={"w": "ejemplo", "r": "hola", "nombre": "Juan"})
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "json")
        self.assertEqual(payload["params"]["w"], "ejemplo")
        self.assertEqual(payload["params"]["nombre"], "Juan")

    def test_parse_invalid_json(self) -> None:
        response = self.client.post(
            "/parse",
            data='{"w": "ejemplo", ',
            headers={"content-type": "application/json"},
        )
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "invalid_json")
        self.assertEqual(payload["params"], {})

    def test_parse_form_urlencoded(self) -> None:
        response = self.client.post(
            "/parse",
            data={"w": "ejemplo", "r": "hola", "apellido": "Perez"},
            headers={"content-type": "application/x-www-form-urlencoded"},
        )
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "form")
        self.assertEqual(payload["params"]["apellido"], "Perez")

    def test_parse_form_multipart(self) -> None:
        response = self.client.post(
            "/parse",
            files={"w": (None, "ejemplo"), "r": (None, "hola"), "nombre": (None, "Ana")},
        )
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "form")
        self.assertEqual(payload["params"]["nombre"], "Ana")

    def test_parse_none_when_missing_dispatch(self) -> None:
        response = self.client.get("/parse?x=1")
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["source"], "none")
        self.assertEqual(payload["params"], {})

    def test_envelope_ok(self) -> None:
        response = envelope_ok({"a": 1}, status_code=201)
        self.assertEqual(response.status_code, 201)
        self.assertEqual(response.body.decode("utf-8"), '{"status":"ok","resp":{"a":1}}')

    def test_envelope_error(self) -> None:
        response = envelope_error("bad", status_code=422)
        self.assertEqual(response.status_code, 422)
        self.assertEqual(response.body.decode("utf-8"), '{"status":"error","resp":"bad"}')


if __name__ == "__main__":
    unittest.main()
