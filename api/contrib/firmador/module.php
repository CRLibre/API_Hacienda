<?php
/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
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

// Old modules compatibility
function firmarXML_bootMeUp()
{
	return firmador_bootMeUp();
}

function signXML_bootMeUp()
{
	return firmador_bootMeUp();
}

/**
 * Boot up procedure
 */
function firmador_bootMeUp()
{
    // Just booting up
}

// Old modules compatibility
function firmarXML_init()
{
	return firmador_init();
}

function signXML_init()
{
	return firmador_init();
}

/**
 * Init function
 */
function firmador_init()
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
                array("key" => "inXml",     "def" => "", "req" => true),
            ),
            'file'          => 'firmador.php'
        ),
        // Backwards compatibility with older module path
        array(
            'r'             => 'signFE',
            'action'        => 'signFE',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "p12Url",    "def" => "", "req" => true),
                array("key" => "pinP12",    "def" => "", "req" => true),
                array("key" => "inXml",     "def" => "", "req" => true),
            ),
            'file'          => 'firmador.php'
        )
    );

    return $paths;
}

/**@}*/
/** @}*/
