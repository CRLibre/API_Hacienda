# Plan Maestro: Migración Total de PHP a Python (sin PHP al final)

## Objetivo
Migrar **100% del API** del repositorio a Python para que la operación diaria no dependa de PHP.

## Aclaración de versión
- “PHP 3.8” no existe como versión oficial de PHP.
- En este repositorio ya dejamos el baseline alineado a **PHP 8.3** solo como estado transitorio mientras se migra.

## Definición de terminado (Done)
La migración se considera terminada únicamente cuando se cumpla todo:
1. Todas las rutas (`w`+`r`) funcionan desde Python con paridad validada.
2. El fallback a PHP está apagado en producción.
3. No hay tráfico de producción hacia `www/api.php` en PHP.
4. CI/CD solo construye, prueba y despliega Python.
5. Se eliminan o archivan módulos PHP del runtime productivo.

## Inventario real de alcance
- Rutas totales: **115** (`docs/contracts/routes.tsv`).
- Tracker operativo: `docs/contracts/migration-tracker.tsv`.
- Distribución por olas:
- `W1_CORE`: 38 rutas.
- `W2_BUSINESS`: 62 rutas.
- `W3_SUPPORT`: 15 rutas.

## Estrategia de ejecución (ordenada y controlada)

### Fase 0: Control de contrato y línea base (ya iniciada)
- Contrato generado desde código:
- `docs/contracts/routes.tsv`
- `docs/contracts/params.tsv`
- Fixtures de paridad inicializados:
- `tests/fixtures/parity/**`
- Servicio Python de compatibilidad en modo fallback:
- `python-api/python_api/main.py`
- `python-api/python_api/fallback.py`

### Fase 1: W1_CORE (38 rutas, prioridad alta)
Módulos:
- `users`, `clave`, `token`, `consultar`, `send`, `genXML`, `firmarXML`, `db`, `crypto`, `files`, `cala`.

Entregables:
1. Implementación real en Python por ruta (handler por `w/r`).
2. Captura de fixture golden por escenario (`success`, `validation_error`, `auth_error`, `malformed`).
3. Prueba de paridad por ruta y corte por canary.

Salida de fase:
- 100% de rutas W1 con `parity_status=passed` y `cutover_status=cutover`.

### Fase 2: W2_BUSINESS (62 rutas, mayor volumen funcional)
Módulos:
- `facturador`, `crlibreall`, `fileUploader`, `sendMail`, `signXML`, `makeQR`.

Entregables:
1. Migración funcional con paridad.
2. Pruebas de regresión por flujos de negocio (emisión, firmado, envío, consulta).
3. Endurecimiento de dependencias criptográficas y de correo en Python.

Salida de fase:
- 100% de W2 con paridad y cutover.

### Fase 3: W3_SUPPORT (15 rutas)
Módulos:
- `wirez`, `geoloc`, `XmlToBase64`, `makeJson`, `callback`, `check`, `version`, `ejemplo`, `baseModule`.

Salida de fase:
- 100% de W3 con paridad y cutover.

### Fase 4: Retiro de PHP
1. Poner `API_HACIENDA_PHP_FALLBACK_URL` en null/deshabilitado.
2. Verificación de tráfico: cero requests de producción al PHP runtime.
3. Remover servicio PHP de `docker-compose` productivo.
4. Congelar/eliminar árbol PHP del runtime.

## Reglas de calidad por ruta
Cada ruta pasa por este pipeline:
1. `status=not_started` -> `in_progress`
2. Handler Python implementado.
3. Fixtures capturados (`parity_status=captured`).
4. Paridad aprobada (`parity_status=passed`).
5. Canary y cutover (`cutover_status=cutover`).
6. Monitoreo 7 días sin regresiones.

## Riesgos y mitigación
- **Riesgo**: Big bang con caída de servicio.
- Mitigación: cutover por ruta/módulo, no por repo completo en un solo switch.
- **Riesgo**: diferencias en validaciones y errores.
- Mitigación: golden fixtures y pruebas de paridad obligatorias antes de cutover.
- **Riesgo**: deuda en módulos viejos (`facturador`).
- Mitigación: W2 dedicado con tracker por endpoint.

## Gobierno de ejecución
- Fuente única de avance: `docs/contracts/migration-tracker.tsv`.
- Script para regenerar tracker:
- `scripts/generate_migration_tracker.sh`
- Regla: no se marca `cutover` sin `parity_status=passed`.

## Comandos útiles
```bash
# Regenerar contrato
./scripts/generate_api_contract.sh .

# Regenerar tracker de migración
./scripts/generate_migration_tracker.sh .

# Sincronizar contrato/tracker dentro de python-api
./scripts/sync_python_route_data.sh .

# Portado automático masivo (raw, sin refactor)
./scripts/auto_port_php_to_python.py --root . --out python-api/auto_port

# Inicializar fixtures de paridad
./scripts/init_parity_fixtures.sh .

# Capturar golden fixtures desde PHP actual
API_BASE_URL=http://127.0.0.1:8080 ./scripts/capture_golden_fixtures.sh .
```
