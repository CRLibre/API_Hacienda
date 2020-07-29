#!/usr/bin/env php

<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
 * Copyright (C) 2018 Moritz von Schweinitz
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

 # TODOs:
 # - if no xmlIN and/or xmlOut arguments are given, read/write to STDIN/STDOUT.

 date_default_timezone_set("America/Costa_Rica");

 include __DIR__.'/../contrib/signXML/Firmadohaciendacr.php';

 $cert      = $argv[1];
 $pin       = $argv[2];
 $xmlIn     = $argv[3];
 $xmlOut    = $argv[4];

 if ($xmlIn == '-')
    $xmlIn = "php://stdin";

 #$doctype = $argv[5];

 if (!file_exists($xmlIn))
 {
    die("Infile '$xmlIn' doesn't exist\n");
 }

 if (!$xmlOut)
 {
    die("Need an outfile.");
 }

 $signer = new Firmadocr();
 fwrite(STDERR, "P12:\t" . $cert . "\n");
 # echo "PIN: " . $pin .  "\n";
 fwrite(STDERR, "In:\t" . $xmlIn . "\n");
 fwrite(STDERR, "Out:\t" . $xmlOut . "\n");

 $xmlIn = file_get_contents( $xmlIn );

 #if (preg_match('/\<FacturaElectronica\>/',$xmlIn))
 if (preg_match('/\<FacturaElectronica/', $xmlIn))
 {
    $doctype = '01';
    fwrite(STDERR,  "Tipo:\t($doctype) FE\n");
 }
 else if (preg_match('/\<NotaDebitoElectronica/', $xmlIn))
 {
    $doctype = '02';
    fwrite(STDERR,  "Tipo:\t($doctype) ND\n");
 }
 else if (preg_match('/\<NotaCreditoElectronica/', $xmlIn))
 {
    $doctype = '03';
    fwrite(STDERR,  "Tipo:\t($doctype) NC\n");
 }
 else if (preg_match('/\<TiqueteElectronico/', $xmlIn))
 {
    $doctype = "04";
    fwrite(STDERR,  "Tipo:\t($doctype) TE\n");
 }
 else if (preg_match('/\<MensajeReceptor/', $xmlIn))
 {
    $doctype = "05";
    fwrite(STDERR,  "Tipo:\t($doctype) MR\n");
 }

 if ($doctype != '01' && $doctype != '02' && $doctype != '03' && $doctype != '04' && $doctype != '05')
 {
    #die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firmar> <tipodoc>\n");
    die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firma> <xml_con_firma>\n(xml_sin_firma y/o xml_con_firma puede ser '-' para STDIN o STDOUT, respetivamente)");
 }

 #exit();

 $xmlIn = base64_encode($xmlIn);

 $foo = $signer->firmar($cert, $pin, $xmlIn, $doctype);
 $foo = base64_decode($foo);

 #echo "\n\nOUT\n\n$foo\n";

 if ($xmlOut == '-')
    echo $foo;
 else
    file_put_contents($xmlOut, $foo);

?>
