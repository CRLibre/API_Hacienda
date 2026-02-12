from __future__ import annotations

import os
import subprocess
from pathlib import Path

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api import constants as c
from python_api.config import get_settings
from python_api.db.session import engine
from python_api.responses import tools_reply_compatible

settings = get_settings()
REPO_ROOT = Path(__file__).resolve().parents[4]


async def cala_core(_: Request, __: dict[str, str]) -> JSONResponse:
    return tools_reply_compatible("I don't actualyl do anything :(")


async def cala_default(_: Request, __: dict[str, str]) -> JSONResponse:
    # Compatibility: route exists in module.php but function is missing in legacy PHP.
    return tools_reply_compatible(c.ERROR_BAD_REQUEST)


async def cala_test_install(_: Request, __: dict[str, str]) -> JSONResponse:
    bl = "<br/>"
    all_good_msg = f"All good :) {bl} "
    all_not_good_msg = f"Errors found :( {bl} "

    core_install = str((REPO_ROOT / "api").resolve()) + "/"
    files_path = Path(settings.files_base_path).expanduser()
    contrib_path = Path(core_install) / "contrib"
    resources_path = Path(core_install) / "resources"

    php_version = "unknown"
    try:
        res = subprocess.run(
            ["php", "-r", "echo PHP_VERSION;"],
            capture_output=True,
            text=True,
            check=False,
        )
        if res.returncode == 0 and res.stdout.strip():
            php_version = res.stdout.strip()
    except Exception:
        pass

    db_comment = "All good"
    try:
        with engine.connect():
            pass
    except Exception as exc:
        db_comment = str(exc)

    files_good = os.access(files_path, os.W_OK)
    try:
        files_perms = f"{files_path.stat().st_mode & 0o777:04o}"
    except OSError:
        files_perms = "0000"
    files_perms_good = files_perms == "0777"
    contrib_good = contrib_path.is_dir()
    resources_good = resources_path.is_dir()
    cron_token_is_default = (
        settings.cron_token == "ItIsGoodIfThisIsBigAndHasW3irDLeeT3rsAnd$ymb0lz.IniT"
    )

    all_tests = [
        ("Core Installation", f"Your core installation is in: {core_install}"),
        ("PHP Version", f"I am at least PHP version 5.3.0, my version: {php_version}"),
        ("Database connection", db_comment),
        (
            "Files storage",
            (all_good_msg if files_good else all_not_good_msg + "Your files storage was not found or the path is not accesible by me: ")
            + f"{files_path}/",
        ),
        (
            "Files storage permissions",
            (all_good_msg if not files_perms_good else all_not_good_msg)
            + (
                "Remember to put your files in a NON WEB ACCESSIBLE path and to secure its permissions,\n"
                "        it is best if they are only writable/redable by the web process which is usually www-root. Current perms are '%s'\n"
                "        %s They should be: 0644?\n"
                "        %s Are they secure? %s"
                % (
                    files_perms,
                    bl,
                    bl,
                    (all_good_msg if not files_perms_good else "They don't look like it"),
                )
            ),
        ),
        (
            "Contributed modules",
            (all_good_msg if contrib_good else all_not_good_msg + "Your contrib modules where not found or the path is not accessible: ")
            + f"{contrib_path}/",
        ),
        (
            "Resources path",
            (all_good_msg if resources_good else all_not_good_msg + "Your resources path was not found or the path is not accessible: ")
            + f"{resources_path}/",
        ),
        (
            "Security Token",
            (all_good_msg if not cron_token_is_default else all_not_good_msg + "You really need to change your token! "),
        ),
    ]

    output = "<h1>Cala Installation check proccess</h1>"
    output += f"This is highly advanced installation check, please read carefully any errors found and correct them before using Cala. {bl} {bl}"
    for name, comment in all_tests:
        output += f"<strong>Name:</strong> {name} {bl} Result: {comment} {bl} {bl}"
    output += f"-------------------------------------------------- {bl}"
    output += "Please correct any errors found and go on with it!"
    return tools_reply_compatible(output)
