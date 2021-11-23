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
 *
 */
function cala_bootMeUp()
{
    grace_info("Cala was booted...");
}

/**
 * Init
 */
function cala_init()
{
    grace_debug("Cala is in the house!");

    $paths = array(
        array(
            'r'             => 'cala_core',
            'action'        => 'cala_core',
            'access'        => 'users_openAccess',
            'params'        => array(
                array("key" => "iam",           "def" => "",            "req" => true, "cli" => "i"),
                array("key" => "sessionKey",    "def" => "",            "req" => true, 'cli' => 's'),
                array("key" => "replyType",     "def" => "json",        "req" => true, 'cli' => 't'),
                array("key" => "w",             "def" => "cala",        "req" => true, "cli" => "w"),
                array("key" => "r",             "def" => "cala_empty",  "req" => true, "cli" => "r")
            ),
        ),
        array(
            'r'         => 'cala_default',
            'action'    => 'cala_default',
            'access'    => 'users_openAccess',
        ),
        # This is an open access call, but you will need a special key to use it
        array(
            'r'         => 'cala_test_install',
            'action'    => 'cala_testInstall',
            'access'    => 'users_openAccess',
            'params'    => array(
                    array("key" => "cronKey", "def" => "", "req" => false, "cli" => "c")
            ))

    );

    return $paths;

}

/**
 * Say hello to the world, this is called if nothing else is specified
 */
function cala_helloWorld()
{
    return "Hello World from Cala :)";
}

/**
 * Dummy func
 */
function cala_core()
{
    return "I don't actualyl do anything :(";
}

/**
 * Just an empty response, it means your request is probably wrong
 */
function cala_testInstall()
{
    grace_debug("Running install tests");

    $bl = "<br/>";

    $output  = "<h1>Cala Installation check proccess</h1>";
    $output .= "This is highly advanced installation check, please read carefully any errors found and correct them before using Cala. $bl $bl";

    # All tests
    $allTests       = array();
    $allGoodMsg     = "All good :) $bl ";
    $allNotGoodMsg  = "Errors found :( $bl ";

    # Lets run the tests

    # Core installation
    $allTests['coreInstall'] = array(
        'name'      => 'Core Installation',
        'comment'   => "Your core installation is in: ". conf_get("coreInstall", "modules", "/")
    );

    # Php Version
    if (version_compare(PHP_VERSION, '5.3.0') >= 0)
        $phpVersion = true;
    else
        $phpVersion = false;

    $allTests['phpVersion'] = array(
        'name'      => 'PHP Version',
        'comment'   => $phpVersion === true ? "I am at least PHP version 5.3.0, my version: " . PHP_VERSION : "You need at least PHP version 5.3.0"
    );

    # Database connection
    $dbConn = db_allGood();
    $allTests['dbConn'] = array(
        'name'      => 'Database connection',
        'comment'   => $dbConn === true ? "All good" : $dbConn
    );

    # Files path
    $filesPath = conf_get('basePath', 'files', '/');
    $filesGood = is_writable($filesPath);
    $allTests['filesGood'] = array(
        'name'      => 'Files storage',
        'comment'   => ($filesGood === true ? $allGoodMsg : $allNotGoodMsg . "Your files storage was not found or the path is not accesible by me: ") . $filesPath
    );

    //$filesPermsGood = is_writable($filesPath);
    $filesPerms = @substr(sprintf('%o', fileperms($filesPath)), -4);
    $filesPermsGood = $filesPerms == '0777';
    $allTests['filesPermsGood'] = array(
        'name'      => 'Files storage permissions',
        'comment'   => ($filesPermsGood == false ? $allGoodMsg : $allNotGoodMsg) . 
        sprintf("Remember to put your files in a NON WEB ACCESSIBLE path and to secure its permissions,
        it is best if they are only writable/redable by the web process which is usually www-root. Current perms are '%s'
        $bl They should be: 0644?
        $bl Are they secure? %s",
        $filesPerms, ($filesPermsGood == false ? $allGoodMsg : "They don't look like it"))
    );

    # Contrib modules
    $contribPath = conf_get('contribPath', 'modules', '/');
    $contribGood = is_dir($contribPath);
    $allTests['contribGood'] = array(
        'name'      => 'Contributed modules',
        'comment'   => ($contribGood === true ? $allGoodMsg : $allNotGoodMsg . "Your contrib modules where not found or the path is not accessible: ") . $contribPath
    );

    # Resources
    $resourcesPath = conf_get('resourcesPath', 'core', '/');
    $resourcesGood = is_dir($resourcesPath);
    $allTests['resourcesGood'] = array(
        'name'      => 'Resources path',
        'comment'   => ($resourcesGood === true ? $allGoodMsg : $allNotGoodMsg . "Your resources path was not found or the path is not accessible: ") . $resourcesPath
    );

    # Cron token
    $cronToken      = conf_get('cronToken', 'cron', 'ItIsGoodIfThisIsBigAndHasW3irDLeeT3rsAnd$ymb0lz.IniT') == 'ItIsGoodIfThisIsBigAndHasW3irDLeeT3rsAnd$ymb0lz.IniT';
    $contribGood    = is_dir($contribPath);
    $allTests['cronToken'] = array(
        'name'      => 'Security Token',
        'comment'   => ($cronToken !== true ? $allGoodMsg : $allNotGoodMsg . "You really need to change your token! ")
    );

    foreach ($allTests as $test => $t)
    {
        $output .= sprintf("<strong>Name:</strong> %s $bl Result: %s $bl $bl", $t['name'], $t['comment']);
    }

    $output .= "-------------------------------------------------- $bl";
    $output .= "Please correct any errors found and go on with it!";

    return $output;
}

