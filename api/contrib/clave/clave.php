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


    //Validamos el parametro de cedula    
    if ($cedula == "" && strlen($cedula) == 0) {
        return "El valor cedula no debe ser vacio";
    } else if (!ctype_digit($cedula)) {
        return "El valor cedula no es numeral";
    }


    //Validamos el parametro de cedula    
    if ($codigoPais == "" && strlen($codigoPais) == 0) {
        return "El valor codigoPais no debe ser vacio";
    } else if (!ctype_digit($codigoPais)) {
        return "El valor codigoPais no es numeral";
    }


    //Validamos que venga el parametro de sucursal

    if ($sucursal == "" && strlen($sucursal) == 0) {
        $sucursal = "001";
    } else if (ctype_digit($sucursal)) {

        if (strlen($sucursal) < 3) {
            $sucursal = str_pad($sucursal, 3, "0", STR_PAD_LEFT);
        } else if (strlen($sucursal) != 3 && $sucursal != 0) {
            $arrayResp = array(
                "error" => "Error en sucursal",
                "razon" => "el tamaño es diferente de 3 digitos"
            );
            return $arrayResp;
        }
    } else {
        return "El valor sucursal no es numeral";
    }




    //Validamos que venga el parametro de terminal
    if ($terminal == "" && strlen($terminal) == 0) {
        $terminal = "00001";
    } else if (ctype_digit($terminal)) {

        if (strlen($terminal) < 5) {
            $terminal = str_pad($terminal, 5, "0", STR_PAD_LEFT);
        } else if (strlen($terminal) != 5 && $terminal != 0) {
            $arrayResp = array(
                "error" => "Error en terminal",
                "razon" => "el tamaño es diferente de 5 digitos"
            );
            return $arrayResp;
        }
    } else {
        return "El valor terminal no es numeral";
    }




//Validamos el consecutivo


    if ($consecutivo == "" && strlen($consecutivo) == 0) {
        return "El consecutivo no puede ser vacio";
    } else if (strlen($consecutivo) < 10) {
        $consecutivo = str_pad($consecutivo, 10, "0", STR_PAD_LEFT);
    } else if (strlen($consecutivo) != 10 && $consecutivo != 0) {
        $arrayResp = array(
            "error" => "Error en consecutivo",
            "razon" => "el tamaño consecutivo es diferente de 10 digitos"
        );
        return $arrayResp;
    }


//Validamos el codigoSeguridad


    if ($codigoSeguridad == "" && strlen($codigoSeguridad) == 0) {
        return "El consecutivo no puede ser vacio";
    } else if (strlen($codigoSeguridad) < 8) {
        $codigoSeguridad = str_pad($codigoSeguridad, 8, "0", STR_PAD_LEFT);
    } else if (strlen($codigoSeguridad) != 8 && $codigoSeguridad != 0) {
        $arrayResp = array(
            "error" => "Error en codigo Seguridad",
            "razon" => "el tamaño codigo Seguridad es diferente de 8 digitos"
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
    $cedulas = array("fisico", "juridico", "dimex", "nite");
    if (in_array($tipoCedula, $cedulas)) {
        switch ($tipoCedula) {
            case 'fisico': //fisico se agregan 3 ceros para completar los 12 caracteres
                $identificacion = "000" . $cedula;
                break;
            case 'juridico': // juridico se agregan 2 ceros para completar los 12 caracteres
                $identificacion = "00" . $cedula;
                break;
            case 'dimex': // dimex puede ser de 11 0 12 caracteres
                if (strlen($cedula) == 11) {
                    //En caso de ser 11 caracteres se le agrega un 0
                    $identificacion = "0" . $cedula;
                }else if(strlen($cedula) == 12){
                    $identificacion =$cedula;
                }else{
                    return "dimex incorrecto";
                }
                break;
            case 'nite': // nite se agregan 2 ceros para completar los 12 caracteres
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
