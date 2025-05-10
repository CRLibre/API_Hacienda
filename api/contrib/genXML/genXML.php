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
define("TIPODOCREFVALUES", array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '99'));
define('CODIDOREFVALUES', array('01', '02', '04', '05', '06', '07', '08', '09', '10', '11', '12', '99'));
const CODIGOACTIVIDADSIZE = 6;
const EMISORNOMBREMAXSIZE = 100;
const EMISORNUMEROTELMIN = 8;
const EMISORNUMEROTELMAX = 20;
const RECEPTORNOMBREMAXSIZE = 100;
const RECEPTOROTRASSENASMAXSIZE = 250;
const RECEPTOROTRASSENASEXTRANJEROMAXSIZE = 300;
const EMAIL_REGEX = "/^\s*\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*\s*$/";


/* * ************************************************** */
/* Funcion para generar XML                          */
/* * ************************************************** */

function genXMLFe()
{
    // Datos contribuyente
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $codigoActividadReceptor = params_get("codigo_actividad_receptor");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email"); // This API only supports one email address for emisor
    $registroFiscal8707 = params_get("registrofiscal8707");

    // Datos receptor
    $omitir_receptor = params_get("omitir_receptor");        // Deprecated
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalServExonerados = params_get("total_serv_exonerados");
    $totalServNoSujeto = params_get("total_serv_no_sujeto");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalMercExonerada = params_get("total_merc_exonerada");
    $totalMercNoSujeta = params_get("total_merc_no_sujeta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalExonerado = params_get("total_exonerado");
    $totalNoSujeto = params_get("total_no_sujeto");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");
    $totalIVADevuelto = params_get("totalIVADevuelto");
    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");

    $otros = params_get("otros");
    $otrosType = params_get("otrosType");
    $infoRefeTipoDoc = params_get("infoRefeTipoDoc");
    $infoRefeTipoDocOtro = params_get("infoRefeTipoDocOTRO");
    $infoRefeNumero = params_get("infoRefeNumero");
    $infoRefeFechaEmision = params_get("infoRefeFechaEmision");
    $infoRefeCodigo = params_get("infoRefeCodigo");
    $infoRefeCodigoOtro = params_get("infoRefeCodigoOTRO");
    $infoRefeRazon = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $mediosPago = json_decode(params_get("medios_pago"));

    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    grace_debug(params_get("detalles"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($mediosPago) && $mediosPago != "") {
        grace_debug(params_get("medios_pago"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenas);
    }

    if (isset($otrosCargos) && $otrosCargos != "") {
        if (count($otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos) . " is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }
    }

    if (isset($mediosPago) && $mediosPago != "") {
        if (count($mediosPago) > 4) {
            error_log("mediosPago: " . count($mediosPago) . " is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }
    }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Clave>' . $clave . '</Clave>
        <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
        <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>';

    if (isset($codigoActividadReceptor) && $codigoActividadReceptor != "") {
        $codigoActividadReceptor = str_pad($codigoActividadReceptor, 6, "0", STR_PAD_LEFT);
        if (strlen($codigoActividadReceptor) != CODIGOACTIVIDADSIZE) {
            error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadReceptor is " . $codigoActividadReceptor);
        }

        $xmlString .= '
        <CodigoActividadReceptor>' . $codigoActividadReceptor . '</CodigoActividadReceptor>';
    }

    $xmlString .= '
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . $fechaEmision . '</FechaEmision>
        <Emisor>
            <Nombre>' . $emisorNombre . '</Nombre>
            <Identificacion>
                <Tipo>' . $emisorTipoIdentif . '</Tipo>
                <Numero>' . $emisorNumIdentif . '</Numero>
            </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $emisorTel . '</NumTelefono>
            </Telefono>';
    }

    if (preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }

    $xmlString .= '<Receptor>
        <Nombre>' . $receptorNombre . '</Nombre>';

    $xmlString .= '
        <Identificacion>
            <Tipo>' . $receptorTipoIdentif . '</Tipo>
            <Numero>' . $receptorNumIdentif . '</Numero>
        </Identificacion>';

    if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
        $xmlString .= '
            <IdentificacionExtranjero>'
            . $receptorIdentifExtranjero .
            '</IdentificacionExtranjero>';
    }

    if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
        $xmlString .= '
            <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
    }

    if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
        $xmlString .= '
            <Ubicacion>
                <Provincia>' . $receptorProvincia . '</Provincia>
                <Canton>' . $receptorCanton . '</Canton>
                <Distrito>' . $receptorDistrito . '</Distrito>';
        if ($receptorBarrio != '') {
            $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
        $xmlString .= '
            <OtrasSenasExtranjero>'
            . $receptorOtrasSenasExtranjero .
            '</OtrasSenasExtranjero>';
    }

    if ($receptorCodPaisTel != '' && $receptorTel != '') {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorEmail != '') {
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
    }

    $xmlString .= '</Receptor>';

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
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
    foreach ($detalles as $d) {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5) {
                error_log("codigoComercial: " . count($d->codigoComercial) . " is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0) {
            foreach ($d->codigoComercialLinea as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';

        if (isset($c->codigo) && $c->codigo != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
        $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5) {
                error_log("descuento: " . count($d->descuento) . " is greater than 5");
            }
            $d->descuento = array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0) {
            foreach ($d->descuentoLinea as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->IVACobradoFabrica) && $d->IVACobradoFabrica != "") {
            $xmlString .= '<IVACobradoFabrica>' . $IVACobradoFabrica . '</IVACobradoFabrica>';
        }

        if (isset($d->baseImponible) && $d->baseImponible != "") {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }

        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "") {
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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';
    //OtrosCargos
    if (isset($otrosCargos) && $otrosCargos != "") {
        foreach ($otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            if (isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "") {
                $xmlString .= '
                <NumeroIdentidadTercero>' . $o->numeroIdentidadTercero . '</NumeroIdentidadTercero>';
            }
            if (isset($o->nombreTercero) && $o->nombreTercero != "") {
                $xmlString .= '
                <NombreTercero>' . $o->nombreTercero . '</NombreTercero>';
            }
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalServExonerados != '') {
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
    }

    if ($totalServNoSujeto != '') {
        $xmlString .= '
        <TotalServNoSujeto>' . $totalServNoSujeto . '</TotalServNoSujeto>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalMercExonerada != '') {
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
    }

    if ($totalMercNoSujeta != '') {
        $xmlString .= '
        <TotalMercNoSujeta>' . $totalMercNoSujeta . '</TotalMercNoSujeta>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    if ($totalExonerado != '') {
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';
    }

    if ($totalNoSujeto != '') {
        $xmlString .= '
        <TotalNoSujeto>' . $totalNoSujeto . '</TotalNoSujeto>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if ($totalIVADevuelto != '') {
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != '') {

        $xmlString .= '
    <InformacionReferencia>';

        if (in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true)) {
            $xmlString .= '
        <TipoDocIR>' . $infoRefeTipoDoc . '</TipoDocIR>';
        } else {
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = " . $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ($infoRefeTipoDoc === '99') {
            if (isset($infoRefeTipoDocOtro) && strlen($infoRefeTipoDocOtro) >= 5 && strlen($infoRefeTipoDocOtro) <= 100) {
                $xmlString .= '
        <TipoDocRefOTRO>' . htmlspecialchars($infoRefeTipoDocOtro) . '</TipoDocRefOTRO>';
            } else {
                grace_error("El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida. infoRefeTipoDocOtro = " . $infoRefeTipoDocOtro);
                return "El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeNumero) && $infoRefeNumero != "") {
            $xmlString .= '
        <Numero>' . $infoRefeNumero . '</Numero>';
        }

        $xmlString .= '
        <FechaEmisionIR>' . $infoRefeFechaEmision . '</FechaEmisionIR>';

        if (isset($infoRefeCodigo) && $infoRefeCodigo != "") {
            if (in_array($infoRefeCodigo, CODIDOREFVALUES, true)) {
                $xmlString .= '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            } else {
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = " . $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ($infoRefeCodigo === '99') {
            if (isset($infoRefeCodigoOtro) && strlen($infoRefeCodigoOtro) >= 5 && strlen($infoRefeCodigoOtro) <= 100) {
                $xmlString .= '
        <CodigoReferenciaOTRO>' . htmlspecialchars($infoRefeCodigoOtro) . '</CodigoReferenciaOTRO>';
            } else {
                grace_error("El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida. infoRefeCodigoOTRO = " . $infoRefeCodigoOtro);
                return "El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeRazon) && $infoRefeRazon != "") {
            $xmlString .= '
        <Razon>' . $infoRefeRazon . '</Razon>';
        }

        $xmlString .= '
    </InformacionReferencia>';

    }

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

    $xmlString .= '
    </FacturaElectronica>';
    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLNC()
{

    // Datos contribuyente
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $codigoActividadReceptor = params_get("codigo_actividad_receptor");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email");
    $registroFiscal8707 = params_get("registrofiscal8707");

    // Datos receptor
    $omitir_receptor = params_get("omitir_receptor");        // Deprecated
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalServExonerados = params_get("total_serv_exonerados");
    $totalServNoSujeto = params_get("total_serv_no_sujeto");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalMercExonerada = params_get("total_merc_exonerada");
    $totalMercNoSujeta = params_get("total_merc_no_sujeta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalExonerado = params_get("total_exonerado");
    $totalNoSujeto = params_get("total_no_sujeto");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");
    $totalIVADevuelto = params_get("totalIVADevuelto");
    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");

    $otros = params_get("otros");
    $otrosType = params_get("otrosType");
    $infoRefeTipoDoc = params_get("infoRefeTipoDoc");
    $infoRefeTipoDocOtro = params_get("infoRefeTipoDocOTRO");
    $infoRefeNumero = params_get("infoRefeNumero");
    $infoRefeFechaEmision = params_get("infoRefeFechaEmision");
    $infoRefeCodigo = params_get("infoRefeCodigo");
    $infoRefeCodigoOtro = params_get("infoRefeCodigoOTRO");
    $infoRefeRazon = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $mediosPago = json_decode(params_get("medios_pago"));
    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($mediosPago) && $mediosPago != "") {
        grace_debug(params_get("medios_pago"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenas);
    }

    if (isset($otrosCargos) && $otrosCargos != "") {
        if (count($otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos) . " is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }
    }

    if (isset($mediosPago) && $mediosPago != "") {
        if (count($mediosPago) > 4) {
            error_log("otrosCargos: " . count($mediosPago) . " is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }
    }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <NotaCreditoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaCreditoElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
    <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>';

    if (isset($codigoActividadReceptor) && $codigoActividadReceptor != "") {
        $codigoActividadReceptor = str_pad($codigoActividadReceptor, 6, "0", STR_PAD_LEFT);
        if (strlen($codigoActividadReceptor) != CODIGOACTIVIDADSIZE) {
            error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadReceptor is " . $codigoActividadReceptor);
        }

        $xmlString .= '
        <CodigoActividadReceptor>' . $codigoActividadReceptor . '</CodigoActividadReceptor>';
    }

    $xmlString .= '
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if (preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }

    if ($omitir_receptor != 'true') {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '') {
            $xmlString .= '
            <Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero .
                '</IdentificacionExtranjero>';
        }

        if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
            $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
        }

        if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
            $xmlString .= '
                <Ubicacion>
                    <Provincia>' . $receptorProvincia . '</Provincia>
                    <Canton>' . $receptorCanton . '</Canton>
                    <Distrito>' . $receptorDistrito . '</Distrito>';
            if ($receptorBarrio != '') {
                $xmlString .= '
                    <Barrio>' . $receptorBarrio . '</Barrio>';
            }
            $xmlString .= '
                    <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                </Ubicacion>';
        }

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
            $xmlString .= '
            <OtrasSenasExtranjero>'
                . $receptorOtrasSenasExtranjero .
                '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorEmail != '') {
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
        }

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
    <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
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
    foreach ($detalles as $d) {
        $xmlString .= '<LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->partidaArancelaria) && $d->partidaArancelaria != "") {
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';
        }

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5) {
                error_log("codigoComercial: " . count($d->codigoComercial) . " is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0) {
            foreach ($d->codigoComercialLinea as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
        if (isset($c->codigo) && $c->codigo != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
        $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5) {
                error_log("descuento: " . count($d->descuento) . " is greater than 5");
            }
            $d->descuento = array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0) {
            foreach ($d->descuentoLinea as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->IVACobradoFabrica) && $d->IVACobradoFabrica != "") {
            $xmlString .= '<IVACobradoFabrica>' . $IVACobradoFabrica . '</IVACobradoFabrica>';
        }

        if (isset($d->baseImponible) && $d->baseImponible != "") {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>';

                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->montoExportacion) && $i->montoExportacion != "") {
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';
                }

                if (isset($i->exoneracion) && $i->exoneracion != "") {
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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if (isset($otrosCargos) && $otrosCargos != "") {
        foreach ($otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            if (isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "") {
                $xmlString .= '
                <NumeroIdentidadTercero>' . $o->numeroIdentidadTercero . '</NumeroIdentidadTercero>';
            }
            if (isset($o->nombreTercero) && $o->nombreTercero != "") {
                $xmlString .= '
                <NombreTercero>' . $o->nombreTercero . '</NombreTercero>';
            }
            //if ( isset($o->detalle) && $o->detalle != "")
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            //if ( isset($o->montoCargo) && $o->montoCargo != "")
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalServExonerados != '') {
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
    }

    if ($totalServNoSujeto != '') {
        $xmlString .= '
        <TotalServNoSujeto>' . $totalServNoSujeto . '</TotalServNoSujeto>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalMercExonerada != '') {
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
    }

    if ($totalMercNoSujeta != '') {
        $xmlString .= '
        <TotalMercNoSujeta>' . $totalMercNoSujeta . '</TotalMercNoSujeta>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    if ($totalExonerado != '') {
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';
    }

    if ($totalNoSujeto != '') {
        $xmlString .= '
        <TotalNoSujeto>' . $totalNoSujeto . '</TotalNoSujeto>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if ($totalIVADevuelto != '') {
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    $xmlString .= '
    <InformacionReferencia>';
    if (in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true)) {
        $xmlString .= '
        <TipoDocIR>' . $infoRefeTipoDoc . '</TipoDocIR>';
    } else {
        grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = " . $infoRefeTipoDoc);
        return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
    }

    if ($infoRefeTipoDoc === '99') {
        if (isset($infoRefeTipoDocOtro) && strlen($infoRefeTipoDocOtro) >= 5 && strlen($infoRefeTipoDocOtro) <= 100) {
            $xmlString .= '
        <TipoDocRefOTRO>' . htmlspecialchars($infoRefeTipoDocOtro) . '</TipoDocRefOTRO>';
        } else {
            grace_error("El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida. infoRefeTipoDocOtro = " . $infoRefeTipoDocOtro);
            return "El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida.";
        }
    }

    if (isset($infoRefeNumero) && $infoRefeNumero != "") {
        $xmlString .= '
        <Numero>' . $infoRefeNumero . '</Numero>';
    }

    $xmlString .= '
        <FechaEmisionIR>' . $infoRefeFechaEmision . '</FechaEmisionIR>';

    if (isset($infoRefeCodigo) && $infoRefeCodigo != "") {
        if (in_array($infoRefeCodigo, CODIDOREFVALUES, true)) {
            $xmlString .= '
        <Codigo>' . $infoRefeCodigo . '</Codigo>';
        } else {
            grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = " . $infoRefeCodigo);
            return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
        }
    }

    if ($infoRefeCodigo === '99') {
        if (isset($infoRefeCodigoOtro) && strlen($infoRefeCodigoOtro) >= 5 && strlen($infoRefeCodigoOtro) <= 100) {
            $xmlString .= '
        <CodigoReferenciaOTRO>' . htmlspecialchars($infoRefeCodigoOtro) . '</CodigoReferenciaOTRO>';
        } else {
            grace_error("El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida. infoRefeCodigoOTRO = " . $infoRefeCodigoOtro);
            return "El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida.";
        }
    }

    if (isset($infoRefeRazon) && $infoRefeRazon != "") {
        $xmlString .= '
        <Razon>' . $infoRefeRazon . '</Razon>';
    }

    $xmlString .= '
    </InformacionReferencia>';

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

    $xmlString .= '
    </NotaCreditoElectronica>';

    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLND()
{

    // Datos contribuyente
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $codigoActividadReceptor = params_get("codigo_actividad_receptor");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email");
    $registroFiscal8707 = params_get("registrofiscal8707");

    // Datos receptor
    $omitir_receptor = params_get("omitir_receptor");        // Deprecated
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalServExonerados = params_get("total_serv_exonerados");
    $totalServNoSujeto = params_get("total_serv_no_sujeto");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalMercExonerada = params_get("total_merc_exonerada");
    $totalMercNoSujeta = params_get("total_merc_no_sujeta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalExonerado = params_get("total_exonerado");
    $totalNoSujeto = params_get("total_no_sujeto");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");
    $totalIVADevuelto = params_get("totalIVADevuelto");
    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");
    $otros = params_get("otros");
    $otrosType = params_get("otrosType");
    $infoRefeTipoDoc = params_get("infoRefeTipoDoc");
    $infoRefeTipoDocOtro = params_get("infoRefeTipoDocOTRO");
    $infoRefeNumero = params_get("infoRefeNumero");
    $infoRefeFechaEmision = params_get("infoRefeFechaEmision");
    $infoRefeCodigo = params_get("infoRefeCodigo");
    $infoRefeCodigoOtro = params_get("infoRefeCodigoOTRO");
    $infoRefeRazon = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $mediosPago = json_decode(params_get("medios_pago"));
    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($mediosPago) && $mediosPago != "") {
        grace_debug(params_get("medios_pago"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenas);
    }

    if (isset($otrosCargos) && $otrosCargos != "") {
        if (count($otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos) . " is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }
    }

    if (isset($mediosPago) && $mediosPago != "") {
        if (count($mediosPago) > 4) {
            error_log("medios_pago: " . count($mediosPago) . " is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }
    }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <NotaDebitoElectronica
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/notaDebitoElectronica"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
    <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>';

    if (isset($codigoActividadReceptor) && $codigoActividadReceptor != "") {
        $codigoActividadReceptor = str_pad($codigoActividadReceptor, 6, "0", STR_PAD_LEFT);
        if (strlen($codigoActividadReceptor) != CODIGOACTIVIDADSIZE) {
            error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadReceptor is " . $codigoActividadReceptor);
        }

        $xmlString .= '
        <CodigoActividadReceptor>' . $codigoActividadReceptor . '</CodigoActividadReceptor>';
    }

    $xmlString .= '
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if (preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }

    if ($omitir_receptor != 'true') {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '') {
            $xmlString .= '<Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero .
                '</IdentificacionExtranjero>';
        }

        if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
            $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
        }

        if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
            $xmlString .= '
                <Ubicacion>
                    <Provincia>' . $receptorProvincia . '</Provincia>
                    <Canton>' . $receptorCanton . '</Canton>
                    <Distrito>' . $receptorDistrito . '</Distrito>';
            if ($receptorBarrio != '') {
                $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
            }
            $xmlString .= '
                    <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                </Ubicacion>';
        }

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
            $xmlString .= '
            <OtrasSenasExtranjero>'
                . $receptorOtrasSenasExtranjero .
                '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorEmail != '') {
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
        }

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
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
    foreach ($detalles as $d) {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';
        if (isset($d->partidaArancelaria) && $d->partidaArancelaria != "") {
            $xmlString .= '<PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';
        }

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5) {
                error_log("codigoComercial: " . count($d->codigoComercial) . " is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0) {
            foreach ($d->codigoComercialLinea as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
        if (isset($c->codigo) && $c->codigo != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
        $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5) {
                error_log("descuento: " . count($d->descuento) . " is greater than 5");
            }
            $d->descuento = array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0) {
            foreach ($d->descuentoLinea as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->IVACobradoFabrica) && $d->IVACobradoFabrica != "") {
            $xmlString .= '<IVACobradoFabrica>' . $IVACobradoFabrica . '</IVACobradoFabrica>';
        }

        if (isset($d->baseImponible) && $d->baseImponible != "") {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '<Impuesto>
                <Codigo>' . $i->codigo . '</Codigo>';

                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->montoExportacion) && $i->montoExportacion != "") {
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';
                }

                if (isset($i->exoneracion) && $i->exoneracion != "") {
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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if (isset($otrosCargos) && $otrosCargos != "") {
        foreach ($otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            if (isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "") {
                $xmlString .= '
                <NumeroIdentidadTercero>' . $o->numeroIdentidadTercero . '</NumeroIdentidadTercero>';
            }
            if (isset($o->nombreTercero) && $o->nombreTercero != "") {
                $xmlString .= '
                <NombreTercero>' . $o->nombreTercero . '</NombreTercero>';
            }
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalServExonerados != '') {
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
    }

    if ($totalServNoSujeto != '') {
        $xmlString .= '
        <TotalServNoSujeto>' . $totalServNoSujeto . '</TotalServNoSujeto>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalMercExonerada != '') {
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
    }

    if ($totalMercNoSujeta != '') {
        $xmlString .= '
        <TotalMercNoSujeta>' . $totalMercNoSujeta . '</TotalMercNoSujeta>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    if ($totalExonerado != '') {
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';
    }

    if ($totalNoSujeto != '') {
        $xmlString .= '
        <TotalNoSujeto>' . $totalNoSujeto . '</TotalNoSujeto>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if ($totalIVADevuelto != '') {
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    $xmlString .= '
    <InformacionReferencia>';

    if (in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true)) {
        $xmlString .= '
        <TipoDocIR>' . $infoRefeTipoDoc . '</TipoDocIR>';
    } else {
        grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = " . $infoRefeTipoDoc);
        return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
    }

    if ($infoRefeTipoDoc === '99') {
        if (isset($infoRefeTipoDocOtro) && strlen($infoRefeTipoDocOtro) >= 5 && strlen($infoRefeTipoDocOtro) <= 100) {
            $xmlString .= '
        <TipoDocRefOTRO>' . htmlspecialchars($infoRefeTipoDocOtro) . '</TipoDocRefOTRO>';
        } else {
            grace_error("El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida. infoRefeTipoDocOtro = " . $infoRefeTipoDocOtro);
            return "El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida.";
        }
    }

    if (isset($infoRefeNumero) && $infoRefeNumero != "") {
        $xmlString .= '
        <Numero>' . $infoRefeNumero . '</Numero>';
    }

    $xmlString .= '
        <FechaEmisionIR>' . $infoRefeFechaEmision . '</FechaEmisionIR>';

    if (isset($infoRefeCodigo) && $infoRefeCodigo != "") {
        if (in_array($infoRefeCodigo, CODIDOREFVALUES, true)) {
            $xmlString .= '
        <Codigo>' . $infoRefeCodigo . '</Codigo>';
        } else {
            grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = " . $infoRefeCodigo);
            return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
        }
    }

    if ($infoRefeCodigo === '99') {
        if (isset($infoRefeCodigoOtro) && strlen($infoRefeCodigoOtro) >= 5 && strlen($infoRefeCodigoOtro) <= 100) {
            $xmlString .= '
        <CodigoReferenciaOTRO>' . htmlspecialchars($infoRefeCodigoOtro) . '</CodigoReferenciaOTRO>';
        } else {
            grace_error("El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida. infoRefeCodigoOTRO = " . $infoRefeCodigoOtro);
            return "El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida.";
        }
    }

    if (isset($infoRefeRazon) && $infoRefeRazon != "") {
        $xmlString .= '
        <Razon>' . $infoRefeRazon . '</Razon>';
    }

    $xmlString .= '
    </InformacionReferencia>';

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

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
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email");
    $registroFiscal8707 = params_get("registrofiscal8707");

    // Datos receptor
    $omitir_receptor = params_get("omitir_receptor");        // Deprecated
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorOtrasSenasExtranjero = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalServExonerados = params_get("total_serv_exonerados");
    $totalServNoSujeto = params_get("total_serv_no_sujeto");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalMercExonerada = params_get("total_merc_exonerada");
    $totalMercNoSujeta = params_get("total_merc_no_sujeta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalExonerado = params_get("total_exonerado");
    $totalNoSujeto = params_get("total_no_sujeto");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");
    $totalIVADevuelto = params_get("totalIVADevuelto");
    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");

    $otros = params_get("otros");
    $otrosType = params_get("otrosType");
    $infoRefeTipoDoc = params_get("infoRefeTipoDoc");
    $infoRefeTipoDocOtro = params_get("infoRefeTipoDocOTRO");
    $infoRefeNumero = params_get("infoRefeNumero");
    $infoRefeFechaEmision = params_get("infoRefeFechaEmision");
    $infoRefeCodigo = params_get("infoRefeCodigo");
    $infoRefeCodigoOtro = params_get("infoRefeCodigoOTRO");
    $infoRefeRazon = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $mediosPago = json_decode(params_get("medios_pago"));
    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    grace_debug(params_get("detalles"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($mediosPago) && $mediosPago != "") {
        grace_debug(params_get("medios_pago"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenas);
    }

    if (isset($otrosCargos) && $otrosCargos != "") {
        if (count($otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos) . " is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }
    }

    if (isset($mediosPago) && $mediosPago != "") {
        if (count($mediosPago) > 4) {
            error_log("medios_pago: " . count($mediosPago) . " is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }
    }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <TiqueteElectronico
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/tiqueteElectronico"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
    <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>
    <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
    <FechaEmision>' . $fechaEmision . '</FechaEmision>
    <Emisor>
        <Nombre>' . $emisorNombre . '</Nombre>
        <Identificacion>
            <Tipo>' . $emisorTipoIdentif . '</Tipo>
            <Numero>' . $emisorNumIdentif . '</Numero>
        </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
        <Telefono>
            <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
            <NumTelefono>' . $emisorTel . '</NumTelefono>
        </Telefono>';
    }

    if (preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }

    if ($omitir_receptor != 'true') {
        $xmlString .= '<Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>';

        if ($receptorTipoIdentif != '' && $receptorNumIdentif != '') {
            $xmlString .= '
            <Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $receptorNumIdentif . '</Numero>
            </Identificacion>';
        }

        if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
            $xmlString .= '
            <IdentificacionExtranjero>'
                . $receptorIdentifExtranjero .
                '</IdentificacionExtranjero>';
        }

        if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
            $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
        }

        if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
            $xmlString .= '
                <Ubicacion>
                    <Provincia>' . $receptorProvincia . '</Provincia>
                    <Canton>' . $receptorCanton . '</Canton>
                    <Distrito>' . $receptorDistrito . '</Distrito>';
            if ($receptorBarrio != '') {
                $xmlString .= '
                    <Barrio>' . $receptorBarrio . '</Barrio>';
            }
            $xmlString .= '
                    <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
                </Ubicacion>';
        }

        if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
            $xmlString .= '
            <OtrasSenasExtranjero>'
                . $receptorOtrasSenasExtranjero .
                '</OtrasSenasExtranjero>';
        }

        if ($receptorCodPaisTel != '' && $receptorTel != '') {
            $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
        }

        if ($receptorEmail != '') {
            $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
        }

        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
    <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
    }

    $xmlString .= '<DetalleServicio>';

    // cant - unidad medida - detalle - precio unitario - monto total - subtotal - monto total linea - Monto desc -Naturaleza Desc - Impuesto : Codigo / Tarifa / Monto

    /* EJEMPLO DE DETALLES
      {
        "1":["1","Sp","Honorarios","100000","100000","100000","100000","1000","Pronto pago",{"Imp": [{"cod": 122,"tarifa": 1,"monto": 100},{"cod": 133,"tarifa": 1,"monto": 1300}]}],
        "2":["1","Sp","Honorarios","100000","100000","100000","100000"]
      }
     */

    $l = 1;
    foreach ($detalles as $d) {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5) {
                error_log("codigoComercial: " . count($d->codigoComercial) . " is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0) {
            foreach ($d->codigoComercialLinea as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
        if (isset($c->codigo) && $c->codigo != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
        $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5) {
                error_log("descuento: " . count($d->descuento) . " is greater than 5");
            }
            $d->descuento = array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0) {
            foreach ($d->descuentoLinea as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->IVACobradoFabrica) && $d->IVACobradoFabrica != "") {
            $xmlString .= '<IVACobradoFabrica>' . $IVACobradoFabrica . '</IVACobradoFabrica>';
        }

        if (isset($d->baseImponible) && $d->baseImponible != "") {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }
        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '
                    <CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '
                    <Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '
                    <FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                $xmlString .= '
                    <Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "") {
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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';

    //OtrosCargos
    if (isset($otrosCargos) && $otrosCargos != "") {
        foreach ($otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            if (isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "") {
                $xmlString .= '
                <NumeroIdentidadTercero>' . $o->numeroIdentidadTercero . '</NumeroIdentidadTercero>';
            }
            if (isset($o->nombreTercero) && $o->nombreTercero != "") {
                $xmlString .= '
                <NombreTercero>' . $o->nombreTercero . '</NombreTercero>';
            }
            //if ( isset($o->detalle) && $o->detalle != "")
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            //if ( isset($o->montoCargo) && $o->montoCargo != "")
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalServExonerados != '') {
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
    }

    if ($totalServNoSujeto != '') {
        $xmlString .= '
        <TotalServNoSujeto>' . $totalServNoSujeto . '</TotalServNoSujeto>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalMercExonerada != '') {
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
    }

    if ($totalMercNoSujeta != '') {
        $xmlString .= '
        <TotalMercNoSujeta>' . $totalMercNoSujeta . '</TotalMercNoSujeta>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    if ($totalExonerado != '') {
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';
    }

    if ($totalNoSujeto != '') {
        $xmlString .= '
        <TotalNoSujeto>' . $totalNoSujeto . '</TotalNoSujeto>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if ($totalIVADevuelto != '') {
        $xmlString .= '
        <TotalIVADevuelto>' . $totalIVADevuelto . '</TotalIVADevuelto>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != '') {

        $xmlString .= '
    <InformacionReferencia>';
        if (in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true)) {
            $xmlString .= '
            <TipoDocIR>' . $infoRefeTipoDoc . '</TipoDocIR>';
        } else {
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = " . $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ($infoRefeTipoDoc === '99') {
            if (isset($infoRefeTipoDocOtro) && strlen($infoRefeTipoDocOtro) >= 5 && strlen($infoRefeTipoDocOtro) <= 100) {
                $xmlString .= '
        <TipoDocRefOTRO>' . htmlspecialchars($infoRefeTipoDocOtro) . '</TipoDocRefOTRO>';
            } else {
                grace_error("El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida. infoRefeTipoDocOtro = " . $infoRefeTipoDocOtro);
                return "El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeNumero) && $infoRefeNumero != "") {
            $xmlString .= '
        <Numero>' . $infoRefeNumero . '</Numero>';
        }

        $xmlString .= '
        <FechaEmisionIR>' . $infoRefeFechaEmision . '</FechaEmisionIR>';

        if (isset($infoRefeCodigo) && $infoRefeCodigo != "") {
            if (in_array($infoRefeCodigo, CODIDOREFVALUES, true)) {
                $xmlString .= '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            } else {
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = " . $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ($infoRefeCodigo === '99') {
            if (isset($infoRefeCodigoOtro) && strlen($infoRefeCodigoOtro) >= 5 && strlen($infoRefeCodigoOtro) <= 100) {
                $xmlString .= '
        <CodigoReferenciaOTRO>' . htmlspecialchars($infoRefeCodigoOtro) . '</CodigoReferenciaOTRO>';
            } else {
                grace_error("El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida. infoRefeCodigoOTRO = " . $infoRefeCodigoOtro);
                return "El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeRazon) && $infoRefeRazon != "") {
            $xmlString .= '
        <Razon>' . $infoRefeRazon . '</Razon>';
        }

        $xmlString .= '
    </InformacionReferencia>';

    }

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

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

    $clave = params_get("clave");                                      // d{50,50}
    // Datos vendedor = emisor
    $numeroCedulaEmisor = params_get("numero_cedula_emisor");                       // d{12,12} cedula fisica,juridica,NITE,DIMEX
    $numeroCedulaEmisor = str_pad($numeroCedulaEmisor, 12, "0", STR_PAD_LEFT);

    // Datos mensaje receptor
    $fechaEmisionDoc = params_get("fecha_emision_doc");                          // fecha de emision de la confirmacion
    $mensaje = params_get("mensaje");                                    // 1 - Aceptado, 2 - Aceptado Parcialmente, 3 - Rechazado
    $detalleMensaje = params_get("detalle_mensaje");
    $montoTotalImpuesto = params_get("monto_total_impuesto");                       // d18,5 opcional /obligatorio si comprobante tenga impuesto
    $codigoActividad = params_get("codigo_actividad");                            // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $totalFactura = params_get("total_factura");                              // d18,5
    $numeroConsecutivoReceptor = params_get("numero_consecutivo_receptor");                // d{20,20} numeracion consecutiva de los mensajes de confirmacion

    // Datos comprador = receptor
    $numeroCedulaReceptor = params_get("numero_cedula_receptor");                     // d{12,12}cedula fisica, juridica, NITE, DIMEX del comprador
    $numeroCedulaReceptor = str_pad($numeroCedulaReceptor, 12, "0", STR_PAD_LEFT);

    // Validate string sizes
    $codigoActividad = str_pad($codigoActividad, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividad) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize: " . CODIGOACTIVIDADSIZE . " is not codigoActividad: " . $codigoActividad);
    }

    $xmlString = '<?xml version="1.0" encoding="utf-8"?>
    <MensajeReceptor
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/mensajeReceptor"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <Clave>' . $clave . '</Clave>
    <NumeroCedulaEmisor>' . $numeroCedulaEmisor . '</NumeroCedulaEmisor>
    <FechaEmisionDoc>' . $fechaEmisionDoc . '</FechaEmisionDoc>
    <Mensaje>' . $mensaje . '</Mensaje>';
    if (!empty($detalleMensaje)) {
        $xmlString .= '<DetalleMensaje>' . $detalleMensaje . '</DetalleMensaje>';
    }

    if (!empty($montoTotalImpuesto)) {
        $xmlString .= '<MontoTotalImpuesto>' . $montoTotalImpuesto . '</MontoTotalImpuesto>';
    }
    $xmlString .= '<CodigoActividad>' . $codigoActividad . '</CodigoActividad>
    <TotalFactura>' . $totalFactura . '</TotalFactura>
    <NumeroCedulaReceptor>' . $numeroCedulaReceptor . '</NumeroCedulaReceptor>
    <NumeroConsecutivoReceptor>' . $numeroConsecutivoReceptor . '</NumeroConsecutivoReceptor>';

    $xmlString .= '</MensajeReceptor>';
    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLFec()
{
    // Datos contribuyente
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $codigoActividadReceptor = params_get("codigo_actividad_receptor");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    // Datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorOtrasSenasExtranjero = params_get("emisor_otras_senas_extranjero");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email");
    $registroFiscal8707 = params_get("registrofiscal8707");

    // Datos receptor
    $omitir_receptor = params_get("omitir_receptor");        // Deprecated
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    // Detalles de tiquete / Factura
    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalServExonerados = params_get("total_serv_exonerados");
    $totalServNoSujeto = params_get("total_serv_no_sujeto");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalMercExonerada = params_get("total_merc_exonerada");
    $totalMercNoSujeta = params_get("total_merc_no_sujeta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalExonerado = params_get("total_exonerado");
    $totalNoSujeto = params_get("total_no_sujeto");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");

    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");
    $otros = params_get("otros");
    $otrosType = params_get("otrosType");
    $infoRefeTipoDoc = params_get("infoRefeTipoDoc");
    $infoRefeTipoDocOtro = params_get("infoRefeTipoDocOTRO");
    $infoRefeNumero = params_get("infoRefeNumero");
    $infoRefeFechaEmision = params_get("infoRefeFechaEmision");
    $infoRefeCodigo = params_get("infoRefeCodigo");
    $infoRefeCodigoOtro = params_get("infoRefeCodigoOTRO");
    $infoRefeRazon = params_get("infoRefeRazon");

    // Detalles de la compra
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $mediosPago = json_decode(params_get("medios_pago"));
    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    grace_debug(params_get("detalles"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($mediosPago) && $mediosPago != "") {
        grace_debug(params_get("medios_pago"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    $codigoActividadReceptor = str_pad($codigoActividadReceptor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadReceptor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadReceptor is " . $codigoActividadReceptor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenas) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenas);
    }

    if (isset($otrosCargos) && $otrosCargos != "") {
        if (count($otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos) . " is greater than 15");
            //Delimita el array a solo 15 elementos
            $otrosCargos = array_slice($otrosCargos, 0, 15);
        }
    }

    if (isset($mediosPago) && $mediosPago != "") {
        if (count($mediosPago) > 4) {
            error_log("mediosPago: " . count($mediosPago) . " is greater than 4");
            //Delimita el array a solo 4 elementos
            $mediosPago = array_slice($mediosPago, 0, 4);
        }
    }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronicaCompra
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaCompra"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Clave>' . $clave . '</Clave>
        <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
        <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>
        <CodigoActividadReceptor>' . $codigoActividadReceptor . '</CodigoActividadReceptor>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . $fechaEmision . '</FechaEmision>
        <Emisor>
            <Nombre>' . $emisorNombre . '</Nombre>
            <Identificacion>
                <Tipo>' . $emisorTipoIdentif . '</Tipo>
                <Numero>' . $emisorNumIdentif . '</Numero>
            </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
         </Ubicacion>';
    }

    if ($emisorOtrasSenasExtranjero != '' && strlen($emisorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
        $xmlString .= '
        <OtrasSenasExtranjero>' . $emisorOtrasSenasExtranjero . '</OtrasSenasExtranjero>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $emisorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($emisorEmail != '' && preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }


    $xmlString .= '<Receptor>
        <Nombre>' . $receptorNombre . '</Nombre>';

    $xmlString .= '
        <Identificacion>
            <Tipo>' . $receptorTipoIdentif . '</Tipo>
            <Numero>' . $receptorNumIdentif . '</Numero>
        </Identificacion>';

    if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
        $xmlString .= '
            <IdentificacionExtranjero>'
            . $receptorIdentifExtranjero .
            '</IdentificacionExtranjero>';
    }

    if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
    }

    if ($receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
        $xmlString .= '
            <Ubicacion>
                <Provincia>' . $receptorProvincia . '</Provincia>
                <Canton>' . $receptorCanton . '</Canton>
                <Distrito>' . $receptorDistrito . '</Distrito>';
        if ($receptorBarrio != '') {
            $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($receptorCodPaisTel != '' && $receptorTel != '') {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorEmail != '') {
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
    }

    $xmlString .= '</Receptor>';

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
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
    foreach ($detalles as $d) {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

        if (isset($d->codigoComercial) && is_string($d->codigoComercial) && strlen($d->codigoComercial) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->codigoComercial) > 5) {
                error_log("codigoComercial: " . count($d->codigoComercial) . " is greater than 5");
            }
            $d->codigoComercial = array_slice($d->codigoComercial, 0, 5);
            foreach ($d->codigoComercial as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        if (isset($d->codigoComercialLinea) && $d->codigoComercialLinea != "" && $d->codigoComercialLinea != 0) {
            foreach ($d->codigoComercialLinea as $c) {
                if (isset($c->tipo) && $c->tipo != "" && isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                    <CodigoComercial>
                        <Tipo>' . $c->tipo . '</Tipo>';
                }
                if (isset($c->codigo) && $c->codigo != "") {
                    $xmlString .= '
                        <Codigo>' . $c->codigo . '</Codigo>';
                }
                $xmlString .= '
                    </CodigoComercial>';
            }
        }

        $xmlString .= '
            <Cantidad>' . $d->cantidad . '</Cantidad>
            <UnidadMedida>' . $d->unidadMedida . '</UnidadMedida>';
        if (isset($c->codigo) && $c->codigo != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
        $xmlString .= '
            <Detalle>' . $d->detalle . '</Detalle>
            <PrecioUnitario>' . $d->precioUnitario . '</PrecioUnitario>
            <MontoTotal>' . $d->montoTotal . '</MontoTotal>';

        if (isset($d->descuento) && is_string($d->descuento) && strlen($d->descuento) != 0) {
            //Delimita el array a solo 5 elementos
            if (count($d->descuento) > 5) {
                error_log("descuento: " . count($d->descuento) . " is greater than 5");
            }
            $d->descuento = array_slice($d->descuento, 0, 5);
            foreach ($d->descuento as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '<Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        if (isset($d->descuentoLinea) && $d->descuentoLinea != "" && $d->descuentoLinea != 0) {
            foreach ($d->descuentoLinea as $dsc) {
                if (isset($dsc->montoDescuento) && $dsc->montoDescuento != "" && isset($dsc->naturalezaDescuento) && $dsc->naturalezaDescuento != "") {
                    $xmlString .= '
                    <Descuento>
                        <MontoDescuento>' . $dsc->montoDescuento . '</MontoDescuento>
                        <NaturalezaDescuento>' . $dsc->naturalezaDescuento . '</NaturalezaDescuento>
                    </Descuento>';
                }
            }
        }

        $xmlString .= '<SubTotal>' . $d->subTotal . '</SubTotal>';

        if (isset($d->baseImponible) && $d->baseImponible != "") {
            $xmlString .= '<BaseImponible>' . $d->baseImponible . '</BaseImponible>';
        }

        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '
                <Impuesto>
                    <Codigo>' . $i->codigo . '</Codigo>';
                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                $xmlString .= '<Monto>' . $i->monto . '</Monto>';

                if (isset($i->exoneracion) && $i->exoneracion != "") {
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

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
            $xmlString .= '<ImpuestoNeto>' . $d->impuestoNeto . '</ImpuestoNeto>';
        }
        $xmlString .= '<MontoTotalLinea>' . $d->montoTotalLinea . '</MontoTotalLinea>';
        $xmlString .= '</LineaDetalle>';
        $l++;
    }

    $xmlString .= '</DetalleServicio>';
    //OtrosCargos
    if (isset($otrosCargos) && $otrosCargos != "") {
        foreach ($otrosCargos as $o) {
            $xmlString .= '
            <OtrosCargos>
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            if (isset($o->numeroIdentidadTercero) && $o->numeroIdentidadTercero != "") {
                $xmlString .= '
                <NumeroIdentidadTercero>' . $o->numeroIdentidadTercero . '</NumeroIdentidadTercero>';
            }
            if (isset($o->nombreTercero) && $o->nombreTercero != "") {
                $xmlString .= '
                <NombreTercero>' . $o->nombreTercero . '</NombreTercero>';
            }
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
            $xmlString .= '
            </OtrosCargos>';
        }
    }

    $xmlString .= '
    <ResumenFactura>';

    if ($codMoneda != '' && $codMoneda != 'CRC' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalServExonerados != '') {
        $xmlString .= '
        <TotalServExonerado>' . $totalServExonerados . '</TotalServExonerado>';
    }

    if ($totalServNoSujeto != '') {
        $xmlString .= '
        <TotalServNoSujeto>' . $totalServNoSujeto . '</TotalServNoSujeto>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalMercExonerada != '') {
        $xmlString .= '
        <TotalMercExonerada>' . $totalMercExonerada . '</TotalMercExonerada>';
    }

    if ($totalMercNoSujeta != '') {
        $xmlString .= '
        <TotalMercNoSujeta>' . $totalMercNoSujeta . '</TotalMercNoSujeta>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    if ($totalExonerado != '') {
        $xmlString .= '
        <TotalExonerado>' . $totalExonerado . '</TotalExonerado>';
    }

    if ($totalNoSujeto != '') {
        $xmlString .= '
        <TotalNoSujeto>' . $totalNoSujeto . '</TotalNoSujeto>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }

    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    if ($infoRefeTipoDoc != '' && $infoRefeFechaEmision != '') {

        $xmlString .= '
    <InformacionReferencia>';

        if (in_array($infoRefeTipoDoc, TIPODOCREFVALUES, true)) {
            $xmlString .= '
        <TipoDocIR>' . $infoRefeTipoDoc . '</TipoDocIR>';
        } else {
            grace_error("El parámetro infoRefeTipoDoc no cumple con la estructura establecida. infoRefeTipoDoc = " . $infoRefeTipoDoc);
            return "El parámetro infoRefeTipoDoc no cumple con la estructura establecida.";
        }

        if ($infoRefeTipoDoc === '99') {
            if (isset($infoRefeTipoDocOtro) && strlen($infoRefeTipoDocOtro) >= 5 && strlen($infoRefeTipoDocOtro) <= 100) {
                $xmlString .= '
        <TipoDocRefOTRO>' . htmlspecialchars($infoRefeTipoDocOtro) . '</TipoDocRefOTRO>';
            } else {
                grace_error("El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida. infoRefeTipoDocOtro = " . $infoRefeTipoDocOtro);
                return "El parámetro infoRefeTipoDocOtro no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeNumero) && $infoRefeNumero != "") {
            $xmlString .= '
        <Numero>' . $infoRefeNumero . '</Numero>';
        }

        $xmlString .= '
        <FechaEmisionIR>' . $infoRefeFechaEmision . '</FechaEmisionIR>';

        if (isset($infoRefeCodigo) && $infoRefeCodigo != "") {
            if (in_array($infoRefeCodigo, CODIDOREFVALUES, true)) {
                $xmlString .= '
            <Codigo>' . $infoRefeCodigo . '</Codigo>';
            } else {
                grace_error("El parámetro infoRefeCodigo no cumple con la estructura establecida. infoRefeCodigo = " . $infoRefeCodigo);
                return "El parámetro infoRefeCodigo no cumple con la estructura establecida.";
            }
        }

        if ($infoRefeCodigo === '99') {
            if (isset($infoRefeCodigoOtro) && strlen($infoRefeCodigoOtro) >= 5 && strlen($infoRefeCodigoOtro) <= 100) {
                $xmlString .= '
        <CodigoReferenciaOTRO>' . htmlspecialchars($infoRefeCodigoOtro) . '</CodigoReferenciaOTRO>';
            } else {
                grace_error("El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida. infoRefeCodigoOTRO = " . $infoRefeCodigoOtro);
                return "El parámetro infoRefeCodigoOTRO no cumple con la longitud establecida.";
            }
        }

        if (isset($infoRefeRazon) && $infoRefeRazon != "") {
            $xmlString .= '
        <Razon>' . $infoRefeRazon . '</Razon>';
        }

        $xmlString .= '
    </InformacionReferencia>';

    }

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

    $xmlString .= '
    </FacturaElectronicaCompra>';
    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}

function genXMLFee()
{
    $clave = params_get("clave");
    $proveedorSistemas = params_get("proveedor_sistemas");
    $codigoActividadEmisor = params_get("codigo_actividad_emisor");        // https://cloud-cube.s3.amazonaws.com/sp5z9nxkd1ra/public/assets/json/actividades_por_codigo.json
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");

    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_identif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $emisorNombreComercial = params_get("emisor_nombre_comercial");
    $emisorProv = params_get("emisor_provincia");
    $emisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorEmail = params_get("emisor_email");
    $registroFiscal8707 = params_get("registrofiscal8707");

    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $receptorNumIdentif = params_get("receptor_num_identif");
    $receptorIdentifExtranjero = params_get("receptor_identif_extranjero");
    $receptorNombreComercial = params_get("receptor_nombre_comercial");
    $receptorOtrasSenasExtranjero = params_get("receptor_otras_senas_extranjero");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorEmail = params_get("receptor_email");

    $condVenta = params_get("condicion_venta");
    $condVentaOtros = params_get("condicion_venta_otros");
    $plazoCredito = params_get("plazo_credito");
    $detalles = json_decode(params_get("detalles"));
    $otrosCargos = json_decode(params_get("otrosCargos"));
    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");

    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalGravados = params_get("total_gravados");
    $totalExento = params_get("total_exento");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalImpAsumidoEmisorFabrica = params_get("total_impuestos_asumidos_fabrica");
    $totalOtrosCargos = params_get("totalOtrosCargos");
    $totalComprobante = params_get("total_comprobante");

    $informacionReferencia = json_decode(params_get("informacionReferencia"));
    $otros = json_decode(params_get("otros"));
    // Resumen
    $totalDesgloseImpuesto = json_decode(params_get("totalDesgloseImpuesto"));

    grace_debug(params_get("detalles"));

    if (isset($otrosCargos) && $otrosCargos != "") {
        grace_debug(params_get("otrosCargos"));
    }

    if (isset($totalDesgloseImpuesto) && $totalDesgloseImpuesto != "") {
        grace_debug(params_get("totalDesgloseImpuesto"));
    }

    // Validate string sizes
    $codigoActividadEmisor = str_pad($codigoActividadEmisor, 6, "0", STR_PAD_LEFT);
    if (strlen($codigoActividadEmisor) != CODIGOACTIVIDADSIZE) {
        error_log("codigoActividadSize is: " . CODIGOACTIVIDADSIZE . " and codigoActividadEmisor is " . $codigoActividadEmisor);
    }

    if (strlen($emisorNombre) > EMISORNOMBREMAXSIZE) {
        error_log("emisorNombreSize: " . EMISORNOMBREMAXSIZE . " is greater than emisorNombre: " . $emisorNombre);
    }

    if (strlen($receptorNombre) > RECEPTORNOMBREMAXSIZE) {
        error_log("receptorNombreMaxSize: " . RECEPTORNOMBREMAXSIZE . " is greater than receptorNombre: " . $receptorNombre);
    }

    if (strlen($receptorOtrasSenasExtranjero) > RECEPTOROTRASSENASMAXSIZE) {
        error_log("RECEPTOROTRASSENASEXTRANJEROMAXSIZE: " . RECEPTOROTRASSENASMAXSIZE . " is greater than receptorOtrasSenas: " . $receptorOtrasSenasExtranjero);
    }

    if (isset($otrosCargos) && !empty($otrosCargos)) {
        if (count($otrosCargos->otrosCargos) > 15) {
            error_log("otrosCargos: " . count($otrosCargos->otrosCargos) . " is greater than 15");
            //Delimita el array a solo 4 elementos
            $otrosCargos->otrosCargos = array_slice($otrosCargos->otrosCargos, 0, 15);
        }
    }

    $xmlString = '<?xml version = "1.0" encoding = "utf-8"?>
    <FacturaElectronicaExportacion
    xmlns="https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronicaExportacion"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Clave>' . $clave . '</Clave>
        <ProveedorSistemas>' . $proveedorSistemas . '</ProveedorSistemas>
        <CodigoActividadEmisor>' . $codigoActividadEmisor . '</CodigoActividadEmisor>
        <NumeroConsecutivo>' . $consecutivo . '</NumeroConsecutivo>
        <FechaEmision>' . $fechaEmision . '</FechaEmision>
        <Emisor>
            <Nombre>' . $emisorNombre . '</Nombre>
            <Identificacion>
                <Tipo>' . $emisorTipoIdentif . '</Tipo>
                <Numero>' . $emisorNumIdentif . '</Numero>
            </Identificacion>';

    if (isset($registroFiscal8707) && $registroFiscal8707 != "") {
        $xmlString .= '
        <Registrofiscal8707>' . $registroFiscal8707 . '</Registrofiscal8707>';
    }

    if (isset($emisorNombreComercial) && $emisorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $emisorNombreComercial . '</NombreComercial>';
    }

    if ($emisorProv != '' && $emisorCanton != '' && $emisorDistrito != '' && $emisorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $emisorProv . '</Provincia>
            <Canton>' . $emisorCanton . '</Canton>
            <Distrito>' . $emisorDistrito . '</Distrito>';
        if ($emisorBarrio != '') {
            $xmlString .= '<Barrio>' . $emisorBarrio . '</Barrio>';
        }
        $xmlString .= '
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>';
    }

    if ($emisorCodPaisTel != '' && $emisorTel != '' && $emisorTel >= EMISORNUMEROTELMIN && $emisorTel <= EMISORNUMEROTELMAX) {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $emisorTel . '</NumTelefono>
            </Telefono>';
    }

    if (preg_match(EMAIL_REGEX, trim($emisorEmail))) {
        $xmlString .= '<CorreoElectronico>' . trim($emisorEmail) . '</CorreoElectronico></Emisor>';
    } else {
        error_log(sprintf("Invalid email format: '%s' does not meet the regex pattern: %s", $emisorEmail, EMAIL_REGEX));
    }

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

    if ($receptorIdentifExtranjero != '' && $receptorIdentifExtranjero != '') {
        $xmlString .= '
        <IdentificacionExtranjero>'
            . $receptorIdentifExtranjero .
            '</IdentificacionExtranjero>';
    }

    if (isset($receptorNombreComercial) && $receptorNombreComercial != "") {
        $xmlString .= '
        <NombreComercial>' . $receptorNombreComercial . '</NombreComercial>';
    }

    if (isset($receptorProvincia) && $receptorProvincia != '' && $receptorCanton != '' && $receptorDistrito != '' && $receptorOtrasSenas != '') {
        $xmlString .= '
        <Ubicacion>
            <Provincia>' . $receptorProvincia . '</Provincia>
            <Canton>' . $receptorCanton . '</Canton>
            <Distrito>' . $receptorDistrito . '</Distrito>';
        if ($receptorBarrio != '') {
            $xmlString .= '<Barrio>' . $receptorBarrio . '</Barrio>';
        }
        $xmlString .= '
            <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
        </Ubicacion>';
    }

    if ($receptorOtrasSenasExtranjero != '' && strlen($receptorOtrasSenasExtranjero) <= RECEPTOROTRASSENASEXTRANJEROMAXSIZE) {
        $xmlString .= '
        <OtrasSenasExtranjero>'
            . $receptorOtrasSenasExtranjero .
            '</OtrasSenasExtranjero>';
    }


    if ($receptorCodPaisTel != '' && $receptorTel != '') {
        $xmlString .= '
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>';
    }

    if ($receptorEmail != '') {
        $xmlString .= '<CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>';
        $xmlString .= '</Receptor>';
    }

    $xmlString .= '
        <CondicionVenta>' . $condVenta . '</CondicionVenta>';

    if (isset($condVentaOtros) && $condVentaOtros != "") {
        $xmlString .= '
        <CondicionVentaOtros>' . $condVentaOtros . '</CondicionVentaOtros>';
    }

    if (isset($plazoCredito) && $plazoCredito != "") {
        $xmlString .= '
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>';
    }

    $xmlString .= '
        <DetalleServicio>';


    $l = 1;
    foreach ($detalles as $d) {
        $xmlString .= '
        <LineaDetalle>
            <NumeroLinea>' . $l . '</NumeroLinea>';

        if (isset($d->partidaArancelaria) && $d->partidaArancelaria != "") {
            $xmlString .= '
            <PartidaArancelaria>' . $d->partidaArancelaria . '</PartidaArancelaria>';
        }

        if (isset($d->codigo) && $d->codigo != "") {
            $xmlString .= '
            <Codigo>' . $d->codigo . '</Codigo>';
        }

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
        if (isset($d->unidadMedidaComercial) && $d->unidadMedidaComercial != "") {
            $xmlString .= '
                <UnidadMedidaComercial>' . $d->unidadMedidaComercial . '</UnidadMedidaComercial>';
        }
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

        if (isset($d->impuesto) && $d->impuesto != "") {
            foreach ($d->impuesto as $i) {
                $xmlString .= '
                <Impuesto>';
                if (isset($i->codigo) && $i->codigo != "") {
                    $xmlString .= '<Codigo>' . $i->codigo . '</Codigo>';
                }

                if (isset($i->codigoTarifa) && $i->codigoTarifa != "") {
                    $xmlString .= '<CodigoTarifa>' . $i->codigoTarifa . '</CodigoTarifa>';
                }

                if (isset($i->tarifa) && $i->tarifa != "") {
                    $xmlString .= '<Tarifa>' . $i->tarifa . '</Tarifa>';
                }

                if (isset($i->factorIVA) && $i->factorIVA != "") {
                    $xmlString .= '<FactorIVA>' . $i->factorIVA . '</FactorIVA>';
                }

                if (isset($i->monto) && $i->monto != "") {
                    $xmlString .= '<Monto>' . $i->monto . '</Monto>';
                }

                if (isset($i->montoExportacion) && $i->montoExportacion != "") {
                    $xmlString .= '<MontoExportacion>' . $i->montoExportacion . '</MontoExportacion>';
                }

                $xmlString .= '</Impuesto>';
            }
        }

        if (isset($d->impuestoNeto) && $d->impuestoNeto != "") {
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
                <TipoDocumento>' . $o->tipoDocumento . '</TipoDocumento>';
            $xmlString .= '
                <Detalle>' . $o->detalle . '</Detalle>';
            if (isset($o->porcentaje) && $o->porcentaje != "") {
                $xmlString .= '
                <Porcentaje>' . $o->porcentaje . '</Porcentaje>';
            }
            $xmlString .= '
                <MontoCargo>' . $o->montoCargo . '</MontoCargo>';
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

    if ($codMoneda != '' && $tipoCambio != '' && $tipoCambio != 0) {
        $xmlString .= '
        <CodigoTipoMoneda>
            <CodigoMoneda>' . $codMoneda . '</CodigoMoneda>
            <TipoCambio>' . $tipoCambio . '</TipoCambio>
        </CodigoTipoMoneda>';
    }

    if ($totalServGravados != '') {
        $xmlString .= '
        <TotalServGravados>' . $totalServGravados . '</TotalServGravados>';
    }

    if ($totalServExentos != '') {
        $xmlString .= '
        <TotalServExentos>' . $totalServExentos . '</TotalServExentos>';
    }

    if ($totalMercGravadas != '') {
        $xmlString .= '
        <TotalMercanciasGravadas>' . $totalMercGravadas . '</TotalMercanciasGravadas>';
    }

    if ($totalMercExentas != '') {
        $xmlString .= '
        <TotalMercanciasExentas>' . $totalMercExentas . '</TotalMercanciasExentas>';
    }

    if ($totalGravados != '') {
        $xmlString .= '
        <TotalGravado>' . $totalGravados . '</TotalGravado>';
    }

    if ($totalExento != '') {
        $xmlString .= '
        <TotalExento>' . $totalExento . '</TotalExento>';
    }

    $xmlString .= '
        <TotalVenta>' . $totalVentas . '</TotalVenta>';

    if ($totalDescuentos != '') {
        $xmlString .= '
        <TotalDescuentos>' . $totalDescuentos . '</TotalDescuentos>';
    }

    $xmlString .= '
        <TotalVentaNeta>' . $totalVentasNeta . '</TotalVentaNeta>';

    // Add logic for TotalDesgloseImpuesto
    if (isset($totalDesgloseImpuesto) && !empty($totalDesgloseImpuesto)) {
        foreach ($totalDesgloseImpuesto as $impuesto) {
            $xmlString .= '
            <TotalDesgloseImpuesto>';
            if (isset($impuesto->Codigo)) {
                $xmlString .= '<Codigo>' . $impuesto->Codigo . '</Codigo>';
            }
            if (isset($impuesto->CodigoTarifaIVA)) {
                $xmlString .= '<CodigoTarifaIVA>' . $impuesto->CodigoTarifaIVA . '</CodigoTarifaIVA>';
            }
            if (isset($impuesto->TotalMontoImpuesto)) {
                $xmlString .= '<TotalMontoImpuesto>' . $impuesto->TotalMontoImpuesto . '</TotalMontoImpuesto>';
            }
            $xmlString .= '</TotalDesgloseImpuesto>';
        }
    }

    if ($totalImp != '') {
        $xmlString .= '
        <TotalImpuesto>' . $totalImp . '</TotalImpuesto>';
    }

    if ($totalImpAsumidoEmisorFabrica != '') {
        $xmlString .= '
        <TotalImpAsumEmisorFabrica>' . $totalImpAsumidoEmisorFabrica . '</TotalImpAsumEmisorFabrica>';
    }

    if (isset($totalOtrosCargos) && $totalOtrosCargos != "") {
        $xmlString .= '
        <TotalOtrosCargos>' . $totalOtrosCargos . '</TotalOtrosCargos>';
    }

    if (isset($mediosPago) && !empty($mediosPago)) {
        foreach ($mediosPago as $o) {
            $xmlString .= '
            <MedioPago>';

            // Add TipoMedioPago
            if (isset($o->tipoMedioPago) && !empty($o->tipoMedioPago)) {
                $xmlString .= '<TipoMedioPago>' . $o->tipoMedioPago . '</TipoMedioPago>';
            }

            // Add MedioPagoOtros (only if TipoMedioPago is "99")
            if (isset($o->tipoMedioPago) && $o->tipoMedioPago === "99" && isset($o->medioPagoOtros) && !empty($o->medioPagoOtros)) {
                $xmlString .= '<MedioPagoOtros>' . htmlspecialchars($o->medioPagoOtros) . '</MedioPagoOtros>';
            }

            // Add TotalMedioPago
            if (isset($o->totalMedioPago) && is_numeric($o->totalMedioPago)) {
                $xmlString .= '<TotalMedioPago>' . number_format($o->totalMedioPago, 2, '.', '') . '</TotalMedioPago>';
            }

            $xmlString .= '</MedioPago>';
        }
    }


    $xmlString .= '
        <TotalComprobante>' . $totalComprobante . '</TotalComprobante>
    </ResumenFactura>';

    // JSON de ejemplo
    //    {
    //        "informacionReferencia": [
    //        {
    //            "tipoDoc": "01",
    //          "tipoDocOtro": "Factura",
    //          "numero": "50620032400020536006000100001010000000017100000017",
    //          "fechaEmision": "2023-10-01T12:00:00",
    //          "codigo": "99",
    //          "codigoOtro": "OTRO1",
    //          "razon": "Corrección de datos"
    //        },
    //        {
    //            "tipoDoc": "02",
    //          "numero": "50620032400020536006000100001010000000017200000018",
    //          "fechaEmision": "2023-10-02T15:30:00",
    //          "codigo": "01",
    //          "razon": "Devolución de producto"
    //        }
    //      ]
    //    }

    if (isset($informacionReferencia) && $informacionReferencia != "") {
        if (count((array)$informacionReferencia) > 10) {
            error_log("informacionReferencia: " . count((array)$informacionReferencia) . " is greater than 10");
        } else {
            foreach ($informacionReferencia as $i) {
                $xmlString .= '
                    <InformacionReferencia>';

                if (isset($i->tipoDoc) && $i->tipoDoc != "") {
                    $xmlString .= '
                    <TipoDocIR>' . $i->tipoDoc . '</TipoDocIR>';
                }

                if (isset($i->tipoDocOtro) && $i->tipoDocOtro != "") {
                    $xmlString .= '
                    <TipoDocRefOTRO>' . $i->tipoDocOtro . '</TipoDocRefOTRO>';
                }

                if (isset($i->numero) && $i->numero != "") {
                    $xmlString .= '
                    <Numero>' . $i->numero . '</Numero>';
                }

                if (isset($i->fechaEmision) && $i->fechaEmision != "") {
                    $xmlString .= '
                    <FechaEmisionIR>' . $i->fechaEmision . '</FechaEmisionIR>';
                }

                if (isset($i->codigo) && $i->codigo != "") {
                    $xmlString .= '
                    <Codigo>' . $i->codigo . '</Codigo>';
                }

                if (isset($i->codigoOtro) && $i->codigoOtro != "") {
                    $xmlString .= '
                    <CodigoReferenciaOTRO>' . $i->codigoOtro . '</CodigoReferenciaOTRO>';
                }

                if (isset($i->razon) && $i->razon != "") {
                    $xmlString .= '
                    <Razon>' . $i->razon . '</Razon>';
                }

                $xmlString .= '</InformacionReferencia>';
            }
        }
    }

    // XML Resultante
    //<InformacionReferencia>
    //    <TipoDocIR>01</TipoDocIR>
    //    <TipoDocRefOTRO>Factura</TipoDocRefOTRO>
    //    <Numero>50620032400020536006000100001010000000017100000017</Numero>
    //    <FechaEmisionIR>2023-10-01T12:00:00</FechaEmisionIR>
    //    <Codigo>99</Codigo>
    //    <CodigoReferenciaOTRO>OTRO1</CodigoReferenciaOTRO>
    //    <Razon>Corrección de datos</Razon>
    //</InformacionReferencia>
    //<InformacionReferencia>
    //    <TipoDocIR>02</TipoDocIR>
    //    <Numero>50620032400020536006000100001010000000017200000018</Numero>
    //    <FechaEmisionIR>2023-10-02T15:30:00</FechaEmisionIR>
    //    <Codigo>01</Codigo>
    //    <Razon>Devolución de producto</Razon>
    //</InformacionReferencia>

    // -----------------------------------------------------------------------------------------------------

    // JSON de ejemplo
    //    {
    //        "otroTexto": {
    //        "codigo": "COD1",
    //    "texto": "Texto opcional 1"
    //  },
    //  "otroContenido": [
    //    {
    //        "codigo": "CONT1",
    //      "contenidoEstructurado": {
    //        "ContactoDesarrollador": {
    //            "Correo": "developer@example.com",
    //          "Nombre": "Developer Name",
    //          "Telefono": "+123456789"
    //        }
    //      }
    //    },
    //    {
    //        "codigo": "CONT2",
    //      "contenidoEstructurado": {
    //        "SoporteTecnico": {
    //            "Correo": "support@example.com",
    //          "Nombre": "Support Team",
    //          "Telefono": "+987654321"
    //        }
    //      }
    //    }
    //  ]
    //}

    if (isset($otros) && !empty($otros)) {
        $xmlString .= '<Otros>';

        // Handle OtroTexto elements
        if (isset($otros->otroTexto)) {
            $xmlString .= '<OtroTexto';
            if (isset($otros->otroTexto->codigo)) {
                $xmlString .= ' codigo="' . htmlspecialchars($otros->otroTexto->codigo) . '"';
            }
            $xmlString .= '>' . htmlspecialchars($otros->otroTexto->texto) . '</OtroTexto>';
        }

        // Handle OtroContenido elements
        if (isset($otros->otroContenido)) {
            foreach ($otros->otroContenido as $item) {
                $xmlString .= '<OtroContenido';
                if (isset($item->codigo)) {
                    $xmlString .= ' codigo="' . htmlspecialchars($item->codigo) . '"';
                }
                $xmlString .= '>';
                if (isset($item->contenidoEstructurado)) {
                    foreach ($item->contenidoEstructurado as $element => $content) {
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
    //<Otros>
    //    <OtroTexto codigo="COD1">Texto opcional 1</OtroTexto>
    //    <OtroContenido codigo="CONT1">
    //        <ContactoDesarrollador xmlns="https://www.grupoice.com">
    //            <Correo>developer@example.com</Correo>
    //            <Nombre>Developer Name</Nombre>
    //            <Telefono>+123456789</Telefono>
    //        </ContactoDesarrollador>
    //    </OtroContenido>
    //    <OtroContenido codigo="CONT2">
    //        <SoporteTecnico xmlns="https://www.grupoice.com">
    //            <Correo>support@example.com</Correo>
    //            <Nombre>Support Team</Nombre>
    //            <Telefono>+987654321</Telefono>
    //        </SoporteTecnico>
    //    </OtroContenido>
    //</Otros>

    $xmlString .= '
    </FacturaElectronicaExportacion>';
    $arrayResp = array(
        "clave" => $clave,
        "xml" => base64_encode($xmlString)
    );

    return $arrayResp;
}


/* * ************************************************** */
/* Funcion de prueba                                    */
/* * ************************************************** */

function test()
{
    return "Esto es un test";
}

?>
