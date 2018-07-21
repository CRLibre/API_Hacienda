<?php

/** @file module.php
 * A brief file description.
 * A more elaborated file description.
 */
/** \addtogroup Core 
 *  @{
 */
/**
 * \defgroup Module
 * @{
 */

/**
 * Boot up procedure
 */
function crlibreall_bootMeUp() {
    // Just booting up
}

/**
 * Init function
 */
function crlibreall_init() {
    $paths = array(
        array(
            'r' => 'FE',
            'action' => 'allFE',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                //Para modulo clave -> r=clave
                array("key" => "tipoDocumento", "def" => "", "req" => true),
                array("key" => "tipoCedula", "def" => "", "req" => true),
                array("key" => "cedula", "def" => "", "req" => true),
                array("key" => "codigoPais", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "situacion", "def" => "", "req" => true),
                array("key" => "terminal", "def" => "", "req" => false),
                array("key" => "sucursal", "def" => "", "req" => false),
                array("key" => "codigoSeguridad", "def" => "", "req" => true),
                //Para modulo genXML -> r=gen_xml_fe
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "fecha_emision", "def" => "", "req" => true),
                array("key" => "emisor_nombre", "def" => "", "req" => true),
                array("key" => "emisor_tipo_indetif", "def" => "", "req" => true),
                array("key" => "emisor_num_identif", "def" => "", "req" => true),
                array("key" => "nombre_comercial", "def" => "", "req" => true),
                array("key" => "emisor_provincia", "def" => "", "req" => false),
                array("key" => "emisor_canton", "def" => "", "req" => false),
                array("key" => "emisor_distrito", "def" => "", "req" => false),
                array("key" => "emisor_barrio", "def" => "", "req" => false),
                array("key" => "emisor_otras_senas", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "emisor_tel", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "emisor_fax", "def" => "", "req" => false),
                array("key" => "emisor_email", "def" => "", "req" => true),
                array("key" => "receptor_nombre", "def" => "", "req" => true),
                array("key" => "receptor_tipo_identif", "def" => "", "req" => true),
                array("key" => "receptor_num_identif", "def" => "", "req" => true),
                array("key" => "receptor_provincia", "def" => "", "req" => false),
                array("key" => "receptor_canton", "def" => "", "req" => false),
                array("key" => "receptor_distrito", "def" => "", "req" => false),
                array("key" => "receptor_barrio", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "receptor_tel", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "receptor_fax", "def" => "", "req" => false),
                array("key" => "receptor_email", "def" => "", "req" => true),
                array("key" => "condicion_venta", "def" => "", "req" => true),
                array("key" => "plazo_credito", "def" => "0", "req" => false),
                array("key" => "medio_pago", "def" => "", "req" => true),
                array("key" => "cod_moneda", "def" => "", "req" => true),
                array("key" => "tipo_cambio", "def" => "", "req" => true),
                array("key" => "total_serv_gravados", "def" => "", "req" => true),
                array("key" => "total_serv_exentos", "def" => "", "req" => true),
                array("key" => "total_merc_gravada", "def" => "", "req" => true),
                array("key" => "total_merc_exenta", "def" => "", "req" => true),
                array("key" => "total_gravados", "def" => "", "req" => true),
                array("key" => "total_exentos", "def" => "", "req" => true),
                array("key" => "total_ventas", "def" => "", "req" => true),
                array("key" => "total_descuentos", "def" => "", "req" => true),
                array("key" => "total_ventas_neta", "def" => "", "req" => true),
                array("key" => "total_impuestos", "def" => "", "req" => true),
                array("key" => "total_comprobante", "def" => "", "req" => true),
                array("key" => "otros", "def" => "", "req" => true),
                array("key" => "detalles", "def" => "", "req" => true),
                //Para modulo signXML -> r=signFE
                array("key" => "p12Url", "def" => "", "req" => true),
                array("key" => "pinP12", "def" => "", "req" => true),
                array("key" => "inXml", "def" => "", "req" => false),
                array("key" => "tipodoc", "def" => "", "req" => true),
                //Para modulo token -> r=gettoken
                array("key" => "grant_type", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true),
                array("key" => "client_secret", "def" => "", "req" => false),
                array("key" => "username", "def" => "", "req" => true),
                array("key" => "password", "def" => "", "req" => true),
                //Para modulo send -> r=json 
                array("key" => "token", "def" => "", "req" => true),
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "fecha", "def" => "", "req" => true),
                array("key" => "emi_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "emi_numeroIdentificacion", "def" => "", "req" => false),
                array("key" => "recp_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "recp_numeroIdentificacion", "def" => "", "req" => true),
                array("key" => "comprobanteXml", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true)
            ),
            'file' => 'crlibreall.php'
        ),
        array(
            'r' => 'NC',
            'action' => 'allNC',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                //Para modulo clave -> r=clave
                array("key" => "tipoDocumento", "def" => "", "req" => true),
                array("key" => "tipoCedula", "def" => "", "req" => true),
                array("key" => "cedula", "def" => "", "req" => true),
                array("key" => "codigoPais", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "situacion", "def" => "", "req" => true),
                array("key" => "terminal", "def" => "", "req" => false),
                array("key" => "sucursal", "def" => "", "req" => false),
                array("key" => "codigoSeguridad", "def" => "", "req" => true),
                //Para modulo genXML -> r=gen_xml_nc
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "fecha_emision", "def" => "", "req" => true),
                array("key" => "emisor_nombre", "def" => "", "req" => true),
                array("key" => "emisor_tipo_indetif", "def" => "", "req" => true),
                array("key" => "emisor_num_identif", "def" => "", "req" => true),
                array("key" => "nombre_comercial", "def" => "", "req" => true),
                array("key" => "emisor_provincia", "def" => "", "req" => false),
                array("key" => "emisor_canton", "def" => "", "req" => false),
                array("key" => "emisor_distrito", "def" => "", "req" => false),
                array("key" => "emisor_barrio", "def" => "", "req" => false),
                array("key" => "emisor_otras_senas", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "emisor_tel", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "emisor_fax", "def" => "", "req" => false),
                array("key" => "emisor_email", "def" => "", "req" => true),
                array("key" => "receptor_nombre", "def" => "", "req" => true),
                array("key" => "receptor_tipo_identif", "def" => "", "req" => true),
                array("key" => "receptor_num_identif", "def" => "", "req" => true),
                array("key" => "receptor_provincia", "def" => "", "req" => false),
                array("key" => "receptor_canton", "def" => "", "req" => false),
                array("key" => "receptor_distrito", "def" => "", "req" => false),
                array("key" => "receptor_barrio", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "receptor_tel", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "receptor_fax", "def" => "", "req" => false),
                array("key" => "receptor_email", "def" => "", "req" => true),
                array("key" => "condicion_venta", "def" => "", "req" => true),
                array("key" => "plazo_credito", "def" => "0", "req" => false),
                array("key" => "medio_pago", "def" => "", "req" => true),
                array("key" => "cod_moneda", "def" => "", "req" => true),
                array("key" => "tipo_cambio", "def" => "", "req" => true),
                array("key" => "total_serv_gravados", "def" => "", "req" => true),
                array("key" => "total_serv_exentos", "def" => "", "req" => true),
                array("key" => "total_merc_gravada", "def" => "", "req" => true),
                array("key" => "total_merc_exenta", "def" => "", "req" => true),
                array("key" => "total_gravados", "def" => "", "req" => true),
                array("key" => "total_exentos", "def" => "", "req" => true),
                array("key" => "total_ventas", "def" => "", "req" => true),
                array("key" => "total_descuentos", "def" => "", "req" => true),
                array("key" => "total_ventas_neta", "def" => "", "req" => true),
                array("key" => "total_impuestos", "def" => "", "req" => true),
                array("key" => "total_comprobante", "def" => "", "req" => true),
                array("key" => "otros", "def" => "", "req" => true),
                array("key" => "detalles", "def" => "", "req" => true),
                array("key" => "infoRefeTipoDoc", "def" => "", "req" => true),
                array("key" => "infoRefeNumero", "def" => "", "req" => true),
                array("key" => "infoRefeFechaEmision", "def" => "", "req" => true),
                array("key" => "infoRefeCodigo", "def" => "", "req" => true),
                array("key" => "infoRefeRazon", "def" => "", "req" => true),
                //Para modulo signXML -> r=signFE
                array("key" => "p12Url", "def" => "", "req" => true),
                array("key" => "pinP12", "def" => "", "req" => true),
                array("key" => "inXml", "def" => "", "req" => false),
                array("key" => "tipodoc", "def" => "", "req" => true),
                //Para modulo token -> r=gettoken
                array("key" => "grant_type", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true),
                array("key" => "client_secret", "def" => "", "req" => false),
                array("key" => "username", "def" => "", "req" => true),
                array("key" => "password", "def" => "", "req" => true),
                //Para modulo send -> r=json 
                array("key" => "token", "def" => "", "req" => true),
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "fecha", "def" => "", "req" => true),
                array("key" => "emi_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "emi_numeroIdentificacion", "def" => "", "req" => false),
                array("key" => "recp_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "recp_numeroIdentificacion", "def" => "", "req" => true),
                array("key" => "comprobanteXml", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true)
            ),
            'file' => 'crlibreall.php'
        ),
        array(
            'r' => 'ND',
            'action' => 'allND',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                //Para modulo clave -> r=clave
                array("key" => "tipoDocumento", "def" => "", "req" => true),
                array("key" => "tipoCedula", "def" => "", "req" => true),
                array("key" => "cedula", "def" => "", "req" => true),
                array("key" => "codigoPais", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "situacion", "def" => "", "req" => true),
                array("key" => "terminal", "def" => "", "req" => false),
                array("key" => "sucursal", "def" => "", "req" => false),
                array("key" => "codigoSeguridad", "def" => "", "req" => true),
                //Para modulo genXML -> r=gen_xml_nd
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "fecha_emision", "def" => "", "req" => true),
                array("key" => "emisor_nombre", "def" => "", "req" => true),
                array("key" => "emisor_tipo_indetif", "def" => "", "req" => true),
                array("key" => "emisor_num_identif", "def" => "", "req" => true),
                array("key" => "nombre_comercial", "def" => "", "req" => true),
                array("key" => "emisor_provincia", "def" => "", "req" => false),
                array("key" => "emisor_canton", "def" => "", "req" => false),
                array("key" => "emisor_distrito", "def" => "", "req" => false),
                array("key" => "emisor_barrio", "def" => "", "req" => false),
                array("key" => "emisor_otras_senas", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "emisor_tel", "def" => "", "req" => false),
                array("key" => "emisor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "emisor_fax", "def" => "", "req" => false),
                array("key" => "emisor_email", "def" => "", "req" => true),
                array("key" => "receptor_nombre", "def" => "", "req" => true),
                array("key" => "receptor_tipo_identif", "def" => "", "req" => true),
                array("key" => "receptor_num_identif", "def" => "", "req" => true),
                array("key" => "receptor_provincia", "def" => "", "req" => false),
                array("key" => "receptor_canton", "def" => "", "req" => false),
                array("key" => "receptor_distrito", "def" => "", "req" => false),
                array("key" => "receptor_barrio", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_tel", "def" => "", "req" => false),
                array("key" => "receptor_tel", "def" => "", "req" => false),
                array("key" => "receptor_cod_pais_fax", "def" => "", "req" => false),
                array("key" => "receptor_fax", "def" => "", "req" => false),
                array("key" => "receptor_email", "def" => "", "req" => true),
                array("key" => "condicion_venta", "def" => "", "req" => true),
                array("key" => "plazo_credito", "def" => "0", "req" => false),
                array("key" => "medio_pago", "def" => "", "req" => true),
                array("key" => "cod_moneda", "def" => "", "req" => true),
                array("key" => "tipo_cambio", "def" => "", "req" => true),
                array("key" => "total_serv_gravados", "def" => "", "req" => true),
                array("key" => "total_serv_exentos", "def" => "", "req" => true),
                array("key" => "total_merc_gravada", "def" => "", "req" => true),
                array("key" => "total_merc_exenta", "def" => "", "req" => true),
                array("key" => "total_gravados", "def" => "", "req" => true),
                array("key" => "total_exentos", "def" => "", "req" => true),
                array("key" => "total_ventas", "def" => "", "req" => true),
                array("key" => "total_descuentos", "def" => "", "req" => true),
                array("key" => "total_ventas_neta", "def" => "", "req" => true),
                array("key" => "total_impuestos", "def" => "", "req" => true),
                array("key" => "total_comprobante", "def" => "", "req" => true),
                array("key" => "otros", "def" => "", "req" => true),
                array("key" => "detalles", "def" => "", "req" => true),
                array("key" => "infoRefeTipoDoc", "def" => "", "req" => true),
                array("key" => "infoRefeNumero", "def" => "", "req" => true),
                array("key" => "infoRefeFechaEmision", "def" => "", "req" => true),
                array("key" => "infoRefeCodigo", "def" => "", "req" => true),
                array("key" => "infoRefeRazon", "def" => "", "req" => true),
                //Para modulo signXML -> r=signFE
                array("key" => "p12Url", "def" => "", "req" => true),
                array("key" => "pinP12", "def" => "", "req" => true),
                array("key" => "inXml", "def" => "", "req" => false),
                array("key" => "tipodoc", "def" => "", "req" => true),
                //Para modulo token -> r=gettoken
                array("key" => "grant_type", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true),
                array("key" => "client_secret", "def" => "", "req" => false),
                array("key" => "username", "def" => "", "req" => true),
                array("key" => "password", "def" => "", "req" => true),
                //Para modulo send -> r=json 
                array("key" => "token", "def" => "", "req" => true),
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "fecha", "def" => "", "req" => true),
                array("key" => "emi_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "emi_numeroIdentificacion", "def" => "", "req" => false),
                array("key" => "recp_tipoIdentificacion", "def" => "", "req" => true),
                array("key" => "recp_numeroIdentificacion", "def" => "", "req" => true),
                array("key" => "comprobanteXml", "def" => "", "req" => true),
                array("key" => "client_id", "def" => "", "req" => true)
            ),
            'file' => 'crlibreall.php'
        )
    );

    return $paths;
}

/* * *********************************************** */
//In the access you can use users_openAccess if you want anyone can use the function
// or users_loggedIn if the user must be logged in
/* * *********************************************** */

/**
 * Get the perms for this module
 */
function MODULENAME_access() {

    $perms = array(
        array(
            # A human readable name
            'name' => 'Do something with this module',
            # Something to remember what it is for
            'description' => 'What can be achieved with this permission',
            # Internal machine name, no spaces, no funny symbols, same rules as a variable
            # Use yourmodule_ prefix
            'code' => 'mymodule_access_one',
            # Default value in case it is not set
            'def' => false, //Or true, you decide
        ),
    );
}

/**@}*/
/** @}*/
