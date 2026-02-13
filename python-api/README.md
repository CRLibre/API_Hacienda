# Python API Service (`python-api`)

## Purpose
Python 3.14.3 service for `API_Hacienda` preserving the legacy API contract (`w` + `r`, envelope, and status semantics).

## Entrypoints
- API: `GET|POST|PUT /api.php`
- Health: `GET /healthz`
- Readiness: `GET /readyz`
- Root metadata: `GET /`
- FE worker pump: `POST /internal/fe/worker/pump`
- FE worker requeue: `POST /internal/fe/worker/requeue`
- FE job status: `GET /internal/fe/jobs/{job_id}`

## Runtime mode
- Native mode only: `API_HACIENDA_PHP_FALLBACK_URL` must be empty/unset.
- DB backend can run in:
  - `sqlalchemy` (default, direct MySQL URL)
  - `rds_data_api` (Aurora/RDS Data API, no direct DB socket from app)
- Async FE mode:
  - `API_HACIENDA_FE_ASYNC_ENABLED=true`
  - `API_HACIENDA_FE_SQS_QUEUE_URL` configured
  - `send/*` routes enqueue jobs; worker endpoints process/retry with persisted state.
  - Built-in idempotency key avoids duplicate processing for same logical invoice payload.
  - Automatic stuck-job recovery (`PROCESSING` timeout) protects against app/worker crashes.

## Local run
```bash
uvicorn python_api.main:app --host 0.0.0.0 --port 8080
```

## FE async worker tick (manual)
```bash
cd /Users/juandi/Documents/github/API_Hacienda
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python scripts/fe_worker_tick.py
```

## AWS Lambda handlers
- API Gateway adapter: `python_api.aws_lambda_handlers.api_gateway_handler`
- SQS worker: `python_api.aws_lambda_handlers.sqs_worker_handler`
- EventBridge scheduler: `python_api.aws_lambda_handlers.scheduler_requeue_handler`

Container build for Lambda:

```bash
cd /Users/juandi/Documents/github/API_Hacienda/python-api
docker build -f Dockerfile.lambda -t api-hacienda-lambda:latest .
```

Reference serverless template:
- `/Users/juandi/Documents/github/API_Hacienda/infra/aws/serverless.yml`

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
