<?php
// SPDX-License-Identifier: 0BSD
// SPDX-FileCopyrightText: © Francisco de la Peña. https://fran.cr/
// Based on https://codeberg.org/fdelapena/xades-b-b-enveloped-signers/

function firmar()
{
    modules_loader("files");

    // Format XML
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML(base64_decode(params_get('inXml')));
    $formatted_xml = $dom->saveXML();

    // Canonicalize XML
    $dom = new DOMDocument();
    $dom->loadXML($formatted_xml);
    $canonical_xml = $dom->C14N(true);
    $document_digest = base64_encode(hash('sha256', $canonical_xml, true));

    date_default_timezone_set('America/Costa_Rica');
    $signing_time = date(DATE_ATOM);

    // Read P12 file
    $p12Content = file_get_contents(filesGetUrl(params_get('p12Url')));
    openssl_pkcs12_read($p12Content, $p12, params_get('pinP12'));

    $cert_digest = base64_encode(openssl_x509_fingerprint($p12['cert'], 'sha256', true));

    $cert = openssl_x509_parse($p12['cert']);

    foreach ($cert['issuer'] as $key => $value) $subject[] = "$key=$value";
    $subject = implode(',', array_reverse($subject));

    $policy_id = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/Resoluci%C3%B3n_General_sobre_disposiciones_t%C3%A9cnicas_comprobantes_electr%C3%B3nicos_para_efectos_tributarios.pdf';
    $policy_digest = 'DWxin1xWOeI8OuWQXazh4VjLWAaCLAA954em7DMh0h8=';

    openssl_x509_export($p12['cert'], $cert_base64);
    $cert_base64 = str_replace('-----BEGIN CERTIFICATE-----', '', $cert_base64);
    $cert_base64 = str_replace('-----END CERTIFICATE-----', '', $cert_base64);
    $cert_base64 = str_replace("\r", '', str_replace("\n", '', $cert_base64));

    $serial = hexdec($cert['serialNumberHex']);

    $signed_properties =
    "<xades:SignedProperties xmlns:xades=\"http://uri.etsi.org/01903/v1.3.2#\" Id=\"p1\">\n" .
    "          <xades:SignedSignatureProperties>\n" .
    "            <xades:SigningTime>$signing_time</xades:SigningTime>\n" .
    "            <xades:SigningCertificate>\n" .
    "              <xades:Cert>\n" .
    "                <xades:CertDigest>\n" .
    "                  <ds:DigestMethod xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"></ds:DigestMethod>\n" .
    "                  <ds:DigestValue xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\">$cert_digest</ds:DigestValue>\n" .
    "                </xades:CertDigest>\n" .
    "                <xades:IssuerSerial>\n" .
    "                  <ds:X509IssuerName xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\">$subject</ds:X509IssuerName>\n" .
    "                  <ds:X509SerialNumber xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\">$serial</ds:X509SerialNumber>\n" .
    "                </xades:IssuerSerial>\n" .
    "              </xades:Cert>\n" .
    "            </xades:SigningCertificate>\n" .
    "            <xades:SignaturePolicyIdentifier>\n" .
    "              <xades:SignaturePolicyId>\n" .
    "                <xades:SigPolicyId>\n" .
    "                  <xades:Identifier>$policy_id</xades:Identifier>\n" .
    "                </xades:SigPolicyId>\n" .
    "                <xades:SigPolicyHash>\n" .
    "                  <ds:DigestMethod xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"></ds:DigestMethod>\n" .
    "                  <ds:DigestValue xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\">$policy_digest</ds:DigestValue>\n" .
    "                </xades:SigPolicyHash>\n" .
    "              </xades:SignaturePolicyId>\n" .
    "            </xades:SignaturePolicyIdentifier>\n" .
    "          </xades:SignedSignatureProperties>\n" .
    "          <xades:SignedDataObjectProperties>\n" .
    "            <xades:DataObjectFormat ObjectReference=\"#r1\">\n" .
    "              <xades:MimeType>text/xml</xades:MimeType>\n" .
    "            </xades:DataObjectFormat>\n" .
    "          </xades:SignedDataObjectProperties>\n" .
    "        </xades:SignedProperties>";

    $properties_digest = base64_encode(hash('sha256', $signed_properties, true));

    // Clean unneeded xmlns after hashing (optional)
    $signed_properties = str_replace(' xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"', '', $signed_properties);
    $signed_properties = str_replace(' xmlns:ds="http://www.w3.org/2000/09/xmldsig#"', '', $signed_properties);

    $signed_info =
    "<ds:SignedInfo xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\">\n" .
    "      <ds:CanonicalizationMethod Algorithm=\"http://www.w3.org/2001/10/xml-exc-c14n#\"></ds:CanonicalizationMethod>\n" .
    "      <ds:SignatureMethod Algorithm=\"http://www.w3.org/2001/04/xmldsig-more#rsa-sha256\"></ds:SignatureMethod>\n" .
    "      <ds:Reference Id=\"r1\" URI=\"\">\n" .
    "        <ds:Transforms>\n" .
    "          <ds:Transform Algorithm=\"http://www.w3.org/2002/06/xmldsig-filter2\">\n" .
    "            <dsig-filter2:XPath xmlns:dsig-filter2=\"http://www.w3.org/2002/06/xmldsig-filter2\" Filter=\"subtract\">/descendant::ds:Signature</dsig-filter2:XPath>\n" .
    "          </ds:Transform>\n" .
    "          <ds:Transform Algorithm=\"http://www.w3.org/2001/10/xml-exc-c14n#\"></ds:Transform>\n" .
    "        </ds:Transforms>\n" .
    "        <ds:DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"></ds:DigestMethod>\n" .
    "        <ds:DigestValue>$document_digest</ds:DigestValue>\n" .
    "      </ds:Reference>\n" .
    "      <ds:Reference Type=\"http://uri.etsi.org/01903#SignedProperties\" URI=\"#p1\">\n" .
    "        <ds:Transforms>\n" .
    "          <ds:Transform Algorithm=\"http://www.w3.org/2001/10/xml-exc-c14n#\"></ds:Transform>\n" .
    "        </ds:Transforms>\n" .
    "        <ds:DigestMethod Algorithm=\"http://www.w3.org/2001/04/xmlenc#sha256\"></ds:DigestMethod>\n" .
    "        <ds:DigestValue>$properties_digest</ds:DigestValue>\n" .
    "      </ds:Reference>\n" .
    "    </ds:SignedInfo>";

    openssl_sign($signed_info, $signature_value, $p12['pkey'], OPENSSL_ALGO_SHA256);
    $signature_value_base64 = base64_encode($signature_value);

    // Clean another unneeded xmlns after signing (optional)
    $signed_info = str_replace(' xmlns:ds="http://www.w3.org/2000/09/xmldsig#"', '', $signed_info);

    $signature =
    "<ds:Signature xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" Id=\"s1\">\n" .
    "    $signed_info\n" .
    "    <ds:SignatureValue Id=\"v1\">$signature_value_base64</ds:SignatureValue>\n" .
    "    <ds:KeyInfo>\n" .
    "      <ds:X509Data>\n" .
    "        <ds:X509Certificate>$cert_base64</ds:X509Certificate>\n" .
    "      </ds:X509Data>\n" .
    "    </ds:KeyInfo>\n" .
    "    <ds:Object>\n" .
    "      <xades:QualifyingProperties xmlns:xades=\"http://uri.etsi.org/01903/v1.3.2#\" Target=\"#s1\">\n" .
    "        $signed_properties\n" .
    "      </xades:QualifyingProperties>\n" .
    "    </ds:Object>\n" .
    "  </ds:Signature>";

    $dom_canonical_xml = new DOMDocument();
    $dom_canonical_xml->loadXML($canonical_xml);
    $dom_canonical_xml->encoding = 'UTF-8';

    $dom_signature = new DOMDocument();
    $dom_signature->loadXML($signature);

    $dom_signature = $dom_canonical_xml->importNode($dom_signature->documentElement, true);
    $dom_canonical_xml->documentElement->appendChild($dom_signature);

    $signed_xml = $dom_canonical_xml->saveXML();

    // Clean yet another unneeded xmlns after saving to file (optional)
    $signed_xml = str_replace('xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" Id', 'Id', $signed_xml);

    return ["xmlFirmado" => base64_encode($signed_xml)];
}

// Backwards compatibility with older module API syntax
function signFE()
{
    return firmar();
}
