# Python Migration Implementation Notes

## What is now in-repo
- New service scaffold in `./python-api`.
- FastAPI compatibility entrypoint at `/api.php` with PHP-like dispatch parsing.
- Full route manifest loaded from `./python-api/python_api/data/routes.tsv` (115 routes).
- Fallback proxy to existing PHP API for all non-implemented routes.
- Health endpoints:
- `/healthz`
- `/readyz`
- DB migration scaffolding:
- SQLAlchemy models in `./python-api/python_api/db/models.py`.
- Alembic setup in `./python-api/alembic`.
- Parity fixture framework:
- `./tests/fixtures/parity/manifest.json`
- capture and init scripts in `./scripts`.

## Current execution mode
- Compatibility mode is hybrid:
- Implemented routes execute directly in Python handlers.
- Non-implemented routes are proxied to `API_HACIENDA_PHP_FALLBACK_URL`.
- if fallback fails, service returns JSON envelope error.

### Implemented handlers (current)
- `clave/clave`
- `cala/cala_core`
- `cala/cala_default` (compatibility behavior: missing action in legacy module -> bad request code)
- `cala/cala_test_install`
- `token/gettoken`
- `token/refresh`
- `consultar/consultarCom`
- `crypto/makeKey`
- `crypto/encrypt` (compatibility behavior: access denied)
- `crypto/desencrypt` (compatibility behavior: access denied)
- `files/filesGetUrl`
- `files/files_view_file`
- `files/upload`
- `firmarXML/firmar`
- `send/json`
- `send/sendMensaje`
- `send/sendTE`
- `users/login_auto`
- `users/users_avatar_get`
- `users/users_avatar_upload`
- `users/users_personal_bg_get`
- `users/users_personal_bg_upload`
- `users/users_recover_pwd`
- `users/users_register`
- `users/users_log_me_in` (includes transitional MD5->bcrypt+crypto upgrade on successful legacy login)
- `users/users_log_me_out`
- `users/users_get_my_details`
- `users/users_update_profile`
- `users/users_get_list` (compatibility behavior: access denied)
- `users/users_confirm_session_vilidity`

## Next implementation steps
1. Implement `genXML` routes with XSD validation against `./www/xsd`.
2. Implement `genXML` routes with payload/XSD parity against legacy behavior.

## Full migration governance
- Master plan: `./docs/full-python-migration-plan.md`
- Route tracker: `./docs/contracts/migration-tracker.tsv`
- Tracker generator: `./scripts/generate_migration_tracker.sh`

## Automatic raw port (no-refactor baseline)
- Script: `./scripts/auto_port_php_to_python.py`
- Output: `./python-api/auto_port/`
- Current generated baseline:
- 148 PHP files mirrored
- 282 function blocks detected and stubbed
