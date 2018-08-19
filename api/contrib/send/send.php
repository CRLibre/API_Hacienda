<?php

function send() {
    $url;
    $datos;
    $apiTo = params_get("client_id");

    if ($apiTo == 'api-stag') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
    } else if ($apiTo == 'api-prod') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    }

    if (params_get("recp_tipoIdentificacion") == "" or params_get("recp_numeroIdentificacion") == "") {
        if (params_get('callbackUrl') == "") {
            $datos = array(
                'clave' => params_get('clave'),
                'fecha' => params_get("fecha"),
                'emisor' => array(
                    'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
                ),
                'comprobanteXml' => params_get("comprobanteXml")
            );
        } else {
            $datos = array(
                'clave' => params_get('clave'),
                'fecha' => params_get("fecha"),
                'emisor' => array(
                    'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
                ),
                'comprobanteXml' => params_get("comprobanteXml"),
                'callbackUrl' => params_get('callbackUrl')
            );
        }
    } else {
        if (params_get('callbackUrl') == "") {
            $datos = array(
                'clave' => params_get('clave'),
                'fecha' => params_get("fecha"),
                'emisor' => array(
                    'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
                ),
                'receptor' => array(
                    'tipoIdentificacion' => params_get("recp_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("recp_numeroIdentificacion")
                ),
                'comprobanteXml' => params_get("comprobanteXml")
            );
        } else {
            $datos = array(
                'clave' => params_get('clave'),
                'fecha' => params_get("fecha"),
                'emisor' => array(
                    'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
                ),
                'receptor' => array(
                    'tipoIdentificacion' => params_get("recp_tipoIdentificacion"),
                    'numeroIdentificacion' => params_get("recp_numeroIdentificacion")
                ),
                'comprobanteXml' => params_get("comprobanteXml"),
                'callbackUrl' => params_get('callbackUrl')
            );
        }
    }
//$datosJ= http_build_query($datos);
    $mensaje = json_encode($datos);
    grace_debug("JSON:" . $mensaje);
    $header = array(
        'Authorization: bearer ' . params_get('token'),
        'Content-Type: application/json'
    );
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $respuesta = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $arrayResp = array(
            "Status" => $status,
            "to" => $apiTo,
            "text" => $err
        );
        return $arrayResp;
    } else {
        $arrayResp = array(
            "Status" => $status,
            "text" => explode("\n", $respuesta)
        );
        return $arrayResp;
    }
}

function sendMensaje() {
    $url;
    $apiTo = params_get("client_id");
    if ($apiTo == 'api-stag') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
    } else if ($apiTo == 'api-prod') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    }

    $datos = array(
        'clave' => params_get('clave'),
        'fecha' => params_get("fecha"),
        'emisor' => array(
            'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
            'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
        ),
        'receptor' => array(
            'tipoIdentificacion' => params_get("recp_tipoIdentificacion"),
            'numeroIdentificacion' => params_get("recp_numeroIdentificacion")
        ),
        'consecutivoReceptor' => str_pad(params_get("consecutivoReceptor"), 20, "0", STR_PAD_LEFT),
        'comprobanteXml' => params_get("comprobanteXml")
    );

//$datosJ= http_build_query($datos);

    $mensaje = json_encode($datos);


    $header = array(
        'Authorization: bearer ' . params_get('token'),
        'Content-Type: application/json'
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $respuesta = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $arrayResp = array(
            "Status" => $status,
            "to" => $apiTo,
            "text" => $err
        );
        return $arrayResp;
    } else {
        $arrayResp = array(
            "Status" => $status,
            "text" => explode("\n", $respuesta)
        );
        return $arrayResp;
    }
}

function sendTE() {
    $url;
    $apiTo = params_get("client_id");
    if ($apiTo == 'api-stag') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
    } else if ($apiTo == 'api-prod') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    }

    $datos = array(
        'clave' => params_get('clave'),
        'fecha' => params_get("fecha"),
        'emisor' => array(
            'tipoIdentificacion' => params_get("emi_tipoIdentificacion"),
            'numeroIdentificacion' => params_get("emi_numeroIdentificacion")
        ),
        'comprobanteXml' => params_get("comprobanteXml")
    );

//$datosJ= http_build_query($datos);

    $mensaje = json_encode($datos);


    $header = array(
        'Authorization: bearer ' . params_get('token'),
        'Content-Type: application/json'
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $respuesta = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        $arrayResp = array(
            "Status" => $status,
            "to" => $apiTo,
            "text" => $err
        );
        return $arrayResp;
    } else {
        $arrayResp = array(
            "Status" => $status,
            "text" => explode("\n", $respuesta)
        );
        return $arrayResp;
    }
}

?>
