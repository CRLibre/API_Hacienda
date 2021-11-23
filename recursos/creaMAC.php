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
<MensajeReceptor xmlns="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="https://tribunet.hacienda.go.cr/docs/esquemas/2017/v4.2/mensajeReceptor https://tribunet.hacienda.go.cr/docs/esquemas/2016/v4.2/MensajeReceptor_4.2.xsd">
        <Clave>50630041800011538077800100001010000000269183168485</Clave>
        <NumeroCedulaEmisor>000702320717</NumeroCedulaEmisor>
        <FechaEmisionDoc>2018-04-30T11:40:03-06:00</FechaEmisionDoc>
        <Mensaje>1</Mensaje>
        <DetalleMensaje>Acepto la factura</DetalleMensaje>
        <MontoTotalImpuesto>0</MontoTotalImpuesto>
        <TotalFactura>150000000</TotalFactura>
        <NumeroCedulaReceptor>000702320717</NumeroCedulaReceptor>
        <NumeroConsecutivoReceptor>00100001051522773402</NumeroConsecutivoReceptor>
   </MensajeReceptor>';
   /*
    <clave> => Clave del documento que estoy aceptando
    <NumeroConsecutivoReceptor> => Este se debe de generar segun las indicaciones del consecutivo para Mensaje Receptor
    <Mensaje> => 1 para aceptacion total, 2 aceptacion parcial, 3 para rechazo.
    <TotalFactura> => debe de ser igual al documento que se esta aprobando.
    <NumeroCedulaEmisor> => Este debe tener estrictamente 12 caracteres, por lo tanto en caso de cedula fisica se debe completar con 000
    <NumeroCedulaReceptor> => Este debe tener estrictamente 12 caracteres, por lo tanto en caso de cedula fisica se debe completar con 000
   */
$dom = new DomDocument();
$dom->preseveWhiteSpace = FALSE;
$dom->loadXML($xmlString);
$dom->save('MAC.xml');
?>
