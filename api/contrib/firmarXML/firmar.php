<?php
require(dirname(__FILE__) . '/hacienda/firmador.php');
use Hacienda\Firmador;

function firmar()
{
	modules_loader("files");
	$pfx = filesGetUrl(params_get('p12Url'));
	$pin = params_get('pinP12'); // PIN de 4 dígitos de la llave criptográfica
	$xml = params_get('inXml');

	// Nuevo firmador
	$firmador = new Firmador();

	// Se firma XML y se recibe un string resultado en Base64
	$base64 = $firmador->firmarXml($pfx, $pin, base64_decode($xml), $firmador::TO_BASE64_STRING);
	return array("xmlFirmado" => $base64);
	// print_r($base64);

	// Se firma XML y se recibe un string resultado en Xml
	// $xml_string = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_XML_STRING);

	// Se firma XML, se guarda en disco duro ($ruta) y se recibe el número de bytes del archivo guardado. En caso de error se recibe FALSE
	// $archivo = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_XML_FILE, $ruta);
	// print_r($archivo);
}