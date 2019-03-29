# Docker 

Docker es una forma de virtualizar ambientes, disponible en los principales sistemas operativos Windows, Linux, Mac OS. Su mayor ventaja es automatizar entornos, por ejemplo entornos de desarrollo o bien entornos de ejecución. 
El siguiente documento provee información de como configurar el API para que trabaje en un ambiente virtualizado.

## Version
* `Docker version 18.09.2, build 6247962`
* `docker-compose version 1.23.2, build 1110ad01`

## Mappings

* Apache2 HTTP ports : 80 => 8080, 443 => 8443
* MySQL database system port: 4407

El contenedor de base de datos almacena la información en un _volume_ lo que permite que despues de detenido el contenedor los datos persistan.

## Comandos relacionados

###
To RUN the container please type:
`docker-compose.exe up `

Here are some other commands that might result handy:
### Container
* `docker container ls` lista todos los contenedores
* `docker container rm <containerId>` borra un contenedor en particular
* `docker exec -it <containerId> bash` Abre un bash en un container
    
### Volume
* `docker volume ls` lista todos los volumenes
* `docker volume rm <volumeId>` borra un volumn en especifico
* `docker volume prune` borra todos los volumenes que no estan en uso

### Docker-Compose
* `docker-compose up`
* `docker-compose rm`
