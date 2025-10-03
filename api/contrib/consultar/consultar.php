<?php
/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
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

function consutar()
{
    $curl   = curl_init();
    $clave  = params_get('clave');

    if ($clave == "" || strlen($clave) == 0)
        return "La clave no puede ser en blanco";

    $url = null;
    if (params_get("client_id") == 'api-stag')
        $url = "https://api-sandbox.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    else if (params_get("client_id") == 'api-prod')
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";

    if ($url == null)
        return "Ha ocurrido un error en el client_id.";

    curl_setopt_array($curl, array(
        CURLOPT_URL             => $url . $clave,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_ENCODING        => "",
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_SSL_VERIFYHOST  => 0,
        CURLOPT_SSL_VERIFYPEER  => 0,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST   => "GET",
        CURLOPT_HTTPHEADER      => array(
            "Authorization: Bearer " . params_get('token'),
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "Postman-Token: bf8dc171-5bb7-fa54-7416-56c5cda9bf5c"
        ),
    ));

    $response   = curl_exec($curl);
    $status     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err        = curl_error($curl);
    curl_close($curl);

    if ($err)
    {
        $arrayResp = array(
            "Status" => $status,
            "to" => $apiTo,
            "text" => $err
        );
        return $arrayResp;
    }
    else
    {
         $response = json_decode($response);
        return $response;
    }
}

?>
