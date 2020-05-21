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
 * Boot up procedure
 */
function firmarXML_bootMeUp()
{
    // Just booting up
}

/**
 * Init function
 */
function firmarXML_init()
{
    $paths = array(
        array(
            'r'             => 'firmar',
            'action'        => 'firmar',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "p12Url",    "def" => "", "req" => true),
                array("key" => "pinP12",    "def" => "", "req" => true),
                array("key" => "inXml",     "def" => "", "req" => false),
            ),
            'file'          => 'firmar.php'
        )
    );

    return $paths;
}


/**************************************************/
//In the access you can use users_openAccess if you want anyone can use the function
// or users_loggedIn if the user must be logged in
/**************************************************/



/**
 * Get the perms for this module
 */
function firmarXML_access()
{

}

/**@}*/
/** @}*/
