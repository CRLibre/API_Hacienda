#### Modulo para solicitar token y refrescar los token
//
###Obtener un token nuevo
W : token
r : gettoken
url: donde se pide el TOken
grant_type: password
client_id: en blanco
client_secret:en blanco
username: usuario en contribuyende
password: contraseña del contribuyente

###Ejemplo en Get: 
http://api-demo.crlibre.org/api.php seria la URL donde se ubique el API
```bash
http://api-demo.crlibre.org/api.php?r=gettoken&w=token&url=https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token&grant_type=password&client_id=api-stag&username=cpf-07-0232-0717@stag.comprobanteselectronicos.go.cr&password=N%26%404%2Bp%5BH-e%5B%2B%23%24DcOP%409
``` 
Tambien se puede hacer por Post
//

###Refrescar token
W : token
r : refresh
url =
grant_type:refresh_token
client_id:api-stag
client_secret:
refresh_token:Token de refrestar

###Ejemplo de ejecucion:
 http://api-demo.crlibre.org/api.php seria la URL donde se ubique el API en este caso usamos un servidor de pruebas.
```bash
http://api-demo.crlibre.org/api.php?r=refresh&w=token&url=https://idp.comprobanteselectronicos.go.cr/auth/realms/rut-stag/protocol/openid-connect/token&grant_type=refresh_token&client_id=api-stag&refresh_token=eyJhbGciOiJSUzI1NiJ9.eyJqdGkiOiIwNGEwODEzZC0zYzUyLTQ4MzUtOTQwNS1kNzY0ZmMzODY0MWYiLCJleHAiOjE1MTcxOTM4MDUsIm5iZiI6MCwiaWF0IjoxNTE3MTkyMDA1LCJpc3MiOiJodHRwczovL2lkcC5jb21wcm9iYW50ZXNlbGVjdHJvbmljb3MuZ28uY3IvYXV0aC9yZWFsbXMvcnV0LXN0YWciLCJhdWQiOiJhcGktc3RhZyIsInN1YiI6IjEzZjRjMzEzLTFmN2EtNDY5ZS1iM2RkLTRlMjA3ODQ2OGE5MiIsInR5cCI6IlJlZnJlc2giLCJhenAiOiJhcGktc3RhZyIsInNlc3Npb25fc3RhdGUiOiIyMjMyNTNmYi04OTQwLTRjNDEtYTFiNS1iOGMyOGNjODYxZGUiLCJjbGllbnRfc2Vzc2lvbiI6Ijc2N2VhYmIwLWYxMDctNGZiZi05NGNkLWU2NmVhNWFkY2U1OCIsInJlc291cmNlX2FjY2VzcyI6eyJhY2NvdW50Ijp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50Iiwidmlldy1wcm9maWxlIl19fX0.gG06dimDzUipEJuIT3AOIDfHbJkcTRNTbOe8jZPxxM6XnlbCUde-Q45p1L59KDSKHcTozpzdRu59MTUN9qG6158uUhTKOjMoCEpYZ8D9QN475h5Sg8X6vc8W3RGfj1tKnlcWrfAdvC_ZXkkW2bEyjyQJlWE5OydsPAzLF5ZBaLf3nRVw29OpxWseO5Ic1NHy5DSjZIPpvdxbp_vm81lMxYdYj7-b9O_PGHzMzCooXFoY61j278pspZDTBN8DSvYmAonvvNkPpWUxP9G103D4i_fyPDoUWW-xjj32Y11-ctGIZ3teMDFoGSQwwkJV2s-VqzH9l6mAGGdaMFpofekw6A
```
Tambien se puede hacer por Post



