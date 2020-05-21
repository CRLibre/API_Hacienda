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
 * Gets a list of users
 */
function users_getList()
{
    # Where should I look for
    # By wire only
    if(strpos(params_get('like'), "::") === 0)
    {
        //TMP solution while I make up my mind how wirez countersign will be handled
        $where = sprintf("AND userName like '%s%%'
            ORDER BY wire ASC
            ", str_replace("::", "", params_get('like')));
    }
    else
    {
        $where = sprintf("AND userName like '%%%s%%'
            OR userName like '%%%s%%'
            ORDER BY lastAccess DESC
            ", params_get('like'), params_get('like'));
    }

    $q = sprintf("SELECT fullName, userName, about, country, avatar, lastAccess
        FROM `users` 
        WHERE status = 1
        %s
        LIMIT %s, %s", $where, params_get('ini', 0), params_get('end', 10));
    $allPeople = db_query($q, 2);
    return $allPeople;
}


