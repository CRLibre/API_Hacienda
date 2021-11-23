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

/**
 * Send messages via wire!
 */
function wirez_messagesSend()
{
    global $user;

    grace_debug("Sending a message");

    # Get the sender and recipient details
    $sender = wirez_loadContact(params_get('from', ''));

    if ($sender == ERROR_DB_NO_RESULTS_FOUND)
        return ERROR_WIREZ_NO_VALID_CONTACT;

    $recipient = wirez_loadContact(params_get('to', ''));

    if ($recipient == ERROR_DB_NO_RESULTS_FOUND)
        return ERROR_WIREZ_NO_VALID_CONTACT;

    # Get the recipient
    # @todo Add wirez correctly
    $recipient = users_load(array('userName' => params_get('to', 0)));

    # If there is no conversation, I will create one, but only if there is a subject
    if (params_get('idConversation', 0) == 0 && params_get('subject', '') != "")
        $idConversation = wirez_conversationsCreate($user->idUser, params_get('subject', 0), $recipient->idUser);
    else
        $idConversation = params_get('idConversation', 0);

    # @ todo CHECK THAT THE CONVERSATION EXISTS AND VALIDATE USER!!!
    $q = sprintf("INSERT INTO wirez_msgs (timestamp, ip, idSender, idRecipient, text, attachments, idConversation)
        VALUES('%s','%s','%s','%s','%s','%s','%s')",
            time(),
            $_SERVER['REMOTE_ADDR'],
            $user->idUser,
            $recipient->idUser,
            addslashes(json_decode(params_get('text',''), ENT_QUOTES)),
            '',
            $idConversation
    );

    $r = db_query($q, 0);

    return $idConversation;

}

/**
 * Create new conversations
 */
function wirez_conversationsCreate($idUser, $subject, $idRecipient)
{
    $timestamp = time();

    $insert = sprintf("INSERT INTO conversations (idUser, idRecipient, timestamp, subject) VALUES('%s', '%s', '%s', '%s')",
        $idUser,
        $idRecipient,
        $timestamp,
        $subject);

    $r = db_query($insert, 0);

    # Now I need to know the id of the conversation
    $q = sprintf("SELECT idConversation FROM conversations WHERE timestamp = '%s' AND idUser = '%s'", $timestamp, $idUser);

    $r = db_query($q, 1);

    $idConversation = $r->idConversation;

    return $idConversation;

}
