# Migration Workspace

Todo el trabajo de migración (audit, contratos, scripts, fixtures y servicio Python) vive bajo esta carpeta.

## Estructura

- `docs/`: auditoría, contrato y plan de migración.
- `scripts/`: utilitarios para generar contrato/tracker, fixtures y sync de datos.
- `tests/fixtures/parity/`: baseline de paridad para rutas migradas.
- `python-api/`: servicio de compatibilidad/migración en Python.

## Comandos rápidos

Desde la raíz del repositorio:

```bash
./migration/scripts/generate_api_contract.sh
./migration/scripts/generate_migration_tracker.sh
./migration/scripts/sync_python_route_data.sh
./migration/scripts/init_parity_fixtures.sh
```

Servicio Python:

```bash
cd migration/python-api
uvicorn python_api.main:app --host 0.0.0.0 --port 8090
```
