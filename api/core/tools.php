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

function tools_reply($response, $killMe = false)
{
    if ($killMe)
    {
        /* do something some day */
    }

    if (params_get('replyType', 'json') == 'json')
    {
        _tools_reply(tools_returnJson(array('resp' => $response)));
        # There will be other reply types soon...
    //}
    //elseif(params_get('replyType', 'json') == 'plain')
    //{
        # Reply all other replyTypes
    }
    else
    {
        if (conf_get('mode', 'core', 'web') == 'cli')
            $response .= "\n";

        _tools_reply($response);
    }

}

function _tools_reply($response)
{
    print $response;

    # This really should not be here, but so far, it should do
    //! @todo move this two functions somewhere else
    users_updateLastAccess();

    # Close the log
    grace_storeLog();

    exit;
}

function tools_returnJson($response, $addHeaders = true)
{
    if ($addHeaders && conf_get('mode', 'core', 'web') != 'cli')
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Type: application/json');
    }

    return json_encode($response);
}

/**
 * Process the path according to what was requested
 */
function tools_proccesPath($paths)
{
    grace_debug("Looking for path: " . params_get('r'));

    foreach ($paths as $p)
    {
        if ($p['r'] == params_get('r'))
        {
            grace_debug("Found path: " . $p['r']);
            if (isset($p['params']))
                params_verifyRequest($p['params']);

            # Check for permissions
            $p['access_params'] = isset($p['access_params']) ? $p['access_params'] : '';
            if (call_user_func($p['access'], $p['access_params']) === false)
                return ERROR_USERS_ACCESS_DENIED;

            // Load the correct file
            if (isset($p['file']))
                modules_loader(params_get('w'), $p['file']);

            if (function_exists($p['action']))
            {
                grace_debug("Found function: " . $p['action']);
                $response = call_user_func($p['action']);
                return $response;
            }
            else
                return ERROR_BAD_REQUEST;
        }
    }

    grace_debug("Path not found?");
    return "Function not found";
}

/**
 * Load tools from the toolbox
 */
function tools_useTool($which)
{
    $path = conf_get("coreInstall", "modules", "") . "tools/$which";

    grace_debug("Loading tool: " . $path);

    if (file_exists($path))
        include_once($path);
}

/**
 * Load core libraries
 */
function tools_loadLibrary($which)
{
    global $config;

    $path = conf_get('coreInstall', 'modules', '') .  "core/" .$which;

    grace_debug("Loading library: " . $path);

    if (file_exists($path))
    {
        grace_debug("Found library");
        include_once($which);
        return true;
    }
    else
        return false;
}
