### Modulo para encode en base 64 el xml
:shipit:
##Funcion para generar clave

     * Esta funcion se puede llamar desde GET POST si se envian los siguientes parametros
     * w=clave
     * r=getClave
     * tipoCedula=   fisico o juridico
     * cedula=  Numero de Cedula
     * codigoPais=  506
     * consecutivo=  codigo de 10 numeros
     * situacion=  nomal contingencia sininternet
     * codigoSeguridad=  codigo de 8 numeros
     * tipoDocumento=  FE ND NC TE CCE CPCE RCE
     *          
     * Tambien se puede llamar desde un metodo de la siguiente manera:
     * modules_loader("clave");       <-- Esta funcion importa el modulo
     * getClave($tipoDocumento="",$tipoCedula = "", $cedula = "", $situacion = "", $codigoPais = "", $consecutivo = "", $codigoSeguridad = "")  <------------ esta funcion retorna la clave

        Tipo de comprobante o documento asociado Código
        Factura electrónica 01
        Nota de débito electrónica 02
        Nota de crédito electrónica 03
        Tiquete Electrónico 04
        Confirmación de aceptación del comprobante electrónico 05
        Confirmación de aceptación parcial del comprobante
        electrónico
        06
        Confirmación de rechazo del comprobante electrónico 07



