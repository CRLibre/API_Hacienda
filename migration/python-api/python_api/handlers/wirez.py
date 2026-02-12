from __future__ import annotations

import json
import time
from typing import Any

from fastapi import Request
from fastapi.responses import JSONResponse

from python_api import constants as c
from python_api.responses import tools_reply_compatible
from python_api.services.db_compat import execute, fetch_all, fetch_one
from . import users

ERROR_WIREZ_MSGS_NOTHING_FOUND = -100
ERROR_WIREZ_NO_VALID_CONTACT = -101


def _as_int(value: Any, default: int = 0) -> int:
    try:
        return int(value)
    except (TypeError, ValueError):
        return default


def _load_user_by_username(user_name: str) -> dict[str, Any] | None:
    if not user_name:
        return None
    return fetch_one("SELECT * FROM users WHERE userName = :userName", {"userName": user_name})


def _contact_exists(wire: str) -> bool:
    try:
        row = fetch_one("SELECT * FROM wirez_contacts WHERE wire = :wire", {"wire": wire})
    except Exception:
        return False
    return row is not None


def _decode_text(raw_text: str) -> str:
    try:
        parsed = json.loads(raw_text)
    except Exception:
        return raw_text
    if parsed is None:
        return ""
    return str(parsed)


def _strip_cslashes(text: str) -> str:
    try:
        return bytes(text, "utf-8").decode("unicode_escape")
    except Exception:
        return text


def _create_conversation(id_user: int, subject: str, id_recipient: int) -> int:
    timestamp = int(time.time())
    execute(
        """
        INSERT INTO conversations (idUser, idRecipient, timestamp, subject)
        VALUES (:idUser, :idRecipient, :timestamp, :subject)
        """,
        {"idUser": id_user, "idRecipient": id_recipient, "timestamp": timestamp, "subject": subject},
    )
    row = fetch_one(
        """
        SELECT idConversation
        FROM conversations
        WHERE timestamp = :timestamp AND idUser = :idUser
        ORDER BY idConversation DESC
        LIMIT 1
        """,
        {"timestamp": timestamp, "idUser": id_user},
    )
    if row is None:
        return 0
    return _as_int(row.get("idConversation"), 0)


async def messages_send(request: Request, params: dict[str, str]) -> JSONResponse:
    user = users._require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    sender_wire = str(params.get("from", ""))
    recipient_wire = str(params.get("to", ""))
    if not _contact_exists(sender_wire) or not _contact_exists(recipient_wire):
        return tools_reply_compatible(ERROR_WIREZ_NO_VALID_CONTACT)

    recipient = _load_user_by_username(recipient_wire)
    if recipient is None:
        return tools_reply_compatible(ERROR_WIREZ_NO_VALID_CONTACT)

    id_conversation = _as_int(params.get("idConversation", 0), 0)
    subject = str(params.get("subject", ""))
    if id_conversation == 0 and subject != "":
        id_conversation = _create_conversation(
            _as_int(user.get("idUser"), 0),
            subject,
            _as_int(recipient.get("idUser"), 0),
        )

    execute(
        """
        INSERT INTO msgs (timestamp, ip, idSender, idRecipient, text, channel, attachments, idConversation)
        VALUES (:timestamp, :ip, :idSender, :idRecipient, :text, :channel, :attachments, :idConversation)
        """,
        {
            "timestamp": int(time.time()),
            "ip": request.client.host if request.client and request.client.host else "",
            "idSender": _as_int(user.get("idUser"), 0),
            "idRecipient": _as_int(recipient.get("idUser"), 0),
            "text": _decode_text(str(params.get("text", ""))),
            "channel": 0,
            "attachments": 0,
            "idConversation": id_conversation,
        },
    )
    return tools_reply_compatible(id_conversation)


