<?php

define('CALA_VERSION', 0.1);

/**
 * @page
 */

include_once("grace.php");
include_once("params.php");
include_once("modules.php");
include_once("conf.php");
include_once("tools.php");


/** @defgroup Constants
 *  @{
 */

//! Bad request done
define('ERROR_BAD_REQUEST', -1);

//! Some generic error, if no real error happened, but nothing was found or the process did not go as espected
define('ERROR_ERROR', -2);

//! If the w is not setted, there is no module to load
define('ERROR_MODULE_UNDEFINED', -3);

//! If the w is setted, but module not found
define('ERROR_MODULE_NOT_FOUND', -4);

//! If the w is not setted, there is no module to load
define('ERROR_FUNCTION_NOT_FOUND', -5);

//! ALl is good
define('SUCCESS_ALL_GOOD', 1);

//! This will tell me if I am runnig in CLI mode
define('CLI_MODE', false);

/** @}*/

/**
 * Boot me up!
 */
function boot_itUp($mode = 'web'){

	grace_debug("Booting up V:" . CALA_VERSION);

	# Load all core modules
	# @todo Call the current requested module first in case it wants to change the core modules to be loaded
	boot_loadAllCoreModules();

	if($mode == 'web'){
		//	
	}
	else{
		// Set mode
		conf_set('mode', 'core', 'cli');
		params_cliLoadOpts(cala_init());
	}

	# Select and load the correct called module
	# @todo if the module is core and it was already loaded, don't do it again :)
	modules_loader(params_get('w', 'cala'));

	# Init this call
	return boot_initThisPath();
}

/**
 * Call the init function in the correct module
 */
function boot_initThisPath(){

    /** @bug Apparently post requests (at least from Java) require a 
     *  \n (breakline) in each post request parameter, which breaks everything
     *  this issue is still under investigation
     */
	$f = preg_replace("/[\n\r\f]+/m", "", params_get('w', 'core') . "_init");

	if(function_exists($f)){
        
		grace_debug("Function found");
		$response = tools_proccesPath(call_user_func($f));
        
	}else if(params_get("w", "--") == "--"){
		$response = ERROR_MODULE_UNDEFINED;
    }else{
        $response = ERROR_MODULE_NOT_FOUND;
	}
	return tools_reply($response);
}

/**
 * Load all core modules
 * @bug If the called module is a core module, it will get booted twice
 */
function boot_loadAllCoreModules(){

    grace_debug("Loading all core modules");

    foreach(conf_get('coreLoad', 'modules') as $module){
        grace_debug("Loading module: >>(" . $module . ")<<");
        modules_loader($module);
    }
}

/**
 * Load boot up modules
 */
function boot_loadBootModules(){
    //Todo
}