<?php

function encode(){

$str = file_get_contents(params_get("xml"));
$result = base64_encode($str);
		return $result;	

}
?>
