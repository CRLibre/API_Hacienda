# Obtener la version del API

La version de runtime se consulta por el modulo legacy-compatible:

- `w=version`
- `r=version`

Ejemplo:

`https://127.0.0.1/api.php?w=version&r=version`

Si el modulo no puede usar `git describe`, usa el fallback de archivo:

- `migration/python-api/resources/version/VERSION`

Tambien puedes consultar el commit actual con:

`git log --pretty="%h" -n1`
