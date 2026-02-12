# Parity Fixtures

This folder stores golden request/response fixtures used for PHP-to-Python contract parity validation.

## Layout
- `manifest.json`: authoritative list of routes/scenarios in scope.
- `<w>/<r>/<scenario>.request.json`: request template.
- `<w>/<r>/<scenario>.response.json`: captured golden response.

Scenarios:
- `success`
- `validation_error`
- `auth_error`
- `malformed`

## Initialize templates
```bash
./scripts/init_parity_fixtures.sh .
```

Notes:
- By default it initializes fixtures for all routes in `python-api/python_api/data/routes.tsv`.
- To initialize only the original phase-1 scope, use:
```bash
SCOPE=phase1 ./scripts/init_parity_fixtures.sh .
```

## Capture golden responses from current PHP API
```bash
API_BASE_URL=http://127.0.0.1:8080 ./scripts/capture_golden_fixtures.sh .
```

Capture only one route:
```bash
API_BASE_URL=http://127.0.0.1:8080 ./scripts/capture_golden_fixtures.sh . users users_log_me_in
```

The capture script updates `*.response.json` files in place.
