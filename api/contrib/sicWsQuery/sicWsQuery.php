<?php

function sicWsQuery() {
    ini_set('soap.wsdl_cache_enabled', 0);
    ini_set('soap.wsdl_cache_ttl', 900);
    ini_set('default_socket_timeout', 15);
// TODO: Implementar la función que genere el digito verificador de pertenencia para poder hacer la consulta al webservice.
//       Adaptarlo al ejemplo de la API
    $options = [
        'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'style' => SOAP_RPC,
        'use' => SOAP_ENCODED,
        'soap_version' => SOAP_1_1,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'connection_timeout' => 30,
        'trace' => true,
        'encoding' => 'UTF-8',
        'exceptions' => true
    ];
// El webservice en Hacienda hace la consulta utilizando los valores
// de parámetro que no estén vacíos, así que se puede hacer una consulta
// haciendo combinaciones.
    $params = [
        'origen' => params_get('origen'), // Fisico,  Juridico o DIMEX
        'cedula' => params_get('cedula'),
        'ape1' => params_get('ape1'),
        'ape2' => params_get('ape2'),
        'nomb1' => params_get('nomb1'),
        'nomb2' => params_get('nomb2'),
        'razon' => params_get('razon'),
        'Concatenado' => params_get('Concatenado')
    ];
    $wsdl = "http://196.40.56.20/wsInformativasSICWEB/Service1.asmx?WSDL";
    try {
        $soap = new SoapClient($wsdl, $options);
        $data = $soap->ObtenerDatos($params);
    } catch (Exception $e) {
        return str_replace('"', '\'', $e->getMessage());
    }
    $soap_response = $data->ObtenerDatosResult->any;
    // Remove namespaces
    $xml = str_replace(array("diffgr:", "msdata:"), '', $soap_response);
    // Wrap into root element to make it standard XML
    $xml = "<package>" . $xml . "</package>";
    // Parse with SimpleXML - probably there're much better ways
    $datos = simplexml_load_string($xml);
    $array = array();
    $cantidad = count($datos->diffgram->DocumentElement->Table);
    for ($i = 0; $i < $cantidad; $i++) {
        if (params_get('origen') == "Fisico") {
            array_push($array, array(
                "TIPO" => params_get('origen'),
                "CEDULA" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->CEDULA),
                "APELLIDO1" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->APELLIDO1),
                "APELLIDO2" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->APELLIDO2),
                "NOMBRE1" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->NOMBRE1),
                "NOMBRE2" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->NOMBRE2),
                "ADM" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->ADM),
                "ORI" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->ORI)
                    )
            );
        } else if (params_get('origen') == "Juridico") {

            array_push($array, array(
                "TIPO" => params_get('origen'),
                "CEDULA" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->CEDULA),
                "NOMBRE" => rtrim($datos->diffgram->DocumentElement->Table[$i]->NOMBRE),
                "ADM" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ADM),
                "ORI" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ORI)
            ));
        } else if (params_get('origen') == "DIMEX") {
//            array_push($array, array(
//                "TIPO" => params_get('origen'),
//                "CEDULA" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->CEDULA),
//                "NOMBRE" => rtrim($datos->diffgram->DocumentElement->Table[$i]->NOMBRE),
//                "ADM" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ADM),
//                "ORI" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ORI)
//            ));
            echo $datos->diffgram->DocumentElement->Table[0];
        } else if (params_get('origen') == "NITE") {
//            array_push($array, array(
//                "TIPO" => params_get('origen'),
//                "CEDULA" =>  rtrim($datos->diffgram->DocumentElement->Table[$i]->CEDULA),
//                "NOMBRE" => rtrim($datos->diffgram->DocumentElement->Table[$i]->NOMBRE),
//                "ADM" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ADM),
//                "ORI" =>  rtrim( $datos->diffgram->DocumentElement->Table[$i]->ORI)
//            ));
            echo $datos->diffgram->DocumentElement->Table[0];
        }
    }
    return $array;
}
