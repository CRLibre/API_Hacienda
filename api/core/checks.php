<?php

 /*
  * Copyright (C) 2018 CRLibre <https://crlibre.org>
  * Licensed under the GNU Affero General Public 3.0 License
  * https://www.gnu.org/licenses/agpl-3.0.en.html
  */

 function CheckPHPVersion()
 {
     if (!version_compare(PHP_VERSION, '5.5', '>='))
        die("Requieres la version PHP 5.5 o superior.");
 }

 function CheckPHPExtensions()
 {
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
 }

 //
 CheckPHPVersion();
 CheckPHPExtensions();

?>
