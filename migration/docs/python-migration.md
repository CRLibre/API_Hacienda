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
- `genXML/gen_xml_fe` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_nc` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_nd` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_te` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_mr` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_fec` (legacy PHP bridge mode from Python handler)
- `genXML/gen_xml_fee` (legacy PHP bridge mode from Python handler)
- `genXML/test` (legacy PHP bridge mode from Python handler)
- `send/json`
- `send/sendMensaje`
- `send/sendTE`
- `facturador/*` routes (fully dispatched by native Python handlers; tracker state: `python_handler_native_partial_no_parity` for 52/52 routes)
- `XmlToBase64/encode`
- `callback/callback`
- `check/checkxml`
- `ejemplo/hola|un_usuario`
- `fileUploader/subir_certif|subir_xml|test`
- `makeJson/makeJson`
- `makeQR/makeQR`
- `sendMail/sendmail`
- `signXML/signFE`
- `version/version`
- `geoloc/geoloc_create_tables|geoloc_get_by_ip|geoloc_load_blocks|geoloc_load_locations`
- `wirez/conversations_get_details|messages_get_in_conversation|messages_get_recent|messages_send`
- `crlibreall/FE|NC|ND|gen_xml_nc` (native compatibility handlers; legacy behavior is effectively no-op/incomplete)
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
2. Replace `genXML` bridge mode with native Python XML generation + full XSD/signature parity tests.
3. Build parity fixtures for all `facturador/*` routes and close behavior gaps against legacy PHP.
4. Replace remaining fallback-proxy modules with native Python handlers and close parity fixtures.

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
