import json
from pathlib import Path

import pytest
from httpx import ASGITransport, AsyncClient

from python_api.main import app


@pytest.mark.asyncio
async def test_parity_harness_for_captured_fixtures():
    repo_root = Path(__file__).resolve().parents[2]
    manifest_path = repo_root / "tests" / "fixtures" / "parity" / "manifest.json"
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))

    captured_cases = []
    for route in manifest:
        for scenario in ("success", "validation_error", "auth_error", "malformed"):
            request_path = repo_root / route["scenarios"][scenario]["request"]
            response_path = repo_root / route["scenarios"][scenario]["response"]

            response_fixture = json.loads(response_path.read_text(encoding="utf-8"))
            if not response_fixture.get("captured"):
                continue

            request_fixture = json.loads(request_path.read_text(encoding="utf-8"))
            captured_cases.append((request_fixture, response_fixture))

    if not captured_cases:
        pytest.skip("No captured golden fixtures found. Run scripts/capture_golden_fixtures.sh first.")

    async with AsyncClient(transport=ASGITransport(app=app), base_url="http://testserver") as client:
        for request_fixture, response_fixture in captured_cases:
            method = request_fixture.get("method", "POST")
            path = request_fixture.get("path", "/api.php")
            params = request_fixture.get("params", {})
            expected_status_code = response_fixture["status_code"]

            if method.upper() == "GET":
                response = await client.get(path, params=params)
            else:
                response = await client.request(method, path, data=params)

            assert response.status_code == expected_status_code

