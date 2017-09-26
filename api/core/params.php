<?php

global $params;

/**
 * Get me a parameter
 */
function params_get($p, $def = false){

	global $params;

	# Get them all
	if($p === false){
		return $params;
	}

	if(isset($params[$p]) && trim($params[$p]) != ''){
		return $params[$p];
	}else{
		return $def;
	}
}

/**
 * Set config parameters
 */
function params_set($p, $val = false, $override = false){

	global $params;

	if(is_array($val)){
		foreach($val as $vv => $v){
			_params_set($vv, $v, $override);
		}
	}else{
		_params_set($p, $val, $override);
	}

	return $params[$p];
}

/**
 * Helper function to actually store the params
 */
function _params_set($p, $val = false, $override){

	global $params;

	if(isset($params[$p]) && $override){
		$params[$p] = $val;
	}else{
		$params[$p] = $val;
	}
	return $params[$p];
}

/**
 * Verify request and set default values when required
 * @todo Verify possible options for each a|b|c etc...
 */
function params_verifyRequest($keys){

	foreach($keys as $key){
		if(params_get($key["key"], '') === ''){
			grace_debug("Missing param: " . $key["key"]);
			if($key["req"]){
				tools_reply(ERROR_BAD_REQUEST, true);
			}
			# Set the default value
			else{
				grace_debug("Using default");
				params_set($key["key"], $key["def"]);
			}
		}
	}

}

/**
 * Loads the options from the command line
 */
function params_cliLoadOpts($allParams){

	grace_debug("Loading in CLI mode");

    echo "loading";

	# Load the params
	//$params = core_init();

	# The actual params to be considered
	$params = array();

	$opts = "";
	$longOpts = array();

	# Extract only the params
	foreach($allParams as $param){
		if(isset($param['params'])){
			$params[] = $param['params'];
		}
	}

	# For some reason they are stored in pos 0 of the array
	$params = $params[0];

    print_r($params);
	foreach($params as $p){
		$longOpt = $p['key'];
		$opts .= $p['cli'];
		# Is it mandatory?
		if($p['req']){
			$opts .= ":";
			$longOpt .= ":";
		}
		else{
			$opts .= "::";
			$longOpt .= "::";
		}
		$longOpts[] = $longOpt;
	}

	grace_debug("Opts requested: " . $opts);

	# I need the basics in order to start
	$args = getopt($opts, $longOpts);

	# Now, lets extract them :)
	foreach($params as $p){
		# Was it sent as longOpt?
		if(isset($args[$p['key']])){
			params_set($p['key'], $args[$p['key']]);
		}
		elseif(isset($args[$p['cli']])){
			params_set($p['key'], $args[$p['cli']]);
		}
		# If it is optional, I will load the default value?
		elseif($p['req'] == false){
			params_set($p['key'], $p['def']);
		}
	}

	//print_r(params_get(false));
	params_verifyRequest($params);
	//if($args[''] == 8)
	//var_dump($args);
}

/**
 * This are the core basic params required to function
 */
function core_getBasicParams(){

}
