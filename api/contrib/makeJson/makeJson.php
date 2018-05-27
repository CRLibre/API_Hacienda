  n nh h hb<?php

function makeFinalJson(){
    	$clave= params_get("clave");        
        $fecha= params_get("fecha");
        $emi_tipoIdentificacion= params_get("emi_tipoIdentificacion");
        $emi_numeroIdentificacion= params_get("emi_numeroIdentificacion");
        $recp_tipoIdentificacion= params_get("recp_tipoIdentificacion");
        $recp_numeroIdentificacion= params_get("recp_numeroIdentificacion");
        $comprobanteXml= params_get("comprobanteXml");
        $response=array();
        $response = array('clave'=>$clave,
                                'fecha'=>$fecha,        
                                'emisor'=>array('tipoIdentificacion'=>$emi_tipoIdentificacion,'numeroIdentificacion'=>$emi_numeroIdentificacion),
                                'receptor'=>array('tipoIdentificacion'=>$recp_tipoIdentificacion,'numeroIdentificacion'=>$recp_numeroIdentificacion),                    
                                'comprobanteXml'=>$comprobanteXml);                            
                            
    return  $response;
}
?>
