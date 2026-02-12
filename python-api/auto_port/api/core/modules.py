"""
AUTO-PORTED FROM: api/core/modules.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def modules_getPath(which):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        if (in_array($which, conf_get('core', 'modules', array())))
            $path = conf_get('corePath', 'modules', "") . $which;
        else
            $path = conf_get('contribPath', 'modules', "") . $which;
    
        return $path;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def modules_loader(which, file='module.php', boot=True):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
