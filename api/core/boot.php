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
 * @page
 */
include_once("checks.php");
include_once("conf.php");
include_once("grace.php");
include_once("params.php");
include_once("modules.php");
include_once("tools.php");


/** @defgroup Constants
 *  @{
 */
//! Bad request done
define('ERROR_BAD_REQUEST', -1);

//! Some generic error, if no real error happened, but nothing was found or the process did not go as espected
define('ERROR_ERROR', -2);

//! ALl is good
define('SUCCESS_ALL_GOOD', 1);

//! This will tell me if I am runnig in CLI mode
define('CLI_MODE', false);

/** @} */

/**
 * Boot me up!
 */
function boot_itUp($mode = 'web')
{
    grace_debug("Booting up");

    if (conf_get('alert', 'boot') == 'false')
        error_reporting(0); // Turn off all error reporting

    # Load all core modules
    # @todo Call the current requested module first in case it wants to change the core modules to be loaded
    boot_loadAllCoreModules();

    if ($mode != 'web')
    {
        // Set mode
        conf_set('mode', 'core', 'cli');
        params_cliLoadOpts(cala_init());
    }

    # Select and load the correct called module
    # @todo if the module is core and it was already loaded, don't do it again :)
    modules_loader(params_get('w', 'cala'));

    # Init this call
    return boot_initThisPath();
}

/**
 * Call the init function in the correct module
 */
function boot_initThisPath()
{
    /** @bug Apparently post requests (at least from Java) require a
     *  \n (breakline) in each post request parameter, which breaks everything
     *  this issue is still under investigation
     */
    $f = preg_replace("/[\n\r\f]+/m", "", params_get('w', 'core') . "_init");

    if (function_exists($f))
    {
        grace_debug("Function found");
        $response = tools_proccesPath(call_user_func($f));
    }
    else
        $response = "Module not found";

    tools_reply($response);
}

/**
 * Load all core modules
 * @bug If the called module is a core module, it will get booted twice
 */
function boot_loadAllCoreModules()
{
    grace_debug("Loading all core modules");

    foreach (conf_get('coreLoad', 'modules') as $module)
    {
        grace_debug("Loading module: " . $module);
        modules_loader($module);
    }
}

/**
 * Load boot up modules
 */
function boot_loadBootModules()
{
    //Todo
}
