<?php

/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
 * Copyright (C) 2017 José Carlos Aguilar Vásquez
 * Firmado para Costa Rica xades EPES
 * Este archivo contiene el proceso de firma en PHP de acuerdo a las especificaciones de Hacienda
 * José Carlos Aguilar Vásquez jcaguilar40@gmail.com
 * Donaciones vía Paypal a carlos_a40@hotmail.com
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

class Firmadocr
{
    private static $NODOS_NS = array(
        "URL" => "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/",
        '01' => "facturaElectronica",
        '02' => "notaDebitoElectronica",
        '03' => "notaCreditoElectronica",
        '04' => "tiqueteElectronico",
        '05' => "mensajeReceptor",
        '06' => "mensajeReceptor",
        '07' => "mensajeReceptor"
    );
    
    private static $POLITICA_FIRMA = array(
        "name"      => "",
        "url"       => "https://cdn.comprobanteselectronicos.go.cr/xml-schemas/Resoluci%C3%B3n_General_sobre_disposiciones_t%C3%A9cnicas_comprobantes_electr%C3%B3nicos_para_efectos_tributarios.pdf",
        "digest"    => "DWxin1xWOeI8OuWQXazh4VjLWAaCLAA954em7DMh0h8=" // digest en sha256 y base64
    );

    private $signTime         = null;
    private $signPolicy       = null;
    private $publicKey        = null;
    private $privateKey       = null;
    private $cerROOT          = null;
    private $cerINTERMEDIO    = null;
    private $tipoDoc          = '01';

    public function retC14DigestSha1($strcadena)
    {
        $strcadena    = str_replace("\r", "", str_replace("\n", "", $strcadena));
        $d1p          = new DOMDocument('1.0', 'UTF-8');
        $d1p->loadXML($strcadena);
        $strcadena    = $d1p->C14N();

        return base64_encode(hash('sha256', $strcadena, true));
    }

    // Allows converting large hexadecimal string to large decimal string
    // without precision issues or requiring PHP extensions like BC Math or GMP
    public function stringHex2StringDec($hex) {
        $dec = [];
        $hexLen = strlen($hex);
        for ($h = 0; $h < $hexLen; ++$h) {
            $carry = hexdec($hex[$h]);
            for ($i = 0; $i < count($dec); ++$i) {
                $val = $dec[$i] * 16 + $carry;
                $dec[$i] = $val % 10;
                $carry = (int) ($val / 10);
            }
            while ($carry > 0) {
                $dec[] = $carry % 10;
                $carry = (int) ($carry / 10);
            }
        }
        return join("", array_reverse($dec));
    }


    public function firmar($certificadop12, $clavecertificado, $xmlsinfirma, $tipodoc)
    {
        if (!$pfx = file_get_contents($certificadop12)) {
            echo "Error: No se puede leer el fichero del certificado o no existe en la ruta especificada\n";
            exit;
        }

        if (openssl_pkcs12_read($pfx, $key, $clavecertificado)) {
            $this->publicKey    = $key["cert"];
            $this->privateKey   = $key["pkey"];

            $complem          = openssl_pkey_get_details(openssl_pkey_get_private($this->privateKey));
            $this->Modulus    = base64_encode($complem['rsa']['n']);
            $this->Exponent   = base64_encode($complem['rsa']['e']);
        } else {
            echo "Error: No se puede leer el almacén de certificados o la clave no es la correcta.\n";
            exit;
        }

        $this->signPolicy         = self::$POLITICA_FIRMA;
        $this->signatureID        = "Signature-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
        $this->signatureValue     = "SignatureValue-ddb543c7-ea0c-4b00-95b9-d4bfa2b4e411";
        $this->XadesObjectId      = "XadesObjectId-43208d10-650c-4f42-af80-fc889962c9ac";
        $this->KeyInfoId          = "KeyInfoId-".$this->signatureID;

        $this->Reference0Id       = "Reference-0e79b719-635c-476f-a59e-8ac3ba14365d";
        $this->Reference1Id       = "ReferenceKeyInfo";

        $this->SignedProperties   = "SignedProperties-".$this->signatureID;

        $this->tipoDoc            = $tipodoc;
        $xml1                     = base64_decode($xmlsinfirma);
        $xml1                     = $this->insertaFirma($xml1);

        return base64_encode($xml1);
    }

    /**
     * Función que Inserta la firma e
     * @parametros  archivo xml sin firma según UBL de DIAN
     * @retorna el documento firmando
     */

