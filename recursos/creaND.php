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
<NotaDebitoElectronica xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/notaDebitoElectronica NotaDebitoElectronica_V4.2.xsd">
  <Clave>50611041800070232071700100001021522773384107756324</Clave>
  <NumeroConsecutivo>00100001021522773384</NumeroConsecutivo>
  <FechaEmision>2018-04-11T14:10:00-06:00</FechaEmision>
  <Emisor>
    <Nombre>Walner Borbon</Nombre>
    <Identificacion>
      <Tipo>01</Tipo>
      <Numero>702320717</Numero>
    </Identificacion>
    <NombreComercial>Walner Borbon</NombreComercial>
    <Ubicacion>
      <Provincia>1</Provincia>
      <Canton>01</Canton>
      <Distrito>08</Distrito>
      <Barrio>01</Barrio>
      <OtrasSenas>Frente escuela</OtrasSenas>
    </Ubicacion>
    <Telefono>
      <CodigoPais>506</CodigoPais>
      <NumTelefono>83168485</NumTelefono>
    </Telefono>
    <Fax>
      <CodigoPais>506</CodigoPais>
      <NumTelefono>83168485</NumTelefono>
    </Fax>
    <CorreoElectronico>walner1borbon@gmail.com</CorreoElectronico>
  </Emisor>
  <Receptor>
        <Nombre>Laboratory CLIENTE</Nombre>
        <Identificacion>
            <Tipo>02</Tipo>
            <Numero>3001123208</Numero>
        </Identificacion>
        <NombreComercial/>
        <Ubicacion>
            <Provincia>1</Provincia>
            <Canton>01</Canton>
            <Distrito>01</Distrito>
            <Barrio>01</Barrio>
            <OtrasSenas/>
        </Ubicacion>
        <Telefono>
            <CodigoPais>506</CodigoPais>
            <NumTelefono>83439500</NumTelefono>
        </Telefono>
        <CorreoElectronico>rt@prueba.com</CorreoElectronico>
    </Receptor>
  <CondicionVenta>01</CondicionVenta>
  <PlazoCredito>0</PlazoCredito>
  <MedioPago>01</MedioPago>
  <DetalleServicio>
    <LineaDetalle>
      <NumeroLinea>1</NumeroLinea>
      <Cantidad>1</Cantidad>
      <UnidadMedida>Sp</UnidadMedida>
      <Detalle>Precios de Transferencia</Detalle>
      <PrecioUnitario>875</PrecioUnitario>
      <MontoTotal>875</MontoTotal>
      <SubTotal>875</SubTotal>
      <MontoTotalLinea>875</MontoTotalLinea>
    </LineaDetalle>
  </DetalleServicio>
  <ResumenFactura>
    <CodigoMoneda>USD</CodigoMoneda>
    <TipoCambio>562.04</TipoCambio>
    <TotalServGravados>0</TotalServGravados>
    <TotalServExentos>875</TotalServExentos>
    <TotalMercanciasGravadas>0</TotalMercanciasGravadas>
    <TotalMercanciasExentas>0</TotalMercanciasExentas>
    <TotalGravado>0</TotalGravado>
    <TotalExento>875</TotalExento>
    <TotalVenta>875</TotalVenta>
    <TotalDescuentos>0</TotalDescuentos>
    <TotalVentaNeta>875</TotalVentaNeta>
    <TotalImpuesto>0</TotalImpuesto>
    <TotalComprobante>875</TotalComprobante>
  </ResumenFactura>
  <InformacionReferencia>
    <TipoDoc>03</TipoDoc>
    <Numero>50609041800070232071700100001031522773382107756320</Numero>
    <FechaEmision>2018-04-09T19:10:00-06:00</FechaEmision>
    <Codigo>01</Codigo>
    <Razon>Sin razón</Razon>
  </InformacionReferencia>
  <Normativa>
    <NumeroResolucion>DGT-R-48-2016</NumeroResolucion>
    <FechaResolucion>07-10-2016 08:00:00</FechaResolucion>
  </Normativa>
  <Otros>
  </Otros>
</NotaDebitoElectronica>';


$dom = new DomDocument();
$dom->preseveWhiteSpace = FALSE;
$dom->loadXML($xmlString);
$dom->save('ND.xml');
?>
