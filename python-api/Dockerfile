FROM python:3.14.3-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

WORKDIR /app

COPY pyproject.toml README.md ./
COPY xmldsig-core-schema.xsd ./
COPY python_api ./python_api
COPY resources ./resources
COPY alembic ./alembic
COPY alembic.ini ./

RUN pip install --no-cache-dir .
RUN mkdir -p /app/runtime/files

EXPOSE 8090

CMD ["uvicorn", "python_api.main:app", "--host", "0.0.0.0", "--port", "8090"]
