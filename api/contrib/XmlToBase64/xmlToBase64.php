<?php

function encode(){

$str = params_get("xml");
$result = base64_encode($str);
		return $result;	
}
?>
