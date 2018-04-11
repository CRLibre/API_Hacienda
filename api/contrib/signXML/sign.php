<?php




function signFE(){
        require 'Firmadohaciendacr.php';
        modules_loader("files");        
        $p12Url= filesGetUrl(params_get('p12Url'));
        $pinP12= params_get('pinP12');
        $inXmlUrl= filesGetUrl(params_get('inXmlUrl'));
        $outXmlUrl= params_get('outXmlUrl');
        $tipodoc=params_get('tipodoc');
        
        $fac = new Firmadocr();        
        $fac->firmar($p12Url, $pinP12,$inXmlUrl,$outXmlUrl,$tipodoc );
        
        return $outXmlUrl;
}
?>
