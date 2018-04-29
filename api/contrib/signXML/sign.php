<?php




function signFE(){
        require 'Firmadohaciendacr.php';
        modules_loader("files");        
        $p12Url= filesGetUrl(params_get('p12Url'));
        $pinP12= params_get('pinP12');
        $inXml= params_get('inXml');        
        $tipodoc=params_get('tipodoc');
        
        $fac = new Firmadocr();        
        //$inXmlUrl debe de ser en Base64 
        //$p12Url es un downloadcode previamente suministrado al subir el certificado en el modulo fileUploader -> subir_certif
        //Tipo es el tipo de documento 
        // 01 FE
        //02 ND
        //03 NC
        //04 TE
        //05 Mensaje Receptor
        $returnFile=$fac->firmar($p12Url, $pinP12,$inXml,$tipodoc );
        
        return $returnFile;
}
?>
