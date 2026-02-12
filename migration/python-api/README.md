# Python Compatibility Service (`python-api`)

## Purpose
This service is the Python 3.14.3 compatibility layer for `API_Hacienda`.
It preserves the `w` + `r` dispatch model and JSON envelope while routes are migrated incrementally.

## Current state
- Entrypoint implemented: `GET/POST/PUT /api.php`
- Health endpoints: `/healthz`, `/readyz`
- Correlation ID and JSON structured logging enabled
- Route registry includes all 115 routes from `python_api/data/routes.tsv`
- Hybrid execution:
- Python handlers live for `cala/cala_core|cala_default|cala_test_install`, `clave/clave`, `consultar/consultarCom`, `crypto/makeKey` (+ denied compatibility for `crypto/encrypt|desencrypt`), `files/filesGetUrl|files_view_file|upload`, `firmarXML/firmar`, `genXML/gen_xml_fe|gen_xml_nc|gen_xml_nd|gen_xml_te|gen_xml_mr|gen_xml_fec|gen_xml_fee|test` (legacy PHP bridge mode), `send/json|sendMensaje|sendTE`, `token/gettoken|refresh`, and all 13 `users/*` routes
- `facturador/*` routes are fully dispatched by native Python handlers (52/52) and tracked as `python_handler_native_partial_no_parity`; parity fixtures and behavioral hardening are still pending.
- Additional native handlers are active for `XmlToBase64/encode`, `callback/callback`, `check/checkxml`, `ejemplo/hola|un_usuario`, `fileUploader/subir_certif|subir_xml|test`, `geoloc/geoloc_create_tables|geoloc_get_by_ip|geoloc_load_blocks|geoloc_load_locations`, `wirez/conversations_get_details|messages_get_in_conversation|messages_get_recent|messages_send`, `makeJson/makeJson`, `makeQR/makeQR`, `sendMail/sendmail`, `signXML/signFE`, and `version/version`.
- Remaining fallback-proxy modules are now only `crlibreall/*`.
- Python handlers live for all 13 `users` routes (`login_auto`, `users_avatar_get`, `users_avatar_upload`, `users_confirm_session_vilidity`, `users_get_list`, `users_get_my_details`, `users_log_me_in`, `users_log_me_out`, `users_personal_bg_get`, `users_personal_bg_upload`, `users_recover_pwd`, `users_register`, `users_update_profile`)
- Fallback proxy enabled for remaining not-yet-migrated routes (`API_HACIENDA_PHP_FALLBACK_URL`)
- SQLAlchemy + Alembic scaffolding included for DB-compatible migration
- Automatic raw port mirror generated under `python-api/auto_port/` from PHP source

## Environment
Copy `.env.example` to `.env` and adjust values.

## Run (example)
```bash
uvicorn python_api.main:app --host 0.0.0.0 --port 8090
```

## Contract notes
- Dispatch params are parsed with PHP-compatible precedence:
  1. Query string when `w` exists in query.
  2. Form body when `w` exists in form data.
  3. JSON body when `w` exists in JSON object.
- Local service errors return envelope:
```json
{"status":"error","resp":"<message>"}
```
