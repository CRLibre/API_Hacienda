# AWS Serverless Deployment (MVP)

This folder contains a SAM template for a low-ops deployment with:

- API Gateway (HTTP API)
- Lambda API adapter (FastAPI via Mangum)
- SQS FIFO queue + DLQ
- Lambda worker for FE async jobs
- EventBridge scheduler for retry/recovery ticks
- Aurora/RDS Data API support (no app-side DB sockets)

## Files
- `serverless.yml`: SAM template

## Build lambda image
```bash
cd /Users/juandi/Documents/github/API_Hacienda/python-api
docker build -f Dockerfile.lambda -t api-hacienda-lambda:latest .
```

Push this image to ECR and use the ECR URI as `LambdaImageUri`.

## Deploy (example)
```bash
cd /Users/juandi/Documents/github/API_Hacienda
sam build -t infra/aws/serverless.yml
sam deploy --guided
```

Key parameters:
- `LambdaImageUri`
- `CryptoKey`
- `DbBackend` = `rds_data_api`
- `RdsDataResourceArn`
- `RdsDataSecretArn`
- `RdsDataDatabase`

## Resilience behavior
- `send/*` writes to `fe_async_jobs` and enqueues to SQS.
- Worker retries with exponential backoff and jitter.
- Jobs older than `API_HACIENDA_FE_RETRY_MAX_AGE_SECONDS` go to `DEAD_LETTER` state.
- Scheduler requeues due jobs and recovers stale `PROCESSING` jobs if app/worker crashed.

## Cost posture
- Designed to avoid NAT gateway dependency by using Data API and managed services.
- For very low volume, this avoids fixed NAT costs while keeping automatic recovery paths.
