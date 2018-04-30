<?php

function encode(){
modules_loader("files");
$str = file_get_contents(filesGetUrl(params_get("downloadCode")));
grace_debug($str);
$result = base64_encode($str);
return $result;	
}
?>
