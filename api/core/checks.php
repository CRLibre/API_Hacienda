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
