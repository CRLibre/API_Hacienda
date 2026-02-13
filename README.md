# API Hacienda (Python)

Este repositorio ya esta migrado a un servicio Python para Factura Electronica CR.
El baseline PHP fue retirado del codigo fuente.

## Runtime actual
- Lenguaje: Python 3.14.3
- API framework: FastAPI
- ORM/DB: SQLAlchemy 2.x + Alembic
- XML/XSD: lxml
- Firma XML: signxml + cryptography
- Base de datos: MySQL compatible

## Entrypoints
- `GET|POST|PUT /api.php`
- `GET /healthz`
- `GET /readyz`

Se mantiene el contrato legacy (`w` + `r`) y el envelope JSON para compatibilidad con clientes existentes.

## Estructura
- Servicio principal: `python-api`
- Script operativo FE dev: `scripts/dev_real_fe_flow.py`
- Infra local (SQL y volumen DB): `infra`
- Arbol resumido del repo: `TREE.md`

## Ejecutar local (sin Docker)
```bash
cd python-api
PYTHONPATH=. .venv313/bin/python -m uvicorn python_api.main:app --host 127.0.0.1 --port 8080
```

## Docker local (compose)
```bash
cp .env.local.example .env.local
docker compose --env-file .env.local up -d --build
```

API disponible en `http://127.0.0.1:8080/api.php`.

## Docker para AWS (compose)
```bash
cp .env.aws.example .env.aws
docker compose -f docker-compose.aws.yml --env-file .env.aws config
```

`docker-compose.aws.yml` esta pensado para desplegar el `python-api` con imagen preconstruida (por ejemplo en ECR), sin MySQL local y con endurecimiento base (`read_only`, `cap_drop`, `no-new-privileges`).

## AWS serverless (API + SQS + worker + scheduler)
- Plantilla SAM: `/Users/juandi/Documents/github/API_Hacienda/infra/aws/serverless.yml`
- Guia: `/Users/juandi/Documents/github/API_Hacienda/infra/aws/README.md`
- Docker lambda image: `/Users/juandi/Documents/github/API_Hacienda/python-api/Dockerfile.lambda`

## Pruebas (unittest)
Estructura de pruebas: `/Users/juandi/Documents/github/API_Hacienda/tests`

```bash
cd /Users/juandi/Documents/github/API_Hacienda
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  scripts/run_tests.py --suite unit --suite functional --coverage --min-coverage 80
```

Smoke live opcional:

```bash
cd /Users/juandi/Documents/github/API_Hacienda
SMOKE_API_BASE_URL=http://127.0.0.1:8080 \
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  scripts/run_tests.py --suite smoke --no-coverage
```

## Nota de migracion
- No quedan archivos `.php` en el repositorio.
- Recursos legacy necesarios (XSD/geoloc/version/static) fueron movidos a `python-api/resources`.
