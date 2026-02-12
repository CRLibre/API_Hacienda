# Contract Artifacts

Generated from route declarations in `api/modules/*/module.php` and `api/contrib/*/module.php`.

Files:
- `routes.tsv`: full `w` + `r` manifest.
- `params.tsv`: per-route params with default and required markers.

Regenerate:
```bash
./scripts/generate_api_contract.sh .
```
