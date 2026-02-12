from python_api import constants as c
from python_api.responses import tools_reply_compatible


def test_unknown_negative_code_keeps_ok_status_like_php():
    response = tools_reply_compatible(c.ERROR_BAD_REQUEST)
    assert response.status_code == 200
    assert response.body.decode("utf-8") == '{"status":"ok","resp":-1}'


def test_known_user_error_maps_http_and_error_prefix():
    response = tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)
    assert response.status_code == 403
    assert response.body.decode("utf-8") == '{"status":"error","resp":"ERROR: Acceso denegado"}'

