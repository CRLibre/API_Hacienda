# API Hacienda (Python)

Este repositorio ya esta migrado a un servicio Python para Factura Electronica CR.
El baseline PHP fue retirado del codigo fuente.

## Runtime actual
- Lenguaje: Python 3.14.3
- API framework: FastAPI
- ORM/DB: SQLAlchemy 2.x + Alembic
- XML/XSD: lxml
- Firma XML: signxml + cryptography
- Base de datos: MariaDB/MySQL compatible

## Entrypoints
- `GET|POST|PUT /api.php`
- `GET /healthz`
- `GET /readyz`

Se mantiene el contrato legacy (`w` + `r`) y el envelope JSON para compatibilidad con clientes existentes.

## Estructura
- Servicio principal: `migration/python-api`
- Scripts de migracion/paridad: `migration/scripts`
- Contratos/trackers/reportes: `migration/docs`, `migration/reports`

## Ejecutar local
```bash
cd migration/python-api
PYTHONPATH=. .venv313/bin/python -m uvicorn python_api.main:app --host 127.0.0.1 --port 8080
```

## Docker Compose
```bash
docker compose up -d --build
```

API disponible en `http://127.0.0.1:8080/api.php`.

## Nota de migracion
- No quedan archivos `.php` en el repositorio.
- Recursos legacy necesarios (XSD/geoloc/version/static) fueron movidos a `migration/python-api/resources`.
