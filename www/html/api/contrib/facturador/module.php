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
global $compannyUser;

/**
 * Boot up procedure
 */
function facturador_bootMeUp() {
    // Just booting up
    companny_users_loadCurrentUser();
}

function companny_users_loadCurrentUser() {
    global $compannyUser;

    # If I am running on emebed mode I don't have any users, so I will just load it from the session
    $user = users_load(array('userName' => params_get('iam', '')));
    /*
      if(conf_get('embeded', 'core', false)){
      $user = users_createBasic();
      $tmpUserId = users_confirmSessionKey();
      $user->idUser = $tmpUserId;
      }else{
      }
     */
}

/**
 * Init function
 */
function facturador_init() {

    $paths = array(
        array(
            'r' => 'info',
            'action' => 'module_info',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'copy_master_tables',
            'action' => 'copyMasterTables',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_add_master_Consecutive',
            'action' => 'companny_add_master_Consecutive',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "idUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getSucursales',
            'action' => 'getSucursales',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'addSucursales',
            'action' => 'addSucursales',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "numeroSucursal", "def" => "", "req" => true),
                array("key" => "nombreSucursal", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'add_terminal',
            'action' => 'addTerminal',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "numeroTerminal", "def" => "", "req" => true),
                array("key" => "nombreTerminal", "def" => "", "req" => true),
                array("key" => "idSucursal", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getTerminales',
            'action' => 'getTerminales',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "idSucursal", "def" => "", "req" => false)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getUserPermissionById',
            'action' => 'getUserPermissionById',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getUsersCompanny',
            'action' => 'getUsersCompanny',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_active_receiver',
            'action' => 'getActiveReceiver',
            'access' => 'companny_users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_receiver_by_id',
            'action' => 'getReceiverById',
            'access' => 'companny_users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "idReceptor", "def" => "", "req" => true),
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_vouchers',
            'action' => 'getVouchers',
            'access' => 'companny_users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "env", "def" => "", "req" => true),
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getProductByCode',
            'action' => 'getProductByCode',
            'access' => 'companny_users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "codigo", "def" => "", "req" => true),
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "sucursal", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_inventory',
            'action' => 'getInventory',
            'access' => 'companny_users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "sucursal", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'backup_user',
            'action' => 'backUpUser',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_all_privinces',
            'action' => 'getAllProvinces',
            'access' => 'users_openAccess',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_type_of_id',
            'action' => 'getTypeOfId',
            'access' => 'users_openAccess',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_cantons',
            'action' => 'getCantons',
            'access' => 'users_openAccess',
            'params' => array(
                array("key" => "idProvince", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_district',
            'action' => 'getDistrict',
            'access' => 'users_openAccess',
            'params' => array(
                array("key" => "idProvince", "def" => "", "req" => true),
                array("key" => "idCanton", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'delete_reciver',
            'action' => 'deleteReciver',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idReceptor", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'company_stag_users',
            'action' => 'companyStagUsers',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "userName", "def" => "", "req" => true),
                array("key" => "password", "def" => "", "req" => true),
                array("key" => "pinCerti", "def" => "", "req" => true),
                array("key" => "downloadCode", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'company_change_env',
            'action' => 'companyChangeEnv',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "envProduccion", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'company_get_env',
            'action' => 'companyGetEnv',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'company_get_env',
            'action' => 'companyGetEnv',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasteruser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'company_prod_users',
            'action' => 'companyProdUsers',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "userName", "def" => "", "req" => true),
                array("key" => "password", "def" => "", "req" => true),
                array("key" => "pinCerti", "def" => "", "req" => true),
                array("key" => "downloadCode", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_stag_credentials',
            'action' => 'getStagCredentials',
            'access' => 'users_loggedIn',
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_prod_credentials',
            'action' => 'getProdCredentials',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_prod_companny_credentials',
            'action' => 'getProdCompannyCredentials',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'add_companny_reciver',
            'action' => 'addCompannyReciver',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "nombreCliente", "def" => "", "req" => true),
                array("key" => "numeroCedula", "def" => "", "req" => true),
                array("key" => "tipoCedula", "def" => "", "req" => true),
                array("key" => "telefono", "def" => "", "req" => false),
                array("key" => "idProvincia", "def" => "", "req" => true),
                array("key" => "idCanton", "def" => "", "req" => true),
                array("key" => "idDistrito", "def" => "", "req" => true),
                array("key" => "idBarrio", "def" => "", "req" => true),
                array("key" => "otrasSenas", "def" => "", "req" => true),
                array("key" => "nombreComercial", "def" => "", "req" => true),
                array("key" => "correoPrincipal", "def" => "", "req" => false),
                array("key" => "copiasCorreo", "def" => "", "req" => false),
                array("key" => "numeroFax", "def" => "", "req" => false),
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_stag_companny_credentials',
            'action' => 'getStagCompannyCredentials',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_add_voucher',
            'action' => 'companny_add_voucher',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "clave", "def" => "", "req" => true),
                array("key" => "consecutivo", "def" => "", "req" => true),
                array("key" => "estado", "def" => "", "req" => true),
                array("key" => "xmlEnviadoBase64", "def" => "", "req" => true),
                array("key" => "tipoDocumento", "def" => "", "req" => true),
                array("key" => "respuestaMHBase64", "def" => "", "req" => false),
                array("key" => "idReceptor", "def" => "", "req" => false),
                array("key" => "env", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_updateConsecutive',
            'action' => 'companny_updateConsecutive',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "tipoDocumento", "def" => "", "req" => true),
                array("key" => "env", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_companny_information',
            'action' => 'getCompannyInformation',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_companny_information_admin',
            'action' => 'getCompannyInformationAdmin',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'compannyUpdateTipoCambio',
            'action' => 'compannyUpdateTipoCambio',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "tipoCambio", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'compannyUpdateLocation',
            'action' => 'compannyUpdateLocation',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "idProvincia", "def" => "", "req" => true),
                array("key" => "idCanton", "def" => "", "req" => true),
                array("key" => "idDistrito", "def" => "", "req" => true),
                array("key" => "idBarrio", "def" => "", "req" => true),
                array("key" => "sennas", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'compannyUpdateInformation',
            'action' => 'compannyUpdateInformation',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "nombre", "def" => "", "req" => true),
                array("key" => "nombreComercial", "def" => "", "req" => true),
                array("key" => "email", "def" => "", "req" => true),
                array("key" => "codigoPais", "def" => "", "req" => true),
                array("key" => "fax", "def" => "", "req" => false),
                array("key" => "tipoCedula", "def" => "", "req" => false),
                array("key" => "cedula", "def" => "", "req" => false),
                array("key" => "telefono", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getCompannyLocationInformation',
            'action' => 'getCompannyLocationInformation',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "idProvincia", "def" => "", "req" => true),
                array("key" => "idCanton", "def" => "", "req" => true),
                array("key" => "idDistrito", "def" => "", "req" => true),
                array("key" => "idBarrio", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'get_neighborhood',
            'action' => 'getNeighborhood',
            'access' => 'users_openAccess',
            'params' => array(
                array("key" => "idProvince", "def" => "", "req" => true),
                array("key" => "idCanton", "def" => "", "req" => true),
                array("key" => "idDistrito", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'inser_to_log_table',
            'action' => 'insertToLogTable',
            'access' => 'users_loggedIn',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "json", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_getMyInfo',
            'action' => 'companny_getMyInfo',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_users_getMyDetails',
            'action' => 'companny_users_getMyDetails',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'companny_getMyConsecutive',
            'action' => 'companny_getMyConsecutive',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "tipoComprobante", "def" => "", "req" => true),
                array("key" => "env", "def" => "", "req" => true)
            ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_users_register',
            'action' => 'companny_users_registerNew',
            'access' => 'users_loggedIn',
            'params' => array(
                array("key" => "fullName", "def" => "", "req" => true),
                array("key" => "userName", "def" => "", "req" => true),
                array("key" => "email", "def" => "", "req" => true),
                array("key" => "about", "def" => "", "req" => false),
                array("key" => "country", "def" => "", "req" => true),
                array("key" => "pwd", "def" => "", "req" => true),
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'companny_users_get_my_details',
            'action' => 'companny_users_getMyDetails',
            'access' => 'companny_users_loggedIn',
            'params' => array(array("key" => "idMasterUser", "def" => "", "req" => false)),
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'get_tipo_impuesto',
            'action' => 'getTipoImpuesto',
            'access' => 'companny_users_loggedIn',
            'params' => array(array("key" => "idMasterUser", "def" => "", "req" => false)),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'getUnid',
            'action' => 'getUnid',
            'access' => 'companny_users_loggedIn',
            'params' => array(array("key" => "idMasterUser", "def" => "", "req" => false)),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'addInventaryProduct',
            'action' => 'addInventaryProduct',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => false),
                array("key" => "nombre", "def" => "", "req" => false),
                array("key" => "descripcion", "def" => "", "req" => false),
                array("key" => "unidadMedida", "def" => "", "req" => false),
                array("key" => "precioVenta", "def" => "", "req" => false),
                array("key" => "idImpuesto", "def" => "", "req" => false),
                array("key" => "cantidadImpuesto", "def" => "", "req" => false),
                array("key" => "codigoBarras", "def" => "", "req" => false),
                array("key" => "disponible", "def" => "", "req" => false),
                array("key" => "sucursal", "def" => "", "req" => false),
                ),
            'file' => 'facturadorCRLibre.php'
        ),
        array(
            'r' => 'companny_users_recover_pwd',
            'action' => 'companny_users_recoverPwd',
            'access' => 'companny_users_openAccess',
            'params' => array(
                array("key" => "userName", "def" => "", "req" => true),
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'companny_users_update_profile',
            'action' => 'companny_users_updateProfile',
            'access' => 'companny_users_loggedIn',
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'users_log_me_out',
            'action' => 'companny_users_logMeOut',
            'access' => 'companny_users_loggedIn',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true)
            ),
            'file' => 'companny_user.php'
        ),
        array(
            'r' => 'companny_users_logMeIn',
            'action' => 'companny_users_logMeIn',
            'access' => 'companny_users_openAccess',
            'params' => array(
                array("key" => "idMasterUser", "def" => "", "req" => true),
                array("key" => "userName", "def" => "", "req" => true),
                array("key" => "pwd", "def" => "", "req" => true)
            ),
            'file' => 'companny_user.php'
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
function fileUploader_access() {

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
