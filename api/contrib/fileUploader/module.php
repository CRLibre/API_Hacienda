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
function fileUploader_bootMeUp(){
	// Just booting up
}

/**
 * Init function
 */


function fileUploader_init(){

	$paths = array(
		array(
			'r' => '',
			'action' => '',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'params' => array(array("key" => "", "def" => "", "req" => true)),
			'file' => 'file.php'
		),
		array(
			'r' => 'subir_certif',
			'action' => 'uploadCert',
			'access' => 'users_loggedIn', 
			'access_params' => 'accessName',
			'file' => 'uploader.php'
		),
            array(
			'r' => 'subir_xml',
			'action' => 'uploadXml',
			'access' => 'users_loggedIn', 
			'access_params' => 'accessName',
			'file' => 'uploader.php'
		),
		array(
			'r' => 'test',
			'action' => 'doTest',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'file' => 'uploader.php'
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
function fileUploader_access(){

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
