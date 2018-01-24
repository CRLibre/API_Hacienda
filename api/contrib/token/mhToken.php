<?php

function gettoken(){
	return "Esto me va a dar el primer token :)";
}

function refreshToken(){
	return "Esto me va a refrescar el token :)";
}

function unUsuario(){
	return params_get("nombre") . ", " . params_get("apellido");
}

?>
