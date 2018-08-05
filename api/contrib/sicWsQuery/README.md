# Modulo para hacer consultas de cédulas de personas físicas o juridicas al WebService en Hacienda (SIC)
Se enviar                               
        origen Inidica que tipo de cédula se va a consultar (Fisico,  Juridico o DIMEX)
        cedula Número de cédula que se desea consultar (Los números deben enviarse junto con el digito verificador de pertenencia)
		ape1 Primer apellido de la persona física (En caso de que la consulta sea de cedula de persona física)
		ape2 Segundo apellido de la persona física (En caso de que la consulta sea de cedula de persona física)
		nomb1 Primer nombre de la persona física (En caso de que la consulta sea de cedula de persona física)
		nomb2 Segundo nombre de la persona física (En caso de que la consulta sea de cedula de persona física)
		razon Nombre de la razón social en caso de que la consulta sea por persona jurídica

Nota
        A manera de ejemplo, esta consulta funciona como una sentencia WHERE con condiciones OR, es decir
        se hace un filtro tomando en cuenta todos los parametros que tienen algún valor
	
	Parametros a enviar
* origen
* cedula
* ape1
* ape2
* nomb1
* nomb2
* razon
* Concatenado

### Ejemplo consulta Juridico
http://api-demo.crlibre.org/api.php?w=sicWsQuery&r=sicWsQuery&origen=Juridico&razon=comglo
### Consulta Fisico
Los parametros que se envian son los que no esten en blanco, por lo que permite combinaciones.
Este puede enviar los siguientes parametros:
* origen
* cedula
* ape1
* ape2
* nomb1
* nomb2

http://api-demo.crlibre.org/api.php?w=sicWsQuery&r=sicWsQuery&origen=Fisico&nomb1=walner
