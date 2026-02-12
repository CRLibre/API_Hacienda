from __future__ import annotations

import os
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api import constants as c
from python_api.config import get_settings
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import engine

settings = get_settings()


async def cala_core(_: Request, __: dict[str, str]) -> JSONResponse:
    return tools_reply_compatible("I don't actualyl do anything :(")


async def cala_default(_: Request, __: dict[str, str]) -> JSONResponse:
    # Compatibility: route exists in module.php but function is missing in legacy PHP.
    return tools_reply_compatible(c.ERROR_BAD_REQUEST)


async def cala_test_install(_: Request, __: dict[str, str]) -> JSONResponse:
    br = "<br/>"
    all_good = f"All good :) {br} "
    all_bad = f"Errors found :( {br} "

    core_install = str(Path.cwd())
    files_path = Path(settings.files_base_path).expanduser()
    contrib_path = Path.cwd() / "api" / "contrib"
    resources_path = Path.cwd() / "recursos"

    files_good = files_path.exists() and os.access(files_path, os.W_OK)
    contrib_good = contrib_path.is_dir()
    resources_good = resources_path.is_dir()

    db_comment = "All good"
    try:
        with engine.connect():
            pass
    except Exception as exc:
        db_comment = str(exc)

    all_tests = [
        ("Core Installation", f"Your core installation is in: {core_install}"),
        ("PHP Version", "I am at least PHP version 5.3.0, my version: python-compat"),
        ("Database connection", db_comment),
        (
            "Files storage",
            (all_good if files_good else all_bad + "Your files storage was not found or the path is not accesible by me: ")
            + str(files_path),
        ),
        (
            "Contributed modules",
            (all_good if contrib_good else all_bad + "Your contrib modules where not found or the path is not accessible: ")
            + str(contrib_path),
        ),
        (
            "Resources path",
            (all_good if resources_good else all_bad + "Your resources path was not found or the path is not accessible: ")
            + str(resources_path),
        ),
    ]

    output = "<h1>Cala Installation check proccess</h1>"
    output += f"This is highly advanced installation check, please read carefully any errors found and correct them before using Cala. {br} {br}"

    for name, comment in all_tests:
        output += f"<strong>Name:</strong> {name} {br} Result: {comment} {br} {br}"

    output += f"-------------------------------------------------- {br}"
    output += "Please correct any errors found and go on with it!"
    return tools_reply_compatible(output)
