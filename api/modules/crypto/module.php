
<?php

function crypto_bootMeUp(){
	// Just booting up
}
/**
 * Init function
 */
function crypto_init(){
	$paths = array(

		array(
			'r' => 'encrypt',
			'action' => 'crypto_encrypt',
			'access' => 'users_openAccess',
			'params' => array(
				array("key" => "textEncrypt", "def" => "", "req" => true)
			),
			'file' => 'crypto.php'
		),
		array(
			'r' => 'desencrypt',
			'action' => 'crypto_desencrypt',
			'access' => 'users_openAccess',
			'params' => array(
				array("key" => "textDesEncrypt", "def" => "0", "req" => false)
			),
			'file' => 'crypto.php'
		),
		array(
			'r' => 'makeKey',
			'action' => 'makeKey256',
			'access' => 'users_openAccess',
			'file' => 'crypto.php'
		)
	);
	return $paths;
}