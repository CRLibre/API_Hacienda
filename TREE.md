# Project Tree

```text
API_Hacienda/
├── README.md
├── VERSION.md
├── docker-compose.yml
├── docker-compose.md
├── Procfile
├── ejemplo.env.txt
├── migration/
│   ├── README.md
│   ├── infra/
│   │   ├── recursos/
│   │   │   └── sql/
│   │   └── mysql-data/
│   ├── python-api/
│   │   ├── python_api/
│   │   ├── alembic/
│   │   ├── resources/
│   │   ├── runtime/
│   │   ├── pyproject.toml
│   │   ├── Dockerfile
│   │   └── xmldsig-core-schema.xsd
│   └── scripts/
│       └── dev_real_fe_flow.py
└── .github/workflows/
```

## Notes
- El repositorio quedó limpio para operación Python-only.
- No se conserva código PHP ni artefactos de migración históricos.
