#!/usr/local/bin/php

<?php
        include '../contrib/signXML/Firmadohaciendacr.php';

        $cert = $argv[1];
        $pin = $argv[2];
        $xmlIn = $argv[3];
        
        $xmlOut = $argv[4];
        $doctype = $argv[5];
        
        if ( ! file_exists( $xmlIn ) )
        {
                die("Infile '$xmlIn' doesn't exist\n");
        }


        if ( $doctype != '01' and $doctype != '02' and $doctyp != '03' and $doctype != '04' and $doctype != '05'  )
        {
                die("Usage: cli-signer.php <archivo.p12> <claveP12> <xml_sin_firmar> <tipodoc>\n");
        }


        $signer = new Firmadocr();
        echo "Certificado: " . $cert . "\n";
        echo "PIN: " . $pin .  "\n";
        echo "IN: " . $xmlIn . "\n";
        echo "OUT: " . $xmlOut . "\n";

        $xmlIn = file_get_contents( $xmlIn );
        $xmlIn = base64_encode( $xmlIn );

        $foo = $signer->firmar($cert,$pin,$xmlIn,$doctype);
        $foo = base64_decode( $foo );

        #echo "\n\nOUT\n\n$foo\n";

        file_put_contents( $xmlOut, $foo );

?>
