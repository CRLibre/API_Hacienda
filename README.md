## Sobre CRLibre
Somos una comunidad de individuos y organizaciones que voluntariamente unimos esfuerzos para colaborar y compartir conocimiento, crear software libre para resolver problemas que enfrentamos en nuestra realidad en Costa Rica.


En este repositorio estamos creando un **API abierto** y componentes de software para simplificar el proceso de la **Factura Electrónica** requerido por el Ministerio de Hacienda de Costa Rica.

En repositorios adicionales estaremos colaborando con elementos similares

### Conversemos
* Póngase en contacto con los otros miembros voluntarios de la comunidad.
   * [Sistema de Preguntas y Respuestas de la Comunidad](https://crlibre.org/qa/)
   * [Grupos de CHAT de CRLibre.org](https://crlibre.org/chats/)
   * [Grupo de Facebook CRLibre](https://www.facebook.com/groups/105812240170199/)

## Cómo colaborar
Ver archivo [CONTRIBUTING.md](CONTRIBUTING.md) para más información

## Sobre este API

**Trabajo en proceso [lo estamos creando en conjunto](CONTRIBUTING.md)**

Esta es un API en PHP, la idea de esto es poder realizar módulos sobre una base que maneja ya diferentes aspectos como la conexión a bases de datos y usuarios.

Se encuentran 2 carpetas, una que se llama api y otra que se llama www

La que se llama api, debe ser ubicarla en un lugar en donde no sea accesible, o bien, que no sea en el public_html

La que se llama www contiene un archivo de configuración, en donde se modifican aspectos como la conexión a base de datos, nombre del sitio y muy importante, la ubicación de en donde se encontrará el resto de cosas o bien, la carpeta api.

## Requerimientos minimos
* Php > 5.5.0
* MySQL o MariaDB
* Instalación librería curl
* Instalación php-xml
* Openssl

### Necesitas más información? [Visita el Wiki del API](https://github.com/CRLibre/API_Hacienda/wiki "Wiki CRLibre API_Hacienda")

----------------------------------------------


Para hacer módulos, se realizan en la carpeta api/contrib/mi-modulo

Hay uno de ejemplo que se llama 'ejemplo'

Dentro de la carpeta del módulo, tiene que haber un archivo que se llame module.php, en este se define la estructura de nuestro módulo, la funcion function ejemplo_init() hace referencia a la siguiente estructura MODULO_init(), si el modulo se llama GetDate la funcion init debe tener el nombre GetDate_init().


En esta hay 2 llamadas diferentes a modo de ejemplo, la primera no requere ningún valor y nos devuelve un "hola"

Este es un ejemplo de cómo se haría la llamada a la primera función

http://localhost/api.php?w=ejemplo&r=hola


En donde w = módulo al que ocupamos / r = cual función ocupamos
En este caso ocupamos w=ejemplo y r=hola

La respuesta debería ser similar a esto:

{"resp":"hola :)"}

http://localhost/api.php?w=ejemplo&r=un_usuario&nombre=Juan&apellido=Perez

En la segunda llamada ya es necesario que se envíen 2 parámetros que serán utilizados por la función. Estos están declarados en el menú del módulo cómo mandatorios.

El resultado debería dar algo similar a:

{"resp":"Juan, Perez"}


---------------------------------------------


Todos los resultados están contenidos bajo la variable resp y todos son en json.


Cada bloque del menú del módulo, está compuesto por una estructura similar a esta

array(
			'r' => 'un_usuario',
			'action' => 'unUsuario',
			'access' => 'users_openAccess', 
			'access_params' => 'accessName',
			'params' => array(
				array("key" => "nombre", "def" => "", "req" => true),
				array("key" => "apellido", "def" => "", "req" => true)
			),	
			'file' => 'ejemplo.php'
		)


-En donde el primero es el nombre de request que vamos a usar
-Función en php que se va a ejecutar
-El tipo de acceso con el que se cuenta
-Si se va a acceder por medio del mobre de la función (este no se toca)
-Los parámetros que son necesarios recibir
-La ubicación de la función a llamar

De tal manera, nuestro múdulo puede estar conformadio por diferentes archivos en PHP, y estas direcciones se definen en el menú. De esta manera solamente se incluirán los módulos necesarios para cada ejecución y se omiten el resto.



