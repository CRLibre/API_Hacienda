<?php

/**
 * The api calls receiver :)
 */
 
if (file_exists("settings.php")
    include_once("settings.php");
else
{
    echo "No se ha encontrado el archivo de configuración. Ve a la carpeta \"www\", renombra el archivo \"settings.php.dist\" a \"settings.php\" y ajusta los valores de configuración.";
    exit;
}

$corePath = $config['modules']['coreInstall'];
include_once($corePath . "/core/boot.php");

/*CORS to the app access*/
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control, Pragma, Origin, Authorization, Content-Type, X-Requested-With');
header('Access-Control-Allow-Methods: GET, PUT, POST');


//print_r($argv);

if(isset($_GET['w'])){
	params_set('w', $_GET);
}elseif(isset($_POST['w'])){
	params_set('w', $_POST);
}elseif(isset($_PUT)){
	params_set('w', $_PUT);
}elseif(isset($argv)){
    grace_debug("Booting from cli...");
    foreach($argv as $r){
       list($key, $val) = explode("=", $r);
        $_POST[$key] = $val;   
        grace_debug("Adding to post: $key => $val");
    }
	params_set('w', $_POST); 
}
$r = boot_itUp();
