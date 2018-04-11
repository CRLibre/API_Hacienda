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
function MODULENAME_bootMeUp(){
	// Just booting up
}

/**
 * Init function
 */
function MODULE_init(){

	$paths = array(
		array(
			'r' => '',
			'action' => '',
			'access' => 'users_openAccess',
			'access_params' => 'accessName',
			'params' => array(array("key" => "", "def" => "", "req" => true)),
			'file' => 'file.php'
		)
	);

	return $paths;
}

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
