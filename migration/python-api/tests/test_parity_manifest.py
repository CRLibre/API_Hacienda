import json
from pathlib import Path


def test_parity_manifest_routes_and_files_exist():
    repo_root = Path(__file__).resolve().parents[2]
    manifest_path = repo_root / "tests" / "fixtures" / "parity" / "manifest.json"
    assert manifest_path.exists()

    data = json.loads(manifest_path.read_text(encoding="utf-8"))
    assert len(data) == 29

    for route in data:
        for scenario in ("success", "validation_error", "auth_error", "malformed"):
            request_file = repo_root / route["scenarios"][scenario]["request"]
            response_file = repo_root / route["scenarios"][scenario]["response"]
            assert request_file.exists()
            assert response_file.exists()
