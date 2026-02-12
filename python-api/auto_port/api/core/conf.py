"""
AUTO-PORTED FROM: api/core/conf.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def conf_get(what, whom, def=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $config;
    
        if (isset($config[$whom][$what]))
            return $config[$whom][$what];
        else
            return $def;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def conf_set(what, whom, val=False, override=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $config;
    
        if (isset($config[$whom][$what]) && $override)
            $config[$whom][$what] = $val;
        else
            $config[$whom][$what] = $val;
    
        return $config[$whom][$what];
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def conf_getAll():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $config;
        return $config;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
