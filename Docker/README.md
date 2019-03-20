# Docker 

Docker es una forma de virtualizar ambientes, disponible en los principales sistemas operativos Windows, Linux, Mac OS. Su mayor ventaja es automatizar entornos, por ejemplo entornos de desarrollo o bien entornos de ejecución. 
El siguiente documento provee información de como configurar el API para que trabaje en un ambiente virtualizado.

## Version
* `Docker version 18.09.2, build 6247962`
* `docker-compose version 1.23.2, build 1110ad01`

## Mappings

* Apache2 HTTP port : 8080
* MySQL database system port: 4407

The database is storing all its information in a _volume_ which does not get removed when the container gets removed. If it is required to remove saved data from the volume, please stop all the containers, and execute the following command:

`docker volume prune`
