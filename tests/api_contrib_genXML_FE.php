<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/contrib/genXML/genXML.php';

class api_contrib_genXML_FE extends TestCase
{
    protected $mockParams = [];

    protected function setUp(): void
    {
        // Mock the global function params_get() only if not already declared
        if (!function_exists('params_get')) {
            function params_get($key)
            {
                $mockData = [
                    "clave" => "50620032400310123456700100001010000000017100000017", // 50 digits
                    "proveedor_sistemas" => "Proveedor XYZ",
                    "codigo_actividad_emisor" => "401002", // Valid activity code
                    "consecutivo" => "00100001010000000017", // 20 digits
                    "fecha_emision" => "2024-02-07T12:00:00", // Valid ISO 8601 date
                    "emisor_nombre" => "Empresa XYZ",
                    "emisor_tipo_identif" => "01", // Valid type
                    "emisor_num_identif" => "3101234567", // Valid identifier
                    "emisor_provincia" => "3", // Single digit
                    "emisor_canton" => "01", // Two digits
                    "emisor_distrito" => "01", // Two digits
                    "emisor_otras_senas" => "Dirección de prueba",
                    "emisor_email" => "empresa@example.com", // Valid email
                    "receptor_nombre" => "Cliente ABC",
                    "receptor_tipo_identif" => "02", // Valid type
                    "receptor_num_identif" => "206540123", // Valid identifier
                    "receptor_email" => "cliente@example.com", // Optional email
                    "condicion_venta" => "01", // Valid condition
                    //"medios_pago" => json_encode([["codigo" => "01"]]), // Valid payment method
                    "medios_pago" => json_encode([
                        ["tipoMedioPago" => "01", "totalMedioPago" => 1000.50],
                        ["tipoMedioPago" => "02", "totalMedioPago" => 500.00],
                        ["tipoMedioPago" => "99", "medioPagoOtros" => "Custom Payment", "totalMedioPago" => 250.75]
                    ]),
                    "cod_moneda" => "CRC", // Valid currency code
                    "tipo_cambio" => "1.00", // Valid exchange rate
                    "total_ventas_neta" => "1000.00", // Valid total
                    "total_comprobante" => "1000.00", // Valid total
                    "detalles" => json_encode([
                        [
                            "CodigoCABYS" => "1234567890123", // Valid CABYS code (13 digits)
                            "cantidad" => 1, // Valid quantity
                            "unidadMedida" => "Unid", // Valid unit of measure
                            "detalle" => "Producto 1", // Valid description
                            "precio_unitario" => 1000.00, // Valid unit price
                            "monto_total" => 1000.00, // Valid total amount
                            "subtotal" => 1000.00, // Valid subtotal
                            "monto_total_linea" => 1000.00 // Valid line total
                        ]
                    ]),
                    "informacion_referencia" => json_encode([
                        [
                            "tipoDoc" => "01", // Valid document type
                            "numero" => "50620032400310123456700100001010000000017100000017", // Valid reference number
                            "fechaEmision" => "2024-02-06T12:00:00", // Valid ISO 8601 date
                            "codigo" => "01", // Valid code
                            "razon" => "Corrección de datos" // Valid reason
                        ]
                    ]),
                    "otros" => json_encode([
                        "otroTexto" => [
                            "codigo" => "COD1",
                            "texto" => "Texto opcional 1"
                        ]
                    ])
                ];

                return $mockData[$key] ?? "";
            }
        }
        // Mock the grace_debug function if not already declared
        if (!function_exists('grace_debug')) {
            function grace_debug($message)
            {
                // Optionally capture the debug message or simulate its behavior
            }
        }
    }

    public function testTestFunction()
    {
        $this->assertEquals("Esto es un test", test());
    }

    public function testGenXMLFeBasic()
    {
        // Expect grace_debug to be called with the "detalles" parameter
        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        $result = genXMLFe();

        $this->assertArrayHasKey('clave', $result);
        $this->assertArrayHasKey('xml', $result);
        $this->assertNotEmpty($result['xml']);

        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertEquals('50620032400310123456700100001010000000017100000017', (string)$xml->Clave);
        $this->assertEquals('Empresa XYZ', (string)$xml->Emisor->Nombre);
        $this->assertEquals('Cliente ABC', (string)$xml->Receptor->Nombre);
    }

