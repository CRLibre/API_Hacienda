ARG PYTHON_VERSION=3.14.3

FROM python:${PYTHON_VERSION}-slim AS builder

ENV PIP_DISABLE_PIP_VERSION_CHECK=1 \
    PIP_NO_CACHE_DIR=1 \
    PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /build

RUN python -m venv /opt/venv
ENV PATH="/opt/venv/bin:${PATH}"

COPY pyproject.toml README.md xmldsig-core-schema.xsd ./
COPY python_api ./python_api
COPY resources ./resources
COPY alembic ./alembic
COPY alembic.ini ./

RUN pip install --upgrade pip \
    && pip install .

FROM python:${PYTHON_VERSION}-slim AS runtime

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PATH="/opt/venv/bin:${PATH}" \
    PORT=8090 \
    API_HACIENDA_FILES_BASE_PATH=/app/runtime/files/

WORKDIR /app

RUN addgroup --system app \
    && adduser --system --ingroup app --home /app app \
    && mkdir -p /app/runtime/files \
    && chown -R app:app /app

COPY --from=builder /opt/venv /opt/venv
COPY --chown=app:app python_api ./python_api
COPY --chown=app:app resources ./resources
COPY --chown=app:app alembic ./alembic
COPY --chown=app:app alembic.ini ./alembic.ini
COPY --chown=app:app xmldsig-core-schema.xsd ./xmldsig-core-schema.xsd

USER app

EXPOSE 8090

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD python -c "import os, urllib.request; port=os.getenv('PORT','8090'); urllib.request.urlopen(f'http://127.0.0.1:{port}/healthz', timeout=3)"

CMD ["sh", "-c", "uvicorn python_api.main:app --host 0.0.0.0 --port ${PORT:-8090}"]
