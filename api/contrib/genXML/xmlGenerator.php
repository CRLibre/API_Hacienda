<?php

function genXML() {
    $detalle = params_get("detalle");
    //linea:tipo codigo,codigo,cantidad,"tipo ejemplo SP",Detalle,precio unitario    
    //{"1":["1", "Sp", "Honorarios", "100000", "100000", "100000", "100000", "1000","Pronto pago", "0"]}
    $DetalleServicio = json_decode(params_get("detalles"));
    $tipoDocumento = params_get("tipoDocumento");
    $clave = params_get("clave");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision");
    //datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_indetif");
    $emisorNumIdentif = params_get("emisor_num_identif");
    $nombreComercial = params_get("nombre_comercial");

    $emisorProv = params_get("emisor_provincia");
    $meisorCanton = params_get("emisor_canton");
    $emisorDistrito = params_get("emisor_distrito");
    $emisorBarrio = params_get("emisor_barrio");
    $emisorOtrasSenas = params_get("emisor_otras_senas");
    $emisorCodPaisTel = params_get("emisor_cod_pais_tel");
    $emisorTel = params_get("emisor_tel");
    $emisorCodPaisFax = params_get("emisor_cod_pais_fax");
    $emisorFax = params_get("emisor_fax");
    $emisorEmail = params_get("emisor_email");

    //datos receptor
    $receptorNombre = params_get("receptor_nombre");
    $receptorTipoIdentif = params_get("receptor_tipo_identif");
    $recenprotNumIdentif = params_get("receptor_num_identif");
    $receptorProvincia = params_get("receptor_provincia");
    $receptorCanton = params_get("receptor_canton");
    $receptorDistrito = params_get("receptor_distrito");
    $receptorBarrio = params_get("receptor_barrio");
    $receptorOtrasSenas = params_get("receptor_otras_senas");
    $receptorCodPaisTel = params_get("receptor_cod_pais_tel");
    $receptorTel = params_get("receptor_tel");
    $receptorCodPaisFax = params_get("receptor_cod_pais_fax");
    $receptorFax = params_get("receptor_fax");
    $receptorEmail = params_get("receptor_email");

    //detalles de tiquete / factura
    $condVenta = params_get("condicion_venta");
    $plazoCredito = params_get("plazo_credito");
    $medioPago = params_get("medio_pago");

    $codMoneda = params_get("cod_moneda");
    $tipoCambio = params_get("tipo_cambio");
    $totalServGravados = params_get("total_serv_gravados");
    $totalServExentos = params_get("total_serv_exentos");
    $totalMercGravadas = params_get("total_merc_gravada");
    $totalMercExentas = params_get("total_merc_exenta");
    $totalGravados = params_get("total_gravados");
    $totalExentos = params_get("total_exentos");
    $totalVentas = params_get("total_ventas");
    $totalDescuentos = params_get("total_descuentos");
    $totalVentasNeta = params_get("total_ventas_neta");
    $totalImp = params_get("total_impuestos");
    $totalComprobante = params_get("total_comprobante");
    $otros = params_get("otros");



    foreach ($detalle as $key => $value) {
        if ($key > 0) {
            $DetalleServicio[] = array(
                'LineaDetalle' => array(
                    'NumeroLinea' => $key,
                    'Codigo' => array(
                        'Tipo' => $value[0],
                        'Codigo' => $value[1],
                    ),
                    'Cantidad' => round($value[2], 3),
                    'UnidadMedida' => [3],
                    'Detalle' => $value[4],
                    'PrecioUnitario' => round($value[5], 5),
                    'MontoTotal' => round($value[5] * $value[3], 5),
                    'SubTotal' => round($value[5] * $value[3], 5),
                    'MontoTotalLinea' => round($value[5] * $value[3], 5),
                )
            );
        }
    }

    foreach ($DetalleServicio as $key => $value) {
        $TotalComprobante += $value['LineaDetalle']['MontoTotalLinea'];
    }

    $TotalComprobante = round($TotalComprobante, 5);
    $myArray = array(
        'Clave' => $clave,
        'NumeroConsecutivo' => $consecutivo,
        'FechaEmision' => $fechaEmision,
        'Emisor' => array(
            'Nombre' => $emisorNombre,
            'Identificacion' => array(
                'Tipo' => $emisorTipoIdentif,
                'Numero' => $emisorNumIdentif,
            ),
            'NombreComercial' => $nombreComercial,
            'Ubicacion' => array(
                'Provincia' => $emisorProv,
                'Canton' => $meisorCanton,
                'Distrito' => $emisorDistrito,
                'Barrio' => $emisorBarrio,
                'OtrasSenas' => $emisorOtrasSenas,
            ),
            'Telefono' => array(
                'CodigoPais' => $emisorCodPaisTel,
                'NumTelefono' => $emisorTel,
            ),
            'Fax' => array(
                'CodigoPais' => $emisorCodPaisFax,
                'NumTelefono' => $emisorFax,
            ),
            'CorreoElectronico' => $emisorEmail,
        ),
        'CondicionVenta' => $condVenta,
        'MedioPago' => $medioPago,
        'DetalleServicio' => $DetalleServicio,
        'ResumenFactura' => array(
            'CodigoMoneda' => $codMoneda,
            'TipoCambio' => round($tipoCambio, 5),
            'TotalVenta' => $TotalComprobante,
            'TotalVentaNeta' => $TotalComprobante,
            'TotalComprobante' => $TotalComprobante,
        ),
        'Normativa' => array(
            'NumeroResolucion' => 'DGT-R-48-2016',
            'FechaResolucion' => '20-02-2017 13:22:22',
        ),
    );
    $xmltext = array_to_xml($myArray);

    $headerDoc="";
    $footerDoc="";    
     if ($tipoDocumento == 'FE'){
        $headerDoc='<FacturaElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica FacturaElectronica_V.4.2.xsd">" ';
        $footerDoc='</FacturaElectronica>';
    } elseif ($tipoDocumento == 'TE'){
        $headerDoc='<TiqueteElectronico xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico TiqueteElectronico_V.4.2.xsd">" ';
        $footerDoc='</TiqueteElectronico>';
    } elseif ($tipoDocumento == 'NC'){
        $headerDoc='<NotaCreditoElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica NotaCreditoElectronica_V.4.2.xsd">" ';
        $footerDoc='</NotaCreditoElectronica>';
    } elseif ($tipoDocumento == 'ND'){
        $headerDoc='<NotaDebitoElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica NotaDebitoElectronica_V.4.2.xsd">" ';
        $footerDoc='</NotaDebitoElectronica>';
    }
    
    $finalXML= $headerDoc . $xmltext . $footerDoc;
    return $finalXML;
}

?>