    public function testGenXMLFeMissingRequiredParams()
    {
        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        // Capture error_log output
        $errorMessages = [];
        set_error_handler(function ($errno, $errstr) use (&$errorMessages) {
            $errorMessages[] = $errstr;
        });

        $this->mockParams['clave'] = "";
        $this->mockParams['emisor_nombre'] = "";

        $result = genXMLFe();

        // Restore the original error handler
        restore_error_handler();

        // Assert that the error_log contains the expected message
        $this->assertNotEmpty($errorMessages);
        $this->assertStringContainsString('Missing required parameter', implode("\n", $errorMessages));
    }

    public function testGenXMLFeWithOptionalParams()
    {
        // Expect grace_debug to be called with the "detalles" parameter
        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        $result = genXMLFe();

        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertEmpty((string)$xml->Emisor->NombreComercial);
        $this->assertEquals('cliente@example.com', (string)$xml->Receptor->CorreoElectronico);
    }

    public function testGenXMLFeMedioPago()
    {
        // Expect grace_debug to be called with the "detalles" parameter
        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        $result = genXMLFe();

        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);
        // Print the final XML for debugging
        fwrite(STDERR, $xmlString . PHP_EOL); // Use STDERR to ensure output is visible

        // Validate TipoMedioPago
        $medioPagos = $xml->ResumenFactura->MedioPago;

        $this->assertEquals('01', (string)$medioPagos[0]->TipoMedioPago);
        $this->assertEquals('02', (string)$medioPagos[1]->TipoMedioPago);
        $this->assertEquals('99', (string)$medioPagos[2]->TipoMedioPago);

        $this->assertEquals('Custom Payment', (string)$medioPagos[2]->MedioPagoOtros);

        $this->assertEquals('1000.50', (string)$medioPagos[0]->TotalMedioPago);
        $this->assertEquals('500.00', (string)$medioPagos[1]->TotalMedioPago);
        $this->assertEquals('250.75', (string)$medioPagos[2]->TotalMedioPago);
    }

    public function testGenXMLFeInvalidData()
    {
        // Expect grace_debug to be called with the "detalles" parameter
        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        // Capture error_log output
        $errorMessages = [];
        set_error_handler(function ($errno, $errstr) use (&$errorMessages) {
            $errorMessages[] = $errstr;
        });

        $this->mockParams['detalles'] = json_encode([["item" => "Producto 1", "cantidad" => "invalid"]]);

        $result = genXMLFe();

        // Restore the original error handler
        restore_error_handler();

        $this->assertNotEmpty($errorMessages);
        $this->assertStringContainsString('Missing required parameter', implode("\n", $errorMessages));

    }

    public function testGenXMLFeFullStructure()
    {
        libxml_use_internal_errors(true); // Enable internal error tracking for libxml

        $this->expectOutputString(""); // If grace_debug outputs to the screen, capture it.

        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        echo $xmlString; // Debugging output

        try {
            $xml = new SimpleXMLElement($xmlString);
        } catch (Exception $e) {
            $this->fail("XML Parsing Error: " . $e->getMessage());
        }

        $this->assertEquals('50620032400310123456700100001010000000017100000017', (string)$xml->Clave);
        $this->assertEquals('Empresa XYZ', (string)$xml->Emisor->Nombre);
        $this->assertEquals('Cliente ABC', (string)$xml->Receptor->Nombre);

        $dom = new DOMDocument();
        $dom->loadXML($xmlString);
        $xsdPath = __DIR__ . '/../www/xsd/FacturaElectronica_V4.4.xsd';


        if (!$dom->schemaValidate($xsdPath)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $errorMessages = array_map(function ($error) {
                return trim($error->message) . " at line " . $error->line;
            }, $errors);

            $this->fail("XML Schema Validation Errors:\n" . implode("\n", $errorMessages));
        }
    }

}