async def messages_get_in_conversation(request: Request, params: dict[str, str]) -> JSONResponse:
    user = users._require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    recipient = _load_user_by_username(str(params.get("withWire", "")))
    if recipient is None:
        return tools_reply_compatible(ERROR_WIREZ_NO_VALID_CONTACT)

    rows = fetch_all(
        """
        SELECT m.*, m.timestamp AS msgTime,
               u.fullName AS senderName, u.userName AS senderWire,
               uu.fullName AS recipientName, uu.userName AS recipientWire
        FROM msgs m
        INNER JOIN users u ON u.idUser = m.idSender
        INNER JOIN users uu ON uu.idUser = m.idRecipient
        LEFT JOIN conversations c ON c.idConversation = m.idConversation
        WHERE m.idConversation = :idConversation
          AND ((m.idSender = :idSender AND m.idRecipient = :idRecipient)
               OR (m.idSender = :idRecipient AND m.idRecipient = :idSender))
          AND m.idMsg > :lastMsgId
        ORDER BY m.idMsg DESC
        LIMIT :ini, :end
        """,
        {
            "idConversation": _as_int(params.get("idConversation", 0), 0),
            "idSender": _as_int(user.get("idUser"), 0),
            "idRecipient": _as_int(recipient.get("idUser"), 0),
            "lastMsgId": _as_int(params.get("lastMsgId", 0), 0),
            "ini": _as_int(params.get("ini", 0), 0),
            "end": _as_int(params.get("end", 10), 10),
        },
    )
    if not rows:
        return tools_reply_compatible(ERROR_WIREZ_MSGS_NOTHING_FOUND)

    response = {
        "msgs": list(reversed(rows)),
        "totalMessages": len(rows),
        "lastMsgId": _as_int(rows[0].get("idMsg"), 0),
    }
    return tools_reply_compatible(response)


async def messages_get_recent(request: Request, params: dict[str, str]) -> JSONResponse:
    user = users._require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    query_params: dict[str, Any] = {
        "currentUser": _as_int(user.get("idUser"), 0),
        "lastMessageId": _as_int(params.get("lastMessageId", 0), 0),
        "ini": _as_int(params.get("ini", 0), 0),
        "end": _as_int(params.get("end", 10), 10),
    }
    with_wire = str(params.get("withWire", ""))
    if with_wire:
        recipient = _load_user_by_username(with_wire)
        if recipient is None:
            return tools_reply_compatible(ERROR_WIREZ_NO_VALID_CONTACT)
        query_params["recipient"] = _as_int(recipient.get("idUser"), 0)
        where = """WHERE (mm.idSender = :recipient AND mm.idRecipient = :currentUser)
                   OR (mm.idSender = :currentUser AND mm.idRecipient = :recipient)"""
    else:
        where = "WHERE (mm.idSender = :currentUser OR mm.idRecipient = :currentUser)"

    rows = fetch_all(
        f"""
        SELECT m.*, c.subject,
               u.fullName AS senderName, u.userName AS senderWire, u.avatar AS senderAvatar,
               uu.fullName AS recipientName, uu.userName AS recipientWire
        FROM msgs m
        LEFT JOIN conversations c ON c.idConversation = m.idConversation
        INNER JOIN users u ON u.idUser = m.idSender
        INNER JOIN users uu ON uu.idUser = m.idRecipient
        JOIN (
            SELECT MAX(idMsg) idMsg
            FROM msgs mm
            {where}
              AND mm.idMsg > :lastMessageId
            GROUP BY mm.idConversation
        ) m2 ON m.idMsg = m2.idMsg
        ORDER BY m.idMsg DESC
        LIMIT :ini, :end
        """,
        query_params,
    )
    if not rows:
        return tools_reply_compatible(ERROR_WIREZ_MSGS_NOTHING_FOUND)

    for row in rows:
        row["text"] = _strip_cslashes(str(row.get("text", "")))

    response = {
        "msgs": rows,
        "totalMessages": len(rows),
        "lastMsgId": _as_int(rows[0].get("idMsg"), 0),
    }
    return tools_reply_compatible(response)


async def conversations_get_details(request: Request, params: dict[str, str]) -> JSONResponse:
    user = users._require_logged_in(request, params)
    if user is None:
        return tools_reply_compatible(c.ERROR_USERS_ACCESS_DENIED)

    row = fetch_one(
        """
        SELECT c.*,
               u.fullName AS senderName, u.userName AS senderWire,
               uu.fullName AS recipientName, uu.userName AS recipientWire
        FROM conversations c
        INNER JOIN users u ON u.idUser = c.idUser
        INNER JOIN users uu ON uu.idUser = c.idRecipient
        WHERE c.idConversation = :idConversation
        """,
        {"idConversation": _as_int(params.get("idConversation", 0), 0)},
    )
    if row is None:
        return tools_reply_compatible({})
    return tools_reply_compatible(row)

