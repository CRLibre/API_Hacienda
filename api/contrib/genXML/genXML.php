<?php



/*****************************************************/
/* Funcion para generar XML                          */
/*****************************************************/
function genXMLFe(){    

    //datos contribuyente
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
    
    //detalles de la compra
    $detalles = json_decode(params_get("detalles"));

    $xmlString = 
    '<?xml version="1.0" encoding="utf-8"?>
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
            <NombreComercial>' . $nombreComercial . '</NombreComercial>
            <Ubicacion>
                <Provincia>' . $emisorProv . '</Provincia>
                <Canton>' . $meisorCanton . '</Canton>
                <Distrito>' . $emisorDistrito . '</Distrito>
                <Barrio>' . $emisorBarrio . '</Barrio>
                <OtrasSenas>' . $emisorOtrasSenas . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
                <CodigoPais>' . $emisorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $emisorTel . '</NumTelefono>
            </Telefono>
            <Fax>
                <CodigoPais>' . $emisorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $emisorFax . '</NumTelefono>
            </Fax>
            <CorreoElectronico>' . $emisorEmail . '</CorreoElectronico>
        </Emisor>
        <Receptor>
            <Nombre>' . $receptorNombre . '</Nombre>
            <Identificacion>
                <Tipo>' . $receptorTipoIdentif . '</Tipo>
                <Numero>' . $recenprotNumIdentif . '</Numero>
            </Identificacion>
            <Ubicacion>
                <Provincia>' . $receptorProvincia . '</Provincia>
                <Canton>' . $receptorCanton . '</Canton>
                <Distrito>' . $receptorDistrito . '</Distrito>
                <Barrio>' . $receptorBarrio . '</Barrio>
                <OtrasSenas>' . $receptorOtrasSenas . '</OtrasSenas>
            </Ubicacion>
            <Telefono>
                <CodigoPais>' . $receptorCodPaisTel . '</CodigoPais>
                <NumTelefono>' . $receptorTel . '</NumTelefono>
            </Telefono>
            <Fax>
                <CodigoPais>' . $receptorCodPaisFax . '</CodigoPais>
                <NumTelefono>' . $receptorFax . '</NumTelefono>
            </Fax>
            <CorreoElectronico>' . $receptorEmail . '</CorreoElectronico>
        </Receptor>
        <CondicionVenta>' . $condVenta . '</CondicionVenta>
        <PlazoCredito>' . $plazoCredito . '</PlazoCredito>
        <MedioPago>' . $medioPago . '</MedioPago>
        <DetalleServicio>
        ';

        //cant - unidad medida - detalle - precio unitario - monto total - subtotal - monto total linea   
    
        /* EJEMPLO DE DETALLES
        
        {
            "1":["1", "Sp", "Honorarios", "100000", "100000", "100000", "100000"], 
            "2":["1", "Sp", "Honorarios", "100000", "100000", "100000", "100000"]
        }
        
        */
        $l = 1;
        foreach($detalles as $d){
            $xmlString .= 
                '<LineaDetalle>
                  <NumeroLinea>' . $l . '</NumeroLinea>
                  <Cantidad>' . $d[0] . '</Cantidad>
                  <UnidadMedida>' . $d[1] . '</UnidadMedida>
                  <Detalle>' . $d[2] . '</Detalle>
                  <PrecioUnitario>' . $d[3] . '</PrecioUnitario>
                  <MontoTotal>' . $d[4] . '</MontoTotal>
                  <SubTotal>' . $d[5] . '</SubTotal>
                  <MontoTotalLinea>' . $d[6] . '</MontoTotalLinea>
                </LineaDetalle>
                ';
            $l++;
        }

        $xmlString .=
        '</DetalleServicio>
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
        </Normativa>
        <Otros>
            <OtroTexto>' . $otros . '</OtroTexto>
        </Otros>
    </FacturaElectronica>';


    $name = md5(microtime() . $clave . $consecutivo) . ".xml";

    $xmlFolder = realpath(dirname(__FILE__));
    $xmlFolder .= "/tmpXML/$name";
    
    $dom = new DomDocument();
    $dom->preseveWhiteSpace = FALSE;
    $dom->loadXML($xmlString);
    $dom->save($xmlFolder);
    
    return $xmlString;
}


/*****************************************************/
/* Funcion de prueba                                 */
/*****************************************************/
function test(){
	return "Esto es un test";
}


?>
