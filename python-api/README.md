# Python API Service (`python-api`)

## Purpose
Python 3.14.3 service for `API_Hacienda` preserving the legacy API contract (`w` + `r`, envelope, and status semantics).

## Entrypoints
- API: `GET|POST|PUT /api.php`
- Health: `GET /healthz`
- Readiness: `GET /readyz`
- Root metadata: `GET /`

## Runtime mode
- Native mode only: `API_HACIENDA_PHP_FALLBACK_URL` must be empty/unset.

## Local run
```bash
uvicorn python_api.main:app --host 0.0.0.0 --port 8080
```

## Real dev FE flow (optional)
```bash
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  /Users/juandi/Documents/github/API_Hacienda/scripts/dev_real_fe_flow.py \
  --api-base-url http://127.0.0.1:8080 \
  --p12-path "/ruta/certificado.p12" \
  --p12-pin "<pin_certificado>" \
  --cedula "<cedula_emisor>" \
  --proveedor-sistemas "<cedula_proveedor_sistemas>" \
  --skip-send
```

## Tests
El repositorio usa `unittest` centralizado en:

- `/Users/juandi/Documents/github/API_Hacienda/tests/unit`
- `/Users/juandi/Documents/github/API_Hacienda/tests/functional`
- `/Users/juandi/Documents/github/API_Hacienda/tests/smoke`

Runner:

```bash
cd /Users/juandi/Documents/github/API_Hacienda
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  scripts/run_tests.py --suite unit --suite functional --coverage --min-coverage 80
```
