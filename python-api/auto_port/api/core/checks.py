"""
AUTO-PORTED FROM: api/core/checks.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def CheckPHPVersion():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
         if (!version_compare(PHP_VERSION, '5.5', '>='))
            die("Requieres la version PHP 5.5 o superior.");
     
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def CheckPHPExtensions():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
         $extReq = array('curl', 'xml', 'openssl', 'mysqli');
         $extInstalled = get_loaded_extensions();
         $errors = array();
    
         foreach ($extReq as $ext)
         {
             if (!in_array($ext, $extInstalled))
                $errors[] = $ext;
         }
    
         if (count($errors) > 0)
            die("Necesitas instalar las siguientes extensiones PHP: ". join(", ", $errors));
     
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
