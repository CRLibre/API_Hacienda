# Checkpoint: Factura Electronica CR en Python

Fecha: 2026-02-12

## Estado ejecutivo
- Total rutas: 115.
- `status=completed`: 115/115.
- `parity_status=passed`: 115/115.
- `cutover_status=cutover`: 115/115.
- W1 (`W1_CORE`): 38/38 en `status=completed` y `cutover`.
- Verificacion global live (PHP vs Python): `460/460` escenarios y `114/114` rutas unicas en paridad.
- Rutas con fallback/proxy activo en runtime Python: `0`.

## Que ya esta listo
- Servicio de compatibilidad Python activo (`python-api`) con despacho `w/r`.
- Inventario completo de rutas y parametros en contratos TSV.
- Captura de fixtures por bloques `w/r` usando filtro.
- Tracker unificado. Fuente unica: `migration/docs/contracts/migration-tracker.tsv`.
- Sincronizacion al runtime Python con `migration/scripts/sync_python_route_data.sh`.
- Paridad real completada para todo el contrato actual (incluyendo `facturador` y rutas de soporte).

## Lo que falta para que Factura Electronica CR quede bien en Python
- Endurecer operacion post-cutover: monitoreo, alertas y SLOs por ruta.
- Plan de retiro controlado del baseline PHP una vez validado el periodo de estabilidad.
- Ejecucion del runbook de retiro: `migration/docs/php-decommission-runbook.md`.
- Validacion automatizada GO/NO-GO: `migration/scripts/native_go_no_go.py`.

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
1. Iniciar canary W2 (`facturador`) por sub-bloques y mover `cutover_status` ruta por ruta.
2. Consolidar canary W3 soporte y decidir cutover o retiro por bajo uso.
3. Mantener regla: no `cutover` sin `parity_status=passed` (ya cumplido en 115/115).

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
