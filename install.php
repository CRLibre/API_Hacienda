<?php

$path = $_SERVER["SCRIPT_FILENAME"];
$path = str_replace("install.php", "", $path);

$os = $_SERVER["OS"];

echo "\n\n\n";


//print_r($_SERVER);

echo "\n\n\n/*****************************************************************/\n
                    Welcome to the CalaAPI Installer  V0.1                  \n
/*****************************************************************/\n\n\n";

echo "The CalaAPI, for security reasons it has to be installed in two different areas. \n
The www MUST be installed into a public area, for example public_html/www or htdocs. \n \n
The second part wich is the api file MUST be installed into a non public area, such as /var/subs \n\n";


$wwwDone = false;
$apiDone = false;
$wwwPath = "";
$apiPath = "";

$allDone = false;

while(!$wwwDone){

    echo "So, where do you want to locate the www file?: ";
    $line = trim(fgets(STDIN));

    echo "\nIs this the right path for www? (Y/N): $line\n";
    $wwwPath = $line;
    
    $line = trim(fgets(STDIN));

    $wwwDone = $line == "Y";    
}

echo "\n\nNice! Lets keep on rocking! \n\n\n";

while(!$apiDone){

    echo "So, where do you want to locate the api file?: ";
    $handle = fopen ("php://stdin","r");
    $line = trim(fgets(STDIN));

    echo "\nIs this the right path for www? (Y/N): $line\n";
    $apiPath = $line;
    
    $line = trim(fgets(STDIN));
    $apiDone = $line == "Y";
}


echo "\n\n So we will going to install \n\nwww:$wwwPath \napi:$apiPath \n\n\n\n\n\n";

$www = $path . "www";

if(rCopy($www, $wwwPath, $os, "www")){
    echo "\n[OK] >> www";
    $allDone = true;
}else{
    echo "\n[ERROR] >> www";
    $allDone = false;
}

$api = $path . "api";

if($allDone && rCopy($api, $apiPath, $os, "api")){
    echo "\n[OK] >> api";
    $allDone = true;
}else{
    echo "\n[ERROR] >> api";
    $allDone = false;
}

/*********************************************************************/
/*                      DB conf                                      */
/*********************************************************************/
$dbName = "";
$dbPss = "";
$dbUser = "";
$dbHost = "";

$dbCorrect = false;


if($allDone){
    while(!$dbCorrect){
        echo "\n\nIt seems all is working well. Lets configure the DB info\n\n";
        echo "\nDB Name:";
        $line = trim(fgets(STDIN));
        $dbName = $line;
        
        echo "\nDB User:";
        $line = trim(fgets(STDIN));
        $dbUser = $line;
        
        echo "\nDB Pass:";
        $line = trim(fgets(STDIN));
        $dbPss = $line;
        
        echo "\nDB Host:";
        $line = trim(fgets(STDIN));
        $dbHost = $line;
        
        echo "\n Is all correct? \nDB Name:$dbName \nDB User:$dbUser \nDB Pass:$dbPss \nDB Host:$dbHost \n\n(Y / N):";
        
        $line = trim(fgets(STDIN));
        $dbCorrect = $line == "Y";
    }
    
    $key = md5($dbName . $dbHost . $dbPss . $dbUser);
    
    $tpl = "";
    if(strpos($os, "Windows") !== false){
        $tpl = file_get_contents("$wwwPath\www\settings.tpl.php");
    }else{
        $tpl = file_get_contents("$wwwPath/www/settings.tpl.php");
    }
    
    $tpl = str_replace("{dbName}", $dbName, $tpl);
    $tpl = str_replace("{dbPss}", $dbPss, $tpl);
    $tpl = str_replace("{dbUser}", $dbUser, $tpl);
    $tpl = str_replace("{dbHost}", $dbHost, $tpl);
    $tpl = str_replace("{cryptoKey}", $key, $tpl);
    
    $realPath = str_replace("\\", "/", $apiPath);
    
    $tpl = str_replace("{apiPath}", "$realPath/api/", $tpl);
    
    $file = fopen("$wwwPath/www/settings.php", "w"); 
    if(fwrite($file, $tpl)){
        echo "DONE";
    } 
}


function rCopy($dir, $dest, $os, $f){
    if(strpos($os, "Windows") !== false){
        mkdir("$dest\\$f");
        $cmd = "xcopy $dir $dest\\$f\\ /E";
        echo "\n\n$cmd\n\n";
        return exec($cmd);
    }else{
        mkdir("$dest/$f");
		$cmd = "cp -r $dir/* $dest/$f";
		echo "\n\n$cmd\n\n";
        $res = exec($cmd);
		echo $res . "<<<<<<<<<";
		return true;
    }
}
