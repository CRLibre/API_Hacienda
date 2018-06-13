<?php

function consutar() {

    $curl = curl_init();
    $clave = params_get('clave');
    //Validamos que venga el parametro de sucursal

    if ($clave == "" && strlen($clave) == 0) {
        return "La clave no puede ser en blanco";
    }
    
    $url;
    if (params_get("client_id") == 'api-stag') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion/";
    } else if (params_get("client_id") == 'api-prod') {
        $url = "https://api.comprobanteselectronicos.go.cr/recepcion/v1/recepcion/";
    }


    curl_setopt_array($curl, array(
        CURLOPT_URL => $url . $clave,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . params_get('token'),
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
            "Postman-Token: bf8dc171-5bb7-fa54-7416-56c5cda9bf5c"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $responseT = json_decode($response);
        return $responseT;
    }
}

?>
