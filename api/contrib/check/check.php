<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
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

function check_XML()
{
    function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level)
        {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
            default:
                break;
        }

        $return .= trim($error->message);

        if ($error->file) {
            $return .= " in <b>$error->file</b>";
        }

        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    function libxml_display_errors()
    {
        $errors = libxml_get_errors();

        foreach ($errors as $error)
        {
            print libxml_display_error($error);
        }

        libxml_clear_errors();
    }

    $tipoDoc = params_get('tipoDocumento');
    $tipos = array("FE", "ND", "NC", "TE", "CCE", "CPCE", "RCE");

    grace_debug($tipoDoc);
    if (in_array($tipoDoc, $tipos))
    {
        switch ($tipoDoc)
        {
            case 'FE': //Factura Electronica
            {
                // Enable user error handling 
                libxml_use_internal_errors(true);

                $xml = new DOMDocument();
                $xml->load('fac.xml');

                if (!$xml->schemaValidate('xsd/FacturaElectronica_V.4.2.xsd'))
                    libxml_display_errors();
                else
                    echo "validated";

                break;
            }
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
                break;
        }
    }
    else
        return "No se encuentra tipo de documento";
}

?>
