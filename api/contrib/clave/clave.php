<?php
//Funcion para generar clave
/**
     * Esta funcion se puede llamar desde GET POST si se envian los siguientes parametros
     * w=clave
     * r=getClave
     * tipoCedula=   fisico o juridico
     * cedula=  Cedula de persona fisica o juridico
     * codigoPais=  Cedula de persona fisica o juridico
     * consecutivo=  Cedula de persona fisica o juridico
     * situacion=  Cedula de persona fisica o juridico
     * codigoSeguridad=  Cedula de persona fisica o juridico
     * tipoDocumento=   Esto es los tipos de 
     *          
     * Tambien se puede llamar desde un metodo de la siguiente manera:
     * modules_loader("clave");       <-- Esta funcion importa el modulo
     * getClave($tipoDocumento="",$tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "")  <------------ esta funcion retorna la clave
     **/

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

function getClave($tipoDocumento="",$tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "") {
    
    if ($tipoCedula == '' or $tipoDocumento="" or $cedula = '' or $situacion = '' or $codigoPais = '' or $consecutivo = '' or $codigoSeguridad = '') {
        //-----------------------------------------------//
        $tipoDocumento= params_get('tipoDocumento');
        $tipoCedula = params_get('tipoCedula');
        $cedula = params_get('cedula');
        $situacion = params_get('situacion');
        $codigoPais = params_get('codigoPais'); // 3 digitos Codigo pais 506
        $consecutivo = params_get('consecutivo'); //9 caracteres
        $codigoSeguridad = params_get('codigoSeguridad');  //8 digitos codigo de seguridad   
    }
    $dia = date('d');
    $mes = date('m');
    $ano = date('y');
    $sucursal="001";
    $terminal="00001";                  
    switch ($tipoDocumento) {
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
            $tipoDocumento = "04" ;
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
    $consecutivoFinal=$sucursal.$terminal.$tipoDocumento.$consecutivo;
    //-----------------------------------------------//
    $value;
    switch ($tipoCedula) {
        case 'fisico': //fisico
            $value = "000" . $cedula;
            break;
        case 'juridico': // juridico
            $value = "00" . $cedula;
            break;
    }
    //Numero de Cedula + el indice identificador
    $identificacion = $value;

    //-----------------------------------------------//
    //1	Normal	Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
    //2	Contingencia	Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
    //3	Sin internet	Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
    switch ($situacion) {
        case 'normal': //fisico
            $situacion = 1;
            break;
        case 'contingencia': // Situacion de contingencia
            $situacion = 2;
            break;
        case 'sininternet': //Situacion sin internet
            $situacion = 3;
            break;
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
