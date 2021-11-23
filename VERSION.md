# Obtener la versión del API
La versión del API es indispensable para el [reporte de incidencias](https://github.com/CRLibre/API_Hacienda/issues), y así, poder hacer las pruebas necesarias y facilitar la solución.

### Usando el módulo `version`

Debes usar los parametros con los siguientes valores:

```
w = version

r = version
```

Por ejemplo: https://127.0.0.1/api.php?w=version&r=version

#

En el caso de no funcionar el módulo, puedes hacerlo de los siguientes métodos:


### Método de descarga ZIP

Si has descargado el API por medio de descarga ZIP podrías ver la versión de la siguiente manera:

- En el archivo llamado `VERSION` que se encuentra en la ruta `/api/contrib/version`, lo abres y la versión es el contenido.

### Método de descarga GIT

Usando en la consola (o terminal) sobre el repositorio ejecutas el siguiente comando:

`git log --pretty="%h" -n1`
