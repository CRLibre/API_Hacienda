<?php
/*
 * Copyright (C) 2017-2019 CRLibre <https://crlibre.org>
 *
 * 
 * 
 * 
 * Modified by: JeanCarlos Chavarria Hughes - May 2019
 * jchavarria@imagineing.com
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

/* * ************************************************** */
/* Constantes de validacion                             */
/* * ************************************************** */
$codigoActividadSize = 6;
$emisorNombreMaxSize = 100;
$receptorNombreMaxSize = 100;
$receptorOtrasSenasMaxSize = 250;

/* * ************************************************** */
/* Funcion para generar XML                          */
/* * ************************************************** */

function genXMLFe()
{
    global $codigoActividadSize;
    global $emisorNombreMaxSize;
    global $receptorNombreMaxSize;
    global $receptorOtrasSenasMaxSize;

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_indetif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $nombreComercial                = params_get("nombre_comercial");
    $emisorProv                     = params_get("emisor_provincia");
    $emisorCanton                   = params_get("emisor_canton");
    $emisorDistrito                 = params_get("emisor_distrito");
    $emisorBarrio                   = params_get("emisor_barrio");
    $emisorOtrasSenas               = params_get("emisor_otras_senas");
    $emisorCodPaisTel               = params_get("emisor_cod_pais_tel");
    $emisorTel                      = params_get("emisor_tel");
    $emisorCodPaisFax               = params_get("emisor_cod_pais_fax");
    $emisorFax                      = params_get("emisor_fax");
    $emisorEmail                    = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor                = params_get("omitir_receptor");        // Deprecated
    $receptorNombre                 = params_get("receptor_nombre");
    $receptorTipoIdentif            = params_get("receptor_tipo_identif");
    $receptorNumIdentif             = params_get("receptor_num_identif");
    $receptorProvincia              = params_get("receptor_provincia");
    $receptorCanton                 = params_get("receptor_canton");
    $receptorDistrito               = params_get("receptor_distrito");
    $receptorBarrio                 = params_get("receptor_barrio");
    $receptorOtrasSenas             = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero   = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel             = params_get("receptor_cod_pais_tel");
    $receptorTel                    = params_get("receptor_tel");
    $receptorCodPaisFax             = params_get("receptor_cod_pais_fax");
    $receptorFax                    = params_get("receptor_fax");
    $receptorEmail                  = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta                      = params_get("condicion_venta");
    $plazoCredito                   = params_get("plazo_credito");
    $medioPago                      = params_get("medio_pago");
    $codMoneda                      = params_get("cod_moneda");
    $tipoCambio                     = params_get("tipo_cambio");
    $totalServGravados              = params_get("total_serv_gravados");
    $totalServExentos               = params_get("total_serv_exentos");
    $totalServExonerados            = params_get("total_serv_exonerados");
    $totalMercGravadas              = params_get("total_merc_gravada");
    $totalMercExentas               = params_get("total_merc_exenta");
    $totalMercExonerada             = params_get("total_merc_exonerada");
    $totalGravados                  = params_get("total_gravados");
    $totalExento                    = params_get("total_exento");
    $totalExonerado                 = params_get("total_exonerado");
    $totalVentas                    = params_get("total_ventas");
    $totalDescuentos                = params_get("total_descuentos");
    $totalVentasNeta                = params_get("total_ventas_neta");
    $totalImp                       = params_get("total_impuestos");
    $totalIVADevuelto               = params_get("totalIVADevuelto");
    $totalOtrosCargos               = params_get("totalOtrosCargos");
    $totalComprobante               = params_get("total_comprobante");
    $otros                          = params_get("otros");
    $otrosType                      = params_get("otrosType");
    $infoRefeTipoDoc                = params_get("infoRefeTipoDoc");
    $infoRefeNumero                 = params_get("infoRefeNumero");
    $infoRefeFechaEmision           = params_get("infoRefeFechaEmision");
    $infoRefeCodigo                 = params_get("infoRefeCodigo");
    $infoRefeRazon                  = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles                       = json_decode(params_get("detalles"));
    $otrosCargos                     = json_decode(params_get("otrosCargos"));
    

    grace_debug(params_get("detalles"));
    grace_debug(params_get("otrosCargos"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != $codigoActividadSize)
        error_log("codigoActividadSize is: $codigoActividadSize and codigoActividad is $codigoActividad");

    if (strlen($emisorNombre) > $emisorNombreMaxSize)
        error_log("emisorNombreSize: $emisorNombreMaxSize is greater than emisorNombre: $emisorNombre");

    if (strlen($receptorNombre) > $receptorNombreMaxSize)
        error_log("receptorNombreMaxSize: $receptorNombreMaxSize is greater than receptorNombre: $receptorNombre");

    if (strlen($receptorOtrasSenas) > $receptorOtrasSenasMaxSize)
        error_log("receptorOtrasSenasMaxSize: $receptorOtrasSenasMaxSize is greater than receptorOtrasSenas: $receptorOtrasSenas");

    if (count($otrosCargos) > 15){
        error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
        //Delimita el array a solo 15 elementos
        $otrosCargos = array_slice($otrosCargos, 0, 15);
    }
        


    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Clave>' . $clave . '</Clave>
        <CodigoActividad>' . $codigoActividad . '</CodigoActividad>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . $fechaEmision . '</FechaEmision>
        <Emisor>
            <Nombre>' . $emisorNombre . '</Nombre>
            <Identificacion>
                <Tipo>' . $emisorTipoIdentif . '</Tipo>
                <Numero>' . $emisorNumIdentif . '</Numero>
            </Identificacion>
            <NombreComercial>' . $nombreComercial . '</NombreComercial>';

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '')
    {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '')
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '')
    {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $emisorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($emisorCodPaisFax != '' && $emisorFax != '')
    {
        $xmlString .= '
            <Fax>
                <CodigoPais>' . $emisorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $emisorFax . '</NumTelefono>
            </Fax>';
    }

    $xmlString .= '<CorreoElectronico>' . $emisorEmail . '</CorreoElectronico>
        </Emisor>';

    if ($omitir_receptor != 'true')
    {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif == '05')
        {
            if ($receptorTipoIdentif != '' &&  $receptorNumIdentif != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                        . $receptorNumIdentif
                        . ' </IdentificacionExtranjero>';
            }
            if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300)
            {
                $xmlString .= '<OtrasSenasExtranjero>'
                        . $receptorOtrasSenasExtranjero
                        . ' </OtrasSenasExtranjero>';
            }
        }
        else
        {
            if ($receptorTipoIdentif != '' &&  $receptorNumIdentif != '')
            {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $receptorTipoIdentif . '</Tipo>
                    <Numero>' . $receptorNumIdentif . '</Numero>
                </Identificacion>';
            }

            if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
            {
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $receptorProvincia . '</Provincia>
                        <Canton>' . $receptorCanton . '</Canton>
                        <Distrito>' . $receptorDistrito . '</Distrito>';
                if ($receptorBarrio != '')
                    $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
                $xmlString .= '
                        <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                    </Ubicacion>';
            }
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '<Telefono>
                              <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                              <NumTelefono>' . $receptorTel . '</NumTelefono>
                    </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '<Fax>
                              <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                             <NumTelefono>' . $receptorFax . '</NumTelefono>
                    </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>
        <MedioPago>' . $medioPago . '</MedioPago>
        <DetalleServicio>';

    // cant - unidad medida - detalle - precio unitario - monto total - subtotal - monto total linea - Monto desc -Naturaleza Desc - Impuesto : Codigo / Tarifa / Monto
    /* EJEMPLO DE DETALLES
      {
      "1":["1","Sp","Honorarios","100000","100000","100000","100000","1000","Pronto pago",{"Imp": [{"cod": 122,"tarifa": 1,"monto": 100},{"cod": 133,"tarifa": 1,"monto": 1300}]}],
      "2":["1","Sp","Honorarios","100000","100000","100000","100000"]
      }
     */
    $l = 1;
    foreach ($detalles as $d)
    {
        $xmlString .= '<LineaDetalle>
                  <NumeroLinea>' . $l . '</NumeroLinea>
                  <Cantidad>' . $d->cantidad . '</Cantidad>
                  <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>
                  <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>
                  <Detalle>' . $d->detalle . '</Detalle>
                  <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
                  <Codigo>' . $d->codigo . '</Codigo>
                  <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->codigoComercial) && $d->codigoComercial != "" && $d->codigoComercial != 0)
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '<CodigoComercial>
                    <Codigo>' . $c->codigo . '</Codigo>
                    <Tipo>' . $c->tipo . '</Tipo>
                    </CodigoComercial>';
            }
        
        if (isset($d->descuento) && $d->descuento != "" && $d->descuento != 0)
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                    <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                    <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';
        $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Monto>' . $i->monto . '</Monto>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                
                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';
    //OtrosCargos
    foreach ($otrosCargos as $o)
    {
        $xmlString .= '<OtrosCargos>
        //if ( isset($o->tipoDocumento) && $o->tipoDocumento != "")
        <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
        if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
            $xmlString .= '<NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
        if ( isset($o->nombreTercero) && $o->nombreTercero != "")
            $xmlString .= '<NombreTercero>'.$o->nombreTercero.'</NombreTercero>';   
        //if ( isset($o->detalle) && $o->detalle != "")
        $xmlString .= '<Detalle>'.$o->detalle.'</Detalle>';
        if ( isset($o->porcentaje) && $o->porcentaje != "")
            $xmlString .= '<Porcentaje>'.$o->porcentaje.'</Porcentaje>';
        //if ( isset($o->montoCargo) && $o->montoCargo != "")
        $xmlString .= '<MontoCargo>'.$o->montoCargo.'</MontoCargo>';
        $xmlString .= '</OtrosCargos>';
    }

    $xmlString .= '<ResumenFactura>
    <TotalGravado>' . $totalGravados . '</TotalGravado>
    <TotalExento>' . $totalExento . '</TotalExento>
    <TotalExonerado>' . $totalExonerado . '</TotalExonerado>
    <TotalVenta>' . $totalVentas . '</TotalVenta>
    <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
    <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
    <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
    <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>
    <TotalComprobante>' . $totalComprobante . '</TotalComprobante>';

    if ($totalIVADevuelto != '')
        {
            $xmlString .= '
            <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
        }

    if ($totalServGravados != '')
        {
            $xmlString .= '
            <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
        }
        
    if ($totalServExentos != '')
        {
            $xmlString .= '
            <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
        }

    if ($totalMercGravadas != '')
        {
            $xmlString .= '
            <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
        }
        
    if ($totalMercExentas != '')
        {
            $xmlString .= '
            <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
        }
        
    if ($totalMercExonerada != '')
        {
            $xmlString .= '
            <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
        }

    if ($totalServExonerados != '')
        {
            $xmlString .= '
            <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
        }

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        {
            $xmlString .= '<CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
        }

    $xmlString .= '
        </ResumenFactura>';
    
    if ($infoRefeTipoDoc != '' && $infoRefeNumero != '' && $infoRefeFechaEmision != '' && $infoRefeCodigo != '' && $infoRefeRazon != ''){

        $xmlString .=   '<InformacionReferencia>
                            <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
                            <Numero>' . $infoRefeNumero . '</Numero>
                            <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
                            <Codigo>' . $infoRefeCodigo . '</Codigo>
                            <Razon>' . $infoRefeRazon . '</Razon>
                        </InformacionReferencia>';
    }

    if ($otros != '' && $otrosType != '')
    {
        $tipos = array("Otros", "OtroTexto", "OtroContenido");
        if (in_array($otrosType, $tipos))
        {
            $xmlString .= '
                <Otros>
            <' . $otrosType . '>' . $otros . '</' . $otrosType . '>
            </Otros>';
        }
    }

    if ($otros != '' && $otrosType != '')
    {
        $tipos = array("Otros", "OtroTexto", "OtroContenido");
        if (in_array($otrosType, $tipos))
        {
            $xmlString .= '
                <Otros>
            <' . $otrosType . '>' . $otros . '</' . $otrosType . '>
            </Otros>';
        }
    }

    $xmlString .= '
    </FacturaElectronica>';
    $arrayResp = array(
        "clave" => $clave,
        "xml"   => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLNC()
{
    global $codigoActividadSize;
    global $emisorNombreMaxSize;
    global $receptorNombreMaxSize;
    global $receptorOtrasSenasMaxSize;

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_indetif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $nombreComercial                = params_get("nombre_comercial");
    $emisorProv                     = params_get("emisor_provincia");
    $emisorCanton                   = params_get("emisor_canton");
    $emisorDistrito                 = params_get("emisor_distrito");
    $emisorBarrio                   = params_get("emisor_barrio");
    $emisorOtrasSenas               = params_get("emisor_otras_senas");
    $emisorCodPaisTel               = params_get("emisor_cod_pais_tel");
    $emisorTel                      = params_get("emisor_tel");
    $emisorCodPaisFax               = params_get("emisor_cod_pais_fax");
    $emisorFax                      = params_get("emisor_fax");
    $emisorEmail                    = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor                = params_get("omitir_receptor");        // Deprecated
    $receptorNombre                 = params_get("receptor_nombre");
    $receptorTipoIdentif            = params_get("receptor_tipo_identif");
    $receptorNumIdentif             = params_get("receptor_num_identif");
    $receptorProvincia              = params_get("receptor_provincia");
    $receptorCanton                 = params_get("receptor_canton");
    $receptorDistrito               = params_get("receptor_distrito");
    $receptorBarrio                 = params_get("receptor_barrio");
    $receptorOtrasSenas             = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero   = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel             = params_get("receptor_cod_pais_tel");
    $receptorTel                    = params_get("receptor_tel");
    $receptorCodPaisFax             = params_get("receptor_cod_pais_fax");
    $receptorFax                    = params_get("receptor_fax");
    $receptorEmail                  = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta                      = params_get("condicion_venta");
    $plazoCredito                   = params_get("plazo_credito");
    $medioPago                      = params_get("medio_pago");
    $codMoneda                      = params_get("cod_moneda");
    $tipoCambio                     = params_get("tipo_cambio");
    $totalServGravados              = params_get("total_serv_gravados");
    $totalServExentos               = params_get("total_serv_exentos");
    $totalServExonerados            = params_get("total_serv_exonerados");
    $totalMercGravadas              = params_get("total_merc_gravada");
    $totalMercExentas               = params_get("total_merc_exenta");
    $totalMercExonerada             = params_get("total_merc_exonerada");
    $totalGravados                  = params_get("total_gravados");
    $totalExento                    = params_get("total_exento");
    $totalExonerado                 = params_get("total_exonerado");
    $totalVentas                    = params_get("total_ventas");
    $totalDescuentos                = params_get("total_descuentos");
    $totalVentasNeta                = params_get("total_ventas_neta");
    $totalImp                       = params_get("total_impuestos");
    $totalIVADevuelto               = params_get("totalIVADevuelto");
    $totalOtrosCargos               = params_get("totalOtrosCargos");
    $totalComprobante               = params_get("total_comprobante");
    $otros                          = params_get("otros");
    $otrosType                      = params_get("otrosType");
    $infoRefeTipoDoc                = params_get("infoRefeTipoDoc");
    $infoRefeNumero                 = params_get("infoRefeNumero");
    $infoRefeFechaEmision           = params_get("infoRefeFechaEmision");
    $infoRefeCodigo                 = params_get("infoRefeCodigo");
    $infoRefeRazon                  = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles                       = json_decode(params_get("detalles"));
    $otrosCargos                     = json_decode(params_get("otrosCargos"));
    grace_debug(params_get("otrosCargos"));
    //return $detalles;

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != $codigoActividadSize)
        error_log("codigoActividadSize is: $codigoActividadSize and codigoActividad is $codigoActividad");

    if (strlen($emisorNombre) > $emisorNombreMaxSize)
        error_log("emisorNombreSize: $emisorNombreMaxSize is greater than emisorNombre: $emisorNombre");

    if (strlen($receptorNombre) > $receptorNombreMaxSize)
        error_log("receptorNombreMaxSize: $receptorNombreMaxSize is greater than receptorNombre: $receptorNombre");

    if (strlen($receptorOtrasSenas) > $receptorOtrasSenasMaxSize)
        error_log("receptorOtrasSenasMaxSize: $receptorOtrasSenasMaxSize is greater than receptorOtrasSenas: $receptorOtrasSenas");

    if (count($otrosCargos) > 15){
        error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
        //Delimita el array a solo 15 elementos
        $otrosCargos = array_slice($otrosCargos, 0, 15);
    }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <NotaCreditoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaCreditoElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <CodigoActividad>' . $codigoActividad . '</CodigoActividad>
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>
        <NombreComercial>' . $nombreComercial . '</NombreComercial>';


    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '')
    {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '')
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '')
    {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if ($emisorCodPaisFax != '' && $emisorFax != '')
    {
        $xmlString .= '
        <Fax>
            <CodigoPais>' . $emisorCodPaisFax . '</CodigoPais>
            <NumTelefono>' . $emisorFax . '</NumTelefono>
        </Fax>';
    }


    $xmlString .= '<CorreoElectronico>' . $emisorEmail . '</CorreoElectronico>
    </Emisor>';

    if ($omitir_receptor != 'true')
    {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif == '05')
        {
            if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                        . $receptorNumIdentif
                        . ' </IdentificacionExtranjero>';
            }
            if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300)
            {
                $xmlString .= '<OtrasSenasExtranjero>'
                        . $receptorOtrasSenasExtranjero
                        . ' </OtrasSenasExtranjero>';
            }
        }
        else
        {
            if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
            {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $receptorTipoIdentif . '</Tipo>
                    <Numero>' . $receptorNumIdentif . '</Numero>
                </Identificacion>';
            }

            if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
            {
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $receptorProvincia . '</Provincia>
                        <Canton>' . $receptorCanton . '</Canton>
                        <Distrito>' . $receptorDistrito . '</Distrito>';
                if ($receptorBarrio != '')
                    $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
                $xmlString .= '
                        <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                    </Ubicacion>';
            }
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '<Telefono>
                              <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                              <NumTelefono>' . $receptorTel . '</NumTelefono>
                    </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '<Fax>
                              <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                             <NumTelefono>' . $receptorFax . '</NumTelefono>
                    </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>
    <PlazoCredito>' . $plazoCredito . '</PlazoCredito>
    <MedioPago>' . $medioPago . '</MedioPago>
    <DetalleServicio>';

    /* EJEMPLO DE DETALLES
      {
        "1":["1","Sp","Honorarios","100000","100000","100000","100000","1000","Pronto pago",{"Imp": [{"cod": 122,"tarifa": 1,"monto": 100},{"cod": 133,"tarifa": 1,"monto": 1300}]}],
        "2":["1","Sp","Honorarios","100000","100000","100000","100000"]
      }
     */
    $l = 1;
    foreach ($detalles as $d)
    {
        $xmlString .= '<LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>
            <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <Codigo>' . $d->codigo . '</Codigo>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';
        
        if ( isset($d->partidaArancelaria) && $d->partidaArancelaria != "" )
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';

        if (isset($d->codigoComercial) && $d->codigoComercial != "" && $d->codigoComercial != 0)
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '<CodigoComercial>
                    <Codigo>' . $c->codigo . '</Codigo>
                    <Tipo>' . $c->tipo . '</Tipo>
                    </CodigoComercial>';
            }

        if (isset($d->descuento) && $d->descuento != "" && $d->descuento != 0)
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                    <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                    <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';
        $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Monto>' . $i->monto . '</Monto>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                
                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                if ( isset($i->montoExportacion) && $i->montoExportacion != "")
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';
                
                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    foreach ($otrosCargos as $o)
    {
        $xmlString .= '<OtrosCargos>
        //if ( isset($o->tipoDocumento) && $o->tipoDocumento != "")
        <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
        if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
            $xmlString .= '<NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
        if ( isset($o->nombreTercero) && $o->nombreTercero != "")
            $xmlString .= '<NombreTercero>'.$o->nombreTercero.'</NombreTercero>';   
        //if ( isset($o->detalle) && $o->detalle != "")
        $xmlString .= '<Detalle>'.$o->detalle.'</Detalle>';
        if ( isset($o->porcentaje) && $o->porcentaje != "")
            $xmlString .= '<Porcentaje>'.$o->porcentaje.'</Porcentaje>';
        //if ( isset($o->montoCargo) && $o->montoCargo != "")
        $xmlString .= '<MontoCargo>'.$o->montoCargo.'</MontoCargo>';
        $xmlString .= '</OtrosCargos>';
    }
    
    $xmlString .= '<ResumenFactura>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExento . '</TotalExento>
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>';
    

    if ($totalIVADevuelto != '')
        {
            $xmlString .= '
            <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
        }

    if ($totalServGravados != '')
        {
            $xmlString .= '
            <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
        }
        
    if ($totalServExentos != '')
        {
            $xmlString .= '
            <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
        }

    if ($totalMercGravadas != '')
        {
            $xmlString .= '
            <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
        }
        
    if ($totalMercExentas != '')
        {
            $xmlString .= '
            <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
        }
        
    if ($totalMercExonerada != '')
        {
            $xmlString .= '
            <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
        }

    if ($totalServExonerados != '')
        {
            $xmlString .= '
            <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
        }

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        {
            $xmlString .= '<CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
        }

    $xmlString .= '    
    </ResumenFactura>
    <InformacionReferencia>
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
        <Numero>' . $infoRefeNumero . '</Numero>
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
        <Codigo>' . $infoRefeCodigo . '</Codigo>
        <Razon>' . $infoRefeRazon . '</Razon>
    </InformacionReferencia>';
    if ($otros != '' && $otrosType != '')
    {
        $tipos = array("Otros", "OtroTexto", "OtroContenido");
        if (in_array($otrosType, $tipos))
        {
            $xmlString .= '
                <Otros>
            <' . $otrosType . '>' . $otros . '</' . $otrosType . '>
            </Otros>';
        }
    }
    $xmlString .= '
    </NotaCreditoElectronica>';

    $arrayResp = array(
        "clave" => $clave,
        "xml"   => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLND()
{
    global $codigoActividadSize;
    global $emisorNombreMaxSize;
    global $receptorNombreMaxSize;
    global $receptorOtrasSenasMaxSize;

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_indetif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $nombreComercial                = params_get("nombre_comercial");
    $emisorProv                     = params_get("emisor_provincia");
    $emisorCanton                   = params_get("emisor_canton");
    $emisorDistrito                 = params_get("emisor_distrito");
    $emisorBarrio                   = params_get("emisor_barrio");
    $emisorOtrasSenas               = params_get("emisor_otras_senas");
    $emisorCodPaisTel               = params_get("emisor_cod_pais_tel");
    $emisorTel                      = params_get("emisor_tel");
    $emisorCodPaisFax               = params_get("emisor_cod_pais_fax");
    $emisorFax                      = params_get("emisor_fax");
    $emisorEmail                    = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor                = params_get("omitir_receptor");        // Deprecated
    $receptorNombre                 = params_get("receptor_nombre");
    $receptorTipoIdentif            = params_get("receptor_tipo_identif");
    $receptorNumIdentif             = params_get("receptor_num_identif");
    $receptorProvincia              = params_get("receptor_provincia");
    $receptorCanton                 = params_get("receptor_canton");
    $receptorDistrito               = params_get("receptor_distrito");
    $receptorBarrio                 = params_get("receptor_barrio");
    $receptorOtrasSenas             = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero   = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel             = params_get("receptor_cod_pais_tel");
    $receptorTel                    = params_get("receptor_tel");
    $receptorCodPaisFax             = params_get("receptor_cod_pais_fax");
    $receptorFax                    = params_get("receptor_fax");
    $receptorEmail                  = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta                      = params_get("condicion_venta");
    $plazoCredito                   = params_get("plazo_credito");
    $medioPago                      = params_get("medio_pago");
    $codMoneda                      = params_get("cod_moneda");
    $tipoCambio                     = params_get("tipo_cambio");
    $totalServGravados              = params_get("total_serv_gravados");
    $totalServExentos               = params_get("total_serv_exentos");
    $totalServExonerados            = params_get("total_serv_exonerados");
    $totalMercGravadas              = params_get("total_merc_gravada");
    $totalMercExentas               = params_get("total_merc_exenta");
    $totalMercExonerada             = params_get("total_merc_exonerada");
    $totalGravados                  = params_get("total_gravados");
    $totalExento                    = params_get("total_exento");
    $totalExonerado                 = params_get("total_exonerado");
    $totalVentas                    = params_get("total_ventas");
    $totalDescuentos                = params_get("total_descuentos");
    $totalVentasNeta                = params_get("total_ventas_neta");
    $totalImp                       = params_get("total_impuestos");
    $totalIVADevuelto               = params_get("totalIVADevuelto");
    $totalOtrosCargos               = params_get("totalOtrosCargos");
    $totalComprobante               = params_get("total_comprobante");
    $otros                          = params_get("otros");
    $otrosType                      = params_get("otrosType");
    $infoRefeTipoDoc                = params_get("infoRefeTipoDoc");
    $infoRefeNumero                 = params_get("infoRefeNumero");
    $infoRefeFechaEmision           = params_get("infoRefeFechaEmision");
    $infoRefeCodigo                 = params_get("infoRefeCodigo");
    $infoRefeRazon                  = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles                       = json_decode(params_get("detalles"));
    $otrosCargos                     = json_decode(params_get("otrosCargos"));
    grace_debug(params_get("otrosCargos"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != $codigoActividadSize)
        error_log("codigoActividadSize is: $codigoActividadSize and codigoActividad is $codigoActividad");

    if (strlen($emisorNombre) > $emisorNombreMaxSize)
        error_log("emisorNombreSize: $emisorNombreMaxSize is greater than emisorNombre: $emisorNombre");

    if (strlen($receptorNombre) > $receptorNombreMaxSize)
        error_log("receptorNombreMaxSize: $receptorNombreMaxSize is greater than receptorNombre: $receptorNombre");

    if (strlen($receptorOtrasSenas) > $receptorOtrasSenasMaxSize)
        error_log("receptorOtrasSenasMaxSize: $receptorOtrasSenasMaxSize is greater than receptorOtrasSenas: $receptorOtrasSenas");

    if (count($otrosCargos) > 15){
        error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
        //Delimita el array a solo 15 elementos
        $otrosCargos = array_slice($otrosCargos, 0, 15);
    }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <NotaDebitoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaDebitoElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <CodigoActividad>' . $codigoActividad . '</CodigoActividad>
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>
        <NombreComercial>' . $nombreComercial . '</NombreComercial>';

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '')
    {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '')
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '')
    {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if ($emisorCodPaisFax != '' && $emisorFax != '')
    {
        $xmlString .= '
        <Fax>
            <CodigoPais>' . $emisorCodPaisFax . '</CodigoPais>
            <NumTelefono>' . $emisorFax . '</NumTelefono>
        </Fax>';
    }

    $xmlString .= '<CorreoElectronico>' . $emisorEmail . '</CorreoElectronico>
    </Emisor>';

    if ($omitir_receptor != 'true')
    {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif == '05')
        {
            if ($receptorTipoIdentif != '' &&  $receptorNumIdentif != '')
            {
                $xmlString .= '<IdentificacionExtranjero>'
                        . $receptorNumIdentif
                        . ' </IdentificacionExtranjero>';
            }
            if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300)
            {
                $xmlString .= '<OtrasSenasExtranjero>'
                        . $receptorOtrasSenasExtranjero
                        . ' </OtrasSenasExtranjero>';
            }
        }
        else
        {
            if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
            {
                $xmlString .= '<Identificacion>
                    <Tipo>' . $receptorTipoIdentif . '</Tipo>
                    <Numero>' . $receptorNumIdentif . '</Numero>
                </Identificacion>';
            }

            if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
            {
                $xmlString .= '
                    <Ubicacion>
                        <Provincia>' . $receptorProvincia . '</Provincia>
                        <Canton>' . $receptorCanton . '</Canton>
                        <Distrito>' . $receptorDistrito . '</Distrito>';
                if ($receptorBarrio != '')
                    $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
                $xmlString .= '
                        <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                    </Ubicacion>';
            }
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '<Telefono>
                              <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                              <NumTelefono>' . $receptorTel . '</NumTelefono>
                    </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '<Fax>
                              <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                             <NumTelefono>' . $receptorFax . '</NumTelefono>
                    </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>
    <PlazoCredito>' . $plazoCredito . '</PlazoCredito>
    <MedioPago>' . $medioPago . '</MedioPago>
    <DetalleServicio>';

    /* EJEMPLO DE DETALLES
      {
        "1":["1","Sp","Honorarios","100000","100000","100000","100000","1000","Pronto pago",{"Imp": [{"cod": 122,"tarifa": 1,"monto": 100},{"cod": 133,"tarifa": 1,"monto": 1300}]}],
        "2":["1","Sp","Honorarios","100000","100000","100000","100000"]
      }
     */

    $l = 1;
    foreach ($detalles as $d)
    {
        $xmlString .= '<LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>
            <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <Codigo>' . $d->codigo . '</Codigo>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if ( isset($d->partidaArancelaria) && $d->partidaArancelaria != "" )
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';

        if (isset($d->codigoComercial) && $d->codigoComercial != "" && $d->codigoComercial != 0)
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '<CodigoComercial>
                    <Codigo>' . $c->codigo . '</Codigo>
                    <Tipo>' . $c->tipo . '</Tipo>
                    </CodigoComercial>';
            }

        if (isset($d->descuento) && $d->descuento != "" && $d->descuento != 0)
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                    <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                    <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';
        $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Monto>' . $i->monto . '</Monto>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                
                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                if ( isset($i->montoExportacion) && $i->montoExportacion != "")
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';
                
                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    foreach ($otrosCargos as $o)
    {
        $xmlString .= '<OtrosCargos>
        //if ( isset($o->tipoDocumento) && $o->tipoDocumento != "")
        <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
        if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
            $xmlString .= '<NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
        if ( isset($o->nombreTercero) && $o->nombreTercero != "")
            $xmlString .= '<NombreTercero>'.$o->nombreTercero.'</NombreTercero>';   
        //if ( isset($o->detalle) && $o->detalle != "")
        $xmlString .= '<Detalle>'.$o->detalle.'</Detalle>';
        if ( isset($o->porcentaje) && $o->porcentaje != "")
            $xmlString .= '<Porcentaje>'.$o->porcentaje.'</Porcentaje>';
        //if ( isset($o->montoCargo) && $o->montoCargo != "")
        $xmlString .= '<MontoCargo>'.$o->montoCargo.'</MontoCargo>';
        $xmlString .= '</OtrosCargos>';
    }

    $xmlString .= '<ResumenFactura>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExento . '</TotalExento>
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>';
    

    if ($totalIVADevuelto != '')
        {
            $xmlString .= '
            <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
        }

    if ($totalServGravados != '')
        {
            $xmlString .= '
            <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
        }
        
    if ($totalServExentos != '')
        {
            $xmlString .= '
            <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
        }

    if ($totalMercGravadas != '')
        {
            $xmlString .= '
            <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
        }
        
    if ($totalMercExentas != '')
        {
            $xmlString .= '
            <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
        }

    if ($totalMercExonerada != '')
        {
            $xmlString .= '
            <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
        }

    if ($totalServExonerados != '')
        {
            $xmlString .= '
            <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
        }

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        {
            $xmlString .= '<CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
        }

    $xmlString .= '
    </ResumenFactura>
    <InformacionReferencia>
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
        <Numero>' . $infoRefeNumero . '</Numero>
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
        <Codigo>' . $infoRefeCodigo . '</Codigo>
        <Razon>' . $infoRefeRazon . '</Razon>
    </InformacionReferencia>';
    if ($otros != '' && $otrosType != '')
    {
        $tipos = array("Otros", "OtroTexto", "OtroContenido");
        if (in_array($otrosType, $tipos))
        {
            $xmlString .= '
                <Otros>
            <' . $otrosType . '>' . $otros . '</' . $otrosType . '>
            </Otros>';
        }
    }

    $xmlString .= '
        </NotaDebitoElectronica>';

    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLTE()
{
    global $codigoActividadSize;
    global $emisorNombreMaxSize;
    global $receptorNombreMaxSize;
    global $receptorOtrasSenasMaxSize;

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_indetif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $nombreComercial                = params_get("nombre_comercial");
    $emisorProv                     = params_get("emisor_provincia");
    $emisorCanton                   = params_get("emisor_canton");
    $emisorDistrito                 = params_get("emisor_distrito");
    $emisorBarrio                   = params_get("emisor_barrio");
    $emisorOtrasSenas               = params_get("emisor_otras_senas");
    $emisorCodPaisTel               = params_get("emisor_cod_pais_tel");
    $emisorTel                      = params_get("emisor_tel");
    $emisorCodPaisFax               = params_get("emisor_cod_pais_fax");
    $emisorFax                      = params_get("emisor_fax");
    $emisorEmail                    = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor                = params_get("omitir_receptor");        // Deprecated
    $receptorNombre                 = params_get("receptor_nombre");
    $receptorTipoIdentif            = params_get("receptor_tipo_identif");
    $receptorNumIdentif             = params_get("receptor_num_identif");
    $receptorProvincia              = params_get("receptor_provincia");
    $receptorCanton                 = params_get("receptor_canton");
    $receptorDistrito               = params_get("receptor_distrito");
    $receptorBarrio                 = params_get("receptor_barrio");
    $receptorOtrasSenas             = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero   = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel             = params_get("receptor_cod_pais_tel");
    $receptorTel                    = params_get("receptor_tel");
    $receptorCodPaisFax             = params_get("receptor_cod_pais_fax");
    $receptorFax                    = params_get("receptor_fax");
    $receptorEmail                  = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta                      = params_get("condicion_venta");
    $plazoCredito                   = params_get("plazo_credito");
    $medioPago                      = params_get("medio_pago");
    $codMoneda                      = params_get("cod_moneda");
    $tipoCambio                     = params_get("tipo_cambio");
    $totalServGravados              = params_get("total_serv_gravados");
    $totalServExentos               = params_get("total_serv_exentos");
    $totalServExonerados            = params_get("total_serv_exonerados");
    $totalMercGravadas              = params_get("total_merc_gravada");
    $totalMercExentas               = params_get("total_merc_exenta");
    $totalMercExonerada             = params_get("total_merc_exonerada");
    $totalGravados                  = params_get("total_gravados");
    $totalExento                    = params_get("total_exento");
    $totalExonerado                 = params_get("total_exonerado");
    $totalVentas                    = params_get("total_ventas");
    $totalDescuentos                = params_get("total_descuentos");
    $totalVentasNeta                = params_get("total_ventas_neta");
    $totalImp                       = params_get("total_impuestos");
    $totalIVADevuelto               = params_get("totalIVADevuelto");
    $totalOtrosCargos               = params_get("totalOtrosCargos");
    $totalComprobante               = params_get("total_comprobante");
    $otros                          = params_get("otros");
    $otrosType                      = params_get("otrosType");
    $infoRefeTipoDoc                = params_get("infoRefeTipoDoc");
    $infoRefeNumero                 = params_get("infoRefeNumero");
    $infoRefeFechaEmision           = params_get("infoRefeFechaEmision");
    $infoRefeCodigo                 = params_get("infoRefeCodigo");
    $infoRefeRazon                  = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles                       = json_decode(params_get("detalles"));
    $otrosCargos                     = json_decode(params_get("otrosCargos"));

    grace_debug(params_get("detalles"));
    grace_debug(params_get("otrosCargos"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != $codigoActividadSize)
        error_log("codigoActividadSize is: $codigoActividadSize and codigoActividad is $codigoActividad");

    if (strlen($emisorNombre) > $emisorNombreMaxSize)
        error_log("emisorNombreSize: $emisorNombreMaxSize is greater than emisorNombre: $emisorNombre");

    if (strlen($receptorNombre) > $receptorNombreMaxSize)
        error_log("receptorNombreMaxSize: $receptorNombreMaxSize is greater than receptorNombre: $receptorNombre");

    if (strlen($receptorOtrasSenas) > $receptorOtrasSenasMaxSize)
        error_log("receptorOtrasSenasMaxSize: $receptorOtrasSenasMaxSize is greater than receptorOtrasSenas: $receptorOtrasSenas");

    if (count($otrosCargos) > 15){
        error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
        //Delimita el array a solo 15 elementos
        $otrosCargos = array_slice($otrosCargos, 0, 15);
    }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <TiqueteElectronico
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/tiqueteElectronico"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <CodigoActividad>' . $codigoActividad . '</CodigoActividad>
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>
        <NombreComercial>' . $nombreComercial . '</NombreComercial>';

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '')
    {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '')
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '')
    {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if ($emisorCodPaisFax != '' && $emisorFax != '')
    {
        $xmlString .= '
        <Fax>
            <CodigoPais>' . $emisorCodPaisFax . '</CodigoPais>
            <NumTelefono>' . $emisorFax . '</NumTelefono>
        </Fax>';
    }

    $xmlString .= '<CorreoElectronico>' . $emisorEmail . '</CorreoElectronico>
    </Emisor>';

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>
    <PlazoCredito>' . $plazoCredito . '</PlazoCredito>
    <MedioPago>' . $medioPago . '</MedioPago>
    <DetalleServicio>';

    // cant - unidad medida - detalle - precio unitario - monto total - subtotal - monto total linea - Monto desc -Naturaleza Desc - Impuesto : Codigo / Tarifa / Monto

    /* EJEMPLO DE DETALLES
      {
        "1":["1","Sp","Honorarios","100000","100000","100000","100000","1000","Pronto pago",{"Imp": [{"cod": 122,"tarifa": 1,"monto": 100},{"cod": 133,"tarifa": 1,"monto": 1300}]}],
        "2":["1","Sp","Honorarios","100000","100000","100000","100000"]
      }
     */

    $l = 1;
    foreach ($detalles as $d)
    {
        $xmlString .= '<LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>
            <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <Codigo>' . $d->codigo . '</Codigo>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->codigoComercial) && $d->codigoComercial != "" && $d->codigoComercial != 0)
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '<CodigoComercial>
                    <Codigo>' . $c->codigo . '</Codigo>
                    <Tipo>' . $c->tipo . '</Tipo>
                    </CodigoComercial>';
            }

        if (isset($d->descuento) && $d->descuento != "" && $d->descuento != 0)
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                    <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                    <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';
        $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Monto>' . $i->monto . '</Monto>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                
                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                
                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    foreach ($otrosCargos as $o)
    {
        $xmlString .= '<OtrosCargos>
        //if ( isset($o->tipoDocumento) && $o->tipoDocumento != "")
        <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
        if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
            $xmlString .= '<NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
        if ( isset($o->nombreTercero) && $o->nombreTercero != "")
            $xmlString .= '<NombreTercero>'.$o->nombreTercero.'</NombreTercero>';   
        //if ( isset($o->detalle) && $o->detalle != "")
        $xmlString .= '<Detalle>'.$o->detalle.'</Detalle>';
        if ( isset($o->porcentaje) && $o->porcentaje != "")
            $xmlString .= '<Porcentaje>'.$o->porcentaje.'</Porcentaje>';
        //if ( isset($o->montoCargo) && $o->montoCargo != "")
        $xmlString .= '<MontoCargo>'.$o->montoCargo.'</MontoCargo>';
        $xmlString .= '</OtrosCargos>';
    }

    $xmlString .= '<ResumenFactura>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExento . '</TotalExento>
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>';
    

    if ($totalIVADevuelto != '')
        {
            $xmlString .= '
            <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
        }

    if ($totalServGravados != '')
        {
            $xmlString .= '
            <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
        }
        
    if ($totalServExentos != '')
        {
            $xmlString .= '
            <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
        }

    if ($totalMercGravadas != '')
        {
            $xmlString .= '
            <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
        }
        
    if ($totalMercExentas != '')
        {
            $xmlString .= '
            <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
        }

    if ($totalMercExonerada != '')
        {
            $xmlString .= '
            <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
        }

    if ($totalServExonerados != '')
        {
            $xmlString .= '
            <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
        }
    
    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        {
            $xmlString .= '<CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
        }

    $xmlString .= '
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeNumero != '' && $infoRefeFechaEmision != '' && $infoRefeCodigo != '' && $infoRefeRazon != ''){

        $xmlString .=   '<InformacionReferencia>
                            <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
                            <Numero>' . $infoRefeNumero . '</Numero>
                            <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
                            <Codigo>' . $infoRefeCodigo . '</Codigo>
                            <Razon>' . $infoRefeRazon . '</Razon>
                        </InformacionReferencia>';
    }

    if ($otros != '' && $otrosType != '')
    {
        $tipos = array("Otros", "OtroTexto", "OtroContenido");
        if (in_array($otrosType, $tipos))
        {
            $xmlString .= '
                <Otros>
            <' . $otrosType . '>' . $otros . '</' . $otrosType . '>
            </Otros>';
        }
    }

    $xmlString .= '
    </TiqueteElectronico>';
    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLMr()
{
    global $codigoActividadSize;
    global $emisorNombreMaxSize;
    global $receptorNombreMaxSize;
    global $receptorOtrasSenasMaxSize;

    $clave                          = params_get("clave");                                      // d{50,50}
    // Datos vendedor = emisor
    $numeroCedulaEmisor             = params_get("numero_cedula_emisor");                       // d{12,12} cedula fisica,juridica,NITE,DIMEX
    $numeroCedulaEmisor             = str_pad($numeroCedulaEmisor, 12, "0", STR_PAD_LEFT);

    // Datos mensaje receptor
    $fechaEmisionDoc                = params_get("fecha_emision_doc");                          // fecha de emision de la confirmacion
    $mensaje                        = params_get("mensaje");                                    // 1 - Aceptado, 2 - Aceptado Parcialmente, 3 - Rechazado
    $detalleMensaje                 = params_get("detalle_mensaje");
    $montoTotalImpuesto             = params_get("monto_total_impuesto");                       // d18,5 opcional /obligatorio si comprobante tenga impuesto
    $codigoActividad                = params_get("codigo_actividad");                            // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $totalFactura                   = params_get("total_factura");                              // d18,5
    $numeroConsecutivoReceptor      = params_get("numero_consecutivo_receptor");                // d{20,20} numeracion consecutiva de los mensajes de confirmacion

    // Datos comprador = receptor
    $numeroCedulaReceptor           = params_get("numero_cedula_receptor");                     // d{12,12}cedula fisica, juridica, NITE, DIMEX del comprador
    $numeroCedulaReceptor           = str_pad($numeroCedulaReceptor, 12, "0", STR_PAD_LEFT);

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != $codigoActividadSize)
        error_log("codigoActividadSize: $codigoActividadSize is not codigoActividad: $codigoActividad");

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <MensajeReceptor
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/mensajeReceptor"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <NumeroCedulaEmisor>' . $numeroCedulaEmisor . '</NumeroCedulaEmisor>
    <FechaEmisionDoc>' . $fechaEmisionDoc . '</FechaEmisionDoc>
    <Mensaje>' . $mensaje . '</Mensaje>';
    if (!empty($detalleMensaje))
        $xmlString .= '<DetalleMensaje>' . $detalleMensaje . '</DetalleMensaje>';

    if (!empty($montoTotalImpuesto))
        $xmlString .= '<MontoTotalImpuesto>' . $montoTotalImpuesto . '</MontoTotalImpuesto>';
    $xmlString .=     '<CodigoActividad>' . $codigoActividad . '</CodigoActividad>
    <TotalFactura>' . $totalFactura . '</TotalFactura>
    <NumeroCedulaReceptor>' . $numeroCedulaReceptor . '</NumeroCedulaReceptor>
    <NumeroConsecutivoReceptor>' . $numeroConsecutivoReceptor . '</NumeroConsecutivoReceptor>';

    $xmlString .= '</MensajeReceptor>';
    $arrayResp = array(
        "clave" => $clave,
        "xml"   => base64_encode($xmlString)
    );

    return $arrayResp;
}

/* * ************************************************** */
/* Funcion de prueba                                 */
/* * ************************************************** */

function test()
{
    return "Esto es un test";
}

?>
