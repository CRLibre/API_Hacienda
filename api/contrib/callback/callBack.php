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

 function api_callback()
 {
    $idUser = params_get('idUser');
    $json   = file_get_contents('php://input');
    grace_debug("Json de Ingreso:\n" . $json);

    $json           = str_replace("ind-estado", "ind_estado", $json);
    $json           = str_replace("respuesta-xml", "respuesta_xml", $json);
    $json           = json_decode($json);
    $clave          = json_encode($json->clave);
    $ind_estado     = json_encode($json->ind_estado);
    $fecha          = json_encode($json->fecha);
    $respuesta_xml  = json_encode($json->respuesta_xml);
    grace_debug("El formulario " . $clave . " fue " . $ind_estado . " a la fecha " . $fecha . " respuesta de hacienda " . $respuesta_xml);
    return 202;
 }

?>
