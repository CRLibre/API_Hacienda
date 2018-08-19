<?php

function api_callback() {
    $idUser = params_get('idUser');
    $json = file_get_contents('php://input');
    grace_debug("Json de Ingreso:\n" . $json);
    $json = str_replace("ind-estado", "ind_estado", $json);
    $json = str_replace("respuesta-xml", "respuesta_xml", $json);
    $json = json_decode($json);
    $clave = json_encode($json->clave);
    $ind_estado = json_encode($json->ind_estado);
    $fecha = json_encode($json->fecha);
    $respuesta_xml = json_encode($json->respuesta_xml);
    grace_debug("El formulario " . $clave . " fue " . $ind_estado . " a la fecha " . $fecha . " respuesta de hacienda " . $respuesta_xml);
    return 202;
    
}

?>
