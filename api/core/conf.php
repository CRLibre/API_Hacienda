<?php

/**
 * @page This page is for...
 */

/**
 * Get config parameters
 */
function conf_get($what, $whom, $def = false){

	global $config;

	if(isset($config[$whom][$what])){
		return $config[$whom][$what];
	}else{
		return $def;
	}
}

/**
 * Set config parameters
 */
function conf_set($what, $whom, $val = false, $override = false){

	global $config;

	if(isset($config[$whom][$what]) && $override){
		$config[$whom][$what] = $val;
	}else{
		$config[$whom][$what] = $val;
	}

	return $config[$whom][$what];
}

function conf_getAll(){

	global $config;
	return $config;
}
