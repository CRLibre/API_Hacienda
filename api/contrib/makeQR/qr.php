<?php

function makeQR()
{
    $str = params_get('string');

    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;

    //html PNG location prefix
    $PNG_WEB_DIR = 'temp/';

    include 'phpqrcode/qrlib.php';

    ob_start();
    QRCode::png($str, null);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();

    return $imageString;
}
