<?php

 require 'Firmadohaciendacr.php';

function sign(){
        $p12Url= params_get('p12Url');
        $pinP12= params_get('pinP12');
        $inXmlUrl= params_get('inXmlUrl');
        $outXmlUrl= params_get('outXmlUrl');
        
        $fac = new Firmadocr();        
        $fac->firmar($p12Url, $pinP12,$inXmlUrl,$outXmlUrl );
        
        return $outXmlUrl;
                         
}

?>
