# Project Tree

## Root
```text
API_Hacienda/
├── README.md
├── VERSION.md
├── docker-compose.yml
├── docker-compose.md
├── Procfile
├── ejemplo.env.txt
├── LICENSE
├── migration/
│   ├── README.md
│   ├── docs/
│   │   ├── archive/
│   │   └── contracts/
│   ├── infra/
│   │   ├── recursos/
│   │   │   └── sql/
│   │   └── mysql-data/
│   ├── python-api/
│   │   ├── python_api/
│   │   ├── alembic/
│   │   ├── auto_port/
│   │   ├── resources/
│   │   ├── runtime/
│   │   ├── tests/
│   │   └── xmldsig-core-schema.xsd
│   ├── reports/
│   ├── scripts/
│   └── tests/
└── .github/workflows/
```

## Notes
- Todo lo operativo de la migración vive dentro de `migration/`.
- El runtime Python usa `migration/python-api/resources` para XSD, geoloc, version y static.
- SQL seed y volumen local de MariaDB viven en `migration/infra`.
