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

function signFE()
{
    require 'Firmadohaciendacr.php';
    modules_loader("files");
    $p12Url     = filesGetUrl(params_get('p12Url'));
    $pinP12     = params_get('pinP12');
    $inXml      = params_get('inXml');
    $tipoDoc    = params_get('tipodoc');
    $tipos      = array("FE", "ND", "NC", "TE","CCE","CPCE","RCE");

    if (in_array($tipoDoc, $tipos))
    {
        switch ($tipoDoc)
        {
            case 'FE': // Factura Electronica
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
            default:
                $tipoDocumento = null;
                break;
        }
    }
    else
        return "No se encuentra tipo de documento";

    if ($tipoDocumento == null)
        return "El tipo de documento es nulo";

    $fac = new Firmadocr();
    //$inXmlUrl debe de ser en Base64 
    //$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo fileUploader -> subir_certif
    //Tipo es el tipo de documento 
    // 01 FE
    //02 ND
    //03 NC
    //04 TE
    //05 06 07 Mensaje Receptor
    $returnFile = $fac->firmar($p12Url, $pinP12, $inXml, $tipoDocumento);
    $arrayResp  = array(
        "xmlFirmado" => $returnFile
    );

    return $arrayResp;
}

?>
