<?php
/** @file module.php
 * Módulo de Ejemplo del API
 * Ver uso en https://github.com/CRLibre/API_Hacienda/wiki/Primera-petici%C3%B3n-al-API
 */

/** \addtogroup Core 
 *  @{
 */

/**
 * \defgroup Module
 * @{
 */


/**
 * Boot up procedure
 */
function ejemplo_bootMeUp(){
	// Just booting up
}

/**
 * Init function
 */


function ejemplo_init(){

	$paths = array(
		array(
			'r' => 'hola',
			'action' => 'module_hola',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'file' => 'ejemplo.php'
		),
		array(
			'r' => 'un_usuario',
			'action' => 'unUsuario',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'params' => array(
				array("key" => "nombre", "def" => "", "req" => true),
				array("key" => "apellido", "def" => "", "req" => true)
			),	
			'file' => 'ejemplo.php'
		)
	);

	return $paths;
}


/**************************************************/
//In the access you can use users_openAccess if you want anyone can use the function
// or users_loggedIn if the user must be logged in
/**************************************************/



/**
 * Get the perms for this module
 */
function MODULENAME_access(){

	$perms = array(
		array(
			# A human readable name
			'name'        => 'Do something with this module',
			# Something to remember what it is for
			'description' => 'What can be achieved with this permission',
			# Internal machine name, no spaces, no funny symbols, same rules as a variable
			# Use yourmodule_ prefix
			'code'        => 'mymodule_access_one',
			# Default value in case it is not set
			'def'        => false, //Or true, you decide
		),
	);

}

/**@}*/
/** @}*/
