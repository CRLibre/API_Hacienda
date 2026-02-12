from __future__ import annotations

import logging
import time
import uuid
from inspect import isawaitable

from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse

from python_api.compat import envelope_error, envelope_ok, parse_legacy_params
from python_api.config import get_settings
from python_api.contract import apply_defaults_and_validate
from python_api.fallback import FallbackProxy
from python_api.handlers import get_handler
from python_api.logging_config import configure_logging
from python_api.responses import tools_reply_compatible

settings = get_settings()
configure_logging(settings.log_level)
logger = logging.getLogger(__name__)

app = FastAPI(
    title="API Hacienda Python Compatibility Service",
    version="0.1.0",
    docs_url="/docs",
    redoc_url=None,
    openapi_url="/openapi.json",
)
fallback_proxy = FallbackProxy(settings)


@app.middleware("http")
async def correlation_middleware(request: Request, call_next):
    request_id = request.headers.get("x-request-id", str(uuid.uuid4()))
    request.state.request_id = request_id
    start = time.perf_counter()
    response = await call_next(request)
    duration_ms = round((time.perf_counter() - start) * 1000, 2)
    response.headers["x-request-id"] = request_id

    logger.info(
        "request_complete",
        extra={
            "request_id": request_id,
            "method": request.method,
            "path": request.url.path,
            "status_code": response.status_code,
            "duration_ms": duration_ms,
        },
    )
    return response


@app.get("/healthz")
async def healthz():
    return {"status": "ok"}


@app.get("/readyz")
async def readyz():
    if not settings.php_fallback_url:
        return JSONResponse(status_code=503, content={"status": "error", "reason": "fallback_not_set"})
    return {"status": "ok"}


@app.api_route("/api.php", methods=["GET", "POST", "PUT"])
async def api_php_compat(request: Request):
    parsed = await parse_legacy_params(request)
    params = parsed.params
    w = params.get("w", "")
    r = params.get("r", "")

    if parsed.source == "invalid_json":
        return envelope_error("La informacion json enviada contiene errores.", status_code=400)

    if not w or not r:
        try:
            return await fallback_proxy.proxy(request, parsed)
        except Exception as exc:  # pragma: no cover
            logger.exception(
                "fallback_proxy_failed_missing_dispatch",
                extra={"request_id": getattr(request.state, "request_id", None)},
            )
            return envelope_error(f"Fallback proxy failed: {exc}", status_code=502)

    params, missing_required_param = apply_defaults_and_validate(params, w, r)
    if missing_required_param:
        return tools_reply_compatible(f"Falta el parametro requerido: {missing_required_param}", kill_me=True)

    handler = get_handler(w, r)
    if handler is not None:
        try:
            response = handler(request, params)
            if isawaitable(response):
                response = await response
            return response
        except Exception as exc:  # pragma: no cover
            logger.exception(
                "python_handler_failed",
                extra={"request_id": getattr(request.state, "request_id", None), "w": w, "r": r},
            )
            return envelope_error(f"Python handler failed: {exc}", status_code=500)

    try:
        return await fallback_proxy.proxy(request, parsed)
    except Exception as exc:  # pragma: no cover
        logger.exception(
            "fallback_proxy_failed",
            extra={"request_id": getattr(request.state, "request_id", None), "w": w, "r": r},
        )
        return envelope_error(f"Fallback proxy failed: {exc}", status_code=502)


@app.get("/")
async def root():
    return envelope_ok(
        {
            "service": "api-hacienda-compat",
            "mode": "hybrid",
            "docs": "/docs",
            "healthz": "/healthz",
            "readyz": "/readyz",
        }
    )
