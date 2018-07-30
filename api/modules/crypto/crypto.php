<?php
function makeKey256(){
    return base64_encode(openssl_random_pseudo_bytes(32));
}

function crypto_encrypt($str = '') {
    $key = conf_get('key', 'crypto');
    if ($str == '') {
        $str = params_get('textEncrypt', '');
    }    
    // se retira el base64 del key
    $encryption_key = base64_decode($key);
    // Se genera un vetor inicial para el proceso
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    // Se encripta usando AES 256 usando la key y el ventor anterior
    $encrypted = openssl_encrypt($str, 'aes-256-cbc', $key, 0, $iv);
    // ese vector es necesario guardarlo, por lo que se concatena con ::
    //se encodea a base64 para tener una sala cadena
    $final = base64_encode($encrypted . '::' . $iv);   
    return $final;
}

function crypto_desencrypt($str = '') {    
    $key = conf_get('key', 'crypto');
    if ($str == '') {
        $str = params_get('textDesEncrypt', '');
    }
    // se retira el base64 del key
    $encryption_key = base64_decode($key);
    // Para desencriptar se debe de des encodear el base64 y leer la parte despues del ::
    @list($encrypted_data, $iv) = explode('::', base64_decode($str), 2);
    //una vez separado el $iv y ya tenemos el $key se procede con las desencriptacion
    $final = openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    
    return $final;
}

function crypto_test(){
    return ("hola");
}