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
function sendMail_bootMeUp(){
	// Just booting up
}

/**
 * Init function
 */


function sendMail_init(){

	$paths = array(
		array(
			'r' => 'sendmail',
			'action' => 'sendmail',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'params' => array(
				array("key" => "xmlEnvia", "def" => "", "req" => true),
				array("key" => "facturaPDF", "def" => "", "req" => true),
				array("key" => "xmlHacienda", "def" => "", "req" => true),
				array("key" => "clave", "def" => "", "req" => true),
				
			),
			'file' => 'sendMail.php'
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
