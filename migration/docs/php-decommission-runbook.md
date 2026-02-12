# PHP Decommission Runbook (Native Python Target)

Fecha base: 2026-02-12

## Objective
Retire legacy PHP baseline safely and run only `migration/python-api` in native mode.

## Exit condition (what "done" means)
- `migration/scripts/native_go_no_go.py` returns `GO`.
- `API_HACIENDA_PHP_FALLBACK_URL` is empty in deployed runtime.
- `/readyz` returns `{"status":"ok","fallback_enabled":false}`.
- No production traffic reaches legacy PHP service.

## Phase 0: Preconditions
1. Update tracker and reports:
```bash
cd migration
python3 scripts/compare_parity_live.py \
  --migration-root . \
  --routes-tsv docs/contracts/migration-tracker.tsv \
  --baseline-url http://127.0.0.1:8090 \
  --candidate-url http://127.0.0.1:8080 \
  --report reports/global-parity-live.tsv
```
2. Run go/no-go:
```bash
./scripts/native_go_no_go.py --migration-root . --candidate-url http://127.0.0.1:8080
```

If result is `NO-GO`, stop and fix failed checks before continuing.

## Phase 1: Native deployment
1. Set runtime env:
```bash
API_HACIENDA_PHP_FALLBACK_URL=
```
2. Restart Python service.
3. Verify:
```bash
curl -s http://<python-host>/readyz
curl -s http://<python-host>/
```
Expected:
- `fallback_enabled=false`
- root `resp.mode=native`

## Phase 2: Traffic cut from PHP
1. Remove PHP backend from LB / ingress upstream pool.
2. Keep PHP service alive but isolated (no external traffic) during soak window.
3. Monitor:
- error-rate
- p95 latency
- 5xx by `w/r`

Recommended soak window: 7-14 days.

## Phase 3: Disable PHP baseline
1. Stop PHP service/runtime.
2. Keep DB backup/snapshots and app logs from last PHP day.
3. Tag repository state before deletion:
```bash
git tag -a php-baseline-final -m "Final PHP baseline before decommission"
git push origin php-baseline-final
```

## Phase 4: Destructive cleanup (only after soak window success)
Delete PHP-specific artifacts in controlled PR(s):
1. Runtime and build:
- `docker-php-apache/`
- PHP workflows/jobs
2. Dependency managers:
- `composer.json`
- `composer.lock`
3. Legacy source:
- `api/**/*.php`
- `www/api.php` (replace with Python gateway if still referenced)
4. Obsolete docs referencing PHP runtime requirements.

Do this in small PR batches with rollback tags preserved.

## Rollback plan
If post-cutover SLOs fail:
1. Re-enable LB route to PHP.
2. Re-set `API_HACIENDA_PHP_FALLBACK_URL` to PHP endpoint.
3. Restart Python service.
4. Investigate and rerun go/no-go before next attempt.
