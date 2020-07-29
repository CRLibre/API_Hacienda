<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function wirez_messagesGetRecent()
{
    global $user;

    grace_debug("Getting new/latest messages for someone");

    # I will hold all the messages
    $allMsgs = array();

    # @todo Check that the user can see the message!!!!
    # With someone in specific?
    if (params_get('withWire', '') != '')
    {
        $recipient = users_loadByWire(users_createWireAddress(params_get('withWire', '')));

        $where = sprintf("WHERE (mm.idSender = %s AND mm.idRecipient = %s)
            OR (mm.idSender = %s AND mm.idRecipient = %s)",
                $recipient->idUser,
                $user->idUser,
                $user->idUser,
                $recipient->idUser
            );
    }
    else
    {
        $where = sprintf("WHERE (mm.idSender = %s
            OR mm.idRecipient = %s)", $user->idUser, $user->idUser
        );
    }

    # I will get the conversation information if the user is either the sender, or the reciever
    $q = sprintf("
        SELECT m.*,
        c.subject,
        u.fullName as senderName, u.userName AS senderWire, u.avatar as senderAvatar,
        uu.fullName AS recipientName, uu.userName as recipientWire
        FROM msgs m
        LEFT JOIN conversations c ON c.idConversation = m.idConversation
        INNER JOIN users u on u.idUser = m.idSender
        INNER JOIN users uu ON uu.idUser = m.idRecipient
        JOIN (
            SELECT MAX(idMsg) idMsg
            FROM msgs mm
            %s
            AND mm.idMsg > %s
            GROUP BY mm.idConversation
        ) m2
        ON m.idMsg = m2.idMsg
        ORDER BY m.idMsg DESC
        LIMIT %s, %s",
        $where,
        params_get('lastMessageId', 0),
        params_get('ini', 0),
        params_get('end', 10)
    );

    $allMsgs = db_query($q, 2);

    # If conversation > 0 and there is a recipient
    # If there are no messages
    if ($allMsgs == ERROR_DB_NO_RESULTS_FOUND)
        return ERROR_DB_NO_RESULTS_FOUND;

    $allMessages = array();

    # Prepare all messages
    # @todo This could be a function
    for ($i = 0; $i < count($allMsgs); $i++)
    {
        $allMsgs[$i]->text = stripcslashes($allMsgs[$i]->text);
    }

    $allMessages['msgs'] = $allMsgs;
    $allMessages['totalMessages'] = count($allMsgs);
    $allMessages['lastMsgId'] = $allMsgs[0]->idMsg;

    return $allMessages;
}

