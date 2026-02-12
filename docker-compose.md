# Docker Compose (Python)

Esta configuracion levanta el API migrado en Python y MariaDB para desarrollo local.

## Requisitos
- Docker Engine
- Docker Compose v2

## Levantar entorno
```bash
docker compose up -d --build
```

Servicios:
- API Python: `http://127.0.0.1:8080/api.php`
- MariaDB: `127.0.0.1:4407`

## Verificar
```bash
curl "http://127.0.0.1:8080/healthz"
curl "http://127.0.0.1:8080/api.php?w=ejemplo&r=hola"
```

## Detener
```bash
docker compose down
```

## Notas
- No se usa `settings.php` ni contenedor Apache/PHP.
- El API corre en modo nativo Python (`API_HACIENDA_PHP_FALLBACK_URL` vacio).
