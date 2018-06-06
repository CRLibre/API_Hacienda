<!DOCTYPE html>
<html>

<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $("button").click(function() {
                var valoridentificacion = '155801158217'
                var tipo = 'ED' //ED = DIMEX & EN = NITE & FE = DIDI & FN = Fisico Nacional & FP = Pasaporte & JN = Juridico Nacional


                var datos = '1' + ',' + valoridentificacion + ',' + tipo;

                try {
                    var datastring = JSON.stringify(datos);
                    $.ajax({
                        url: 'https://www.hacienda.go.cr/ATV/Factura/ConsultarPersonas',
                        type: "POST",
                        cache: false,
                        data: {
                            DATOSPERSONAS: datastring
                        },
                        success: function(result) {
                            $("#div1").html("Resultado de ejecucion: " + result);
                        },
                        //SE ENVIA MENSAJE DE ERROR EN CASO QUE DE UN ERROR AL ENVIAR O TRAER LOS DATOS 
                        error: function(result) {
                            $("#div1").html("Error en la ejecucion, verifica la consola");
                            console.log(result);
                        }
                    });
                } catch (ex) {
                    alert("error:ConsultarPersonas " + ex.message);
                }

            });
        });
    </script>
</head>

<body>

    <div id="div1">
        <h2>Aporte para CRLibre por MLopez</h2>
    </div>

    <button>Obtener contenido</button>

</body>

</html>
