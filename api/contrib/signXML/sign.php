<?php

function sign(){
    
	$jarUrl= params_get("jarUrl");        
        $p12Url= params_get("p12Url");
        $p12Pin= params_get("p12Pin");
        $inXmlUrl= params_get("inXmlUrl");
        $outXmlUrl= params_get("outXmlUrl");
        
        $shell='java -jar '.$jarUrl .' sign ' .$p12Url.' '. $p12Pin.' '. $inXmlUrl.' '. $outXmlUrl;
         shell_exec($shell);          
         echo $shell    ;
  //      return $shell;	
}

?>
