<?php

/**
 * The api calls receiver :)
 */

// Cambiamos zona horaria a Costa Rica
date_default_timezone_set("America/Costa_Rica");

if (file_exists("settings.php"))
    include_once("settings.php");
else
{
    echo "No se ha encontrado el archivo de configuración. Ve a la carpeta \"www\", renombra el archivo \"settings.php.dist\" a \"settings.php\" y ajusta los valores de configuración.";
    exit;
}

$corePath = $config['modules']['coreInstall'];
if (file_exists($corePath."core/boot.php"))
    include_once($corePath."core/boot.php");
else
{
    echo "No se ha encontrado la carpeta de \"api\", por favor verifica la configuración \$config['modules']['coreInstall'] en \"settings.php\".";
    exit;
}

/*CORS to the app access*/
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control, Pragma, Origin, Authorization, Content-Type, X-Requested-With');
header('Access-Control-Allow-Methods: GET, PUT, POST');


//print_r($argv);

if (isset($_GET['w']))
    params_set('w', $_GET);
else if (isset($_POST['w']))
    params_set('w', $_POST);
else if (isset($_PUT))
    params_set('w', $_PUT);
else if (file_get_contents("php://input") !== null)
{
    $content = file_get_contents("php://input");
    if (is_string($content) && is_array(json_decode($content, true)) && (json_last_error() == JSON_ERROR_NONE))
    {
        $datas = json_decode($content, true);
        if (isset($datas['w']))
            params_set('w', $datas);
    }
    else
    {
        grace_error("File Content Input: ". $content);
        die("La informacion json enviada contiene errores.");
    }
}
else if (isset($argv))
{
    grace_debug("Booting from cli...");

    foreach ($argv as $r)
    {
       list($key, $val) = explode("=", $r);
        $_POST[$key] = $val;
        grace_debug("Adding to post: $key => $val");
    }

    params_set('w', $_POST); 
}

$r = boot_itUp();
