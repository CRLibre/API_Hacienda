# Checkpoint: Factura Electronica CR en Python

Fecha: 2026-02-12

## Estado ejecutivo
- Rutas unicas totales: 115 (`114` legacy + `1` aditiva V4.4: `genXML/gen_xml_rep`).
- Filas en tracker: 116 (incluye clave duplicada legacy `facturador/company_get_env` por compatibilidad de parametro legacy).
- `status=completed`: 116/116.
- `parity_status=passed`: 115/116 + `python_only_v44_captured`: 1/116.
- `cutover_status=cutover`: 116/116.
- W1 (`W1_CORE`): 38/38 en `status=completed` y `cutover`.
- Verificacion global live (PHP vs Python, alcance legacy): `460/460` escenarios y `114/114` rutas unicas en paridad.
- Rutas con fallback/proxy activo en runtime Python: `0`.

## Que ya esta listo
- Servicio de compatibilidad Python activo (`python-api`) con despacho `w/r`.
- Inventario completo de rutas y parametros en contratos TSV.
- Captura de fixtures por bloques `w/r` usando filtro.
- Tracker unificado. Fuente unica: `migration/docs/contracts/migration-tracker.tsv`.
- Sincronizacion al runtime Python con `migration/scripts/sync_python_route_data.sh`.
- Paridad real completada para todo el contrato actual (incluyendo `facturador` y rutas de soporte).
- Gate de retiro aprobado localmente: `migration/scripts/native_go_no_go.py` retorna `GO`.

## Lo que falta para apagar PHP
- Correr `native_go_no_go.py` con `--candidate-url` en el ambiente objetivo (staging/prod) para validacion live.
- Ejecutar corte a modo nativo (fallback apagado) y mantener ventana de observacion con SLOs/errores.
- Completar retiro operativo del baseline PHP segun `migration/docs/php-decommission-runbook.md`.

## Bloque prioritario (29 rutas de facturacion)
- `users/*` (13)
- `token/*` (2)
- `clave/clave` (1)
- `consultar/consultarCom` (1)
- `genXML/*` (8)
- `firmarXML/firmar` (1)
- `send/*` (3)

## Criterio para marcar `passed`
- Misma semantica HTTP.
- Misma envoltura JSON de respuesta.
- Mismo comportamiento funcional visible para cliente.
- Diferencias no visibles (orden de llaves/whitespace) permitidas.

## Proximo paso operativo
1. Desplegar Python en modo nativo en staging/prod (`API_HACIENDA_PHP_FALLBACK_URL` vacio).
2. Ejecutar GO/NO-GO live: `migration/scripts/native_go_no_go.py --migration-root . --candidate-url <url-python>`.
3. Si el resultado es `GO`, proceder con retiro definitivo de servicios PHP y limpieza de artefactos legacy.

Comando sugerido para canary live:
```bash
cd migration
python3 scripts/compare_parity_live.py \
  --migration-root . \
  --routes-tsv docs/contracts/canary-block-5-facturador.tsv \
  --manifest tests/fixtures/parity/manifest.json \
  --baseline-url http://127.0.0.1:8090 \
  --candidate-url http://127.0.0.1:8080 \
  --report reports/canary-block-5-facturador.tsv
```

Evidencia actual (real, PHP vs Python):
- `reports/w1-factura-parity-real.tsv` -> `29/29` rutas en paridad.
- `reports/canary-block-1.tsv` -> `clave+token` `3/3`.
- `reports/canary-block-2.tsv` -> `users` `13/13`.
- `reports/canary-block-3.tsv` -> `genXML+firmarXML+send+consultar` `13/13`.
- `reports/canary-block-4.tsv` -> `cala+crypto+files` `9/9`.
- `reports/canary-block-5-facturador.tsv` -> `facturador` `51/51` rutas del bloque.
- `reports/canary-block-remaining-captured.tsv` -> `25/25`.
- `reports/global-parity-live.tsv` -> `460/460` escenarios, `114/114` rutas unicas.
