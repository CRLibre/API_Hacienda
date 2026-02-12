# Legacy Audit (Phase 1 Baseline)

## Scope and intent
- Repository: `API_Hacienda`
- Audit date: 2026-02-12
- Goal: establish a hard baseline before Python 3.14.3 migration and identify legacy/deprecation risks that must be controlled for parity.

## Repository shape
- Primary language: PHP (232 `*.php` files, 0 `*.py` files).
- Route surface: 115 route entries (`'r' => '...'`) across `api/modules/*/module.php` and `api/contrib/*/module.php`.
- Notes in code:
- 38 `TODO`/`@todo` markers.
- 8 `@deprecated` markers.

## Version drift matrix
| Surface | Current value | Source |
|---|---|---|
| README minimum | `PHP >= 5.5.0` | `README.md:78` |
| Runtime check gate | `>= 5.5` | `api/core/checks.php:21` |
| Composer platform | `>=7.4` | `composer.json:4` |
| CI runtime | `8.3` | `.github/workflows/phpunit.yml:26` |
| Docker runtime | `php:7.4.9-apache` | `docker-php-apache/Dockerfile:1` |

## Deprecation and security-risk inventory
### High severity
- `mcrypt` branch still present:
- `api/modules/users/module.php:405` (`mcrypt_create_iv`, `MCRYPT_DEV_URANDOM`).
- Legacy MD5 login fallback still active:
- `api/modules/users/module.php:352`, `api/modules/users/module.php:431`.
- Weak session key seed pattern:
- `api/modules/users/module.php:383` uses `password_hash(time() * rand(...))`.
- Similar weak seed in facturador auth helper:
- `api/contrib/facturador/companny_user.php:137`.

### Medium severity
- Hardcoded/legacy code path likely broken by typo:
- `api/contrib/sendMail/sendMail.php:50` references `$calve` (typo).
- Aggressive world-writable permissions in image build:
- `docker-php-apache/Dockerfile:17`, `docker-php-apache/Dockerfile:18`, `docker-php-apache/Dockerfile:19`.

### Low severity
- Several legacy comments and deprecated wrappers remain (tracked in TODO/deprecated counts above).

## Third-party component inventory and duplication map
### Top-level dependency management
- Root `composer.json` is intentionally minimal (platform/ext + `phpunit/phpunit` dev-only).
- Primary runtime deps are vendored directly in repository under `api/tools` and `api/contrib`.

### Explicit third-party findings
- PHPMailer duplicated in two different versions:
- `api/tools/phpmailer/src/PHPMailer.php:704` -> `6.0.5`.
- `api/tools/phpmailer/vendor/phpmailer/phpmailer/src/PHPMailer.php:759` -> `6.9.3`.
- xmlseclibs vendored snapshot:
- `api/contrib/firmarXML/xmlseclibs/xmlseclibs.php:40` -> `3.0.4-dev`.
- phpqrcode vendored legacy code:
- `api/contrib/makeQR/phpqrcode/*` includes copyright years 2006-2010.

### Duplication risks
- Multiple PHPMailer trees increase patching/CVE drift risk and make behavior inconsistent depending on include path.
- Vendored libraries without centralized update policy increase long-term drift and opaque compatibility behavior.

## Database/schema risk inventory (`recursos/sql/api_base.sql`)
### Age signal
- SQL dump footer indicates baseline from 2018 (`Dump completed on 2018-10-23`).

### Structural risks
- Mixed storage engines:
- MyISAM used in several tables (for example `users`, several `master_*` tables).
- Mixed charset/collation:
- `latin1` appears in some tables while others use `utf8mb4`.
- Legacy column patterns:
- heavy `int(N)` usage (display-width era style),
- inconsistent typing for fields that appear semantic/textual (for example some identifiers encoded as ints).

### Migration implications
- Python service should start with DB-compatibility mode (no breaking schema rewrite).
- Any charset/engine normalization must be staged as explicit migrations with data validation.

## Module freshness snapshot (selected)
| File | Last commit date |
|---|---|
| `api/modules/users/module.php` | 2025-08-14 |
| `api/contrib/genXML/genXML.php` | 2025-01-21 |
| `api/contrib/facturador/module.php` | 2018-12-10 |
| `api/contrib/sendMail/sendMail.php` | 2018-12-10 |
| `api/contrib/firmarXML/hacienda/firmador.php` | 2019-06-19 |
| `docker-php-apache/Dockerfile` | 2024-12-28 |

## Immediate stabilization objectives implemented in this phase
- Align runtime declaration surfaces to PHP 8.3 baseline for parity capture.
- Remove dead PHP 5.5-era gate logic.
- Replace deprecated/weak randomness paths while preserving route contracts.
- Preserve legacy MD5 fallback behavior in current PHP surface until Python compatibility layer implements safe transitional upgrade.

## Remediation status (this branch)
- Implemented:
- Runtime alignment updates applied in `README.md`, `api/core/checks.php`, `composer.json`, `composer.lock`, and `docker-php-apache/Dockerfile`.
- `users_hash()` no longer carries `mcrypt` path.
- Session key generation switched to CSPRNG (`random_bytes`) in:
- `api/modules/users/module.php`
- `api/contrib/facturador/companny_user.php`
- `sendMail` typo fixed (`$calve` -> `$clave`).

- Intentionally deferred:
- Legacy MD5 login fallback remains active for transitional compatibility and will be removed only after safe migration path is completed in Python implementation.
