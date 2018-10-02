#!/usr/bin/env php

<?php
/*
# Copyright 2018 Moritz von Schweinitz
# Published under the GNU Affero General Public License
# https://www.gnu.org/licenses/agpl-3.0.en.html
# For the project https://github.com/CRLibre/API_Hacienda

# TODOs:
# - if no xmlIN and/or xmlOut arguments are given, read/write to STDIN/STDOUT.
*/
        date_default_timezone_set("America/Costa_Rica");

        include __DIR__.'/../contrib/signXML/Firmadohaciendacr.php';

        $cert = $argv[1];
        $pin = $argv[2];
        $xmlIn = $argv[3];
        $xmlOut = $argv[4];

        if ( $xmlIn == '-' )
        {
                $xmlIn = "php://stdin";
        }

        #$doctype = $argv[5];

        if ( ! file_exists( $xmlIn ) )
        {
                die("Infile '$xmlIn' doesn't exist\n");
        }

        if ( ! $xmlOut )
        {
                die("Need an outfile.");
        }


        $signer = new Firmadocr();
        fwrite(STDERR, "P12:\t" . $cert . "\n");
#        echo "PIN: " . $pin .  "\n";
        fwrite(STDERR, "In:\t" . $xmlIn . "\n");
        fwrite(STDERR, "Out:\t" . $xmlOut . "\n");

        $xmlIn = file_get_contents( $xmlIn );

        #if (preg_match('/\<FacturaElectronica\>/',$xmlIn))
        if (preg_match('/\<FacturaElectronica/',$xmlIn))
        {
            $doctype = '01';
            fwrite(STDERR,  "Tipo:\t($doctype) FE\n");
        }
        else if (preg_match('/\<NotaDebitoElectronica/',$xmlIn))
        {
            $doctype = '02';
            fwrite(STDERR,  "Tipo:\t($doctype) ND\n");
        }
        else if (preg_match('/\<NotaCreditoElectronica/',$xmlIn))
        {
            $doctype = '03';
            fwrite(STDERR,  "Tipo:\t($doctype) NC\n");
        }
        else if (preg_match('/\<TiqueteElectronico/',$xmlIn))
        {
            $doctype = "04";
            fwrite(STDERR,  "Tipo:\t($doctype) TE\n");
        }
        else if (preg_match('/\<MensajeReceptor/',$xmlIn))
        {
            $doctype = "05";
            fwrite(STDERR,  "Tipo:\t($doctype) MR\n");
        }
        else
        {
        }

        if ( $doctype != '01' and $doctype != '02' and $doctype != '03' and $doctype != '04' and $doctype != '05'  )
        {
                #die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firmar> <tipodoc>\n");
                die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firma> <xml_con_firma>\n(xml_sin_firma y/o xml_con_firma puede ser '-' para STDIN o STDOUT, respetivamente)");
        }


        #exit();

        $xmlIn = base64_encode( $xmlIn );

        $foo = $signer->firmar($cert,$pin,$xmlIn,$doctype);
        $foo = base64_decode( $foo );

        #echo "\n\nOUT\n\n$foo\n";

        if ( $xmlOut == '-' )
        {
                echo $foo;
        }
        else
        {
                file_put_contents( $xmlOut, $foo );
        }

?>
