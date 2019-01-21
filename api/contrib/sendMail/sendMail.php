<?php

tools_loadLibrary('PHPMailer.php');
tools_loadLibrary('fpdf/fpdf.php');
tools_loadLibrary('PHPMailer/src/SMTP.php');
tools_loadLibrary('PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendmail() {
    
    
    $clave = params_get("clave");
    $mail = new PHPMailer(TRUE);
    /* Set the mail sender. */
    $mail->setFrom('info@crlibre.org');
    /* Add a recipient. */
    $mail->addAddress('walner1borbon@gmail.com');
    /* Set the subject. */
    $mail->Subject = 'Documentos de Factura electronica #' . $clave;

    $pdf = new FPDF('P', 'mm', 'A4');

    $pdf->AddPage();

//set font to arial, bold, 14pt
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Image('https://crlibre.org/wp-content/uploads/2018/03/cropped-CRLibre-Logo_15-1.png', 60, 30, 90, 0, 'PNG');
//Cell(width , height , text , border , end line , [align] )
    $pdf->Cell(59, 55, ' ', 0, 1);
    $pdf->Cell(130, 5, 'CRLibre.org', 0, 0);
    $pdf->Cell(59, 5, 'FACTURA ', 0, 1); //end of line
//set font to arial, regular, 12pt
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(130, 5, 'Heredia', 0, 0);
    $pdf->Cell(59, 5, '', 0, 1); //end of line

    $pdf->Cell(130, 5, 'San Pablo, La Puebla', 0, 0);
    $pdf->Cell(25, 5, 'Fecha', 0, 0);
    $pdf->Cell(34, 5, '27/8/2018', 0, 1); //end of line

    $pdf->Cell(130, 5, 'Telefono 64206205', 0, 0);
    $pdf->Cell(25, 5, 'Factura #', 0, 0);
    $pdf->Cell(34, 5, '1234567', 0, 1); //end of line

    $pdf->Cell(130, 5, 'Fax ', 0, 0);
    $pdf->Cell(130, 5, 'Clave ' . $calve, 0, 0);
    $pdf->Cell(25, 5, 'Numero Cedula', 0, 0);
    $pdf->Cell(34, 5, '702320717', 0, 1); //end of line
//make a dummy empty cell as a vertical spacer
    $pdf->Cell(189, 10, '', 0, 1); //end of line
//billing address
    $pdf->Cell(100, 5, 'Cliente:', 0, 1); //end of line
//add dummy cell at beginning of each line for indentation
    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'Walner Borbon', 0, 1);

    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'CRLibre.org', 0, 1);

    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'Heredia San Pablo', 0, 1);

    $pdf->Cell(10, 5, '', 0, 0);
    $pdf->Cell(90, 5, 'tel: 64206205', 0, 1);

//make a dummy empty cell as a vertical spacer
    $pdf->Cell(189, 10, '', 0, 1); //end of line
//invoice contents
    $pdf->SetFont('Arial', 'B', 12);

    $pdf->Cell(45, 5, 'Description', 1, 0);
    $pdf->Cell(20, 5, 'Codigo', 1, 0);
    $pdf->Cell(20, 5, 'Unidad', 1, 0);
    $pdf->Cell(25, 5, 'Descuento', 1, 0);
    $pdf->Cell(20, 5, 'Impuesto', 1, 0);
    $pdf->Cell(30, 5, 'Pre. Unidad', 1, 0);
    $pdf->Cell(30, 5, 'SubTotal', 1, 1); //end of line

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
    
     $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    
    
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(45, 5, 'Impresora', 1, 0);
    $pdf->Cell(20, 5, '123', 1, 0);
    $pdf->Cell(20, 5, 'Sp', 1, 0);
    $pdf->Cell(25, 5, '0', 1, 0);
    $pdf->Cell(20, 5, '0', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1); //end of line
    $pdf->Cell(59, 10, ' ', 0, 1);
//summary
    $pdf->Cell(120, 15, '', 0, 0);
    
    $pdf->Cell(35, 5, 'Subtotal', 0, 0);
    $pdf->Cell(4, 5, '$', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1, 'R'); //end of line

    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(35, 5, 'Impuesto', 0, 0);
    $pdf->Cell(4, 5, '$', 1, 0);
    $pdf->Cell(30, 5, '0', 1, 1, 'R'); //end of line

    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(35, 5, 'Descuento', 0, 0);
    $pdf->Cell(4, 5, '$', 1, 0);
    $pdf->Cell(30, 5, '0%', 1, 1, 'R'); //end of line

    $pdf->Cell(120, 5, '', 0, 0);
    $pdf->Cell(35, 5, 'Total:', 0, 0);
    $pdf->Cell(4, 5, '$', 1, 0);
    $pdf->Cell(30, 5, '100000', 1, 1, 'R'); //end of line
    $altura=($pdf->GetPageHeight())-50;
    $anchura=($pdf->GetPageWidth());
    
    	// Posición: a 1,5 cm del final
			$pdf->SetY($altura);
			// Arial italic 8
			$pdf->SetFont('Arial','B',10);
			// Número de página
			
			$pdf->text($anchura/4,$altura,'            Emitida conforme lo establecido en la resolucion ');
			$pdf->text($anchura/4,$altura+5,'      de Facturacion Electronica, N DGT-R-48-2016 siete de octubre');
			$pdf->text($anchura/4,$altura+10,'de octubre de dos mil dieciseis de la Direccion General de Tributacion');
		
    $temp = tmpfile();
    $file_contents = $pdf->Output($temp, "S");
    /* Set the mail message body. */
    $mail->Body = 'Se adjuntan las facturas electronicas.';
    $mail->addStringAttachment(base64_decode(params_get("xmlEnvia")), 'Comprobante_' . $clave . '.xml');
    $mail->addStringAttachment(base64_decode(params_get("xmlHacienda")), 'MH_' . $clave . '.xml');
    $mail->addStringAttachment($file_contents, 'name file.pdf');

    /* Finally send the mail. */
    if (!$mail->send()) {
        /* PHPMailer error. */
        echo $mail->ErrorInfo;
    }
    return "test";
}
