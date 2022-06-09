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

[Próximanente!...]

## Quiere contribuir?
Los "Pull Requests" son bienvenidos.
Para cambios importantes, primero abra un "Issue" para discutir qué le gustaría cambiar o mejorar.

## Licencia
[GNU AGPL](http://www.gnu.org/licenses/)
