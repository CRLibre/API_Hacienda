<?php

function sicWsQuery() {
    ini_set('soap.wsdl_cache_enabled', 0);
    ini_set('soap.wsdl_cache_ttl', 900);
    ini_set('default_socket_timeout', 15);

// TODO: Implementar la función que genere el digito verificador de pertenencia para poder hacer la consulta al webservice.
//       Adaptarlo al ejemplo de la API
    $options = [
        'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'style' => SOAP_RPC,
        'use' => SOAP_ENCODED,
        'soap_version' => SOAP_1_1,
        'cache_wsdl' => WSDL_CACHE_NONE,
        'connection_timeout' => 30,
        'trace' => true,
        'encoding' => 'UTF-8',
        'exceptions' => true
    ];

// El webservice en Hacienda hace la consulta utilizando los valores
// de parámetro que no estén vacíos, así que se puede hacer una consulta
// haciendo combinaciones.
    $params = [
        'origen' => params_get('origen'), // Fisico,  Juridico o DIMEX
        'cedula' => params_get('cedula'),
        'ape1' => params_get('ape1'),
        'ape2' => params_get('ape2'),
        'nomb1' => params_get('nomb1'),
        'nomb2' => params_get('nomb2'),
        'razon' => params_get('razon'),
        'Concatenado' => params_get('Concatenado')
    ];

    $wsdl = "http://196.40.56.20/wsInformativasSICWEB/Service1.asmx?WSDL";

    try {
        $soap = new SoapClient($wsdl, $options);
        $data = $soap->ObtenerDatos($params);
    } catch (Exception $e) {
        return str_replace('"', '\'', $e->getMessage());
    }
    $soap_response = $data->ObtenerDatosResult->any;
    $fileXML = str_replace('"', '\'', $soap_response);
    $fileXMLFinal = str_replace('<xs:schema xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:msdata="urn:schemas-microsoft-com:xml-msdata" id="NewDataSet">
    <xs:element name="NewDataSet" msdata:IsDataSet="true" msdata:MainDataTable="Table" msdata:UseCurrentLocale="true">
        <xs:complexType>
            <xs:choice minOccurs="0" maxOccurs="unbounded">
                <xs:element name="Table">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="CEDULA" type="xs:string" minOccurs="0"/>
                            <xs:element name="APELLIDO1" type="xs:string" minOccurs="0"/>
                            <xs:element name="APELLIDO2" type="xs:string" minOccurs="0"/>
                            <xs:element name="NOMBRE1" type="xs:string" minOccurs="0"/>
                            <xs:element name="NOMBRE2" type="xs:string" minOccurs="0"/>
                            <xs:element name="ADM" type="xs:string" minOccurs="0"/>
                            <xs:element name="ORI" type="xs:string" minOccurs="0"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
            </xs:choice>
        </xs:complexType>
    </xs:element>
</xs:schema>', '<?xml version="1.0"  encoding="UTF-8"?>', $fileXML);
    $fileXML+='</xml>';
    
   
    return $fileXMLFinal;
}
