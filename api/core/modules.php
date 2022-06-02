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

global $modules;

/**
 * Gets the correct path for a module
 */
function modules_getPath($which)
{
    if (in_array($which, conf_get('core', 'modules', array())))
        $path = conf_get('corePath', 'modules', "") . $which;
    else
        $path = conf_get('contribPath', 'modules', "") . $which;

    return $path;
}

/**
 *
 */
function modules_loader($which, $file = 'module.php', $boot = true)
{
    $path = modules_getPath($which);
    
        grace_debug("Loading in path: " . $path . "/" . $file);

        # Load the module and the config if one exists
        if (file_exists($path . "/" . $file))
        {
            grace_debug("Including file: " . $path . "/" . $file);
            include_once($path . "/" . $file);
            if (file_exists("$path/settings.php"))
                include_once("$path/settings.php");

            # Boot it up!
            if (function_exists($which . "_bootMeUp"))
            {
                grace_debug("Module was booted too");
                call_user_func($which . "_bootMeUp");
            }
            return true;
        }
        else
        {
            grace_debug("File does not exist");
            return "File " . $file . " not exist";
        }
}
