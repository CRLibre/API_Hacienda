<?php

function token() {

    $url;
    if (params_get("client_id") == 'api-stag') {
        $url = "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token";
    } else if (params_get("client_id") == 'api-prod') {
        $url = "https://idp.comprobanteselectronicos.go.cr/auth/realms/rut/protocol/openid-connect/token";
    }
    $data;
    //Get Data from Post
    if (params_get("grant_type") == "password") {

        $client_id = params_get("client_id");
        $client_secret = params_get("client_secret");
        $grant_type = params_get("grant_type");
        $username = params_get("username");
        $password = params_get("password");
        
        //Validation
        if ($client_id == '') {
            return "El parametro Client ID es requerido";
        } else if ($grant_type == '') {
            return "El parametro Grant Type es requerido";
        } else if ($username == '') {
            return "El parametro Username es requerido";
        } else if ($password == '') {
            return "El parametro Password es requerido";
        }

        $data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => $grant_type,
            'username' => $username,
            'password' => $password
        );
    } else if (params_get("grant_type") == "refresh_token") {

        $client_id = params_get("client_id");
        $client_secret = params_get("client_secret");
        $grant_type = params_get("grant_type");
        $refresh_token = params_get("refresh_token");

        //Validation
        if ($client_id == '') {
            return "El parametro Client ID es requerido";
        } else if ($grant_type == '') {
            return "El parametro Grant Type es requerido";
        } else if ($refresh_token == '') {
            return "El parametro Refresh Token es requerido";
        }

        $data = array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => $grant_type,
            'refresh_token' => $refresh_token
        );
    }

    //Making the options
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $ar = json_decode($result);

    return $ar;
}

?>