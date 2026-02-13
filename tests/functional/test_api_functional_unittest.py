from __future__ import annotations

import unittest

from tests.support import bootstrap  # noqa: F401

from fastapi.testclient import TestClient

from python_api.main import app


class APIFunctionalTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.client = TestClient(app)

    def test_healthz(self) -> None:
        response = self.client.get("/healthz")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["status"], "ok")

    def test_readyz(self) -> None:
        response = self.client.get("/readyz")
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["status"], "ok")
        self.assertFalse(payload["fallback_enabled"])

    def test_root_metadata(self) -> None:
        response = self.client.get("/")
        self.assertEqual(response.status_code, 200)
        payload = response.json()
        self.assertEqual(payload["status"], "ok")
        self.assertEqual(payload["resp"]["mode"], "native")

    def test_api_query_route_version(self) -> None:
        response = self.client.get("/api.php?w=version&r=version")
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["status"], "ok")

    def test_api_json_route(self) -> None:
        response = self.client.post("/api.php", json={"w": "ejemplo", "r": "hola"})
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["status"], "ok")
        self.assertIn("hola", response.json()["resp"])

    def test_api_form_route(self) -> None:
        response = self.client.post(
            "/api.php",
            data={"w": "ejemplo", "r": "un_usuario", "nombre": "Ana", "apellido": "Perez"},
        )
        self.assertEqual(response.status_code, 200)
        self.assertIn("Ana, Perez", response.json()["resp"])

    def test_api_multipart_route(self) -> None:
        response = self.client.post(
            "/api.php",
            files={"w": (None, "ejemplo"), "r": (None, "hola")},
        )
        self.assertEqual(response.status_code, 200)
        self.assertIn("hola", response.json()["resp"])

    def test_invalid_json_returns_400(self) -> None:
        response = self.client.post(
            "/api.php",
            data='{"w":"ejemplo",',
            headers={"content-type": "application/json"},
        )
        self.assertEqual(response.status_code, 400)
        self.assertEqual(response.json()["status"], "error")

    def test_missing_dispatch_returns_legacy_text(self) -> None:
        response = self.client.get("/api.php")
        self.assertEqual(response.status_code, 200)
        self.assertTrue(response.text.startswith("La informacion json enviada contiene errores."))
        self.assertTrue(response.headers["content-type"].startswith("text/html"))

    def test_unknown_module_and_unknown_function(self) -> None:
        missing_module = self.client.get("/api.php?w=nope&r=test")
        self.assertEqual(missing_module.status_code, 200)
        self.assertIn("Module not found", missing_module.json()["resp"])

        missing_function = self.client.get("/api.php?w=version&r=nope")
        self.assertEqual(missing_function.status_code, 200)
        self.assertIn("Function not found", missing_function.json()["resp"])

    def test_required_param_validation_error(self) -> None:
        response = self.client.get("/api.php?w=check&r=checkxml")
        self.assertEqual(response.status_code, 200)
        self.assertIn("Falta el parametro requerido", response.json()["resp"])


if __name__ == "__main__":
    unittest.main()
