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
function send_bootMeUp()
{
    // Just booting up
}

/**
 * Init function
 */
function send_init()
{
    $paths = array(
        array(
            'r'             => 'json',
            'action'        => 'send',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params'        => array(
                array("key" => "token",                     "def" => "",    "req" => true),
                array("key" => "clave",                     "def" => "",    "req" => true),
                array("key" => "fecha",                     "def" => "",    "req" => true),
                array("key" => "emi_tipoIdentificacion",    "def" => "",    "req" => true),
                array("key" => "emi_numeroIdentificacion",  "def" => "",    "req" => true),
                array("key" => "recp_tipoIdentificacion",   "def" => "",    "req" => false),
                array("key" => "recp_numeroIdentificacion", "def" => "",    "req" => false),
                array("key" => "comprobanteXml",            "def" => "",    "req" => true),
                array("key" => "callbackUrl",               "def" => "",    "req" => false),
                array("key" => "client_id",                 "def" => "",    "req" => true)
            ),
            'file'          => 'send.php'
        ),
        array(
            'r'             => 'sendMensaje',
            'action'        => 'sendMensaje',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params'        => array(
                array("key" => "token",                     "def" => "",    "req" => true),
                array("key" => "clave",                     "def" => "",    "req" => true),
                array("key" => "fecha",                     "def" => "",    "req" => true),
                array("key" => "emi_tipoIdentificacion",    "def" => "",    "req" => true),
                array("key" => "emi_numeroIdentificacion",  "def" => "",    "req" => true),
                array("key" => "recp_tipoIdentificacion",   "def" => "",    "req" => true),
                array("key" => "recp_numeroIdentificacion", "def" => "",    "req" => true),
                array("key" => "consecutivoReceptor",       "def" => "",    "req" => true),
                array("key" => "comprobanteXml",            "def" => "",    "req" => true),
                array("key" => "callbackUrl",               "def" => "",    "req" => false),
                array("key" => "client_id",                 "def" => "",    "req" => true)
            ),
            'file'          => 'send.php'
        ),
        array(
            'r'             => 'sendTE',
            'action'        => 'sendTE',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params'        => array(
                array("key" => "token",                     "def" => "", "req" => true),
                array("key" => "clave",                     "def" => "", "req" => true),
                array("key" => "fecha",                     "def" => "", "req" => true),
                array("key" => "emi_tipoIdentificacion",    "def" => "", "req" => true),
                array("key" => "emi_numeroIdentificacion",  "def" => "", "req" => true),
                array("key" => "comprobanteXml",            "def" => "", "req" => true),
                array("key" => "callbackUrl",               "def" => "", "req" => false),
                array("key" => "client_id",                 "def" => "", "req" => true)
            ),
            'file'          => 'send.php'
        )
    );


    return $paths;
}

/* * *********************************************** */
//In the access you can use users_openAccess if you want anyone can use the function
// or users_loggedIn if the user must be logged in
/* * *********************************************** */

/**
 * Get the perms for this module
 */
function MODULENAME_access()
{
    $perms = array(
        array(
            # A human readable name
            'name'          => 'Do something with this module',
            # Something to remember what it is for
            'description'   => 'What can be achieved with this permission',
            # Internal machine name, no spaces, no funny symbols, same rules as a variable
            # Use yourmodule_ prefix
            'code'          => 'mymodule_access_one',
            # Default value in case it is not set
            'def'           => false, //Or true, you decide
        ),
    );
}

/**@}*/
/** @}*/
