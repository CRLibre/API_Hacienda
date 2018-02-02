<?php

function getClave(){
        //-----------------------------------------------//
           $tipoCedula= params_get('tipoCedula');
           $cedula=params_get('cedula');
           $situacion=params_get('situacion');
        
           $codigoPais = params_get('codigoPais'); // 3 digitos Codigo pais 506
           $dia = date('d');
           $mes = date('m');
           $ano = date('y');      
           $consecutivo = params_get('consecutivo'); //20 caracteres
           $codigoSeguridad = params_get('codigoSeguridad');  //8 digitos codigo de seguridad
        
        //-----------------------------------------------//
        $value;
        switch ($tipoCedula) {
        case '01': //fisico
        $value = "000".$cedula;
        break;
        case '02': // juridico
        $value = "00".$cedula;
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
            $situacion=1;
            break;
            case 'contingencia': // juridico
            $situacion=2;
            break;
            case 'sin internet': //dimex
            $situacion=3;
            break;
       }
       
       //-----------------------------------------------//     
       //Crea la clave 
      $clave = $codigoPais.$dia.$mes.$ano.$identificacion.$consecutivo.$situacion.$codigoSeguridad;
      return $clave;
}
?>
