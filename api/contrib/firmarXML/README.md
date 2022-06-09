# Firmador PHP - MdH

Clase PHP para firmar comprobantes electrónicos para el Ministerio de Hacienda de Costa Rica

## Instalación

Requerido: PHP version 5.6.24+ recomendado por razones de seguridad

```bash
git clone https://github.com/enzojimenez/hacienda-firmador-php.git
```

## Uso

### FIRMAR:

```php
<?php
require(dirname(__FILE__) . '/hacienda/firmador.php');

use Hacienda\Firmador;

$pfx    = ''; // Ruta del archivo de la llave criptográfica (*.p12)
$pin    = ''; // PIN de 4 dígitos de la llave criptográfica
$xml    = ''; // String XML ó Ruta del archivo XML (comprobante electrónico)
$ruta   = ''; // Ruta del nuevo arhivo XML cuando se desea guardar en disco

// Nuevo firmador
$firmador = new Firmador();

// Se firma XML y se recibe un string resultado en Base64
$base64 = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_BASE64_STRING);
print_r($base64);

// Se firma XML y se recibe un string resultado en Xml
$xml_string = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_XML_STRING);
print_r($xml_string);

// Se firma XML, se guarda en disco duro ($ruta) y se recibe el número de bytes del archivo guardado. En caso de error se recibe FALSE
$archivo = $firmador->firmarXml($pfx, $pin, $xml, $firmador::TO_XML_FILE, $ruta);
print_r($archivo);
```

### VALIDAR:

Para validar el funcionamiento de este endpoint, se debe tener un archivo p12 con el cual hacer las pruebas.
Supongamos que el p12 se llama 070211023522.p12
En este caso, el firmador firmarXml lo que va a hacer es recibir un downloadCode, el cual representa un archivo en una ruta local asignado a un usuario de la Api. Por lo cuál se requiere tener un usuario activo en la Api con un respectivo idUser.

1. Identificar el idUser
  * Se debe determinar el idUser que desea usar en la Api. `SELECT * from users;`
  * Ejemplo, idUser = 5.

2. Confirmar la existencia del archivo
  * Se debe verificar que la siguiente ruta existe en su servidor de la Api, si no existe la debe crear: `api/files/5/hacienda`
  * Esta ruta representa la carpeta donde se van a almacenar los recursos del usuario con idUser 5, en este caso, el certificado p12.

3. Revisar la tabla `files`
  * Asegurarse que se siguieron bien los pasos de upload certificado y se tiene un registro en la tabla files con el idUser correspondiente. 
  * [Upload Certificado](https://github.com/CRLibre/API_Hacienda/wiki/Upload-del-certificado-o-llave-criptogr%C3%A1fica)

4. Colocar el certificado si no existe.
  * Subir el Certificado a esa ruta si no está aún. Asegurarse que el nombre del archivo es igual al valor de la celda `name` en el registro correspondiente de la tabla `files` en la base de datos.

5. Finalizar con un ejemplo de firmado.
  * Validar con postman. Siguiendo el siguiente ejemplo, donde:
    * `p12Url` es el downloadCode.
    * `pinP12` es el pin del certificado.
    * `inXml` es el Xml en base64
    * `r` es el módulo firmar
    * `w` es la acción a ejecutar, firmarXml

![postman ejemplo](https://user-images.githubusercontent.com/8434928/65374989-564d2680-dc4d-11e9-8224-1caa19b2e3b9.jpeg)

## Quiere contribuir?
Los "Pull Requests" son bienvenidos.
Para cambios importantes, primero abra un "Issue" para discutir qué le gustaría cambiar o mejorar.

## Licencia
[GNU AGPL](http://www.gnu.org/licenses/)
