from fastapi import Request
from starlette.datastructures import Headers

from python_api.compat import parse_legacy_params


class DummyReceive:
    def __init__(self, body: bytes):
        self.body = body
        self.done = False

    async def __call__(self):
        if self.done:
            return {"type": "http.request", "body": b"", "more_body": False}
        self.done = True
        return {"type": "http.request", "body": self.body, "more_body": False}


def make_request(method: str, query: str = "", body: bytes = b"", content_type: str = "") -> Request:
    scope = {
        "type": "http",
        "http_version": "1.1",
        "method": method,
        "path": "/api.php",
        "query_string": query.encode("utf-8"),
        "headers": Headers({"content-type": content_type}).raw,
    }
    return Request(scope, DummyReceive(body))


async def test_query_precedence_over_body():
    request = make_request(
        method="POST",
        query="w=users&r=users_log_me_in",
        body=b'{"w":"clave","r":"clave"}',
        content_type="application/json",
    )
    parsed = await parse_legacy_params(request)
    assert parsed.source == "query"
    assert parsed.params["w"] == "users"


async def test_json_body_parsing():
    request = make_request(
        method="POST",
        body=b'{"w":"clave","r":"clave","tipoDocumento":"01"}',
        content_type="application/json",
    )
    parsed = await parse_legacy_params(request)
    assert parsed.source == "json"
    assert parsed.params["w"] == "clave"

