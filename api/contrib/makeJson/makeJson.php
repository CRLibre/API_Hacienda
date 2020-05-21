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

function makeFinalJson()
{
    $clave                      = params_get("clave");
    $fecha                      = params_get("fecha");
    $emi_tipoIdentificacion     = params_get("emi_tipoIdentificacion");
    $emi_numeroIdentificacion   = params_get("emi_numeroIdentificacion");
    $recp_tipoIdentificacion    = params_get("recp_tipoIdentificacion");
    $recp_numeroIdentificacion  = params_get("recp_numeroIdentificacion");
    $comprobanteXml             = params_get("comprobanteXml");
    $response                   = array(
                                    'clave'         => $clave,
                                    'fecha'         => $fecha,
                                    'emisor'        => array('tipoIdentificacion' => $emi_tipoIdentificacion, 'numeroIdentificacion' => $emi_numeroIdentificacion),
                                    'receptor'      => array('tipoIdentificacion' => $recp_tipoIdentificacion, 'numeroIdentificacion' => $recp_numeroIdentificacion),
                                    'comprobanteXml'=> $comprobanteXml);
    return  $response;
}
?>
