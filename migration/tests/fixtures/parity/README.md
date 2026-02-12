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

## Capture golden responses from current PHP API
```bash
API_BASE_URL=http://127.0.0.1:8080 ./scripts/capture_golden_fixtures.sh .
```

The capture script updates `*.response.json` files in place.
