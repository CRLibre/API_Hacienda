<?php
/**
 * Copyright (C) <2017>  <José Carlos Aguilar Vásquez>
 * Firmado para Costa Rica xades EPES
 * Este archivo contiene el proceso de firma en PHP de acuerdo a las especificaciones de Hacienda
 * José Carlos Aguilar Vásquez jcaguilar40@gmail.com
 * Donaciones vía Paypal a carlos_a40@hotmail.com
 *
 * LICENCIA AGPL
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/
class Firmadocr {
  const POLITICA_FIRMA = array(
    "name" 		=> "",
    "url" 		=> "https://tribunet.hacienda.go.cr/docs/esquemas/2016/v4/Resolucion%20Comprobantes%20Electronicos%20%20DGT-R-48-2016.pdf",
    "digest" 	=> "V8lVVNGDCPen6VELRD1Ja8HARFk=" //digest en sha1 y base64
  );
  private $signTime = NULL;
  private $signPolicy = NULL;
  private $publicKey = NULL;
  private $privateKey = NULL;
  private $cerROOT = NULL;
  private $cerINTERMEDIO = NULL;
  private $tipoDoc = '01';
  
  public function retC14DigestSha1($strcadena){
	$strcadena = str_replace("\r", "", str_replace("\n", "", $strcadena));
	$d1p = new DOMDocument('1.0','UTF-8');
	$d1p->loadXML($strcadena);
	$strcadena=$d1p->C14N();
    return base64_encode(hash('sha256' , $strcadena, true ));
  }
  
   
  
  public function firmar($certificadop12, $clavecertificado,$xmlsinfirma,$tipodoc) {
	if (!$pfx = file_get_contents($certificadop12)) {
		echo "Error: No se puede leer el fichero del certificado o no existe en la ruta especificada\n";
		exit;
	}
	if (openssl_pkcs12_read($pfx, $key, $clavecertificado)) {
		$this->publicKey    =$key["cert"];
		$this->privateKey   =$key["pkey"];
		$complem = openssl_pkey_get_details(openssl_pkey_get_private($this->privateKey));
		$this->Modulus = base64_encode($complem['rsa']['n']);
		$this->Exponent= base64_encode($complem['rsa']['e']);
	} else {
		echo "Error: No se puede leer el almacén de certificados o la clave no es la correcta.\n";
		exit;
	}
	$this->signPolicy = self::POLITICA_FIRMA;
	$this->signatureID 		= "Signature-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
	$this->signatureValue 	= "SignatureValue-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
	$this->XadesObjectId 	= "XadesObjectId-43208d10-650c-4f42-af80-fc889962c9ac";
	$this->KeyInfoId 		= "KeyInfoId-".$this->signatureID;
	
	$this->Reference0Id		= "Reference-0e79b719-635c-476f-a59e-8ac3ba14365d";
	$this->Reference1Id		= "ReferenceKeyInfo";
	
	$this->SignedProperties	= "SignedProperties-".$this->signatureID;

    $this->tipoDoc = $tipodoc;
	$xml1 = base64_decode($xmlsinfirma);
	$xml1 = $this->insertaFirma($xml1);
	return base64_encode($xml1);
  }
  /**
   * Función que Inserta la firma e
   * @parametros  archivo xml sin firma según UBL de DIAN
   * @retorna el documento firmando
   */
  public function insertaFirma($xml) {
    if (is_null($this->publicKey) || is_null($this->privateKey)) return $xml;
	//canoniza todo el documento  para el digest
	$d = new DOMDocument('1.0','UTF-8');
	$d->loadXML($xml);
	$canonizadoreal=$d->C14N(); 
	// Definir los namespace para los diferentes nodos
    $xmlns_keyinfo;$xmnls_signedprops;$xmnls_signeg;
    if ($this->tipoDoc == '01'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" ';
    } elseif ($this->tipoDoc == '02'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" ';
    } elseif ($this->tipoDoc == '03'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaCreditoElectronica" ';
    } elseif ($this->tipoDoc == '04'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/tiqueteElectronico" ';
    } elseif ($this->tipoDoc == '05' || $this->tipoDoc == '06' || $this->tipoDoc == '07'){
        $xmlns_keyinfo='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
        $xmnls_signedprops='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
        $xmnls_signeg='xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" ';
    }
    $xmlns= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
            'xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1" ' .
            'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"';
    $xmlns_keyinfo .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                      'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                      'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $xmnls_signedprops .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                          'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" '.
                          'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                          'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $xmnls_signeg .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" '.
                     'xmlns:xsd="http://www.w3.org/2001/XMLSchema" '.
                     'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';

	//date_default_timezone_set("America/Costa_Rica");
	//$signTime1='2018-01-30T17:16:42-06:00';
	//$signTime1 = date('Y-m-d\TH:i:s.vT:00');
	$signTime1 = date('Y-m-d\TH:i:s-06:00');
	

    $certData   = openssl_x509_parse($this->publicKey);
    $certDigest =base64_encode(openssl_x509_fingerprint($this->publicKey, "sha256", true));
    
    $certIssuer = array();
    foreach ($certData['issuer'] as $item=>$value) {
      $certIssuer[] = $item . '=' . $value;
    }
	$certIssuer = implode(', ', array_reverse($certIssuer));

    $prop = '<xades:SignedProperties Id="' . $this->SignedProperties .  '">' .
      '<xades:SignedSignatureProperties>'.
		  '<xades:SigningTime>' .  $signTime1 . '</xades:SigningTime>' .     
		  '<xades:SigningCertificate>'.
			  '<xades:Cert>'.
				  '<xades:CertDigest>' .
					  '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />'.
					  '<ds:DigestValue>' . $certDigest . '</ds:DigestValue>'.
				  '</xades:CertDigest>'.
				  '<xades:IssuerSerial>' .
					  '<ds:X509IssuerName>'   . $certIssuer       . '</ds:X509IssuerName>'.
					  '<ds:X509SerialNumber>' . $certData['serialNumber'] . '</ds:X509SerialNumber>' .
				  '</xades:IssuerSerial>'.
			  '</xades:Cert>'.
		  '</xades:SigningCertificate>' .
		  '<xades:SignaturePolicyIdentifier>'.
			  '<xades:SignaturePolicyId>' .
				  '<xades:SigPolicyId>'.
					  '<xades:Identifier>' . $this->signPolicy['url'] .  '</xades:Identifier>'.
					  '<xades:Description />'.
				  '</xades:SigPolicyId>'.
				  '<xades:SigPolicyHash>' .
					  '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1" />'. 
					  '<ds:DigestValue>' . $this->signPolicy['digest'] . '</ds:DigestValue>'.
				  '</xades:SigPolicyHash>'.
			  '</xades:SignaturePolicyId>' .
		  '</xades:SignaturePolicyIdentifier>'.
	  '</xades:SignedSignatureProperties>'.
	  '<xades:SignedDataObjectProperties>'.
		  '<xades:DataObjectFormat ObjectReference="#'. $this->Reference0Id . '">'.
			  '<xades:MimeType>text/xml</xades:MimeType>'.
			  '<xades:Encoding>UTF-8</xades:Encoding>'.
		  '</xades:DataObjectFormat>'.
	  '</xades:SignedDataObjectProperties>'.
	  '</xades:SignedProperties>';

    // Prepare key info
    $publicPEM = "";
    openssl_x509_export($this->publicKey, $publicPEM);
    $publicPEM = str_replace("-----BEGIN CERTIFICATE-----", "", $publicPEM);
    $publicPEM = str_replace("-----END CERTIFICATE-----", "", $publicPEM);
	$publicPEM = str_replace("\r", "", str_replace("\n", "", $publicPEM));	
 
	$kInfo = '<ds:KeyInfo Id="'.$this->KeyInfoId.'">' . 
				'<ds:X509Data>'  .  
					'<ds:X509Certificate>'  . $publicPEM .'</ds:X509Certificate>' .
				'</ds:X509Data>' .
				'<ds:KeyValue>'.				
				'<ds:RSAKeyValue>'.
					'<ds:Modulus>'.$this->Modulus .'</ds:Modulus>'.
					'<ds:Exponent>'.$this->Exponent .'</ds:Exponent>'.
				'</ds:RSAKeyValue>'.
				'</ds:KeyValue>'.
			 '</ds:KeyInfo>';

	
	$aconop=str_replace('<xades:SignedProperties', '<xades:SignedProperties ' . $xmnls_signedprops, $prop);
	$propDigest=$this->retC14DigestSha1($aconop);

	
	$keyinfo_para_hash1=str_replace('<ds:KeyInfo', '<ds:KeyInfo ' . $xmlns_keyinfo, $kInfo);
	$kInfoDigest=$this->retC14DigestSha1($keyinfo_para_hash1);

    
	
    $documentDigest = base64_encode(hash('sha256' , $canonizadoreal, true ));

    // Prepare signed info
    $sInfo = '<ds:SignedInfo>' . 
	  '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />' . 
	  '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />' . 
      '<ds:Reference Id="' . $this->Reference0Id . '" URI="">' . 
	  '<ds:Transforms>' . 
	  '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />' .  
	  '</ds:Transforms>' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' .
	  '<ds:DigestValue>' . $documentDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '<ds:Reference Id="'.  $this->Reference1Id . '" URI="#'.$this->KeyInfoId .'">' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' .
	  '<ds:DigestValue>' . $kInfoDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '<ds:Reference Type="http://uri.etsi.org/01903#SignedProperties" URI="#' . $this->SignedProperties . '">' . 
      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />' . 
	  '<ds:DigestValue>' . $propDigest . '</ds:DigestValue>' . 
	  '</ds:Reference>' . 
	  '</ds:SignedInfo>';


    $signaturePayload = str_replace('<ds:SignedInfo', '<ds:SignedInfo ' . $xmnls_signeg, $sInfo);

	
	$d1p = new DOMDocument('1.0','UTF-8');
	$d1p->loadXML($signaturePayload);
	$signaturePayload=$d1p->C14N();
	
    $signatureResult = "";
    $algo = "SHA256";


	openssl_sign($signaturePayload, $signatureResult, $this->privateKey,$algo);
	
	$signatureResult = base64_encode($signatureResult);

    $sig = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="' . $this->signatureID . '">'. 
	   $sInfo . 
      '<ds:SignatureValue Id="' . $this->signatureValue . '">' . 
      $signatureResult .  '</ds:SignatureValue>'  . $kInfo . 
      '<ds:Object Id="'.$this->XadesObjectId .'">'.
	  '<xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="QualifyingProperties-012b8df6-b93e-4867-9901-83447ffce4bf" Target="#' . $this->signatureID . '">' . $prop .
      '</xades:QualifyingProperties></ds:Object></ds:Signature>';

	$buscar;$remplazar;
    if ($this->tipoDoc == '01'){
        $buscar = '</FacturaElectronica>';
        $remplazar = $sig."</FacturaElectronica>";
    } elseif ($this->tipoDoc == '02'){
        $buscar = '</NotaDebitoElectronica>';
        $remplazar = $sig."</NotaDebitoElectronica>";
    } elseif ($this->tipoDoc == '03'){
        $buscar = '</NotaCreditoElectronica>';
        $remplazar = $sig."</NotaCreditoElectronica>";
    } elseif ($this->tipoDoc == '04'){
    	$buscar = '</TiqueteElectronico>';
        $remplazar = $sig."</TiqueteElectronico>";
    } elseif ($this->tipoDoc == '05' || $this->tipoDoc == '06' || $this->tipoDoc == '07'){
    	$buscar = '</MensajeReceptor>';
        $remplazar = $sig."</MensajeReceptor>";
    }
  	$pos = strrpos($xml, $buscar);
    if($pos !== false){
        $xml = substr_replace($xml, $remplazar, $pos, strlen($buscar));
    }

    return $xml;
  }
}
