from __future__ import annotations

import logging

import httpx
from fastapi import Request
from fastapi.responses import Response

from python_api.compat import ParsedLegacyRequest
from python_api.config import Settings

logger = logging.getLogger(__name__)


class FallbackProxy:
    def __init__(self, settings: Settings) -> None:
        self._settings = settings

    async def proxy(self, request: Request, parsed: ParsedLegacyRequest) -> Response:
        fallback_url = self._settings.php_fallback_url
        if not fallback_url:
            raise RuntimeError("Fallback URL is not configured.")

        headers = {}
        request_id = request.headers.get("x-request-id")
        if request_id:
            headers["x-request-id"] = request_id

        kwargs: dict[str, object] = {"headers": headers}
        if parsed.source == "query":
            kwargs["params"] = parsed.params
        elif parsed.source == "json":
            kwargs["json"] = parsed.params
        else:
            kwargs["data"] = parsed.params

        timeout = httpx.Timeout(self._settings.request_timeout_seconds)
        async with httpx.AsyncClient(timeout=timeout, follow_redirects=False) as client:
            response = await client.request(
                method=request.method,
                url=fallback_url,
                **kwargs,
            )

        logger.info(
            "proxied_to_php",
            extra={
                "method": request.method,
                "path": str(request.url.path),
                "status_code": response.status_code,
                "w": parsed.params.get("w"),
                "r": parsed.params.get("r"),
            },
        )

        # Preserve PHP response body and status to keep compatibility behavior.
        passthrough_headers = {}
        content_type = response.headers.get("content-type")
        if content_type:
            passthrough_headers["content-type"] = content_type

        return Response(
            content=response.content,
            status_code=response.status_code,
            headers=passthrough_headers,
        )
