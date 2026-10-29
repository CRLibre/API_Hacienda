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

function tools_reply($response, $killMe = false)
{
    switch ($response) {
        case ERROR_USERS_NO_VALID:
            _tools_responseCode(400);
            $response = "Usuario no válido";
            $killMe = true;
            break;
        case ERROR_USERS_WRONG_LOGIN_INFO:
            _tools_responseCode(401);
            $response = "Información de acceso incorrecta";
            $killMe = true;
            break;
        case ERROR_USERS_NO_VALID_SESSION:
            _tools_responseCode(440);
            $response = "Sesión no válida o expirada";
            $killMe = true;
            break;
        case ERROR_USERS_ACCESS_DENIED:
            _tools_responseCode(403);
            $response = "Acceso denegado";
            $killMe = true;
            break;
        case ERROR_USERS_EXISTS:
            _tools_responseCode(409);
            $response = "El usuario ya existe";
            $killMe = true;
            break;
        default:
            _tools_responseCode(200);
    }

    if (is_array($response) && isset($response['Status'])) {
        switch ($response['Status']) {
            case 'error':
                _tools_responseCode(500);
                $killMe = true;
                break;
            case 'ok':
                _tools_responseCode(200);
                break;
            default:
                _tools_responseCode(400);
                $killMe = true;
        }
        $response = $response['text'];
    }

    if ($killMe) {
        if (is_string($response)) {
            $response = "ERROR: " . $response;
        }
    } else {
        _tools_responseCode(200);
    }

    if (params_get('replyType', 'json') == 'json') {
        _tools_reply(tools_returnJson(array(
            'status' => ($killMe ? 'error' : 'ok'),
            'resp' => $response
        )));
        # There will be other reply types soon...
        //}
        //elseif(params_get('replyType', 'json') == 'plain')
        //{
        # Reply all other replyTypes
    } else {
        if (conf_get('mode', 'core', 'web') == 'cli')
            $response .= "\n";

        _tools_reply($response);
    }
}

/**
 * PHP 8 lanza un warning si ya hubo salida; PHP 7 lo ignoraba en silencio.
 */
function _tools_responseCode($code)
{
    if (!headers_sent())
        http_response_code($code);
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
    if ($addHeaders && conf_get('mode', 'core', 'web') != 'cli') {
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

    foreach ($paths as $p) {
        if ($p['r'] == params_get('r')) {
            grace_debug("Found path: " . $p['r']);
            if (isset($p['params']))
                params_verifyRequest($p['params']);

            # Check for permissions
            $p['access_params'] = isset($p['access_params']) ? $p['access_params'] : '';
            # PHP 7 seguía con un warning si la función de acceso no
            # existía; en PHP 8 call_user_func() lanza TypeError.
            if (!is_callable($p['access']))
                grace_error("Access function not found: " . $p['access']);
            else if (call_user_func($p['access'], $p['access_params']) === false)
                return ERROR_USERS_ACCESS_DENIED;

            // Load the correct file
            if (isset($p['file']))
                modules_loader(params_get('w'), $p['file']);

            if (function_exists($p['action'])) {
                grace_debug("Found function: " . $p['action']);
                $response = call_user_func($p['action']);
                return $response;
            } else
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

    $path = conf_get('coreInstall', 'modules', '') .  "core/" . $which;

    grace_debug("Loading library: " . $path);

    if (file_exists($path)) {
        grace_debug("Found library");
        include_once($which);
        return true;
    } else
        return false;
}
