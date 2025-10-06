<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/contrib/genXML/genXML.php';

class api_contrib_genXML_FE extends TestCase
{
    protected function setUp(): void
    {
        if (!function_exists('params_get')) {
            function params_get($key)
            {
                $mockData = [
                    "clave" => "50620032400310123456700100001010000000017100000017",
                    "proveedor_sistemas" => "Proveedor XYZ",
                    "codigo_actividad_emisor" => "401002",
                    "consecutivo" => "00100001010000000017",
                    "fecha_emision" => "2024-02-07T12:00:00",
                    "emisor_nombre" => "Empresa XYZ",
                    "emisor_tipo_identif" => "01",
                    "emisor_num_identif" => "3101234567",
                    "emisor_provincia" => "3",
                    "emisor_canton" => "01",
                    "emisor_distrito" => "01",
                    "emisor_otras_senas" => "Dirección de prueba",
                    "emisor_email" => "empresa@example.com",
                    "receptor_nombre" => "Cliente ABC",
                    "receptor_tipo_identif" => "02",
                    "receptor_num_identif" => "206540123",
                    "receptor_email" => "cliente@example.com",
                    "condicion_venta" => "01",
                    "medios_pago" => json_encode([
                        ["tipoMedioPago" => "01", "totalMedioPago" => 1000.50],
                        ["tipoMedioPago" => "02", "totalMedioPago" => 500.00],
                        ["tipoMedioPago" => "99", "medioPagoOtros" => "Custom Payment", "totalMedioPago" => 250.75]
                    ]),
                    "cod_moneda" => "CRC",
                    "tipo_cambio" => "1.00",
                    "total_ventas" => "1000.00",
                    "total_ventas_neta" => "1000.00",
                    "total_comprobante" => "1000.00",
                    "receptor_otras_senas_extranjero" => "123 Main St, Miami, FL, USA", // Example: foreign address
                    "registrofiscal8707" => "REG-8707-001", // Example: fiscal registry number
                    "condicion_venta_otros" => "Venta especial", // Example: description for other sale condition
                    "codigo_actividad_receptor" => "502101",
                    "emisor_nombre_comercial" => "Comercial XYZ",
                    "emisor_barrio" => "Barrio01",
                    "emisor_cod_pais_tel" => "506",
                    "emisor_tel" => "22223333",
                    "receptor_nombre_comercial" => "Comercial ABC",
                    "receptor_provincia" => "4",
                    "receptor_canton" => "02",
                    "receptor_distrito" => "03",
                    "receptor_barrio" => "Barrio02",
                    "receptor_otras_senas" => "Calle 123, Edificio ABC",
                    "receptor_cod_pais_tel" => "506",
                    "receptor_tel" => "88887777",
                    "plazo_credito" => "30",
                    "total_serv_gravados" => "0.00",
                    "total_serv_exentos" => "200000.00",
                    "total_serv_exonerados" => "0.00",
                    "total_serv_no_sujeto" => "0.00",
                    "total_merc_gravada" => "0.00",
                    "total_merc_exenta" => "0.00",
                    "total_merc_exonerada" => "0.00",
                    "total_merc_no_sujeta" => "0.00",
                    "total_gravados" => "0.00",
                    "total_exento" => "200000.00",
                    "total_exonerado" => "0.00",
                    "total_no_sujeto" => "0.00",
                    "total_descuentos" => "100.00",
                    "totalDesgloseImpuesto" => "0.00",
                    "total_impuestos" => "0.00",
                    "total_impuestos_asumidos_fabrica" => "0.00",
                    "totalIVADevuelto" => "0.00",
                    "totalOtrosCargos" => "0.00",
                    "otrosCargos" => json_encode([
                        [
                            "tipoDocumentoOC" => "01",
                            "numeroDocumento" => "DOC-123",
                            "detalle" => "Cargo por servicio adicional",
                            "montoCargo" => 150.00
                        ],
                        [
                            "tipoDocumentoOC" => "02",
                            "numeroDocumento" => "DOC-456",
                            "detalle" => "Cargo por gestión",
                            "montoCargo" => 75.50
                        ]
                    ]),
                    "detalles" => json_encode([
                        [
                            "codigoCABYS" => "0111100000100",
                            "codigoComercial" => [
                                ["tipo" => "01", "codigo" => "A123"],
                                ["tipo" => "02", "codigo" => "B456"]
                            ],
                            "cantidad" => 2,
                            "unidadMedida" => "Unid",
                            "tipoTransaccion" => "01",
                            "unidadMedidaComercial" => "Caja",
                            "detalle" => "Medicamento genérico",
                            "numeroVINoSerie" => "VIN123456789",
                            "registroMedicamento" => "REG-CR-2024-0001",
                            "formaFarmaceutica" => "TAB",
                            "detalleSurtido" => [
                                [
                                    "codigoCABYSSurtido" => "2399999002200",
                                    "codigoComercialSurtido" => [
                                        ["tipoSurtido" => "01", "codigoSurtido" => "S123"]
                                    ],
                                    "cantidadSurtido" => 1,
                                    "unidadMedidaSurtido" => "Unid",
                                    "unidadMedidaComercialSurtido" => "Blister",
                                    "detalleSurtido" => "Surtido de medicamento",
                                    "precioUnitarioSurtido" => 120.00,
                                    "montoTotalSurtido" => 120.00,
                                    "descuentoSurtido" => [
                                        [
                                            "montoDescuentoSurtido" => 10.00,
                                            "codigoDescuentoSurtido" => "01",
                                            "descuentoSurtidoOtros" => "Descuento especial"
                                        ]
                                    ],
                                    "subTotalSurtido" => 110.00,
                                    "ivaCobradoFabricaSurtido" => "01",
                                    "baseImponibleSurtido" => 105.00,
                                    "impuestoSurtido" => [
                                        [
                                            "codigoImpuestoSurtido" => "01",
                                            "codigoTarifaIVASurtido" => "08",
                                            "tarifaSurtido" => 13.00,
                                            "montoImpuestoSurtido" => 13.65,
                                            "datosImpuestoEspecificoSurtido" => [
                                                "cantidadUnidadMedidaSurtido" => 1,
                                                "porcentajeSurtido" => 5.0,
                                                "proporcionSurtido" => 0.5,
                                                "volumenUnidadConsumoSurtido" => 0.1,
                                                "impuestoUnidadSurtido" => 2.00
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "precioUnitario" => 150.00,
                            "montoTotal" => 300.00,
                            "descuento" => [
                                [
                                    "montoDescuento" => 20.00,
                                    "codigoDescuento" => "99",
                                    "codigoDescuentoOTRO" => "DESC-OTRO-001",
                                    "naturalezaDescuento" => "Descuento por promoción"
                                ]
                            ],
                            "subTotal" => 280.00,
                            "IVACobradoFabrica" => "01",
                            "baseImponible" => 270.00,
                            "impuesto" => [
                                [
                                    "codigo" => "01",
                                    "codigoTarifa" => "08",
                                    "tarifa" => 13.00,
                                    "factorIVA" => 1.0,
                                    "monto" => 35.10,
                                    "exoneracion" => [
                                        "tipoDocumento" => "01",
                                        "tipoDocumentoOtro" => "OTRODOC",
                                        "numeroDocumento" => "EXON-2024-001",
                                        "numeroArticulo" => "123501",
                                        "numeroInciso" => "000010",
                                        "nombreInstitucion" => "01",
                                        "nombreInstitucionOtros" => "Otra Institución",
                                        "fechaEmision" => "2024-06-01T00:00:00",
                                        "tarifaExoneracion" => 50.0,
                                        "montoExoneracion" => 17.55
                                    ]
                                ],
                                [
                                    "codigo" => "03",
                                    "codigoTarifa" => "01",
                                    "tarifa" => 2.00,
                                    "factorIVA" => 0.5,
                                    "monto" => 5.00,
                                    "datosImpuestoEspecifico" => [
                                        "cantidadUnidadMedida" => 2,
                                        "porcentaje" => 10.0,
                                        "proporcion" => 0.2,
                                        "volumenUnidadConsumo" => 0.5,
                                        "impuestoUnidad" => 1.00
                                    ]
                                ]
                            ],
                            "impuestoAsumidoEmisorFabrica" => 2.00,
                            "impuestoNeto" => 22.55,
                            "montoTotalLinea" => 302.55
                        ],
                        [
                            "codigoCABYS" => "3110100000100",
                            "cantidad" => 1,
                            "unidadMedida" => "Kg",
                            "detalle" => "Producto sin surtido ni descuentos",
                            "precioUnitario" => 50.00,
                            "montoTotal" => 50.00,
                            "subTotal" => 50.00,
                            "baseImponible" => 50.00,
                            "impuesto" => [
                                [
                                    "codigo" => "01",
                                    "codigoTarifa" => "08",
                                    "tarifa" => 13.00,
                                    "monto" => 6.50
                                ]
                            ],
                            "impuestoAsumidoEmisorFabrica" => 0.00,
                            "impuestoNeto" => 6.50,
                            "montoTotalLinea" => 56.50
                        ]
                    ]),
                    "informacion_referencia" => json_encode([
                        [
                            "tipoDoc" => "99",
                            "tipoDocOtro" => "Factura",
                            "numero" => "50620032400020536006000100001010000000017100000017",
                            "fechaEmision" => "2023-10-01T12:00:00",
                            "codigo" => "99",
                            "codigoOtro" => "OTRO1",
                            "razon" => "Corrección de datos"
                        ],
                        [
                            "tipoDoc" => "02",
                            "numero" => "50620032400020536006000100001010000000017200000018",
                            "fechaEmision" => "2023-10-02T15:30:00",
                            "codigo" => "01",
                            "razon" => "Devolucion de producto"
                        ]
                    ]),
                    "otros" => json_encode([
                        "otroTexto" => [
                            "codigo" => "COD1",
                            "texto" => "Texto opcional 1"
                        ],
                        "otroContenido" => [
                            [
                                "codigo" => "CONT1",
                                "contenidoEstructurado" => [
                                    "ContactoDesarrollador" => [
                                        "Correo" => "developer@example.com",
                                        "Nombre" => "Developer Name",
                                        "Telefono" => "+123456789"
                                    ]
                                ]
                            ],
                            [
                                "codigo" => "CONT2",
                                "contenidoEstructurado" => [
                                    "SoporteTecnico" => [
                                        "Correo" => "support@example.com",
                                        "Nombre" => "Support Team",
                                        "Telefono" => "+987654321"
                                    ]
                                ]
                            ]
                        ]
                    ])
                ];
                return $mockData[$key] ?? "";
            }
        }
        if (!function_exists('grace_debug')) {
            function grace_debug($message)
            {
            }
        }
    }

    public function testTestFunction()
    {
        $this->assertEquals("Esto es un test", test());
    }

    public function testGenXMLFeBasic()
    {
        $result = genXMLFe();
        $this->assertArrayHasKey('clave', $result);
        $this->assertArrayHasKey('xml', $result);
        $this->assertNotEmpty($result['xml']);

        $xmlString = base64_decode($result['xml']);
        // Print the XML output to the console
        fwrite(STDERR, "\n--- XML Output ---\n$xmlString\n--- End XML Output ---\n");

        $xml = new SimpleXMLElement($xmlString);

        $this->assertEquals('50620032400310123456700100001010000000017100000017', (string)$xml->Clave);
        $this->assertEquals('Empresa XYZ', (string)$xml->Emisor->Nombre);
        $this->assertEquals('Cliente ABC', (string)$xml->Receptor->Nombre);
    }

    public function testGenXMLFeInformacionReferencia()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        // Print the XML output to the console
        fwrite(STDERR, "\n--- XML Output ---\n$xmlString\n--- End XML Output ---\n");


        $xml = new SimpleXMLElement($xmlString);
        $xml->registerXPathNamespace('fe', 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica');

        $refs = $xml->xpath('//fe:InformacionReferencia');
        $this->assertCount(2, $refs);

        $this->assertEquals('99', (string)$refs[0]->TipoDocIR);
        $this->assertEquals('Factura', (string)$refs[0]->TipoDocRefOTRO);
        $this->assertEquals('50620032400020536006000100001010000000017100000017', (string)$refs[0]->Numero);
        $this->assertEquals('2023-10-01T12:00:00', (string)$refs[0]->FechaEmisionIR);
        $this->assertEquals('99', (string)$refs[0]->Codigo);
        $this->assertEquals('OTRO1', (string)$refs[0]->CodigoReferenciaOTRO);
        $this->assertEquals('Corrección de datos', (string)$refs[0]->Razon);

        $this->assertEquals('02', (string)$refs[1]->TipoDocIR);
        $this->assertEquals('50620032400020536006000100001010000000017200000018', (string)$refs[1]->Numero);
        $this->assertEquals('2023-10-02T15:30:00', (string)$refs[1]->FechaEmisionIR);
        $this->assertEquals('01', (string)$refs[1]->Codigo);
        $this->assertEquals('Devolucion de producto', (string)$refs[1]->Razon);
    }

    public function testGenXMLFeOtros()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        // Print the XML output to the console
        fwrite(STDERR, "\n--- XML Output ---\n$xmlString\n--- End XML Output ---\n");


        $xml = new SimpleXMLElement($xmlString);

        $xml->registerXPathNamespace('fe', 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.4/facturaElectronica');
        $otros = $xml->xpath('//fe:Otros');
        $this->assertNotEmpty($otros);

        $otroTexto = $xml->xpath('//fe:Otros/fe:OtroTexto');
        $this->assertCount(1, $otroTexto);
        $this->assertEquals('COD1', (string)$otroTexto[0]['codigo']);
        $this->assertEquals('Texto opcional 1', (string)$otroTexto[0]);

        $otroContenido = $xml->xpath('//fe:Otros/fe:OtroContenido');
        $this->assertCount(2, $otroContenido);
        $this->assertEquals('CONT1', (string)$otroContenido[0]['codigo']);
        $this->assertEquals('CONT2', (string)$otroContenido[1]['codigo']);
    }

    public function testGenXMLFeMedioPago()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $medioPagos = $xml->ResumenFactura->MedioPago;
        $this->assertEquals('01', (string)$medioPagos[0]->TipoMedioPago);
        $this->assertEquals('02', (string)$medioPagos[1]->TipoMedioPago);
        $this->assertEquals('99', (string)$medioPagos[2]->TipoMedioPago);
        $this->assertEquals('Custom Payment', (string)$medioPagos[2]->MedioPagoOtros);
        $this->assertEquals('1000.50', (string)$medioPagos[0]->TotalMedioPago);
        $this->assertEquals('500.00', (string)$medioPagos[1]->TotalMedioPago);
        $this->assertEquals('250.75', (string)$medioPagos[2]->TotalMedioPago);
    }

    public function testGenXMLFeFullStructure()
    {
        libxml_use_internal_errors(true);
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        // Print the XML output to the console
        fwrite(STDERR, "\n--- XML Output ---\n$xmlString\n--- End XML Output ---\n");


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
        $xsdPath = __DIR__ . '/../www/xsd/FacturaElectronica_V4.4-noSign.xsd';

        if (!$dom->schemaValidate($xsdPath)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $errorMessages = array_map(function ($error) {
                return trim($error->message) . " at line " . $error->line;
            }, $errors);
            $this->fail("XML Schema Validation Errors:\n" . implode("\n", $errorMessages));
        }
    }

    public function testGenXMLFeReceptor()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertEquals('Cliente ABC', (string)$xml->Receptor->Nombre);
        $this->assertEquals('02', (string)$xml->Receptor->Identificacion->Tipo);
        $this->assertEquals('206540123', (string)$xml->Receptor->Identificacion->Numero);
        $this->assertEquals('cliente@example.com', (string)$xml->Receptor->CorreoElectronico);
    }

    public function testGenXMLFeResumenFactura()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertEquals('1000.00', (string)$xml->ResumenFactura->TotalVentaNeta);
        $this->assertEquals('1000.00', (string)$xml->ResumenFactura->TotalComprobante);

        $medioPagos = $xml->ResumenFactura->MedioPago;
        $this->assertEquals('01', (string)$medioPagos[0]->TipoMedioPago);
        $this->assertEquals('1000.50', (string)$medioPagos[0]->TotalMedioPago);
        $this->assertEquals('02', (string)$medioPagos[1]->TipoMedioPago);
        $this->assertEquals('500.00', (string)$medioPagos[1]->TotalMedioPago);
        $this->assertEquals('99', (string)$medioPagos[2]->TipoMedioPago);
        $this->assertEquals('Custom Payment', (string)$medioPagos[2]->MedioPagoOtros);
        $this->assertEquals('250.75', (string)$medioPagos[2]->TotalMedioPago);
    }

    public function testGenXMLFeEmisor()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertEquals('Empresa XYZ', (string)$xml->Emisor->Nombre);
        $this->assertEquals('01', (string)$xml->Emisor->Identificacion->Tipo);
        $this->assertEquals('3101234567', (string)$xml->Emisor->Identificacion->Numero);
        $this->assertEquals('3', (string)$xml->Emisor->Ubicacion->Provincia);
        $this->assertEquals('01', (string)$xml->Emisor->Ubicacion->Canton);
        $this->assertEquals('01', (string)$xml->Emisor->Ubicacion->Distrito);
        $this->assertEquals('Dirección de prueba', (string)$xml->Emisor->Ubicacion->OtrasSenas);
        $this->assertEquals('empresa@example.com', (string)$xml->Emisor->CorreoElectronico);
    }

    public function testGenXMLFeDetalleServicio()
    {
        $result = genXMLFe();
        $xmlString = base64_decode($result['xml']);
        $xml = new SimpleXMLElement($xmlString);

        $linea = $xml->DetalleServicio->LineaDetalle[0];
        $this->assertEquals('1', (string)$linea->NumeroLinea);
        $this->assertEquals('0111100000100', (string)$linea->CodigoCABYS);
        $this->assertEquals('2', (string)$linea->Cantidad);
        $this->assertEquals('Unid', (string)$linea->UnidadMedida);
        $this->assertEquals('01', (string)$linea->TipoTransaccion);
        $this->assertEquals('Caja', (string)$linea->UnidadMedidaComercial);
        $this->assertEquals('Medicamento genérico', (string)$linea->Detalle);
        $this->assertEquals('VIN123456789', (string)$linea->NumeroVINoSerie);
        $this->assertEquals('REG-CR-2024-0001', (string)$linea->RegistroMedicamento);
        $this->assertEquals('TAB', (string)$linea->FormaFarmaceutica);
        $this->assertEquals('150', (string)$linea->PrecioUnitario);
        $this->assertEquals('300', (string)$linea->MontoTotal);
        $this->assertEquals('280', (string)$linea->SubTotal);
        $this->assertEquals('01', (string)$linea->IVACobradoFabrica);
        $this->assertEquals('270', (string)$linea->BaseImponible);
        $this->assertEquals('302.55', (string)$linea->MontoTotalLinea);
    }
}