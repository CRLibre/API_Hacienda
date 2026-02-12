from __future__ import annotations

import re
import subprocess
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible

REPO_ROOT = Path(__file__).resolve().parents[4]
VERSION_FILE = REPO_ROOT / "api/contrib/version/VERSION"


def _git_version() -> str | None:
    try:
        result = subprocess.run(
            ["git", "describe", "--long", "--match", "init", "--abbrev=7"],
            cwd=REPO_ROOT,
            capture_output=True,
            text=True,
            check=False,
        )
    except Exception:
        return None

    if result.returncode != 0:
        return None

    commit = result.stdout.strip()
    if len(commit) != 17:
        return None

    commit = re.sub(r"init\-([0-9]+)\-g", "", commit)
    if len(commit) == 7:
        return commit
    return None


def _file_version() -> str | None:
    if not VERSION_FILE.is_file():
        return None
    try:
        commit = VERSION_FILE.read_text(encoding="utf-8").strip()
    except OSError:
        return None
    if len(commit) == 7:
        return commit
    return None


async def version(_: Request, __: dict[str, str]) -> JSONResponse:
    commit = _git_version()
    if commit is None:
        commit = _file_version()
    if commit is not None:
        return tools_reply_compatible(f"Version: {commit}")
    return tools_reply_compatible("No tiene soporte git.")

