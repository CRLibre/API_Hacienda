"""
AUTO-PORTED FROM: api/core/params.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def params_get(p, def=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $params;
        
        # Get them all
        if ($p === false)
            return $params;
    
        if (isset($params[$p]) && trim($params[$p]) != '')
            return $params[$p];
        else
            return $def;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def params_set(p, val=False, override=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $params;
    
        if (is_array($val)) {
            foreach ($val as $vv => $v) {
                _params_set($vv, $v, $override);
            }
        } else
            _params_set($p, $val, $override);
    
        return $params[$p];
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def _params_set(p, val=False, override=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $params;
    
        if (isset($params[$p]) && $override)
            $params[$p] = $val;
        else
            $params[$p] = $val;
    
        return $params[$p];
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def params_verifyRequest(keys):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        foreach ($keys as $key) {
            if (params_get($key["key"], '') === '') {
                $msg = "Falta el parametro requerido: " . $key["key"];
                grace_debug($msg);
                if ($key["req"])
                    tools_reply($msg, true);
                else # Set the default value
                {
                    grace_debug("Using default");
                    params_set($key["key"], $key["def"]);
                }
            }
        }
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def params_cliLoadOpts(allParams):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        grace_debug("Loading in CLI mode");
    
        echo "loading";
    
        # Load the params
        //$params = core_init();
    
        # The actual params to be considered
        $params = array();
    
        $opts = "";
        $longOpts = array();
    
        # Extract only the params
        foreach ($allParams as $param) {
            if (isset($param['params']))
                $params[] = $param['params'];
        }
    
        # For some reason they are stored in pos 0 of the array
        $params = $params[0];
    
        print_r($params);
        foreach ($params as $p) {
            $longOpt = $p['key'];
            $opts .= $p['cli'];
            # Is it mandatory?
            if ($p['req']) {
                $opts .= ":";
                $longOpt .= ":";
            } else {
                $opts .= "::";
                $longOpt .= "::";
            }
    
            $longOpts[] = $longOpt;
        }
    
        grace_debug("Opts requested: " . $opts);
    
        # I need the basics in order to start
        $args = getopt($opts, $longOpts);
    
        # Now, lets extract them :)
        foreach ($params as $p) {
            # Was it sent as longOpt?
            if (isset($args[$p['key']]))
                params_set($p['key'], $args[$p['key']]);
            else if (isset($args[$p['cli']]))
                params_set($p['key'], $args[$p['cli']]);
            else if ($p['req'] == false) # If it is optional, I will load the default value?
                params_set($p['key'], $p['def']);
        }
    
        //print_r(params_get(false));
        params_verifyRequest($params);
        //if ($args[''] == 8)
        //var_dump($args);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def core_getBasicParams():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    <empty>
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
