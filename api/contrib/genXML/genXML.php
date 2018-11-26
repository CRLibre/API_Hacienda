<?php
/*
 * Copyright (C) 2017-2018 CRLibre <https://crlibre.org>
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
/* Funcion para generar XML                          */
/* * ************************************************** */

function genXMLFe()
{
    // Datos contribuyente
    $clave                  = params_get("clave");
    $consecutivo            = params_get("consecutivo");
    $fechaEmision           = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre           = params_get("emisor_nombre");
    $emisorTipoIdentif      = params_get("emisor_tipo_indetif");
    $emisorNumIdentif       = params_get("emisor_num_identif");
    $nombreComercial        = params_get("nombre_comercial");
    $emisorProv             = params_get("emisor_provincia");
    $emisorCanton           = params_get("emisor_canton");
    $emisorDistrito         = params_get("emisor_distrito");
    $emisorBarrio           = params_get("emisor_barrio");
    $emisorOtrasSenas       = params_get("emisor_otras_senas");
    $emisorCodPaisTel       = params_get("emisor_cod_pais_tel");
    $emisorTel              = params_get("emisor_tel");
    $emisorCodPaisFax       = params_get("emisor_cod_pais_fax");
    $emisorFax              = params_get("emisor_fax");
    $emisorEmail            = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor        = params_get("omitir_receptor");
    $receptorNombre         = params_get("receptor_nombre");
    $receptorTipoIdentif    = params_get("receptor_tipo_identif");
    $receptorNumIdentif     = params_get("receptor_num_identif");
    $receptorProvincia      = params_get("receptor_provincia");
    $receptorCanton         = params_get("receptor_canton");
    $receptorDistrito       = params_get("receptor_distrito");
    $receptorBarrio         = params_get("receptor_barrio");
    $receptorOtrasSenas     = params_get("receptor_otras_senas");
    $receptorCodPaisTel     = params_get("receptor_cod_pais_tel");
    $receptorTel            = params_get("receptor_tel");
    $receptorCodPaisFax     = params_get("receptor_cod_pais_fax");
    $receptorFax            = params_get("receptor_fax");
    $receptorEmail          = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta              = params_get("condicion_venta");
    $plazoCredito           = params_get("plazo_credito");
    $medioPago              = params_get("medio_pago");
    $codMoneda              = params_get("cod_moneda");
    $tipoCambio             = params_get("tipo_cambio");
    $totalServGravados      = params_get("total_serv_gravados");
    $totalServExentos       = params_get("total_serv_exentos");
    $totalMercGravadas      = params_get("total_merc_gravada");
    $totalMercExentas       = params_get("total_merc_exenta");
    $totalGravados          = params_get("total_gravados");
    $totalExentos           = params_get("total_exentos");
    $totalVentas            = params_get("total_ventas");
    $totalDescuentos        = params_get("total_descuentos");
    $totalVentasNeta        = params_get("total_ventas_neta");
    $totalImp               = params_get("total_impuestos");
    $totalComprobante       = params_get("total_comprobante");
    $otros                  = params_get("otros");
    $otrosType              = params_get("otrosType");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    grace_debug(params_get("detalles"));

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <FacturaElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica FacturaElectronica_V.4.2.xsd">
        <Clave>' . $clave . '</Clave>
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
                  <Detalle>' . $d->detalle . '</Detalle>
                  <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
                  <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->montoDescuento) && $d->montoDescuento != "")
            $xmlString .= '<MontoDescuento>' . $d->montoDescuento . '</MontoDescuento>';

        if (isset($d->naturalezaDescuento) && $d->naturalezaDescuento != "")
            $xmlString .= '<NaturalezaDescuento>' . $d->naturalezaDescuento . '</NaturalezaDescuento>';

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';
        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Tarifa>' . $i->tarifa . '</Tarifa>
                <Monto>' . $i->monto . '</Monto>';
                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoImpuesto>' . $i->exoneracion->montoImpuesto . '</MontoImpuesto>
                        <PorcentajeCompra>' . $i->exoneracion->porcentajeCompra . '</PorcentajeCompra>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>
        <ResumenFactura>
        <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
        <TipoCambio>' . $tipoCambio . '</TipoCambio>
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExentos . '</TotalExento>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
        </ResumenFactura>
        <Normativa>
        <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
        <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
        </Normativa>';

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
    $clave                  = params_get("clave");
    $consecutivo            = params_get("consecutivo");
    $fechaEmision           = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre           = params_get("emisor_nombre");
    $emisorTipoIdentif      = params_get("emisor_tipo_indetif");
    $emisorNumIdentif       = params_get("emisor_num_identif");
    $nombreComercial        = params_get("nombre_comercial");
    $emisorProv             = params_get("emisor_provincia");
    $emisorCanton           = params_get("emisor_canton");
    $emisorDistrito         = params_get("emisor_distrito");
    $emisorBarrio           = params_get("emisor_barrio");
    $emisorOtrasSenas       = params_get("emisor_otras_senas");
    $emisorCodPaisTel       = params_get("emisor_cod_pais_tel");
    $emisorTel              = params_get("emisor_tel");
    $emisorCodPaisFax       = params_get("emisor_cod_pais_fax");
    $emisorFax              = params_get("emisor_fax");
    $emisorEmail            = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor        = params_get("omitir_receptor");
    $receptorNombre         = params_get("receptor_nombre");
    $receptorTipoIdentif    = params_get("receptor_tipo_identif");
    $receptorNumIdentif     = params_get("receptor_num_identif");
    $receptorProvincia      = params_get("receptor_provincia");
    $receptorCanton         = params_get("receptor_canton");
    $receptorDistrito       = params_get("receptor_distrito");
    $receptorBarrio         = params_get("receptor_barrio");
    $receptorOtrasSenas     = params_get("receptor_otras_senas");
    $receptorCodPaisTel     = params_get("receptor_cod_pais_tel");
    $receptorTel            = params_get("receptor_tel");
    $receptorCodPaisFax     = params_get("receptor_cod_pais_fax");
    $receptorFax            = params_get("receptor_fax");
    $receptorEmail          = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta              = params_get("condicion_venta");
    $plazoCredito           = params_get("plazo_credito");
    $medioPago              = params_get("medio_pago");
    $codMoneda              = params_get("cod_moneda");
    $tipoCambio             = params_get("tipo_cambio");
    $totalServGravados      = params_get("total_serv_gravados");
    $totalServExentos       = params_get("total_serv_exentos");
    $totalMercGravadas      = params_get("total_merc_gravada");
    $totalMercExentas       = params_get("total_merc_exenta");
    $totalGravados          = params_get("total_gravados");
    $totalExentos           = params_get("total_exentos");
    $totalVentas            = params_get("total_ventas");
    $totalDescuentos        = params_get("total_descuentos");
    $totalVentasNeta        = params_get("total_ventas_neta");
    $totalImp               = params_get("total_impuestos");
    $totalComprobante       = params_get("total_comprobante");
    $otros                  = params_get("otros");
    $otrosType              = params_get("otrosType");
    $infoRefeTipoDoc        = params_get("infoRefeTipoDoc");
    $infoRefeNumero         = params_get("infoRefeNumero");
    $infoRefeFechaEmision   = params_get("infoRefeFechaEmision");
    $infoRefeCodigo         = params_get("infoRefeCodigo");
    $infoRefeRazon          = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles               = json_decode(params_get("detalles"));
    //return $detalles;

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <NotaCreditoElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica NotaCreditoElectronica_V4.2.xsd">
    <Clave>' . $clave . '</Clave>
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
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';
        if (isset($d->montoDescuento) && $d->montoDescuento != "")
            $xmlString .= '<MontoDescuento>' . $d->montoDescuento . '</MontoDescuento>';

        if (isset($d->naturalezaDescuento) && $d->naturalezaDescuento != "")
            $xmlString .= '<NaturalezaDescuento>' . $d->naturalezaDescuento . '</NaturalezaDescuento>';

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Tarifa>' . $i->tarifa . '</Tarifa>
                <Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoImpuesto>' . $i->exoneracion->montoImpuesto . '</MontoImpuesto>
                        <PorcentajeCompra>' . $i->exoneracion->porcentajeCompra . '</PorcentajeCompra>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>
    <ResumenFactura>
        <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
        <TipoCambio>' . $tipoCambio . '</TipoCambio>
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExentos . '</TotalExento>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>
    <InformacionReferencia>
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
        <Numero>' . $infoRefeNumero . '</Numero>
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
        <Codigo>' . $infoRefeCodigo . '</Codigo>
        <Razon>' . $infoRefeRazon . '</Razon>
    </InformacionReferencia>
    <Normativa>
        <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
        <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
    </Normativa>';
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
    $clave                  = params_get("clave");
    $consecutivo            = params_get("consecutivo");
    $fechaEmision           = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre           = params_get("emisor_nombre");
    $emisorTipoIdentif      = params_get("emisor_tipo_indetif");
    $emisorNumIdentif       = params_get("emisor_num_identif");
    $nombreComercial        = params_get("nombre_comercial");
    $emisorProv             = params_get("emisor_provincia");
    $emisorCanton           = params_get("emisor_canton");
    $emisorDistrito         = params_get("emisor_distrito");
    $emisorBarrio           = params_get("emisor_barrio");
    $emisorOtrasSenas       = params_get("emisor_otras_senas");
    $emisorCodPaisTel       = params_get("emisor_cod_pais_tel");
    $emisorTel              = params_get("emisor_tel");
    $emisorCodPaisFax       = params_get("emisor_cod_pais_fax");
    $emisorFax              = params_get("emisor_fax");
    $emisorEmail            = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor        = params_get("omitir_receptor");
    $receptorNombre         = params_get("receptor_nombre");
    $receptorTipoIdentif    = params_get("receptor_tipo_identif");
    $receptorNumIdentif     = params_get("receptor_num_identif");
    $receptorProvincia      = params_get("receptor_provincia");
    $receptorCanton         = params_get("receptor_canton");
    $receptorDistrito       = params_get("receptor_distrito");
    $receptorBarrio         = params_get("receptor_barrio");
    $receptorOtrasSenas     = params_get("receptor_otras_senas");
    $receptorCodPaisTel     = params_get("receptor_cod_pais_tel");
    $receptorTel            = params_get("receptor_tel");
    $receptorCodPaisFax     = params_get("receptor_cod_pais_fax");
    $receptorFax            = params_get("receptor_fax");
    $receptorEmail          = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta              = params_get("condicion_venta");
    $plazoCredito           = params_get("plazo_credito");
    $medioPago              = params_get("medio_pago");
    $codMoneda              = params_get("cod_moneda");
    $tipoCambio             = params_get("tipo_cambio");
    $totalServGravados      = params_get("total_serv_gravados");
    $totalServExentos       = params_get("total_serv_exentos");
    $totalMercGravadas      = params_get("total_merc_gravada");
    $totalMercExentas       = params_get("total_merc_exenta");
    $totalGravados          = params_get("total_gravados");
    $totalExentos           = params_get("total_exentos");
    $totalVentas            = params_get("total_ventas");
    $totalDescuentos        = params_get("total_descuentos");
    $totalVentasNeta        = params_get("total_ventas_neta");
    $totalImp               = params_get("total_impuestos");
    $totalComprobante       = params_get("total_comprobante");
    $otros                  = params_get("otros");
    $otrosType              = params_get("otrosType");
    $infoRefeTipoDoc        = params_get("infoRefeTipoDoc");
    $infoRefeNumero         = params_get("infoRefeNumero");
    $infoRefeFechaEmision   = params_get("infoRefeFechaEmision");
    $infoRefeCodigo         = params_get("infoRefeCodigo");
    $infoRefeRazon          = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles               = json_decode(params_get("detalles"));

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <NotaDebitoElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica NotaDebitoElectronica_V4.2.xsd">
    <Clave>' . $clave . '</Clave>
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
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->montoDescuento) && $d->montoDescuento != "")
            $xmlString .= '<MontoDescuento>' . $d->montoDescuento . '</MontoDescuento>';

        if (isset($d->naturalezaDescuento) && $d->naturalezaDescuento != "")
            $xmlString .= '<NaturalezaDescuento>' . $d->naturalezaDescuento . '</NaturalezaDescuento>';

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Tarifa>' . $i->tarifa . '</Tarifa>
                <Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoImpuesto>' . $i->exoneracion->montoImpuesto . '</MontoImpuesto>
                        <PorcentajeCompra>' . $i->exoneracion->porcentajeCompra . '</PorcentajeCompra>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>
    <ResumenFactura>
        <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
        <TipoCambio>' . $tipoCambio . '</TipoCambio>
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExentos . '</TotalExento>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>
    <InformacionReferencia>
        <TipoDoc>' . $infoRefeTipoDoc . '</TipoDoc>
        <Numero>' . $infoRefeNumero . '</Numero>
        <FechaEmision>' . $infoRefeFechaEmision . '</FechaEmision>
        <Codigo>' . $infoRefeCodigo . '</Codigo>
        <Razon>' . $infoRefeRazon . '</Razon>
    </InformacionReferencia>
    <Normativa>
        <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
        <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
    </Normativa>';
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
    $clave                  = params_get("clave");
    $consecutivo            = params_get("consecutivo");
    $fechaEmision           = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre           = params_get("emisor_nombre");
    $emisorTipoIdentif      = params_get("emisor_tipo_indetif");
    $emisorNumIdentif       = params_get("emisor_num_identif");
    $nombreComercial        = params_get("nombre_comercial");
    $emisorProv             = params_get("emisor_provincia");
    $emisorCanton           = params_get("emisor_canton");
    $emisorDistrito         = params_get("emisor_distrito");
    $emisorBarrio           = params_get("emisor_barrio");
    $emisorOtrasSenas       = params_get("emisor_otras_senas");
    $emisorCodPaisTel       = params_get("emisor_cod_pais_tel");
    $emisorTel              = params_get("emisor_tel");
    $emisorCodPaisFax       = params_get("emisor_cod_pais_fax");
    $emisorFax              = params_get("emisor_fax");
    $emisorEmail            = params_get("emisor_email");

    // Datos receptor
    $omitir_receptor        = params_get("omitir_receptor");
    $receptorNombre         = params_get("receptor_nombre");
    $receptorTipoIdentif    = params_get("receptor_tipo_identif");
    $receptorNumIdentif     = params_get("receptor_num_identif");
    $receptorProvincia      = params_get("receptor_provincia");
    $receptorCanton         = params_get("receptor_canton");
    $receptorDistrito       = params_get("receptor_distrito");
    $receptorBarrio         = params_get("receptor_barrio");
    $receptorOtrasSenas     = params_get("receptor_otras_senas");
    $receptorCodPaisTel     = params_get("receptor_cod_pais_tel");
    $receptorTel            = params_get("receptor_tel");
    $receptorCodPaisFax     = params_get("receptor_cod_pais_fax");
    $receptorFax            = params_get("receptor_fax");
    $receptorEmail          = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta              = params_get("condicion_venta");
    $plazoCredito           = params_get("plazo_credito");
    $medioPago              = params_get("medio_pago");
    $codMoneda              = params_get("cod_moneda");
    $tipoCambio             = params_get("tipo_cambio");
    $totalServGravados      = params_get("total_serv_gravados");
    $totalServExentos       = params_get("total_serv_exentos");
    $totalMercGravadas      = params_get("total_merc_gravada");
    $totalMercExentas       = params_get("total_merc_exenta");
    $totalGravados          = params_get("total_gravados");
    $totalExentos           = params_get("total_exentos");
    $totalVentas            = params_get("total_ventas");
    $totalDescuentos        = params_get("total_descuentos");
    $totalVentasNeta        = params_get("total_ventas_neta");
    $totalImp               = params_get("total_impuestos");
    $totalComprobante       = params_get("total_comprobante");
    $otros                  = params_get("otros");
    $otrosType              = params_get("otrosType");

    // Detalles de la compra
    $detalles               = json_decode(params_get("detalles"));
    grace_debug(params_get("detalles"));

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <TiqueteElectronico xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico.xsd">
    <Clave>' . $clave . '</Clave>
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
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->montoDescuento) && $d->montoDescuento != "")
            $xmlString .= '<MontoDescuento>' . $d->montoDescuento . '</MontoDescuento>';

        if (isset($d->naturalezaDescuento) && $d->naturalezaDescuento != "")
            $xmlString .= '<NaturalezaDescuento>' . $d->naturalezaDescuento . '</NaturalezaDescuento>';

        $xmlString .= '<SubTotal>' . $d->subtotal . '</SubTotal>';

        if (isset($d->impuesto) && $d->impuesto != "")
        {
            foreach ($d->impuesto as $i)
            {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>
                <Tarifa>' . $i->tarifa . '</Tarifa>
                <Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "")
                {
                    $xmlString .= '
                    <Exoneracion>
                        <TipoDocumento>' . $i->exoneracion->tipoDocumento . '</TipoDocumento>
                        <NumeroDocumento>' . $i->exoneracion->numeroDocumento . '</NumeroDocumento>
                        <NombreInstitucion>' . $i->exoneracion->nombreInstitucion . '</NombreInstitucion>
                        <FechaEmision>' . $i->exoneracion->fechaEmision . '</FechaEmision>
                        <MontoImpuesto>' . $i->exoneracion->montoImpuesto . '</MontoImpuesto>
                        <PorcentajeCompra>' . $i->exoneracion->porcentajeCompra . '</PorcentajeCompra>
                    </Exoneracion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>
    <ResumenFactura>
        <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
        <TipoCambio>' . $tipoCambio . '</TipoCambio>
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>
        <TotalGravado>' . $totalGravados . '</TotalGravado>
        <TotalExento>' . $totalExentos . '</TotalExento>
        <TotalVenta>' . $totalVentas . '</TotalVenta>
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>
    <Normativa>
        <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
        <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
    </Normativa>';
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
    $totalFactura                   = params_get("total_factura");                              // d18,5
    $numeroConsecutivoReceptor      = params_get("numero_consecutivo_receptor");                // d{20,20} numeracion consecutiva de los mensajes de confirmacion

    // Datos comprador = receptor
    $numeroCedulaReceptor           = params_get("numero_cedula_receptor");                     // d{12,12}cedula fisica, juridica, NITE, DIMEX del comprador
    $numeroCedulaReceptor           = str_pad($numeroCedulaReceptor, 12, "0", STR_PAD_LEFT);

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <MensajeReceptor xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor MensajeReceptor_4.2.xsd">
    <Clave>' . $clave . '</Clave>
    <NumeroCedulaEmisor>' . $numeroCedulaEmisor . '</NumeroCedulaEmisor>
    <FechaEmisionDoc>' . $fechaEmisionDoc . '</FechaEmisionDoc>
    <Mensaje>' . $mensaje . '</Mensaje>';
    if (!empty($detalleMensaje))
        $xmlString .= '<DetalleMensaje>' . $detalleMensaje . '</DetalleMensaje>';

    if (!empty($montoTotalImpuesto))
        $xmlString .= '<MontoTotalImpuesto>' . $montoTotalImpuesto . '</MontoTotalImpuesto>';

    $xmlString .= '<TotalFactura>' . $totalFactura . '</TotalFactura>
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
