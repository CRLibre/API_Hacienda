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

/* * ************************************************** */
/* Constantes de validacion                             */
/* * ************************************************** */
const TIPODOCREFVALUES = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '99');
const CODIDOREFVALUES = array('01','02','04','05','99');
const CODIGOACTIVIDADSIZE = 6;
const EMISORNOMBREMAXSIZE = 100;
const RECEPTORNOMBREMAXSIZE = 100;
const RECEPTOROTRASSENASEXTRANJEROMAXSIZE = 250;


/* * ************************************************** */
/* Funcion para generar XML                          */
/* * ************************************************** */

function genXMLFe()
{
    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
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
    $mediosPago                     = json_decode(params_get("medios_pago"));

    grace_debug(params_get("detalles"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($mediosPago) && $mediosPago != "")
        grace_debug(params_get("medios_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenas);

    if ( isset($otrosCargos) && $otrosCargos != "")
        if (count($otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }

    if ( isset($mediosPago) && $mediosPago != "")
        if (count($mediosPago) > 4){
            error_log("mediosPago: ".count($mediosPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica"
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
            </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

    $xmlString .= '<Receptor>
        <Nombre>' . $receptorNombre . '</Nombre>';

    /*if ($receptorTipoIdentif == '05')
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
    {*/
        /*if ($receptorTipoIdentif != '' &&  $receptorNumIdentif != '')
        {*/
        $xmlString .= '
        <Identificacion>
            <Tipo>' . $receptorTipoIdentif . '</Tipo>
            <Numero>' . $receptorNumIdentif . '</Numero>
        </Identificacion>';

        if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
        {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero.
            '</IdentificacionExtranjero>';
        }

        if ( isset($receptorNombreComercial) && $receptorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
        //}

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

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
            $xmlString .= '
            <OtrasSenasExtranjero>'
                .$receptorOtrasSenasExtranjero.
            '</OtrasSenasExtranjero>';
        }
    /*}*/

    if ($receptorCodPaisTel != '' && $receptorTel != '')
    {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorCodPaisFax != '' && $receptorFax != '')
    {
        $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
    }

    if ($receptorEmail != '')
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

    $xmlString .= '</Receptor>';

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    if ( isset($medioPago) && $medioPago != "" && $medioPago != 0 )
    $xmlString .= '
        <MedioPago>' . $medioPago . '</MedioPago>';
    else
        //mediosPago 4 nodos nada más
        if ( isset($mediosPago) && $mediosPago != ""){
            foreach ($mediosPago as $o)
            {
                $xmlString .= '<MedioPago>' . $o->codigo . '</MedioPago>';
            }
        }

    $xmlString .= '
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
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5){
                error_log("codigoComercial: ".count($d->codigoComercial)." is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0){
            foreach ($d->codigoComercialLinea as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($c->codigo) && $c->codigo != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5){
                error_log("descuento: ".count($d->descuento)." is greater than 5");
            }
            $d->descuento= array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0){
            foreach ($d->descuentoLinea as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->baseImponible) && $d->baseImponible != "")
        {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';
    //OtrosCargos
    if ( isset($otrosCargos) && $otrosCargos != ""){
        foreach ($otrosCargos as $o)
        {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
                $xmlString .= '
                <NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
            if ( isset($o->nombreTercero) && $o->nombreTercero != "")
                $xmlString .= '
                <NombreTercero>'.$o->nombreTercero.'</NombreTercero>';
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalServExonerados != '')
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalMercExonerada != '')
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    if ($totalExonerado != '')
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ($totalIVADevuelto != '')
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != ''){

        $xmlString .=   '
    <InformacionReferencia>';

        if(in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true))
        $xmlString .='
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>';
        else{
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = ". $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ( isset($infoRefeNumero) && $infoRefeNumero != "")
            $xmlString .=   '
        <Numero>' . $infoRefeNumero . '</Numero>';

        $xmlString .=   '
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>';

        if ( isset($infoRefeCodigo) && $infoRefeCodigo != ""){
            if(in_array($infoRefeCodigo, CODIDOREFVALUES, true)){
                $xmlString .=   '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            }else{
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = ". $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ( isset($infoRefeRazon) && $infoRefeRazon != "")
            $xmlString .=   '
        <Razon>' . $infoRefeRazon . '</Razon>';

        $xmlString .=   '
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
    </FacturaElectronica>';
    $arrayResp = array(
        "clave" => $clave,
        "xml"   => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLNC()
{

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
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
    $mediosPago                     = json_decode(params_get("medios_pago"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($mediosPago) && $mediosPago != "")
        grace_debug(params_get("medios_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenas);

    if ( isset($otrosCargos) && $otrosCargos != "")
        if (count($otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }

    if ( isset($mediosPago) && $mediosPago != "")
        if (count($mediosPago) > 4){
            error_log("otrosCargos: ".count($mediosPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <NotaCreditoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaCreditoElectronica"
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
        </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

        /*if ($receptorTipoIdentif == '05')
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
        {*/
        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
        {
            $xmlString .= '
            <Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
        {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero.
            '</IdentificacionExtranjero>';
        }

        if ( isset($receptorNombreComercial) && $receptorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';

        if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
        {
            $xmlString .= '
                <Ubicacion>
                    <Provincia>' . $receptorProvincia . '</Provincia>
                    <Canton>' . $receptorCanton . '</Canton>
                    <Distrito>' . $receptorDistrito . '</Distrito>';
            if ($receptorBarrio != '')
                $xmlString .= '
                    <Barrio>' . $receptorBarrio . '</Barrio>';
            $xmlString .= '
                    <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                </Ubicacion>';
        }

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
            $xmlString .= '
            <OtrasSenasExtranjero>'
                .$receptorOtrasSenasExtranjero.
            '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
    <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    if ( isset($medioPago) && $medioPago != "" && $medioPago != 0 )
    $xmlString .= '
        <MedioPago>' . $medioPago . '</MedioPago>';
    else
        //mediosPago 4 nodos nada más
        if ( isset($mediosPago) && $mediosPago != ""){
            foreach ($mediosPago as $o)
            {
                $xmlString .= '<MedioPago>' . $o->codigo . '</MedioPago>';
            }
        }

    $xmlString .= '
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
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if ( isset($d->partidaArancelaria) && $d->partidaArancelaria != "" )
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5){
                error_log("codigoComercial: ".count($d->codigoComercial)." is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0){
            foreach ($d->codigoComercialLinea as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($c->codigo) && $c->codigo != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5){
                error_log("descuento: ".count($d->descuento)." is greater than 5");
            }
            $d->descuento= array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0){
            foreach ($d->descuentoLinea as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';
        if (isset($d->baseImponible) && $d->baseImponible != "")
        {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

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
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if ( isset($otrosCargos) && $otrosCargos != ""){
        foreach ($otrosCargos as $o)
        {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
                $xmlString .= '
                <NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
            if ( isset($o->nombreTercero) && $o->nombreTercero != "")
                $xmlString .= '
                <NombreTercero>'.$o->nombreTercero.'</NombreTercero>';
            //if ( isset($o->detalle) && $o->detalle != "")
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            //if ( isset($o->montoCargo) && $o->montoCargo != "")
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalServExonerados != '')
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalMercExonerada != '')
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    if ($totalExonerado != '')
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ($totalIVADevuelto != '')
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    $xmlString .=   '
    <InformacionReferencia>';
    if(in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true))
        $xmlString .='
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>';
    else{
        grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = ". $infoRefeTipoDoc);
        return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
    }

    if ( isset($infoRefeNumero) && $infoRefeNumero != "")
        $xmlString .=   '
        <Numero>' . $infoRefeNumero . '</Numero>';

    $xmlString .=   '
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>';

    if ( isset($infoRefeCodigo) && $infoRefeCodigo != ""){
        if(in_array($infoRefeCodigo, CODIDOREFVALUES, true)){
            $xmlString .=   '
        <Codigo>' . $infoRefeCodigo . '</Codigo>';
        }else{
            grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = ". $infoRefeCodigo);
            return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
        }
    }

    if ( isset($infoRefeRazon) && $infoRefeRazon != "")
        $xmlString .=   '
        <Razon>' . $infoRefeRazon . '</Razon>';

    $xmlString .=   '
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

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
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
    $mediosPago                     = json_decode(params_get("medios_pago"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($mediosPago) && $mediosPago != "")
        grace_debug(params_get("medios_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenas);

    if ( isset($otrosCargos) && $otrosCargos != "")
        if (count($otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }

    if ( isset($mediosPago) && $mediosPago != "")
        if (count($mediosPago) > 4){
            error_log("medios_pago: ".count($mediosPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <NotaDebitoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaDebitoElectronica"
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
        </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

        /*if ($receptorTipoIdentif == '05')
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
        {*/
        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
        {
            $xmlString .= '<Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
        {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero.
            '</IdentificacionExtranjero>';
        }

        if ( isset($receptorNombreComercial) && $receptorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';

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

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
            $xmlString .= '
            <OtrasSenasExtranjero>'
                .$receptorOtrasSenasExtranjero.
            '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    if ( isset($medioPago) && $medioPago != "" && $medioPago != 0 )
    $xmlString .= '
        <MedioPago>' . $medioPago . '</MedioPago>';
    else
        //mediosPago 4 nodos nada más
        if ( isset($mediosPago) && $mediosPago != ""){
            foreach ($mediosPago as $o)
            {
                $xmlString .= '<MedioPago>' . $o->codigo . '</MedioPago>';
            }
        }

    $xmlString .= '
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
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';
        if ( isset($d->partidaArancelaria) && $d->partidaArancelaria != "" )
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5){
                error_log("codigoComercial: ".count($d->codigoComercial)." is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0){
            foreach ($d->codigoComercialLinea as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($c->codigo) && $c->codigo != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5){
                error_log("descuento: ".count($d->descuento)." is greater than 5");
            }
            $d->descuento= array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0){
            foreach ($d->descuentoLinea as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';
        if (isset($d->baseImponible) && $d->baseImponible != "")
        {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if ( isset($otrosCargos) && $otrosCargos != ""){
        foreach ($otrosCargos as $o){
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
                $xmlString .= '
                <NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
            if ( isset($o->nombreTercero) && $o->nombreTercero != "")
                $xmlString .= '
                <NombreTercero>'.$o->nombreTercero.'</NombreTercero>';
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalServExonerados != '')
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalMercExonerada != '')
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    if ($totalExonerado != '')
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ($totalIVADevuelto != '')
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    $xmlString .=   '
    <InformacionReferencia>';

    if(in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true))
        $xmlString .='
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>';
    else{
        grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = ". $infoRefeTipoDoc);
        return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
    }

    if ( isset($infoRefeNumero) && $infoRefeNumero != "")
        $xmlString .=   '
        <Numero>' . $infoRefeNumero . '</Numero>';

    $xmlString .=   '
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>';

    if ( isset($infoRefeCodigo) && $infoRefeCodigo != ""){
        if(in_array($infoRefeCodigo, CODIDOREFVALUES, true)){
            $xmlString .=   '
        <Codigo>' . $infoRefeCodigo . '</Codigo>';
        }else{
            grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = ". $infoRefeCodigo);
            return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
        }
    }

    if ( isset($infoRefeRazon) && $infoRefeRazon != "")
        $xmlString .=   '
        <Razon>' . $infoRefeRazon . '</Razon>';

    $xmlString .=   '
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

    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
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
    $mediosPago                     = json_decode(params_get("medios_pago"));

    grace_debug(params_get("detalles"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($mediosPago) && $mediosPago != "")
        grace_debug(params_get("medios_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenas);

    if ( isset($otrosCargos) && $otrosCargos != "")
        if (count($otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }

    if ( isset($mediosPago) && $mediosPago != "")
        if (count($mediosPago) > 4){
            error_log("medios_pago: ".count($mediosPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <TiqueteElectronico
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico"
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
        </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '')
        {
            $xmlString .= '
            <Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
        {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero. 
            '</IdentificacionExtranjero>';
        }

        if ( isset($receptorNombreComercial) && $receptorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';

        if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
        {
            $xmlString .= '
                <Ubicacion>
                    <Provincia>' . $receptorProvincia . '</Provincia>
                    <Canton>' . $receptorCanton . '</Canton>
                    <Distrito>' . $receptorDistrito . '</Distrito>';
            if ($receptorBarrio != '')
                $xmlString .= '
                    <Barrio>' . $receptorBarrio . '</Barrio>';
            $xmlString .= '
                    <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                </Ubicacion>';
        }

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
            $xmlString .= '
            <OtrasSenasExtranjero>'
                .$receptorOtrasSenasExtranjero.
            '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '')
        {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorCodPaisFax != '' && $receptorFax != '')
        {
            $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
        }

        if ($receptorEmail != '')
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    if ( isset($medioPago) && $medioPago != "" && $medioPago != 0 )
    $xmlString .= '
        <MedioPago>' . $medioPago . '</MedioPago>';
    else
        //mediosPago 4 nodos nada más
        if ( isset($mediosPago) && $mediosPago != ""){
            foreach ($mediosPago as $o)
            {
                $xmlString .= '<MedioPago>' . $o->codigo . '</MedioPago>';
            }
        }

    $xmlString .='<DetalleServicio>';

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
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5){
                error_log("codigoComercial: ".count($d->codigoComercial)." is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0){
            foreach ($d->codigoComercialLinea as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($c->codigo) && $c->codigo != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

            if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5){
                error_log("descuento: ".count($d->descuento)." is greater than 5");
            }
            $d->descuento= array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0){
            foreach ($d->descuentoLinea as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';
        if (isset($d->baseImponible) && $d->baseImponible != "")
        {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '
                    <CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '
                    <Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '
                    <FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                $xmlString .= '
                    <Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '
                </Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if ( isset($otrosCargos) && $otrosCargos != ""){
        foreach ($otrosCargos as $o)
        {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
                $xmlString .= '
                <NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
            if ( isset($o->nombreTercero) && $o->nombreTercero != "")
                $xmlString .= '
                <NombreTercero>'.$o->nombreTercero.'</NombreTercero>';
            //if ( isset($o->detalle) && $o->detalle != "")
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            //if ( isset($o->montoCargo) && $o->montoCargo != "")
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalServExonerados != '')
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalMercExonerada != '')
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    if ($totalExonerado != '')
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ($totalIVADevuelto != '')
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != ''){

        $xmlString .=   '
    <InformacionReferencia>';
        if(in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true))
            $xmlString .='
            <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>';
        else{
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = ". $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ( isset($infoRefeNumero) && $infoRefeNumero != "")
            $xmlString .=   '
        <Numero>' . $infoRefeNumero . '</Numero>';

        $xmlString .=   '
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>';

        if ( isset($infoRefeCodigo) && $infoRefeCodigo != ""){
            if(in_array($infoRefeCodigo, CODIDOREFVALUES, true)){
                $xmlString .=   '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            }else{
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = ". $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ( isset($infoRefeRazon) && $infoRefeRazon != "")
            $xmlString .=   '
        <Razon>' . $infoRefeRazon . '</Razon>';

        $xmlString .=   '
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
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize: ".CODIGOACTIVIDADSIZE." is not codigoActividad: ".$codigoActividad);

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <MensajeReceptor
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/mensajeReceptor"
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

function genXMLFec()
{
    // Datos contribuyente
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
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
    $mediosPago                     = json_decode(params_get("medios_pago"));

    grace_debug(params_get("detalles"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($mediosPago) && $mediosPago != "")
        grace_debug(params_get("medios_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenas);

    if ( isset($otrosCargos) && $otrosCargos != "")
        if (count($otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos)." is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }

    if ( isset($mediosPago) && $mediosPago != "")
        if (count($mediosPago) > 4){
            error_log("mediosPago: ".count($mediosPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronicaCompra
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra"
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
            </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

  
    $xmlString .= '<Receptor>
        <Nombre>' . $receptorNombre . '</Nombre>';

    /*if ($receptorTipoIdentif == '05')
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
    {*/
        /*if ($receptorTipoIdentif != '' &&  $receptorNumIdentif != '')
        {*/
        $xmlString .= '
        <Identificacion>
            <Tipo>' . $receptorTipoIdentif . '</Tipo>
            <Numero>' . $receptorNumIdentif . '</Numero>
        </Identificacion>';

        if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
        {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero.
            '</IdentificacionExtranjero>';
        }

        if ( isset($receptorNombreComercial) && $receptorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
        //}

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

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
            $xmlString .= '
            <OtrasSenasExtranjero>'
                .$receptorOtrasSenasExtranjero.
            '</OtrasSenasExtranjero>';
        }
    /*}*/

    if ($receptorCodPaisTel != '' && $receptorTel != '')
    {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorCodPaisFax != '' && $receptorFax != '')
    {
        $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
    }

    if ($receptorEmail != '')
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';

    $xmlString .= '</Receptor>';

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    if ( isset($medioPago) && $medioPago != "" && $medioPago != 0 )
    $xmlString .= '
        <MedioPago>' . $medioPago . '</MedioPago>';
    else
        //mediosPago 4 nodos nada más
        if ( isset($mediosPago) && $mediosPago != ""){
            foreach ($mediosPago as $o)
            {
                $xmlString .= '<MedioPago>' . $o->codigo . '</MedioPago>';
            }
        }

    $xmlString .= '
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
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5){
                error_log("codigoComercial: ".count($d->codigoComercial)." is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0){
            foreach ($d->codigoComercialLinea as $c)
            {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "" )
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                    if (isset($c->codigo) && $c->codigo != "")
                        $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                    $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($c->codigo) && $c->codigo != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5){
                error_log("descuento: ".count($d->descuento)." is greater than 5");
            }
            $d->descuento= array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0){
            foreach ($d->descuentoLinea as $dsc)
            {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "" )
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->baseImponible) && $d->baseImponible != "")
        {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <PorcentajeExoneracion>' . $i->exoneracion->porcentajeExoneracion . '</PorcentajeExoneracion>
                        <MontoExoneracion>' . $i->exoneracion->montoExoneracion . '</MontoExoneracion>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';
    //OtrosCargos
    if ( isset($otrosCargos) && $otrosCargos != ""){
        foreach ($otrosCargos as $o)
        {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            if ( isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "")
                $xmlString .= '
                <NumeroIdentidadTercero>'.$o->numeroIdentidadTercero.'</NumeroIdentidadTercero>';
            if ( isset($o->nombreTercero) && $o->nombreTercero != "")
                $xmlString .= '
                <NombreTercero>'.$o->nombreTercero.'</NombreTercero>';
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalServExonerados != '')
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalMercExonerada != '')
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    if ($totalExonerado != '')
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != ''){

        $xmlString .=   '
    <InformacionReferencia>';

        if(in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true))
        $xmlString .='
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>';
        else{
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = ". $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ( isset($infoRefeNumero) && $infoRefeNumero != "")
            $xmlString .=   '
        <Numero>' . $infoRefeNumero . '</Numero>';

        $xmlString .=   '
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>';

        if ( isset($infoRefeCodigo) && $infoRefeCodigo != ""){
            if(in_array($infoRefeCodigo, CODIDOREFVALUES, true)){
                $xmlString .=   '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            }else{
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = ". $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ( isset($infoRefeRazon) && $infoRefeRazon != "")
            $xmlString .=   '
        <Razon>' . $infoRefeRazon . '</Razon>';

        $xmlString .=   '
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
    </FacturaElectronicaCompra>';
    $arrayResp = array(
        "clave" => $clave,
        "xml"   => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLFee()
{
    $clave                          = params_get("clave");
    $codigoActividad                = params_get("codigo_actividad");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo                    = params_get("consecutivo");
    $fechaEmision                   = params_get("fecha_emision");
    $emisorNombre                   = params_get("emisor_nombre");
    $emisorTipoIdentif              = params_get("emisor_tipo_identif");
    $emisorNumIdentif               = params_get("emisor_num_identif");
    $emisorNombreComercial          = params_get("emisor_nombre_comercial");
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
    $receptorNombre                 = params_get("receptor_nombre");
    $receptorTipoIdentif            = params_get("receptor_tipo_identif");
    $receptorNumIdentif             = params_get("receptor_num_identif");
    $receptorIdentifExtranjero      = params_get("receptor_identif_extranjero");
    $receptorNombreComercial        = params_get("receptor_nombre_comercial");
    $receptorOtrasSenasExtranjero   = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel             = params_get("receptor_cod_pais_tel");
    $receptorTel                    = params_get("receptor_tel");
    $receptorCodPaisFax             = params_get("receptor_cod_pais_fax");
    $receptorFax                    = params_get("receptor_fax");
    $receptorEmail                  = params_get("receptor_email");
    $condVenta                      = params_get("condicion_venta");
    $plazoCredito                   = params_get("plazo_credito");
    $medioPago                      = json_decode(params_get("medio_pago"));
    $detalles                       = json_decode(params_get("detalles"));
    $otrosCargos                    = json_decode(params_get("otrosCargos"));
    $codMoneda                      = params_get("cod_moneda");
    $tipoCambio                     = params_get("tipo_cambio");
    $totalServGravados              = params_get("total_serv_gravados");
    $totalServExentos               = params_get("total_serv_exentos");
    $totalMercGravadas              = params_get("total_merc_gravada");
    $totalMercExentas               = params_get("total_merc_exenta");
    $totalGravados                  = params_get("total_gravados");
    $totalExento                    = params_get("total_exento");
    $totalVentas                    = params_get("total_ventas");
    $totalDescuentos                = params_get("total_descuentos");
    $totalVentasNeta                = params_get("total_ventas_neta");
    $totalImp                       = params_get("total_impuestos");
    $totalOtrosCargos               = params_get("totalOtrosCargos");
    $totalComprobante               = params_get("total_comprobante");
    $informacionReferencia          = json_decode(params_get("informacionReferencia"));
    $otros                          = json_decode(params_get("otros"));

    grace_debug(params_get("detalles"));

    if ( isset($otrosCargos) && $otrosCargos != "")
        grace_debug(params_get("otrosCargos"));

    if ( isset($medioPago) && $medioPago != "")
        grace_debug(params_get("medio_pago"));

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE)
        error_log("codigoActividadSize is: ".CODIGOACTIVIDADSIZE." and codigoActividad is ".$codigoActividad);

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE)
        error_log("emisorNombreSize: ".EMISORNOMBREMAXSIZE." is greater than emisorNombre: ".$emisorNombre);

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE)
        error_log("receptorNombreMaxSize: ".RECEPTORNOMBREMAXSIZE." is greater than receptorNombre: ".$receptorNombre);

    if (strlen($receptorOtrasSenasExtranjero) > RECEPTOROTRASSENASEXTRANJEROMAXSIZE)
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: ".RECEPTOROTRASSENASEXTRANJEROMAXSIZE." is greater than receptorOtrasSenas: ".$receptorOtrasSenasExtranjero);

    if ( isset($otrosCargos) && !empty($otrosCargos))
        if (count($otrosCargos->otrosCargos) > 15){
            error_log("otrosCargos: ".count($otrosCargos->otrosCargos)." is greater than 15");
            //Delimita el array a solo 4 elementos
            $otrosCargos->otrosCargos = array_slice($otrosCargos->otrosCargos, 0, 15);
        }

    if ( isset($medioPago) && !empty($medioPago))
        if (count($medioPago->medioPago) > 4){
            error_log("medioPago: ".count($medioPago->medioPago)." is greater than 4");
            //Delimita el array a solo 4 elementos
            $medioPago->medioPago = array_slice($medioPago->medioPago, 0, 4);
        }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronicaExportacion
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaExportacion"
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
            </Identificacion>';
    if ( isset($emisorNombreComercial) && $emisorNombreComercial != "")
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';

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

    if (isset($receptorNombre) && $receptorNombre != "") {
        $xmlString .= '<Receptor>
        <Nombre>' . $receptorNombre . '</Nombre>';
    }

    if (isset($receptorTipoIdentif) && $receptorTipoIdentif != "" && isset($receptorNumIdentif) && $receptorNumIdentif != "") {
        $xmlString .= '
        <Identificacion>
            <Tipo>' . $receptorTipoIdentif . '</Tipo>
            <Numero>' . $receptorNumIdentif . '</Numero>
        </Identificacion>';
    }

    if ($receptorIdentifExtranjero != '' &&  $receptorIdentifExtranjero != '')
    {
        $xmlString .= '
        <IdentificacionExtranjero>'
            . $receptorIdentifExtranjero.
        '</IdentificacionExtranjero>';
    }

    if ( isset($receptorNombreComercial) && $receptorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
    }

    if (isset($receptorProvincia) && $receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '')
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

    if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= 300){
        $xmlString .= '
        <OtrasSenasExtranjero>'
            .$receptorOtrasSenasExtranjero.
        '</OtrasSenasExtranjero>';
    }


    if ($receptorCodPaisTel != '' && $receptorTel != '')
    {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorCodPaisFax != '' && $receptorFax != '')
    {
        $xmlString .= '
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>';
    }

    if ($receptorEmail != '') {
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
        $xmlString .= '</Receptor>';
    }


    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if ( isset($plazoCredito) && $plazoCredito != "" )
    $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';

    // JSON DE EJEMPLO
    // {
    //     "medioPago": [
    //         "01",
    //         "02",
    //         "03"
    //     ]
    // }

    if (isset($medioPago) && !empty($medioPago)) {
        // Iteramos sobre los elementos de otroContenido
        foreach ($medioPago->medioPago as $c) {
            $xmlString .= '<MedioPago>' . $c . '</MedioPago>';
        }
    }

    // XML Resultante
    // <MedioPago>01</MedioPago>
	// <MedioPago>02</MedioPago>
	// <MedioPago>03</MedioPago>

    $xmlString .= '
        <DetalleServicio>';


    $l = 1;
    foreach ($detalles as $d)
    {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->partidaArancelaria) && $d->partidaArancelaria != "")
            $xmlString .= '
            <PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';

        if (isset($d->codigo) && $d->codigo != "")
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';

        if (isset($d->codigoComercial) && !empty($d->codigoComercial)) {
            // Convertir el objeto $d->codigoComercial en un array
            $codigoComercialArray = (array)$d->codigoComercial;

            // Delimitar el array a solo 5 elementos
            if (count($codigoComercialArray) > 5) {
                error_log("codigoComercial: " . count($codigoComercialArray) . " is greater than 5");
            }
            $codigoComercialArray = array_slice($codigoComercialArray, 0, 5);

            // Iterar sobre los elementos del array
            foreach ($codigoComercialArray as $codigos) {
                $c = (array)$codigos;
                // Verificar si el elemento es un array asociativo
                if (is_array($c) && isset($c['tipo']) && $c['tipo'] != "" && isset($c['codigo']) && $c['codigo'] != "") {
                    $xmlString .= '
                        <CodigoComercial>
                            <Tipo>' . $c['tipo'] . '</Tipo>
                            <Codigo>' . $c['codigo'] . '</Codigo>
                        </CodigoComercial>';
                }
            }
        }


        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
            if (isset($d->unidadMedidaComercial) && $d->unidadMedidaComercial != "")
                $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
            $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && !empty($d->descuento)) {
            // Convertir el objeto $d->descuento en un array
            $descuentoArray = (array)$d->descuento;

            // Delimitar el array a solo 5 elementos
            if (count($descuentoArray) > 5) {
                error_log("descuento: " . count($descuentoArray) . " is greater than 5");
            }
            $descuentoArray = array_slice($descuentoArray, 0, 5);

            // Iterar sobre los elementos del array
            foreach ($descuentoArray as $descuentos) {
                $c = (array)$descuentos;
                // Verificar si el elemento es un array asociativo
                if (is_array($c) && isset($c['montoDescuento']) && $c['montoDescuento'] != "" && isset($c['naturalezaDescuento']) && $c['naturalezaDescuento'] != "") {
                    $xmlString .= '
                        <Descuento>
                            <MontoDescuento>' . $c['montoDescuento'] . '</MontoDescuento>
                            <NaturalezaDescuento>' . $c['naturalezaDescuento'] . '</NaturalezaDescuento>
                        </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '
                <Impuesto>';
                if ( isset($i->codigo) && $i->codigo != "" )
                    $xmlString .= '<Codigo>' . $i->codigo . '</Codigo>';

                if ( isset($i->codigoTarifa) && $i->codigoTarifa != "" )
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';

                if ( isset($i->tarifa) && $i->tarifa != "")
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';

                if ( isset($i->factorIVA) && $i->factorIVA != "")
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';

                if ( isset($i->monto) && $i->monto != "" )
                    $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if ( isset($i->montoExportacion) && $i->montoExportacion != "" )
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';

                $xmlString .= '</Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "")
        {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    // JSON DE EJEMPLO
    // {
    //     "otrosCargos": [
    //         {
    //             "detalle": "123",
    //             "montoCargo": "123",
    //             "porcentaje": "123",
    //             "tipoDocumento": "01"
    //         }
    //     ]
    // }

    if (isset($otrosCargos) && !empty($otrosCargos)) {
        // Iteramos sobre los elementos de otroContenido
        foreach ($otrosCargos->otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>'.$o->tipoDocumento.'</TipoDocumento>';
            $xmlString .= '
                <Detalle>'.$o->detalle.'</Detalle>';
            if ( isset($o->porcentaje) && $o->porcentaje != "")
                $xmlString .= '
                <Porcentaje>'.$o->porcentaje.'</Porcentaje>';
            $xmlString .= '
                <MontoCargo>'.$o->montoCargo.'</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    // XML Resultante
    // <OtrosCargos>
    //     <TipoDocumento>01</TipoDocumento>
    //     <Detalle>123</Detalle>
    //     <Porcentaje>123</Porcentaje>
    //     <MontoCargo>123</MontoCargo>
    // </OtrosCargos>

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $tipoCambio != '' && $tipoCambio != 0)
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';

    if ($totalServGravados != '')
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';

    if ($totalServExentos != '')
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';

    if ($totalMercGravadas != '')
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';

    if ($totalMercExentas != '')
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';

    if ($totalGravados != '')
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';

    if ($totalExento != '')
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '')
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    if ($totalImp != '')
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';

    if ( isset($totalOtrosCargos) && $totalOtrosCargos != "")
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    // JSON de ejemplo
    // {
    //     "1": {
    //         "codigo": "01",
    //         "fechaEmision": "2024-04-02T12:00:00-06:00",
    //         "numero": "50620032400020536006000100001010000000017100000017",
    //         "razon": "Falta de informacion",
    //         "tipoDoc": "01"
    //     }
    // }

    if (isset($informacionReferencia) && $informacionReferencia != "") {
        if(count((array) $informacionReferencia) > 10) {
            error_log("informacionReferencia: ".count((array) $informacionReferencia)." is greater than 10");
        }
        else
        {
            foreach ($informacionReferencia as $i)
            {
                $xmlString .= '
                    <InformacionReferencia>';

                if (isset($i->tipoDoc) && $i->tipoDoc != "")
                    $xmlString .= '
                    <TipoDoc>' . $i->tipoDoc . '</TipoDoc>';

                if (isset($i->numero) && $i->numero != "")
                    $xmlString .= '
                    <Numero>' . $i->numero . '</Numero>';

                if (isset($i->fechaEmision) && $i->fechaEmision != "")
                    $xmlString .= '
                    <FechaEmision>' . $i->fechaEmision . '</FechaEmision>';

                if (isset($i->codigo) && $i->codigo != "")
                    $xmlString .= '
                    <Codigo>' . $i->codigo . '</Codigo>';

                if (isset($i->razon) && $i->razon != "")
                    $xmlString .= '
                    <Razon>' . $i->razon . '</Razon>';

                $xmlString .= '</InformacionReferencia>';
            }
        }
    }

    // XML Resultante
    // <InformacionReferencia>
	// 	<TipoDoc>01</TipoDoc>
	// 	<Numero>50620032400020536006000100001010000000017100000017</Numero>
	// 	<FechaEmision>2024-04-02T12:00:00-06:00</FechaEmision>
	// 	<Codigo>01</Codigo>
	// 	<Razon>Falta de informacion</Razon>
    // </InformacionReferencia>

    // -----------------------------------------------------------------------------------------------------

    // JSON de ejemplo
    // {
    //     "otroContenido": [
    //         {
    //             "codigo": "CONT1",
    //             "contenidoEstructurado": {
    //                 "ContactoDesarrollador": {
    //                     "Correo": "operacionesfacturaelectronica@ice.go.cr",
    //                     "Nombre": "Equipo Operaciones Factura Electronica",
    //                     "Telefono": "+506 800-400-0000"
    //                 }
    //             }
    //         }
    //     ],
    //     "otroTexto": {
    //         "codigo": "COD1",
    //         "texto": "Texto opcional 1"
    //     }
    // }

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto codigo="' . $otros->otroTexto->codigo . '">' . $otros->otroTexto->texto . '</OtroTexto>';
        }
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
                        // Construimos el XML para objetos anidados
                        $xmlString .= '<' . $element . ' xmlns="https://www.grupoice.com">';
                        foreach ($content as $nestedElement => $nestedContent) {
                            $xmlString .= '<' . $nestedElement . '>' . htmlspecialchars($nestedContent) . '</' . $nestedElement . '>';
                        }
                        $xmlString .= '</' . $element . '>';
                    }
                }
                $xmlString .= '</OtroContenido>';
            }
        }
        $xmlString .= '</Otros>';
    }

    // XML Resultante
    // <OtroContenido>
	//     <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //         <Nombre>Equipo Operaciones Factura Electronica</Nombre>
    //         <Correo>operacionesfacturaelectronica@ice.go.cr</Correo>
    //         <Telefono>+506 800-400-0000</Telefono>
	//     </ContactoDesarrollador>
    // </OtroContenido>

    $xmlString .= '
    </FacturaElectronicaExportacion>';
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
