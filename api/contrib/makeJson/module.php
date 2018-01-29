<?php
/** @file module.php
 * A brief file description.
 * A more elaborated file description.
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
function makeJson_bootMeUp(){
	// Just booting up
}

/**
 * Init function
 */


function makeJson_init(){

	$paths = array(
		array(
			'r' => 'makeJson',
			'action' => 'makeFinalJson',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',			
			'params' => array(
				array("key" => "clave", "def" => "", "req" => true),
				array("key" => "fecha", "def" => "", "req" => true),
				array("key" => "emi_tipoIdentificacion", "def" => "", "req" => true),
				array("key" => "emi_numeroIdentificacion", "def" => "", "req" => false),			
				array("key" => "recp_tipoIdentificacion", "def" => "", "req" => true),
                                array("key" => "recp_numeroIdentificacion", "def" => "", "req" => true),
                                array("key" => "comprobanteXml", "def" => "", "req" => true)
							
			),			
			'file' => 'makeJson.php'
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
