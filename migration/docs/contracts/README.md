# Contract Artifacts

Generated from route declarations in `api/modules/*/module.php` and `api/contrib/*/module.php`.

Files:
- `routes.tsv`: full `w` + `r` manifest.
- `params.tsv`: per-route params with default and required markers.
- `migration-tracker.tsv`: source of truth for migration status (`status`, `parity_status`, `cutover_status`).

Status rules:
- `parity_status=captured`: fixtures captured for the route, but parity not yet approved.
- `parity_status=passed`: response parity verified against PHP baseline.
- `cutover_status=cutover`: route served by Python in production traffic.

Sync to Python service data:
```bash
cd migration
./scripts/sync_python_route_data.sh .
```

Regenerate:
```bash
./scripts/generate_api_contract.sh .
```
