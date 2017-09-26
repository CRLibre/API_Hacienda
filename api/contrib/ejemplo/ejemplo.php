<?php

function module_hola(){
	return "hola :)";
}

function unUsuario(){
	return params_get("nombre") . ", " . params_get("apellido");
}

?>
