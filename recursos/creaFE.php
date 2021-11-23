<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
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

$xmlString = '<?xml version="1.0" encoding="utf-8"?>
<FacturaElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/facturaElectronica FacturaElectronica_V.4.2.xsd">
  <Clave>50609041800070232071700100001010010040000183169901</Clave>
  <NumeroConsecutivo>00100001010010040000</NumeroConsecutivo>
  <FechaEmision>2018-04-09T19:00:00-06:00</FechaEmision>
  <Emisor>
    <Nombre>Walner Borbon</Nombre>
    <Identificacion>
      <Tipo>01</Tipo>
      <Numero>702320717</Numero>
    </Identificacion>
    <NombreComercial>Walner Borbon</NombreComercial>
    <Ubicacion>
      <Provincia>6</Provincia>
      <Canton>02</Canton>
      <Distrito>03</Distrito>
      <Barrio>01</Barrio>
      <OtrasSenas>Frente escuela</OtrasSenas>
    </Ubicacion>
    <Telefono>
      <CodigoPais>506</CodigoPais>
      <NumTelefono>83168485</NumTelefono>
    </Telefono>
    <Fax>
      <CodigoPais>506</CodigoPais>
      <NumTelefono>00000000</NumTelefono>
    </Fax>
    <CorreoElectronico>walner1borbon@gmail.com</CorreoElectronico>
  </Emisor>
  <Receptor>
    <Nombre>Servicios Tecnicos, S.A.</Nombre>
    <CorreoElectronico>jvargas@setec-cr.com</CorreoElectronico>
  </Receptor>
  <CondicionVenta>01</CondicionVenta>
  <PlazoCredito>0</PlazoCredito>
  <MedioPago>01</MedioPago>
  <DetalleServicio>
    <LineaDetalle>
      <NumeroLinea>1</NumeroLinea>
      <Cantidad>1</Cantidad>
      <UnidadMedida>Sp</UnidadMedida>
      <Detalle>Anticipo  honorarios profesionales por la auditoria  a setiembre 30 2018 Mes de abril 2018</Detalle>
      <PrecioUnitario>200000</PrecioUnitario>
      <MontoTotal>200000</MontoTotal>
      <SubTotal>200000</SubTotal>
      <MontoTotalLinea>200000</MontoTotalLinea>
    </LineaDetalle>
  </DetalleServicio>
  <ResumenFactura>
    <CodigoMoneda>CRC</CodigoMoneda>
    <TipoCambio>564.48</TipoCambio>
    <TotalServGravados>0</TotalServGravados>
    <TotalServExentos>200000</TotalServExentos>
    <TotalMercanciasGravadas>0</TotalMercanciasGravadas>
    <TotalMercanciasExentas>0</TotalMercanciasExentas>
    <TotalGravado>0</TotalGravado>
    <TotalExento>200000</TotalExento>
    <TotalVenta>200000</TotalVenta>
    <TotalDescuentos>0</TotalDescuentos>
    <TotalVentaNeta>200000</TotalVentaNeta>
    <TotalImpuesto>0</TotalImpuesto>
    <TotalComprobante>200000</TotalComprobante>
  </ResumenFactura>
  <Normativa>
    <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
    <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
  </Normativa>
  <Otros>
    </Otros>
    </FacturaElectronica>
';


$dom = new DomDocument();
$dom->preseveWhiteSpace = FALSE;
$dom->loadXML($xmlString);
$dom->save('fac.xml');
?>
