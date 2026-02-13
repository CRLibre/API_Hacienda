from __future__ import annotations

import os
import sys
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[2]
PYTHON_API_ROOT = PROJECT_ROOT / "python-api"

if str(PYTHON_API_ROOT) not in sys.path:
    sys.path.insert(0, str(PYTHON_API_ROOT))

os.environ.setdefault("API_HACIENDA_ENV", "test")
os.environ.setdefault("API_HACIENDA_LOG_LEVEL", "WARNING")
os.environ.setdefault("API_HACIENDA_PHP_FALLBACK_URL", "")
os.environ.setdefault("API_HACIENDA_DATABASE_URL", "sqlite+pysqlite:///:memory:")
os.environ.setdefault("API_HACIENDA_CRYPTO_KEY", "test-crypto-key")
