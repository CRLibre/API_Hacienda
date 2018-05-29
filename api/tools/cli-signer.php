#!/usr/local/bin/php

<?php
        include '../contrib/signXML/Firmadohaciendacr.php';

        $cert = $argv[1];
        $pin = $argv[2];
        $xmlIn = $argv[3];

        $xmlOut = $argv[4];
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
        echo "P12:\t" . $cert . "\n";
#        echo "PIN: " . $pin .  "\n";
        echo "In:\t" . $xmlIn . "\n";
        echo "Out:\t" . $xmlOut . "\n";

        $xmlIn = file_get_contents( $xmlIn );

        #if (preg_match('/\<FacturaElectronica\>/',$xmlIn))
        if (preg_match('/\<FacturaElectronica/',$xmlIn))
        {
            echo "FE\n";
            $doctype = '01';
        }
        else if (preg_match('/\<NotaDebitoElectronica/',$xmlIn))
        {
            echo "ND\n";
            $doctype = '02';
        }
        else if (preg_match('/\<NotaCreditoElectronica/',$xmlIn))
        {
            echo "NC\n";
            $doctype = '03';
        }
        else if (preg_match('/\<TiqueteElectronico/',$xmlIn))
        {
            echo "TE\n";
            $doctype = "04";
        }
        else if (preg_match('/\<MensajeReceptor/',$xmlIn))
        {
            echo "MR\n";
            $doctype = "05";
        }
        else
        {
        }

        if ( $doctype != '01' and $doctype != '02' and $doctyp != '03' and $doctype != '04' and $doctype != '05'  )
        {
                #die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firmar> <tipodoc>\n");
                die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firma> <xml_con_firma>\n");
        }


        #exit();

        $xmlIn = base64_encode( $xmlIn );

        $foo = $signer->firmar($cert,$pin,$xmlIn,$doctype);
        $foo = base64_decode( $foo );

        #echo "\n\nOUT\n\n$foo\n";

        file_put_contents( $xmlOut, $foo );

?>
