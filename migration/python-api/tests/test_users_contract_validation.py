import pytest
from httpx import ASGITransport, AsyncClient

from python_api.main import app


@pytest.mark.asyncio
async def test_users_register_missing_required_param_uses_legacy_error_envelope():
    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://testserver") as client:
        response = await client.post("/api.php", data={"w": "users", "r": "users_register"})

    assert response.status_code == 200
    payload = response.json()
    assert payload["status"] == "error"
    assert str(payload["resp"]).startswith("ERROR: Falta el parametro requerido:")

