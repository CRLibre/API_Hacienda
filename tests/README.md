# Test House

Este directorio centraliza los tipos de pruebas del proyecto:

- `tests/unit`: pruebas unitarias (sin red, sin side effects externos).
- `tests/functional`: pruebas funcionales del API (`/api.php`, `healthz`, `readyz`).
- `tests/smoke`: smoke checks contra una instancia viva (opt-in por variable de entorno).
- `tests/support`: utilidades comunes para bootstrap/imports de tests.

## Ejecutar con unittest + cobertura

```bash
cd /Users/juandi/Documents/github/API_Hacienda
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  scripts/run_tests.py --suite unit --suite functional --coverage --min-coverage 80
```

## Ejecutar smoke (instancia real levantada)

```bash
cd /Users/juandi/Documents/github/API_Hacienda
SMOKE_API_BASE_URL=http://127.0.0.1:8080 \
/Users/juandi/Documents/github/API_Hacienda/python-api/.venv313/bin/python \
  scripts/run_tests.py --suite smoke --no-coverage
```

## Notas

- Se usa `unittest` (stdlib) como framework principal.
- Los smoke existentes en `scripts/` se mantienen.
- La meta de cobertura está configurada en 80% sobre la superficie actualmente cubierta.
- Algunos handlers legacy de alto acoplamiento externo están excluidos temporalmente del gate de cobertura y se validan por smoke/functional mientras se incrementa cobertura unitaria en fases.
