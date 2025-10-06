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

function token()
{
    $client_id  = params_get("client_id");
    $grant_type = params_get("grant_type");

    $url = ($client_id == 'api-stag' ? "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token" :
            ($client_id == 'api-prod' ? "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token" : null));

    $data = array();

    // Get Data from Post
    if ($grant_type == "password")
    {
        $client_secret  = params_get("client_secret");
        $username       = params_get("username");
        $password       = params_get("password");

        // Validation
        if ($client_id == '')
            return "El parametro Client ID es requerido";
        else if ($grant_type == '')
            return "El parametro Grant Type es requerido";
        else if ($username == '')
            return "El parametro Username es requerido";
        else if ($password == '')
            return "El parametro Password es requerido";

        $data = array(
            'client_id'         => $client_id,
            'client_secret'     => $client_secret,
            'grant_type'        => $grant_type,
            'username'          => $username,
            'password'          => $password
        );

    }
    else if ($grant_type == "refresh_token")
    {
        $client_secret      = params_get("client_secret");
        $refresh_token      = params_get("refresh_token");

        // Validation
        if ($client_id == '')
            return "El parametro Client ID es requerido";
        else if ($grant_type == '')
            return "El parametro Grant Type es requerido";
        else if ($refresh_token == '')
            return "El parametro Refresh Token es requerido";

        $data = array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => $grant_type,
            'refresh_token' => $refresh_token
        );
    }

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HEADER, 'Content-Type: application/x-www-form-urlencoded');
    $data = http_build_query($data);
    //$data = rtrim($data, '&');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    grace_debug("JSON: ".$data);

    $respuesta  = curl_exec($curl);
    $status     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err        = json_decode(curl_error($curl));
    curl_close($curl);

    if ($err)
    {
        $arrayResp = array(
            "Status"    => $status,
            "text"      => $err
        );

        return $arrayResp;
    }
    else
        return json_decode($respuesta);
}

?>
