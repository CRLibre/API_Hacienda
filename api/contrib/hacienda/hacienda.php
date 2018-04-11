<?php

/*****************************************************/
/* Funcion para refrescar u obtener el token         */
/*****************************************************/
function token(){
 
	//GET URL from Post
	$url = params_get("url"); 
	$data;
	//Get Data from Post
	if (params_get("grant_type")=="password") {
		
		$data = array(
				    "client_id" => params_get("client_id"),
				    "client_secret" => params_get("client_secret"),
				    "grant_type" => params_get("grant_type"),
				    "username" => params_get("username"),
				    "password" => params_get("password")     
					);
		}else if (params_get("grant_type")=="refresh_token") {
			$data = array(
					    "client_id" => params_get("client_id"),
					    "client_secret" => params_get("client_secret"),
					    "grant_type" => params_get("grant_type"),
					    "refresh_token" =>  params_get("refresh_token")
			);
		}

		//Making the options
		$options = array(
				    "http" => array(
						        "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
						        "method"  => "POST",
						        "content" => http_build_query($data)
				    )	
				);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$ar = json_decode($result);

		return ($ar);	
}

/*****************************************************/
/* Funcion para generar XML                          */
/*****************************************************/
function XMLGenerator($inhouse = false){
    
    global $config;
    
    /*  JSON de ejemplo para parametro literal bajo el key "detalles"*/
    /*{
    "1":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"],
    "2":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"],
    "3":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"],
    "4":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"],
    "5":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"],
    "6":["04", "7707236125554", "1.000", "Unid", "Detalle prod", "7500.00000", "7500.00000", "7500.00000", "7500.00000"]
    } */

    //datos contribuyente
    $clave = params_get("clave");
    $consecutivo = params_get("consecutivo");
    $fechaEmision = params_get("fecha_emision"); 
    
    //datos emisor
    $emisorNombre = params_get("emisor_nombre");
    $emisorTipoIdentif = params_get("emisor_tipo_indetif");
    $emisorNumIdentif = params_get("emisor_num_identif");
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
    $numRes = params_get("num_res");
    $fechaRes = params_get("fecha_res");
    
    //detalles de la compra
    $detalles = json_decode(params_get("detalles"));


    
    //XML
    $XML = "HOLA $emisorNomre";

    //Cabeceras
    $XML .= "<?xml version='1.0' encoding='UTF-8'?>";
    $XML .= "<FacturaElectronica xmlns='https://tribunet.hacienda.go.cr/docs/esquemas/2016/v4.1/facturaElectronica' xmlns:xsd='http://www.w3.org/2001/XMLSchema' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>";
    $XML .= "<Clave>$clave</Clave>";
    $XML .= "<NumeroConsecutivo>$consecutivo</NumeroConsecutivo>";
    $XML .= "<FechaEmision>$fechaEmision</FechaEmision>";
    $XML .= "<Emisor>";
    $XML .= "    <Nombre>$emisorNombre</Nombre>";
    $XML .= "    <Identificacion>";
    $XML .= "        <Tipo>$emisorTipoIdentif</Tipo>";
    $XML .= "        <Numero>$emisorNumIdentif</Numero>";
    $XML .= "    </Identificacion>";
    $XML .= "    <NombreComercial />";
    $XML .= "    <Ubicacion>";
    $XML .= "        <Provincia>$emisorProv</Provincia>";
    $XML .= "        <Canton>$meisorCanton</Canton>";
    $XML .= "        <Distrito>$emisorDistrito</Distrito>";
    $XML .= "        <Barrio>$emisorBarrio</Barrio>";
    $XML .= "        <OtrasSenas>$emisorOtrasSenas</OtrasSenas>";
    $XML .= "    </Ubicacion>";
    $XML .= "    <Telefono>";
    $XML .= "        <CodigoPais>$emisorCodPaisTel</CodigoPais>";
    $XML .= "        <NumTelefono>$emisorTel</NumTelefono>";
    $XML .= "    </Telefono>";
    $XML .= "    <Fax>";
    $XML .= "        <CodigoPais>$emisorCodPaisFax</CodigoPais>";
    $XML .= "        <NumTelefono>$emisorFax</NumTelefono>";
    $XML .= "    </Fax>";
    $XML .= "    <CorreoElectronico>$emisorEmail</CorreoElectronico>";
    $XML .= "</Emisor>";
    $XML .= " <Receptor>";
    $XML .= "     <Nombre>$receptorNombre</Nombre>";
    $XML .= "    <Identificacion>";
    $XML .= "        <Tipo>$receptorTipoIdentif</Tipo>";
    $XML .= "        <Numero>$recenprotNumIdentif</Numero>";
    $XML .= "    </Identificacion>";
    $XML .= "    <NombreComercial />";
    $XML .= "    <Ubicacion>";
    $XML .= "        <Provincia>$receptorProvincia</Provincia>";
    $XML .= "        <Canton>$receptorCanton</Canton>";
    $XML .= "        <Distrito>$receptorDistrito</Distrito>";
    $XML .= "        <Barrio>$receptorBarrio</Barrio>";
    $XML .= "        <OtrasSenas />";
    $XML .= "    </Ubicacion>";
    $XML .= "    <Telefono>";
    $XML .= "        <CodigoPais>$receptorCodPaisTel</CodigoPais>";
    $XML .= "        <NumTelefono>$receptorTel</NumTelefono>";
    $XML .= "    </Telefono>";
    $XML .= "    <Fax>";
    $XML .= "       <CodigoPais>$receptorCodPaisFax</CodigoPais>";
    $XML .= "        <NumTelefono>$receptorFax</NumTelefono>";
    $XML .= "    </Fax>";
    $XML .= "    <CorreoElectronico>$receptorEmail</CorreoElectronico>";
    $XML .= "</Receptor>";

    $XML .= "<CondicionVenta>$condVenta</CondicionVenta>";

    //Incluye si hay, plazo de crédito
    if(isset($plaoCredito) && $plazoCredito > 0){
        $XML .= "<PlazoCredito>$plazoCredito<PlazoCredito />";
    }else{
        $XML .= "<PlazoCredito />";
    }

    $XML .= "<MedioPago>$medioPago</MedioPago>";


    foreach($detalles as $d){

        $l = 1;
        
        $XML .= "<DetalleServicio>";
        $XML .= "   <LineaDetalle>";
        $XML .= "    <NumeroLinea>$l</NumeroLinea>";
        $XML .= "        <Codigo>";
        $XML .= "            <Tipo>$d[0]</Tipo>";
        $XML .= "            <Codigo>$d[1]</Codigo>";
        $XML .= "        </Codigo>";
        $XML .= "        <Cantidad>$d[2]</Cantidad>";
        $XML .= "        <UnidadMedida>$d[3]</UnidadMedida>";
        $XML .= "        <UnidadMedidaComercial />";
        $XML .= "        <Detalle>$d[4]</Detalle>";
        $XML .= "        <PrecioUnitario>$d[5]</PrecioUnitario>";
        $XML .= "        <MontoTotal>$d[6]</MontoTotal>";
        $XML .= "        <NaturalezaDescuento />";
        $XML .= "        <SubTotal>$d[7]</SubTotal>";
        $XML .= "        <MontoTotalLinea>$d[8]</MontoTotalLinea>";
        $XML .= "    </LineaDetalle>";
        $XML .= "</DetalleServicio>";

        $l++;
    }

    $XML .= "<ResumenFactura>";
    $XML .= "    <CodigoMoneda>$codMoneda</CodigoMoneda>";
    $XML .= "    <TipoCambio>$tipoCambio</TipoCambio>";
    $XML .= "    <TotalServGravados>$totalServGravados</TotalServGravados>";
    $XML .= "    <TotalServExentos>$totalServExentos</TotalServExentos>";
    $XML .= "    <TotalMercanciasGravadas>$totalMercGravadas</TotalMercanciasGravadas>";
    $XML .= "    <TotalMercanciasExentas>$totalMercExentas</TotalMercanciasExentas>";
    $XML .= "    <TotalGravado>$totalGravados</TotalGravado>";
    $XML .= "    <TotalExento>$totalExentos</TotalExento>";
    $XML .= "    <TotalVenta>$totalVentas</TotalVenta>";
    $XML .= "    <TotalDescuentos>$totalDescuentos</TotalDescuentos>";
    $XML .= "    <TotalVentaNeta>$totalVentasNeta</TotalVentaNeta>";
    $XML .= "    <TotalImpuesto>$totalImp</TotalImpuesto>";
    $XML .= "    <TotalComprobante>$totalComprobante</TotalComprobante>";
    $XML .= "</ResumenFactura>";
    $XML .= "<Normativa>";
    $XML .= "    <NumeroResolucion>$numRes</NumeroResolucion>";
    $XML .= "    <FechaResolucion>$fechaRes</FechaResolucion>";
    $XML .= "</Normativa>";
    $XML .= "</FacturaElectronica>";
    
    $name = md5(microtime() . $clave . $consecutivo) . ".xml";

    $xmlFolder = realpath(dirname(__FILE__));
    $xmlFolder .= "/tmpXML/$name";
    
    $fp = fopen($xmlFolder, 'w');
    fwrite($fp, $XML);
    fclose($fp);
    
    if($inhouse){
        return $xmlFolder;
    }
    return $XML;
}


/*****************************************************/
/* Funcion de prueba                                 */
/*****************************************************/
function test(){
	return "Esto es un test";
}
?>
