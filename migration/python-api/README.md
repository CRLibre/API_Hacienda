# Python API Service (`python-api`)

## Purpose
Python 3.14.3 service for `API_Hacienda` preserving legacy API contract (`w` + `r`, envelope, and status semantics).

## Status
- Contract routes: `115` tracker rows (`114` unique routes).
- Tracker state: `status=completed`, `parity_status=passed`, `cutover_status=cutover` for all rows.
- Global live parity (PHP vs Python): `460/460` scenarios passing (`migration/reports/global-parity-live.tsv`).
- Runtime fallback routes: `0`.

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
