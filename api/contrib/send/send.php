<?php

function send(){
    

$datos =array(
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

//$datosJ= http_build_query($datos);

$mensaje = json_encode($datos);


	$header = array(
		'Authorization: bearer '.params_get('token'),
		'Content-Type: application/json'
	);

	$curl = curl_init("https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion");
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);

        $respuesta = curl_exec($curl);  
        $status= curl_getinfo($curl,CURLINFO_HTTP_CODE);
            $arrayResp = array(
            "Status" => $status,   
            "text"=> explode("\n",$respuesta)
            );
	curl_close($curl);
        return $arrayResp;
};



function sendMensaje(){
    

$datos =array(
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
                'consecutivoReceptor' => params_get("consecutivoReceptor"),
		'comprobanteXml' => params_get("comprobanteXml")
	);

//$datosJ= http_build_query($datos);

$mensaje = json_encode($datos);


	$header = array(
		'Authorization: bearer '.params_get('token'),
		'Content-Type: application/json'
	);

	$curl = curl_init("https://api.comprobanteselectronicos.go.cr/recepcion-sandbox/v1/recepcion");
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);

        $respuesta = curl_exec($curl);  
        $status= curl_getinfo($curl,CURLINFO_HTTP_CODE);
            $arrayResp = array(
            "Status" => $status,    
             "text"=> explode("\n",$respuesta)
            );
	curl_close($curl);
        return $arrayResp;
};
?>
