<?php

// Recibe un numero de cedula y le hace concatenacion a la izquierda respetando el largo de 12 digitos de Mh
function generarCedula12Digitos($NumCedula)
{
    return substr("0000000000{$NumCedula}", -12);
}

?>
