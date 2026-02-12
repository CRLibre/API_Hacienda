# Migration Workspace

Todo el runtime Python y la infraestructura local viven bajo esta carpeta.

## Estructura

- `python-api/`: servicio API en Python.
- `infra/`: SQL seed + volumen local de MariaDB.
- `scripts/dev_real_fe_flow.py`: flujo FE end-to-end para pruebas locales.

## Run local

```bash
cd migration/python-api
uvicorn python_api.main:app --host 0.0.0.0 --port 8090
```
