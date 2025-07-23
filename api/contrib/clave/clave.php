<?php
/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Funcion para generar clave
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
 *
 * - Tipo de comprobante o documento asociado Código
 *      Factura electrónica 01
 *      Nota de débito electrónica 02
 *      Nota de crédito electrónica 03
 *      Tiquete Electrónico 04
 *      Confirmación de aceptación del comprobante electrónico 05
 *      Confirmación de aceptación parcial del comprobante electrónico 06
 *      Confirmación de rechazo del comprobante electrónico 07
 */
function getClave($tipoDocumento = "", $tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "")
{
    $tipoDocumento      = params_get('tipoDocumento');
    $tipoCedula         = params_get('tipoCedula');
    $cedula             = params_get('cedula');
    $situacion          = params_get('situacion');
    $codigoPais         = params_get('codigoPais', "506");         // 3 digitos Codigo pais 506
    $consecutivo        = params_get('consecutivo');        // 9 caracteres
    $codigoSeguridad    = params_get('codigoSeguridad');    // 8 digitos codigo de seguridad
    $sucursal           = params_get("sucursal", "001");
    $terminal           = params_get("terminal", "00001");

    $dia = date('d');
    $mes = date('m');
    $ano = date('y');

    // Validamos el parametro de cedula
    if (!ctype_digit($cedula))
        return "El parametro cedula no es numeral";

    if (!ctype_digit($codigoPais)) {
        return "El parametro codigoPais no es numeral";
    } else if (strlen($codigoPais) != 3) {
        return "El parametro codigoPais debe ser de 3 digitos";
    }

    if (!ctype_digit($sucursal)) {
        return "El parametro sucursal no es numeral";
    } else if (strlen($sucursal) < 3) {
        $sucursal = str_pad($sucursal, 3, "0", STR_PAD_LEFT);
    } else if (strlen($sucursal) > 3) {
        return "El parametro sucursal debe ser de 3 digitos";
    }

    if (!ctype_digit($terminal)) {
        return "El parametro terminal no es numeral";
    } else if (strlen($terminal) < 5) {
        $terminal = str_pad($terminal, 5, "0", STR_PAD_LEFT);
    } else if (strlen($terminal) > 5) {
        return "El parametro terminal debe ser de 5 digitos";
    }

    if (!ctype_digit($consecutivo)) {
        return "El parametro consecutivo no es numeral";
    } else if (strlen($consecutivo) < 10) {
        $consecutivo = str_pad($consecutivo, 10, "0", STR_PAD_LEFT);
    } else if (strlen($consecutivo) > 10) {
        return "El parametro consecutivo debe ser de 10 digitos";
    }

    if (!ctype_digit($codigoSeguridad)) {
        return "El parametro codigoSeguridad no es numeral";
    } else if (strlen($codigoSeguridad) < 8) {
        $codigoSeguridad = str_pad($codigoSeguridad, 8, "0", STR_PAD_LEFT);
    } else if (strlen($codigoSeguridad) > 8) {
        return "El parametro codigoSeguridad debe ser de 8 digitos";
    }

    $tipoDoc = params_get('tipoDocumento');
    $tipos = array(
        'FE'   => '01', // Factura Electronica
        'ND'   => '02', // Nota de Debito
        'NC'   => '03', // Nota de Credito
        'TE'   => '04', // Tiquete Electronico
        'CCE'  => '05', // Confirmacion Comprobante Electronico
        'CPCE' => '06', // Confirmacion Parcial Comprobante Electronico
        'RCE'  => '07', // Rechazo Comprobante Electronico
        'FEC'  => '08', // Factura Electronica de Compra
        'FEE'  => '09'  // Factura Electronica de Exportación
    );

    $tipoDocumento = $tipos[$tipoDoc] ?? null;

    grace_debug($tipoDoc);
    if ($tipoDocumento === null) {
        return "No se encuentra el tipo de documento [$tipoDoc]";
    }

    $consecutivoFinal = $sucursal . $terminal . $tipoDocumento . $consecutivo;

    //-----------------------------------------------//
    // Numero de Cedula + el indice identificador

    $identificacion = null;
    $cedulas = array("fisico", "juridico", "dimex", "nite", "01", "02", "03", "04");

    if (in_array($tipoCedula, $cedulas)) {
        switch ($tipoCedula) {
            case 'fisico': // fisico se agregan 3 ceros para completar los 12 caracteres
            case '01':
                $identificacion = str_pad($cedula, 12, "0", STR_PAD_LEFT);
                break;
            case 'juridico': // juridico se agregan 2 ceros para completar los 12 caracteres
            case '02': {
                    // En caso de ser menor a 12 caracteres
                    if (strlen($cedula) < 12)
                        $identificacion = str_pad($cedula, 12, "0", STR_PAD_LEFT);
                    else if (strlen($cedula) === 12)
                        $identificacion = $cedula;
                    else
                        return "cedula juridico incorrecto";
                    break;
                }
            case 'dimex': // dimex puede ser de 11 0 12 caracteres
            case '03': // dimex puede ser de 11 0 12 caracteres
                {
                    // En caso de ser menor a 12 caracteres
                    if (strlen($cedula) < 12)
                        $identificacion = str_pad($cedula, 12, "0", STR_PAD_LEFT);
                    else if (strlen($cedula) == 12)
                        $identificacion = $cedula;
                    else
                        return "dimex incorrecto";
                    break;
                }
            case 'nite': // nite se agregan 2 ceros para completar los 12 caracteres
            case '04':
                $identificacion = str_pad($cedula, 12, "0", STR_PAD_LEFT);
                break;
            default:
                break;
        }
    } else {
        return "No se encuentra tipo de cedula";
    }

    /*
    * Situacion del comprobante
    * 1 normal          Comprobantes electrónicos que son generados y transmitidos en el mismo acto de compra-venta y prestación del servicio al sistema de validación de comprobantes electrónicos de la Dirección General de Tributación de Costa Rica.
    * 2	contingencia    Comprobantes electrónicos que sustituyen al comprobante físico emitido por contingencia.
    * 3 sininternet     Comprobantes que han sido generados y expresados en formato electrónico, pero no se cuenta con el respectivo acceso a internet para el envío inmediato de los mismos a la Dirección General de Tributación de Costa Rica.
    */
    $situaciones = array(
        "normal"        => 1,
        "contingencia"  => 2,
        "sininternet"   => 3
    );

    $codSituacion = $situaciones[strtolower($situacion)] ?? null;

    if ($codSituacion === null) {
        return "No se encuentra el tipo de situacion [$situacion]";
    }

    // Crea la clave 
    $clave = $codigoPais . $dia . $mes . $ano . $identificacion . $consecutivoFinal . $codSituacion . $codigoSeguridad;
    $arrayResp = array(
        "clave" => "$clave",
        "consecutivo" => "$consecutivoFinal",
        "length" => strlen($clave)
    );
    return $arrayResp;
}
