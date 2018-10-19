<?php

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
