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
function check_bootMeUp()
{
    // Just booting up
}

/**
 * Init function
 */
function check_init()
{
    $paths = array(
        array(
            'r'             => 'checkxml',
            'action'        => 'check_XML',
            'access'        => 'users_openAccess',
            'access_params' => 'accessName',
            'params'        => array(
                array("key" => "tipoDocumento", "def" => "", "req" => true)
              //  array("key" => "tipoDocumento", "def" => "", "req" => true),                
              //  array("key" => "formato", "def" => "", "req" => true),
              //  array("key" => "xml", "def" => "", "req" => true)
            ),
            'file'          => 'check.php'
        )
    );

    return $paths;
}

/* * *********************************************** */
//In the access you can use users_openAccess if you want anyone can use the function
// or users_loggedIn if the user must be logged in
/* * *********************************************** */

/**
 * Get the perms for this module
 */
function MODULENAME_access()
{
    $perms = array(
        array(
            # A human readable name
            'name'          => 'Do something with this module',
            # Something to remember what it is for
            'description'   => 'What can be achieved with this permission',
            # Internal machine name, no spaces, no funny symbols, same rules as a variable
            # Use yourmodule_ prefix
            'code'          => 'mymodule_access_one',
            # Default value in case it is not set
            'def'           => false, //Or true, you decide
        ),
    );
}

/**@}*/
/** @}*/
