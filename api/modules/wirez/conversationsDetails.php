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

function wirez_conversationsGetDetails()
{
    # @todo confirm that the user can see this conversation
    # I will hold the details
    $details = array();

    $q = sprintf("
        SELECT c.*, 
        u.fullName as senderName, u.userName AS senderWire,
        uu.fullName AS recipientName, uu.userName as recipientWire
        FROM conversations c
        INNER JOIN users u on u.idUser = c.idUser
        INNER JOIN users uu ON uu.idUser = c.idRecipient
        WHERE c.idConversation = '%s'
        ", params_get('idConversation', 0));

    $r = db_query($q);

    if ($r != ERROR_DB_NO_RESULTS_FOUND)
        $details = $r;

    return $details;

}

