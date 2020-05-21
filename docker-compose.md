# Resumen

La implementación provista de docker y docker compose, su propósito es proveer una rápida de acceder el API. Porfavor, notese que estos contenedores no están optimizados para producción.

# Instalación

## Windows
En el caso de Windows es necesario instalar _Docker desktop on Windows_ el cual puede ser descargado desde https://docs.docker.com/docker-for-windows/install/ este mismo incluye _Docker Compose_

Adicionalmente el contenedor utiliza volumenes, estos volumenes requieren que se le de accesso al disco duro. 
En el momento de este escrito, eso se puede lograr en la ventana de _Settings_ en el _tab_ de _Shared Drivers_ y seleccionando las unidesde que el servidio de docker puede tener disponibles.


* Docker for Windows
* User have to grant permission for accessing hard drive


## Linux
La instalación de _Docker_ varia dependiendo de la distribución.  Lo importante es instalar _docker_ y _docker compose_. 

En este caso los siguientes commandos son normalmente utilizados en Ubuntu (Linux):

`sudo apt install docker`

`sudo apt install docker-compose`

# Ejecución
Existen varias herramientas que podrían iniciar los contenedores. Despues de haber instalado los productos, normalmente se puede ejecutar comandos para arrancar la configuración de los contenedores. 

Iniciar Contenedores:

`docker-compose -f "docker-compose.yml" up -d --build`

Detener Contenedores

`docker-compose -f "docker-compose.yml" down`


# Solución a Problemas Frecuentes

1. How to fix ERROR: Couldn’t connect to Docker daemon at http+docker://localhost – is it running?
En linux puede que el siguiente error podria aparecer

```ERROR: Couldn’t connect to Docker daemon at http+docker://localhost – is it running?```

Segun https://techoverflow.net/2019/03/16/how-to-fix-error-couldnt-connect-to-docker-daemon-at-httpdocker-localhost-is-it-running/ este error se debe a que el usuario que está ejecutando _docker_ no pertenece a los usuarios que pueden ejecutar docker. En ubuntu (y posiblemente sistemas basados en linux) basta con agregar al usuario al grupo de usuarios: _docker_

Esto se puede lograr en una terminal al ejecutar los siguientes commands: 

Agrega el usuario actual al grupo docker
`sudo usermod -a -G docker $USER `

Reinicia el servicio de docker
`sudo systemctl enable docker `
`sudo systemctl start docker`


2. No encuentra archivos

En ocasiones podría pasar mostrarse el siguiente error:

```standard_init_linux.go:211: exec user process caused "no such file or directory"```

Esto podría pasar por ejemplo si los archivos se editan en Windows OS y se ejecutan en Linux. El sistema de codifiación de archivos de texto de cada sistema operativo es diferente, y en ocasiones imposibilita la lectura o ejecución de los mismos. 

Para convertir los archivos a un formato compatible hay varias opciones. Abrir el archivo y guardarlo en el formato correcto o si esta en linux utilizar el commando `dos2unix`. 
Por ejemplo el siguiente script de bash podría hacer la modifiación :

```sudo apt install dos2unix && for i in `find .`; do dos2unix $i; done```



# Verificando la ejecución del contenedor

La forma más simple es intentar abrir el URI:

`http://localhost:8080/api.php?w=ejemplo&r=hola`