    public function insertaFirma($xml)
    {
        if (is_null($this->publicKey) || is_null($this->privateKey)) {
            return $xml;
        }

        // Canoniza todo el documento  para el digest
        $d = new DOMDocument('1.0', 'UTF-8');
        $d->loadXML($xml);
        $canonizadoreal = $d->C14N();

        // Definir los namespace para los diferentes nodos
        $xmlns_keyinfo = 'xmlns="'.self::$NODOS_NS["URL"].self::$NODOS_NS[$this->tipoDoc].'" ';
        $xmnls_signedprops = $xmlns_keyinfo;
        $xmnls_signeg = $xmlns_keyinfo;
    
        $xmlns = 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" ' .
            'xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1" ' .
            'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"';
    
        $xmlns_keyinfo .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" ' .
                        'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ' .
                        'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    
        $xmnls_signedprops .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" ' .
                            'xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" ' .
                            'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ' .
                            'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    
        $xmnls_signeg .= 'xmlns:ds="http://www.w3.org/2000/09/xmldsig#" ' .
                        'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ' .
                        'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';

        $signTime1 = date('Y-m-d\TH:i:s-06:00');

        $certData   = openssl_x509_parse($this->publicKey);
        $certDigest = base64_encode(openssl_x509_fingerprint($this->publicKey, "sha256", true));

        $certIssuer = array();
        foreach ($certData['issuer'] as $item=>$value) {
            $certIssuer[] = $item . '=' . $value;
        }

        $certIssuer = implode(', ', array_reverse($certIssuer));

        if (strpos($certData['serialNumber'], "0x") === false) {
            // https://bugs.php.net/bug.php?id=77411
            $serialNumber = $certData['serialNumber'];
        } else {
            $serialNumber = stringHex2StringDec($certData['serialNumber']);
       }

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
                      '<ds:X509SerialNumber>' . $serialNumber . '</ds:X509SerialNumber>' .
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
                      '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />'.
                      '<ds:DigestValue>' . $this->signPolicy['digest'] . '</ds:DigestValue>'.
                  '</xades:SigPolicyHash>'.
              '</xades:SignaturePolicyId>' .
          '</xades:SignaturePolicyIdentifier>'.
          '<xades:SignerRole>'.
              '<xades:ClaimedRoles>'.
                  '<xades:ClaimedRole>Emisor</xades:ClaimedRole>'.
              '</xades:ClaimedRoles>'.
          '</xades:SignerRole>'.
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

        $aconop     = str_replace('<xades:SignedProperties', '<xades:SignedProperties ' . $xmnls_signedprops, $prop);
        $propDigest = $this->retC14DigestSha1($aconop);

        $keyinfo_para_hash1 = str_replace('<ds:KeyInfo', '<ds:KeyInfo ' . $xmlns_keyinfo, $kInfo);
        $kInfoDigest = $this->retC14DigestSha1($keyinfo_para_hash1);

        $documentDigest = base64_encode(hash('sha256', $canonizadoreal, true));

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

        $d1p = new DOMDocument('1.0', 'UTF-8');
        $d1p->loadXML($signaturePayload);
        $signaturePayload = $d1p->C14N();

        $signatureResult = "";
        $algo = "SHA256";

        openssl_sign($signaturePayload, $signatureResult, $this->privateKey, $algo);

        $signatureResult = base64_encode($signatureResult);

        $sig = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="' . $this->signatureID . '">'.
        $sInfo .
        '<ds:SignatureValue Id="' . $this->signatureValue . '">' .
        $signatureResult .  '</ds:SignatureValue>'  . $kInfo .
        '<ds:Object Id="'.$this->XadesObjectId .'">'.
        '<xades:QualifyingProperties xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id="QualifyingProperties-012b8df6-b93e-4867-9901-83447ffce4bf" Target="#' . $this->signatureID . '">' . $prop .
        '</xades:QualifyingProperties></ds:Object></ds:Signature>';

        $buscar;
        $remplazar;
        if ($this->tipoDoc == '01') {
            $buscar = '</FacturaElectronica>';
            $remplazar = $sig."</FacturaElectronica>";
        } elseif ($this->tipoDoc == '02') {
            $buscar = '</NotaDebitoElectronica>';
            $remplazar = $sig."</NotaDebitoElectronica>";
        } elseif ($this->tipoDoc == '03') {
            $buscar = '</NotaCreditoElectronica>';
            $remplazar = $sig."</NotaCreditoElectronica>";
        } elseif ($this->tipoDoc == '04') {
            $buscar = '</TiqueteElectronico>';
            $remplazar = $sig."</TiqueteElectronico>";
        } elseif ($this->tipoDoc == '05' || $this->tipoDoc == '06' || $this->tipoDoc == '07') {
            $buscar = '</MensajeReceptor>';
            $remplazar = $sig."</MensajeReceptor>";
        }

        $pos = strrpos($xml, $buscar);
        if ($pos !== false) {
            $xml = substr_replace($xml, $remplazar, $pos, strlen($buscar));
        }

        return $xml;
    }
}
