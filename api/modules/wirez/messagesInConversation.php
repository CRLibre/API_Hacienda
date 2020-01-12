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

function wirez_messagesGetInConversation()
{
    global $user;

    grace_debug("Getting messages in conversations");

    # I will hold all the messages
    $allMsgs = array();

    # With whom is this conversation?
    $recipient = users_load(array('userName' => params_get('withWire', '')));

    # @todo Check that the user can see the message!!!!
    # I will get the conversation information if the user is either the sender, or the reciever
    # @todo This is not the best way to do it, I should first see if the conversation exists, then check for permissions, and THEN get the messages
    # I will do that later on
    $q = sprintf("
        SELECT m.*, m.timestamp AS msgTime,
        u.fullName as senderName, u.userName AS senderWire, uu.fullName AS recipientName, uu.userName as recipientWire
        FROM `msgs` m
        INNER JOIN users u on u.idUser = m.idSender
        INNER JOIN users uu ON uu.idUser = m.idRecipient
        LEFT JOIN conversations c ON c.idConversation = m.idConversation
        WHERE m.idConversation = %s
        AND (m.idSender = %s AND m.idRecipient = %s OR m.idSender = %s AND m.idRecipient = %s)
        AND m.idMsg > %s
        ORDER BY m.idMsg DESC
        LIMIT %s, %s
        ",
        params_get('idConversation', 0),
        $user->idUser,
        $recipient->idUser,
        $recipient->idUser,
        $user->idUser,
        params_get('lastMsgId'),
        params_get('ini', 0),
        params_get('end', 10)
    );

    $allMsgs = db_query($q, 2);

    # If conversation > 0 and there is a recipient
    # If there are no messages
    if ($allMsgs == ERROR_DB_NO_RESULTS_FOUND)
        return ERROR_WIREZ_MSGS_NOTHING_FOUND;

    $allMessages                    = array();
    $allMessages['msgs']            = array_reverse($allMsgs);
    $allMessages['totalMessages']   = count($allMsgs);
    $allMessages['lastMsgId']       = $allMsgs[0]->idMsg;

    return $allMessages;
}

