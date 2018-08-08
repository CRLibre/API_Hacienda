
<?php

//Globales Variables
/* En este se define el tipo de encriptacion
  Ademas se va adefinir el key de encriptacion
  Se inicializa el vector para encriptar y desencriptar
 */
//Esto toma la configuracion de los settings.php del www


function makeKey256() {
    return base64_encode(openssl_random_pseudo_bytes(32));
}

function crypto_encrypt($data = '') {
    $key = conf_get('key', 'crypto');
    if ($data == '') {
        $data = params_get('textEncrypt', '');
    }
    // se retira el base64 del key
    $encryption_key = base64_decode($key);
    // Se genera un vetor inicial para el proceso
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    // Se encripta usando AES 256 usando la key y el ventor anterior
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    // ese vector es necesario guardarlo, por lo que se concatena con ::
    //se encodea a base64 para tener una sala cadena
    $final = base64_encode($encrypted . '::' . $iv);
    return $final;
}

function crypto_desencrypt($data = '') {
    
    $key = conf_get('key', 'crypto');
    if ($data == '') {
        $data = params_get('textDesEncrypt', '');
    }
    // se retira el base64 del key
    $encryption_key = base64_decode($key);
    // Para desencriptar se debe de des encodear el base64 y leer la parte despues del ::
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    //una vez separado el $iv y ya tenemos el $key se procede con las desencriptacion
    $final;
    try {
        $final = openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    } catch (Exception $e) {
        
        $arrayResp = array(
            "Status" => "Error occurred",
            "text" => $e->getMessage()
        );
        return $arrayResp;
    }
    return $final;
}

function crypto_test() {
    return ("hola");
}
