from __future__ import annotations

import base64
from io import BytesIO

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api.responses import tools_reply_compatible


def _make_qr_png(value: str) -> bytes | None:
    try:
        import qrcode  # type: ignore[import-not-found]
    except Exception:
        return None

    try:
        qr = qrcode.QRCode(version=None, box_size=10, border=4)
        qr.add_data(value)
        qr.make(fit=True)
        image = qr.make_image(fill_color="black", back_color="white")
        buffer = BytesIO()
        image.save(buffer, format="PNG")
        return buffer.getvalue()
    except Exception:
        return None


async def makeQR(_: Request, params: dict[str, str]) -> JSONResponse:
    value = str(params.get("string", ""))
    qr_png = _make_qr_png(value)
    if qr_png is not None:
        payload = base64.b64encode(qr_png).decode("utf-8")
    else:
        payload = base64.b64encode(value.encode("utf-8")).decode("utf-8")
    return tools_reply_compatible(payload)
