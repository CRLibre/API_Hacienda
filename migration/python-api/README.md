# Python API Service (`python-api`)

## Purpose
Python 3.14.3 service for `API_Hacienda` preserving legacy API contract (`w` + `r`, envelope, and status semantics).

## Status
- Contract routes: `116` tracker rows (`115` unique routes).
- Tracker state: `115` legacy routes in `parity_status=passed` and `cutover_status=cutover`, plus `genXML/gen_xml_rep` as additive `python_only_v44`.
- Global live parity (PHP vs Python) for legacy scope: `460/460` scenarios passing (`migration/reports/global-parity-live.tsv`).
- Runtime fallback routes: `0`.
- Quick status snapshot: `migration/scripts/migration_status_snapshot.py --migration-root migration`.

## Entrypoints
- API: `GET|POST|PUT /api.php`
- Health: `GET /healthz`
- Readiness: `GET /readyz`
- Root metadata: `GET /`

## Run modes
- Native mode (target): `API_HACIENDA_PHP_FALLBACK_URL` empty/unset.
- Hybrid mode (temporary safety net): set `API_HACIENDA_PHP_FALLBACK_URL=http://<legacy-host>/api.php`.

`/readyz` returns:
```json
{"status":"ok","fallback_enabled":false}
```
when running native.

## Local run
```bash
uvicorn python_api.main:app --host 0.0.0.0 --port 8080
```

## Real Dev flow (laptop)
End-to-end FE test against local Python API (optionally sends/consults Hacienda staging):
```bash
/Users/juandi/Documents/github/API_Hacienda/migration/python-api/.venv313/bin/python \
  /Users/juandi/Documents/github/API_Hacienda/migration/scripts/dev_real_fe_flow.py \
  --api-base-url http://127.0.0.1:8080 \
  --client-id api-stag \
  --username "<usuario_hacienda>" \
  --password "<password_hacienda>" \
  --p12-path "/ruta/certificado.p12" \
  --p12-pin "<pin_certificado>" \
  --cedula "<cedula_emisor>" \
  --proveedor-sistemas "<cedula_proveedor_sistemas>"
```

For local-only validation (no Hacienda send):
```bash
/Users/juandi/Documents/github/API_Hacienda/migration/python-api/.venv313/bin/python \
  /Users/juandi/Documents/github/API_Hacienda/migration/scripts/dev_real_fe_flow.py \
  --api-base-url http://127.0.0.1:8080 \
  --p12-path "/ruta/certificado.p12" \
  --p12-pin "<pin_certificado>" \
  --cedula "<cedula_emisor>" \
  --proveedor-sistemas "<cedula_proveedor_sistemas>" \
  --skip-send
```

## Native go/no-go check
Before decommissioning PHP baseline, run:
```bash
cd ..
./scripts/native_go_no_go.py \
  --migration-root . \
  --candidate-url http://127.0.0.1:8080
```

Expected result:
`GO: native deployment is ready. PHP baseline can be decommissioned.`

## Contract behavior notes
- Request parsing precedence:
1. Query params when `w` exists in query.
2. Form body when `w` exists in form payload.
3. JSON body when `w` exists in JSON object.
- Error envelope:
```json
{"status":"error","resp":"<message>"}
```
