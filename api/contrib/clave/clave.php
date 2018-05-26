<?php

//Funcion para generar clave
/**
 * Esta funcion se puede llamar desde GET POST si se envian los siguientes parametros
 * w=clave
 * r=getClave
 * tipoCedula=   fisico o juridico
 * cedula=  Numero de Cedula
 * codigoPais=  506
 * consecutivo=  codigo de 10 numeros
 * situacion=  nomal contingencia sininternet
 * codigoSeguridad=  codigo de 8 numeros
 * tipoDocumento=  FE ND NC TE CCE CPCE RCE 
 *          
 * Tambien se puede llamar desde un metodo de la siguiente manera:
 * modules_loader("clave");       <-- Esta funcion importa el modulo
 * getClave($tipoDocumento="",$tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "")  <------------ esta funcion retorna la clave
 * */
/*
 * Tipo de comprobante o documento asociado Código
  Factura electrónica 01
  Nota de débito electrónica 02
  Nota de crédito electrónica 03
  Tiquete Electrónico 04
  Confirmación de aceptación del comprobante electrónico 05
  Confirmación de aceptación parcial del comprobante
  electrónico
  06
  Confirmación de rechazo del comprobante electrónico 07

 */

function getClave($tipoDocumento = "", $tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "") {

    if ($tipoCedula == '' or $tipoDocumento = "" or $cedula = '' or $situacion = '' or $codigoPais = '' or $consecutivo = '' or $codigoSeguridad = '') {
        //-----------------------------------------------//
        $tipoDocumento = params_get('tipoDocumento');
        $tipoCedula = params_get('tipoCedula');
        $cedula = params_get('cedula');
        $situacion = params_get('situacion');
        $codigoPais = params_get('codigoPais'); // 3 digitos Codigo pais 506
        $consecutivo = params_get('consecutivo'); //9 caracteres
        $codigoSeguridad = params_get('codigoSeguridad');  //8 digitos codigo de seguridad   
        $sucursal = params_get("sucursal");
        $terminal = params_get("terminal");
    }
    $dia = date('d');
    $mes = date('m');
    $ano = date('y');

    //Validamos que venga el parametro de sucurnal
    if ($sucursal == "" && strlen($sucursal) == 0) {
        $sucursal = "001";
    } else if (strlen($sucursal) != 3 && $sucursal != 0) {
            $arrayResp = array(
                "error" => "Error en sucursal",
                "razon" => "el tamaño es diferente de 3 digitos"
            );
            return $arrayResp;
    }
    

    //Validamos que venga el parametro de terminal
    if ( $terminal == "" && strlen($terminal) == 0 ) {
        $terminal = "00001";
    } else if (strlen($terminal) != 5 && $terminal != 0) {
            $arrayResp = array(
                "error" => "Error en terminal",
                "razon" => "el tamaño la terminal es diferente de 5 digitos"
            );
            return $arrayResp;
        }
    

    $tipoDoc = params_get('tipoDocumento');

    $tipos = array("FE", "ND", "NC", "TE", "CCE", "CPCE", "RCE");
    grace_debug($tipoDoc);
    if (in_array($tipoDoc, $tipos)) {
        switch ($tipoDoc) {
            case 'FE': //Factura Electronica
                $tipoDocumento = "01";
                break;
            case 'ND': // Nota de Debito
                $tipoDocumento = "02";
                break;
            case 'NC': // Nota de Credito
                $tipoDocumento = "03";
                break;
            case 'TE': // Tiquete Electronico
                $tipoDocumento = "04";
                break;
            case 'CCE': // Confirmacion Comprabante Electronico
                $tipoDocumento = "05";
                break;
            case 'CPCE': // Confirmacion Parcial Comprbante Electronico
                $tipoDocumento = "06";
                break;
            case 'RCE': // Rechazo Comprobante Electronico
                $tipoDocumento = "07";
                break;
        }
    } else {
        return "No se encuentra tipo de documento";
    }

    $consecutivoFinal = $sucursal . $terminal . $tipoDocumento . $consecutivo;
    //-----------------------------------------------//
    //Numero de Cedula + el indice identificador
    $identificacion;
    $cedulas = array("fisico", "juridico");
    if (in_array($tipoCedula, $cedulas)) {
        switch ($tipoCedula) {
            case 'fisico': //fisico
                $identificacion = "000" . $cedula;
                break;
            case 'juridico': // juridico
                $identificacion = "00" . $cedula;
                break;
        }
    } else {
        return "No se encuentra tipo de cedula";
    }


    //-----------------------------------------------//
    //1	Normal	Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
    //2	Contingencia	Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
    //3	Sin internet	Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
    $situaciones = array("normal", "contingencia", "sininternet");
    if (in_array($situacion, $situaciones)) {
        switch ($situacion) {
            case 'normal': //normal
                $situacion = 1;
                break;
            case 'contingencia': // Situacion de contingencia
                $situacion = 2;
                break;
            case 'sininternet': //Situacion sin internet
                $situacion = 3;
                break;
        }
    } else {
        return "No se encuentra el tipo de situacion";
    }

    //-----------------------------------------------//     
    //Crea la clave 
    $clave = $codigoPais . $dia . $mes . $ano . $identificacion . $consecutivoFinal . $situacion . $codigoSeguridad;
    $arrayResp = array(
        "clave" => "$clave",
        "consecutivo" => "$consecutivoFinal",
    );
    return $arrayResp;
}

?>
