<?php

define('MODULE_FACTURADOR_VERSION', 'V.0.0');

/**
 * Generates a user hash (for passwords mostly)
 * @todo Use php's function
 */
function module_info() {
    $resp = array(
        "info" => "Modulo de interface de facturacion",
        "version" => MODULE_FACTURADOR_VERSION
    );
    return $resp;
}

/*
 * Function getInventary()
 * this function get all the inventary from the  
 * he can conver the necesary user tables from the master table
 */

function getIdUser($sessionKey) {
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $sessionKey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    return $idUser;
}

function getComapannyIdUser($sessionKey, $idMasterUser) {
    $q = "SELECT `idUser` FROM `" . $idMasterUser . "_master_sessions` WHERE `sessionKey`='" . $sessionKey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    return $idUser;
}

/*
 * funtion getMasterTables()
 * list of the master tables
 */

function getMasterTables() {
    $q = "SELECT table_name FROM information_schema.tables WHERE TABLE_NAME like 'master_%'";
    $result = db_query($q, 2);
    $resp = array();
    for ($i = 0; $i <= count($result) - 1; $i++) {
        $resp[$i] = $result[$i]->table_name;
    }
    return $resp;
}

function getMasterInventaryTable() {
    $q = "SELECT table_name FROM information_schema.tables WHERE TABLE_NAME like 'master_inventary_sucursal_%'";
    $result = db_query($q, 2);
    $resp = array();
    for ($i = 0; $i <= count($result) - 1; $i++) {
        $resp[$i] = $result[$i]->table_name;
    }
    return $resp;
}

function getProdCredentials() {
    $thisSessionkey = params_get("sessionKey");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "SELECT `value` FROM `" . $idUser . "_master_config_companny` where `name`= 'prodUserName' or `name`= 'prodPassword' or `name`= 'prodP12Code' or `name`= 'prodPin'";
    $result = db_query($q, 2);


    return $result;
}

function getStagCredentials() {
    $thisSessionkey = params_get("sessionKey");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "SELECT `value` FROM `" . $idUser . "_master_config_companny` where `name`= 'stagUserName' or `name`= 'stagPassword' or `name`= 'stagP12Code' or `name`= 'stagPin'";
    $result = db_query($q, 2);


    return $result;
}

function getProdCompannyCredentials() {
    $idMasterUser = params_get("idMasterUser");
    $idUser = $idMasterUser;
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "SELECT `value` FROM `" . $idUser . "_master_config_companny` where `name`= 'prodUserName' or `name`= 'prodPassword' or `name`= 'prodP12Code' or `name`= 'prodPin'";
        $result = db_query($q, 2);
        return $result;
    } else {
        return false;
    }
}

function getCompannyInformation() {
    $idMasterUser = params_get("idMasterUser");
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "select `name`,`value` FROM `" . $idMasterUser . "_master_config_companny`   WHERE name='NOMBRE' or name='NCODPAIS' or name='TIPOCAMBIO' or name='situacion' or name='TIPOCED' or name='CEDULA' or name='NOMCOMER' or name='PROVINCIA' or name='CANTON' or name='CANTON' or name='DISTRITO' or name='BARRIO' or name='SENNAS' or name='NNUMER' or name='FCODPAIS' or name='EMAIL' or name='EMAIL' or name='FNUMER' ORDER BY `" . $idMasterUser . "_master_config_companny`.`value` ASC";
        $result = db_query($q, 2);
        return $result;
    } else {
        return false;
    }
}

function getCompannyInformationAdmin() {
    $idMasterUser = params_get("idMasterUser");
    $q = "select `name`,`value` FROM `" . $idMasterUser . "_master_config_companny`   WHERE name='NOMBRE' or name='NCODPAIS' or name='TIPOCAMBIO' or name='situacion' or name='TIPOCED' or name='CEDULA' or name='NOMCOMER' or name='PROVINCIA' or name='CANTON' or name='CANTON' or name='DISTRITO' or name='BARRIO' or name='SENNAS' or name='NNUMER' or name='FCODPAIS' or name='EMAIL' or name='EMAIL' or name='FNUMER' ORDER BY `" . $idMasterUser . "_master_config_companny`.`value` ASC";
    $result = db_query($q, 2);
    return $result;
}

function getCompannyLocationInformation() {
    $idProvincia = params_get("idProvincia");
    $idCanton = params_get("idCanton");
    $idDistrito = params_get("idDistrito");
    $idBarrio = params_get("idBarrio");
    $q = "SELECT `nombreProvincia`,`nombreCanton`,`nombreDistrito`,`nombreBarrio` FROM `codificacion_mh` WHERE `idProvincia`='" . $idProvincia . "' and `idCanton`='" . $idCanton . "' and `idDistrito`='" . $idDistrito . "' and `idBarrio`='" . $idBarrio . "'";
    $result = db_query($q, 2);
    return $result;
}

function compannyUpdateTipoCambio() {
    $idMasterUser = params_get("idMasterUser");
    $tipoCambio = params_get("tipoCambio");
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $tipoCambio . "' WHERE `name`='TIPOCAMBIO'";
    $result = db_query($q, 0);
    return $result;
}

function compannyUpdateLocation() {
    $idMasterUser = params_get("idMasterUser");
    $idProvincia = params_get("idProvincia");
    $idCanton = params_get("idCanton");
    $idDistrito = params_get("idDistrito");
    $idBarrio = params_get("idBarrio");
    $sennas = params_get("sennas");
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $idProvincia . "' WHERE `name`='PROVINCIA'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $idCanton . "' WHERE `name`='CANTON'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $idDistrito . "' WHERE `name`='DISTRITO'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $idBarrio . "' WHERE `name`='BARRIO'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $sennas . "' WHERE `name`='SENNAS'";
    $result = db_query($q, 0);
    return $result;
}

function compannyUpdateInformation() {
    $idMasterUser = params_get("idMasterUser");
    $nombre = params_get("nombre");
    $nombreComercial = params_get("nombreComercial");
    $email = params_get("email");
    $codigoPais = params_get("codigoPais");
    $telefono = params_get("telefono");
    $fax = params_get("fax");
    $tipoCedula = params_get("tipoCedula");
    $cedula = params_get("cedula");

    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $nombre . "' WHERE `name`='NOMBRE'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $nombreComercial . "' WHERE `name`='NOMCOMER'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $email . "' WHERE `name`='EMAIL'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $codigoPais . "' WHERE `name`='NCODPAIS' or `name`='FCODPAIS'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $telefono . "' WHERE `name`='NNUMER'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $fax . "' WHERE `name`='FNUMER'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $tipoCedula . "' WHERE `name`='TIPOCED'";
    db_query($q, 0);
    $q = "UPDATE `" . $idMasterUser . "_master_config_companny` SET `value` = '" . $cedula . "' WHERE `name`='CEDULA'";
    db_query($q, 0);
    return 1;
}

function addInventaryProduct() {
    $nombre = params_get('nombre');
    $descripcion = params_get('descripcion');
    $unidadMedida = params_get('unidadMedida');
    $precioVenta = params_get('precioVenta');
    $idImpuesto = params_get('idImpuesto');
    $cantidadImpuesto = params_get('cantidadImpuesto');
    $codigoBarras = params_get('codigoBarras');
    $disponible = params_get('disponible');
    $idSucursal = params_get('sucursal');

    $idMasterUser = params_get("idMasterUser");
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "INSERT INTO `" . $idMasterUser . "_master_inventary_sucursal_" . $idSucursal . "` (`idProducto`, `nombre`, `descripcion`, `unidadMedida`, `precioVenta`, `idImpuesto`, `cantidadImpuesto`, `codigoBarras`, `disponible`) VALUES "
                . "(NULL, '" . $nombre . "', '" . $descripcion . "', '" . $unidadMedida . "', '" . $precioVenta . "', '" . $idImpuesto . "', '" . $cantidadImpuesto . "', '" . $codigoBarras . "','" . $disponible . "')";
        $result = db_query($q, 0);
        return $result;
    } else {
        return false;
    }
}

function getStagCompannyCredentials() {
    $idMasterUser = params_get("idMasterUser");
    $idUser = $idMasterUser;
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "SELECT `value` FROM `" . $idUser . "_master_config_companny` where `name`= 'stagUserName' or `name`= 'stagPassword' or `name`= 'stagP12Code' or `name`= 'stagPin'";
        $result = db_query($q, 2);
    } else {
        return false;
    }
    return $result;
}

function getMaxConsecutive() {
    $thisSessionkey = params_get("sessionKey");
    $tipoDoc = params_get("tipoDoc");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "SELECT max(`numeroConsecutivo`) as numeroConsecutivo,`tipoComprobante`,ms.sucursal,mt.terminal`tipoComprobante` from " . $idUser . "_master_consecutive as MC INNER JOIN " . $idUser . "_master_companyusers CU on mc.idUser=cu.idUser INNER JOIN " . $idUser . "_master_terminales as mt on cu.idTerminal=mt.idTerminal INNER JOIN " . $idUser . "_master_sucursales as ms ON cu.idSucursal=ms.idSucursal where mc.idUser='" . $idUser . "'";
    $result = db_query($q, 2);
    return $result;
}

function getActiveReceiver() {
    $idUser = params_get("idMasterUser");
    $q = "SELECT DISTINCT  `nombreCliente`,`idReceptor`,correoPrincipal,C.nombreProvincia,`telefono`,`tipoCedula`,`numeroCedula` FROM `" . $idUser . "_master_receiver` as R INNER JOIN codificacion_mh as C on R.`idProvincia` =C.idProvincia where estadoCliente='1'";
    $result = db_query($q, 2);
    return $result;
}

function getReceiverById() {
    $idUser = params_get("idMasterUser");
    $idReceptor = params_get("idReceptor");
    $q = "SELECT * FROM `" . $idUser . "_master_receiver` where estadoCliente='1' and idReceptor='" . $idReceptor . "'";
    $result = db_query($q, 2);
    return $result;
}

function getVouchers() {
    $env = params_get("env");
    $idUser = params_get("idMasterUser");
    grace_debug($idUser);
    $q = "SELECT `idComprobante`,`consecutivo`,`clave`,`tipoDocumento`,`estado`,`fechaCreacion` FROM `" . $idUser . "_master_vouchers` where env='" . $env . "';";
    $result = db_query($q, 2);
    return $result;
}

function getUserPermissionById() {
    $thisSessionkey = params_get("sessionKey");
    $idMasterUser = getIdUser($thisSessionkey);
    $q = "SELECT R.rolCode,R.descripcion,U.userName,U.FullName,idUser FROM `" . $idMasterUser . "_master_permission` as P INNER JOIN " . $idMasterUser . "_master_rol as R on P.`idRol` = R.idRol INNER join " . $idMasterUser . "_master_users as U on P.`idCompanyUser`=U.idUser where U.status='1'";
    $result = db_query($q, 2);
    return $result;
}

function getProductByCode() {
    $idMasterUser = params_get("idMasterUser");
    $sucursal = params_get("sucursal");
    $codigo = params_get("codigo");
    $q = "SELECT I.`idProducto`,I.descripcion,I.unidadMedida,I.precioVenta, I.cantidadImpuesto,IV.codigo FROM `" . $idMasterUser . "_master_inventary_sucursal_" . $sucursal . "` as I INNER JOIN tipo_impuestos as IV on I.`idImpuesto`=IV.idImpuesto  WHERE `codigoBarras`='" . $codigo . "'";
    $result = db_query($q, 2);
    return $result;
}

function getInventory() {
    $idUser = params_get("idMasterUser");
    $sucursal = params_get("sucursal");
    $q = "select `idProducto`,`codigoBarras`,`nombre`,`unidadMedida`,`precioVenta`,`disponible` from " . $idUser . "_master_inventary_sucursal_" . $sucursal . ";";
    insertToLogTable($idUser, $q);
    $result = db_query($q, 2);
    return $result;
}

function deleteReciver() {
    $thisSessionkey = params_get("sessionKey");
    $idReceptor = params_get("idReceptor");
    $resp = getMasterTables();
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "update `" . $idUser . "_master_receiver` set estadoCliente='0' where idReceptor= '" . $idReceptor . "'";
    $result = db_query($q, 0);
    return $result;
}

function copyInventaryTable($idSucursal, $idUser) {
    $resp = getMasterInventaryTable();
    foreach ($resp as $value) {
        try {
            $q = "CREATE TABLE " . $idUser . "_master_inventary_sucursal_" . $idSucursal . " LIKE " . $value;
            db_query($q, 0);
        } catch (Exception $ex) {
            $arrayResp = array(
                "Status" => $e->getCode(),
                "text" => $e->getMessage()
            );
            return $arrayResp;
        }
    }
}

function copyMasterTables() {
    $thisSessionkey = params_get("sessionKey");
    $resp = getMasterTables();
    $idUser = getIdUser($thisSessionkey);
    foreach ($resp as $value) {
        try {
            $q = "CREATE TABLE " . $idUser . "_" . $value . " LIKE " . $value;
            db_query($q, 0);
        } catch (Exception $ex) {
            $arrayResp = array(
                "Status" => $e->getCode(),
                "text" => $e->getMessage()
            );
            return $arrayResp;
        }
    }
    $q = "INSERT INTO `" . $idUser . "_master_config_companny` (`idConfig`, `name`, `value`, `compannyName`) VALUES
(1, 'NOMBRE', 'CRLibre.org', 'CRLibre.org'),
(2, 'TIPOCED', '01', 'CRLibre.org'),
(3, 'CEDULA', '702320717', 'CRLibre.org'),
(4, 'NOMCOMER', 'CRLibre.org', 'CRLibre.org'),
(5, 'EMAIL', 'info@crlibre.org', 'CRLibre.org'),
(6, 'PROVINCIA', '1', 'CRLibre.org'),
(7, 'CANTON', '01', 'CRLibre.org'),
(8, 'DISTRITO', '08', 'CRLibre.org'),
(9, 'BARRIO', '01', 'CRLibre.org'),
(10, 'SENNAS', '250 mts oeste Scotiabank, Rhormoser', 'CRLibre.org'),
(11, 'NCODPAIS', '506', 'CRLibre.org'),
(12, 'NNUMER', '64206205', 'CRLibre.org'),
(13, 'FCODPAIS', '506', 'CRLibre.org'),
(14, 'FNUMER', '', 'CRLibre.org'),
(15, 'ENV', 'api-stag', 'CRLibre.org'),
(16, 'situacion', 'normal', ''),
(17, 'stagUserName', 'cpf-07-0232-0717@stag.comprobanteselectronicos.go.cr', ''),
(18, 'stagPassword', '1PdeUpreble', ''),
(19, 'prodUserName', 'cpf-07-0232-0717@stag.comprobanteselectronicos.go.cr', ''),
(20, 'prodPassword', 'N&@4+p[H-e[+#$DcOP@9', ''),
(21, 'stagP12Code', '', ''),
(22, 'prodP12Code', '', ''),
(23, 'stagPin', '1994', ''),
(24, 'prodPin', '1994', ''),
(25, 'TIPOCAMBIO', '564.48', '')";
    db_query($q, 0);

    return $resp;
}

function companny_add_master_Consecutive() {
    $iam = params_get("iam");
    $idMasterUser = params_get('idMasterUser');
    $idUser = params_get('idUser');

    $q = "INSERT INTO `" . $idMasterUser . "_master_consecutive` (`idConsecutivo`, `ENV`, `companyName`, `numeroConsecutivo`, `tipoComprobante`, `idUser`) "
            . "VALUES "
            . "(NULL, 'api-stag', '', '0', 'FE', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'NC', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'ND', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'TE', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'CCE', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'CPCE', '" . $idUser . "'),"
            . "(NULL, 'api-stag', '', '0', 'RCEFE', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'FE', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'NC', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'ND', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'TE', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'CCE', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'CPCE', '" . $idUser . "'),"
            . "(NULL, 'api-prod', '', '0', 'RCEFE', '" . $idUser . "')";

    return $result = db_query($q, 0);
}

function companyStagUsers() {
    $thisSessionkey = params_get("sessionKey");
    $compannyUserName = params_get("userName");
    $downloadCode = params_get("downloadCode");
    $pinCerti = params_get("pinCerti");
    $password = params_get("password");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey` = '" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "update " . $idUser . "_master_config_companny set value = '" . $compannyUserName . "' where name = 'stagUserName'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $password . "' where name = 'stagPassword'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $downloadCode . "' where name = 'stagP12Code'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $pinCerti . "' where name = 'stagPin'";
    db_query($q, 0);
    return "ok";
}

function companyChangeEnv() {
    $thisSessionkey = params_get("sessionKey");
    $envProduccion = params_get("envProduccion");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey` = '" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    if ($envProduccion == 'true') {
        $q = "update " . $idUser . "_master_config_companny set value = 'api-prod' where name = 'ENV'";
    } else {
        $q = "update " . $idUser . "_master_config_companny set value = 'api-stag' where name = 'ENV'";
    }
    db_query($q, 0);
    return "ok";
}

function companyGetEnv() {
    $idMasterUser = params_get("idMasterUser");
    $q = "select value as 'env' from " . $idMasterUser . "_master_config_companny where name = 'ENV'";
    $result = db_query($q, 2);
    return $result;
}

function companyProdUsers() {
    $thisSessionkey = params_get("sessionKey");
    $compannyUserName = params_get("userName");
    $downloadCode = params_get("downloadCode");
    $password = params_get("password");
    $pinCerti = params_get("pinCerti");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey` = '" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $q = "update " . $idUser . "_master_config_companny set value = '" . $compannyUserName . "' where name = 'prodUserName'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $password . "' where name = 'prodPassword'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $downloadCode . "' where name = 'prodP12Code'";
    db_query($q, 0);
    $q = "update " . $idUser . "_master_config_companny set value = '" . $pinCerti . "' where name = 'stagPin'";
    db_query($q, 0);
    return "ok";
}

function createInventary() {
    $thisSessionkey = params_get("sessionKey");
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey` = '" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    foreach ($resp as $value) {
        try {
            $q = "CREATE TABLE " . $idUser . "_" . $value . " LIKE " . $value;
            db_query($q, 0);
        } catch (Exception $ex) {
            $arrayResp = array(
                "Status" => $e->getCode(),
                "text" => $e->getMessage()
            );
            return $arrayResp;
        }
    }
    return $resp;
}

function backUpUser() {
    $thisSessionkey = params_get("sessionKey");
    $resp = getMasterTables();
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey` = '" . $thisSessionkey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    $backup_file = "C:\\" . $idUser . "_" . time() . "_Respaldo.sql";
    $tables = "";
    foreach ($resp as $value) {
        $tables = $tables . $value . ", ";
    }
    $tables = substr($tables, 0, -1);
    try {
        //$backup_file = $idUser."_".time()."_".$value.".sql";
        $q = "SELECT * INTO OUTFILE '$backup_file' FROM $tables";
        db_query($q, 0);
    } catch (Exception $ex) {
        $arrayResp = array(
            "Status" => $e->getCode(),
            "text" => $e->getMessage()
        );
        return $arrayResp;
    }
    return $resp;
}

/*
 * funtion getMasterTables()
 * list of the master tables
 */

function insertToLogTable($idUser, $json) {
    $q = "INSERT INTO `" . $idUser . "_master_logs` (`idUser`, `json`) VALUES ('" . $idUser . "', '" . $json . "')";
    $result = db_query($q, 2);
    return $result;
}

/* Return Provincias */

function getAllProvinces() {
    $q = "SELECT DISTINCT(`idProvincia`), `nombreProvincia` FROM `codificacion_mh`";
    $result = db_query($q, 2);
    return $result;
}

/* Return cantons by province */

function getCantons() {
    $idProvince = params_get("idProvince");
    $q = "SELECT DISTINCT(`nombreCanton`),`idCanton` FROM `codificacion_mh` where `idProvincia` = '" . $idProvince . "'";
    $result = db_query($q, 2);
    return $result;
}

/* Return district by province and canton */

function getDistrict() {
    $idProvince = params_get("idProvince");
    $idCanton = params_get("idCanton");
    $q = "SELECT DISTINCT(`nombreDistrito`),`idDistrito` FROM `codificacion_mh` where `idProvincia` = '" . $idProvince . "' and idCanton = '" . $idCanton . "'";
    $result = db_query($q, 2);
    return $result;
}

/* Return neighborhood by province, canton and district */

function getNeighborhood() {
    $idProvince = params_get("idProvince");
    $idCanton = params_get("idCanton");
    $idDistrito = params_get("idDistrito");
    $q = "SELECT DISTINCT(`nombreBarrio`),`idBarrio`  FROM `codificacion_mh` where `idProvincia` = '" . $idProvince . "' and idCanton = '" . $idCanton . "' and idDistrito = '" . $idDistrito . "'";
    $result = db_query($q, 2);
    return $result;
}

/* Return type of id */

function getTypeOfId() {
    $q = "SELECT `codigo`, `descripcion` FROM `tipo_cedula`";
    $result = db_query($q, 2);
    return $result;
}

function getSucursales() {
    $thisSessionkey = params_get("sessionKey");
    $idUser = getIdUser($thisSessionkey);
    $q = "SELECT `idSucursal`, `nombreSucursal`, `sucursal` FROM `" . $idUser . "_master_sucursales`";
    $result = db_query($q, 2);
    return $result;
}

function addSucursales() {
    $thisSessionkey = params_get("sessionKey");
    $numeroSucursal = params_get("numeroSucursal");
    $nombreSucursal = params_get("nombreSucursal");
    $idUser = getIdUser($thisSessionkey);
    $q = "INSERT INTO `" . $idUser . "_master_sucursales` (`idSucursal`, `nombreSucursal`, `sucursal`) VALUES (NULL, '" . $nombreSucursal . "', '" . $numeroSucursal . "')";
    $result = db_query($q, 0);
    copyInventaryTable($numeroSucursal, $idUser);

    return $result;
}

function addTerminal() {
    $thisSessionkey = params_get("sessionKey");
    $numeroTerminal = params_get("numeroTerminal");
    $nombreTerminal = params_get("nombreTerminal");
    $idSucursal = params_get("idSucursal");
    $idUser = getIdUser($thisSessionkey);
    $q = "INSERT INTO `" . $idUser . "_master_terminales` (`idTerminal`, `nombreTerminal`, `terminal`, `idSucursal`) VALUES (NULL, '" . $nombreTerminal . "', '" . $numeroTerminal . "', '" . $idSucursal . "')";
    $result = db_query($q, 0);
    return $result;
}

function getTerminales() {
    $thisSessionkey = params_get("sessionKey");
    $idSucursal = params_get("idSucursal");
    $idUser = getIdUser($thisSessionkey);
    if ($idSucursal == "") {
        $q = "SELECT `idTerminal`, `nombreTerminal`, `terminal`, S.nombreSucursal, S.sucursal FROM `" . $idUser . "_master_terminales` as T INNER JOIN " . $idUser . "_master_sucursales as S on T.idSucursal = S.idSucursal";
    } else {
        $q = "SELECT `idTerminal`, `nombreTerminal`, `terminal`, S.nombreSucursal, S.sucursal FROM `" . $idUser . "_master_terminales` as T INNER JOIN " . $idUser . "_master_sucursales as S on T.idSucursal = S.idSucursal where S.idSucursal = '" . $idSucursal . "'";
    }

    $result = db_query($q, 2);
    return $result;
}

function getTipoImpuesto() {
    $idMasterUser = params_get("idMasterUser");
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "SELECT * FROM `tipo_impuestos`";
        $result = db_query($q, 2);
        return $result;
    } else {
        return false;
    }
}

function getUnid() {
    $idMasterUser = params_get("idMasterUser");
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "SELECT * FROM `unidad_medida` order by id asc";
        $result = db_query($q, 2);
        return $result;
    } else {
        return false;
    }
}

function getUsersCompanny() {
    $thisSessionkey = params_get("sessionKey");
    $idUser = getIdUser($thisSessionkey);

    $q = "SELECT `idUser`, `fullName`, `email`, `country`, T.nombreTerminal, T.terminal, S.nombreSucursal, S.sucursal FROM `" . $idUser . "_master_users` as U INNER join " . $idUser . "_master_terminales as T on U.`settings` = T.idTerminal INNER join " . $idUser . "_master_sucursales as S on T.idSucursal = S.idSucursal";
    $result = db_query($q, 2);
    return $result;
}

function companny_getMyInfo() {
    $iam = params_get("iam");
    $idMasterUser = params_get('idMasterUser');
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "SELECT `idUser`, `fullName`, `email`, `country`, T.nombreTerminal, T.terminal, S.nombreSucursal, S.sucursal FROM `" . $idMasterUser . "_master_users` as U INNER join " . $idMasterUser . "_master_terminales as T on U.`settings` = T.idTerminal INNER join " . $idMasterUser . "_master_sucursales as S on T.idSucursal = S.idSucursal where U.userName = '" . $iam . "'";
        $result = db_query($q, 2);
        return $result;
    } else {
        return false;
    }
}

function companny_add_voucher() {
    $iam = params_get("iam");
    $idMasterUser = params_get('idMasterUser');
    $sessionKey = params_get("sessionKey");
    $idUser = getComapannyIdUser($sessionKey, $idMasterUser);
    $clave = params_get("clave");
    $consecutivo = params_get("consecutivo");
    $estado = params_get("estado");
    $xmlEnviadoBase64 = params_get("xmlEnviadoBase64");
    $tipoDocumento = params_get("tipoDocumento");
    $respuestaMHBase64 = params_get("respuestaMHBase64");
    $idReceptor = params_get("idReceptor");
    $env = params_get("env");
    $idComprobanteReferencia = params_get("idComprobanteReferencia");

    if (confirmSessionKey($idMasterUser) != false) {
        $q = "INSERT INTO `" . $idMasterUser . "_master_vouchers` "
                . "(`idComprobante`, `consecutivo`, `clave`, `idComprobanteReferencia`, `idUser`, `tipoDocumento`, `estado`, `xmlEnviadoBase64`, `respuestaMHBase64`,  `idReceptor`, `env`) "
                . "VALUES (NULL, '" . $consecutivo . "', '" . $clave . "','" . $idComprobanteReferencia . "', '" . $idUser . "', '" . $tipoDocumento . "', '" . $estado . "', '" . $xmlEnviadoBase64 . "', '" . $respuestaMHBase64 . "'," . $idReceptor . ", '" . $env . "')";
        $result = db_query($q, 0);
        return $result;
    } else {
        return false;
    }
}

function addCompannyReciver() {
    $nombreCliente = params_get("nombreCliente");
    $numeroCedula = params_get("numeroCedula");
    $tipoCedula = params_get("tipoCedula");
    $telefono = params_get("telefono");
    $idProvincia = params_get("idProvincia");
    $idCanton = params_get("idCanton");
    $idDistrito = params_get("idDistrito");
    $idBarrio = params_get("idBarrio");
    $otrasSenas = params_get("otrasSenas");
    $nombreComercial = params_get("nombreComercial");
    $correoPrincipal = params_get("correoPrincipal");
    $copiasCorreo = params_get("copiasCorreo");
    $numeroFax = params_get("numeroFax");

    $idMasterUser = params_get('idMasterUser');
    $compannyUserId = getComapannyIdUser(params_get('sessionKey'), $idMasterUser);

    if (confirmSessionKey($idMasterUser) != false) {
        $q = "INSERT INTO `" . $idMasterUser . "_master_receiver` (`idReceptor`, `idUser`, `nombreCliente`, `numeroCedula`, `tipoCedula`, `telefono`, `idProvincia`, `idCanton`, `idDistrito`, `idBarrio`, `otrasSenas`, `nombreComercial`, `correoPrincipal`, `copiasCorreo`, `codigoPais`, `numeroFax`, `estadoCliente`) "
                . "VALUES "
                . "(NULL, '$compannyUserId', '$nombreCliente', '$numeroCedula', '$tipoCedula', '$telefono', '$idProvincia', '$idCanton', '$idDistrito', '$idBarrio', '$otrasSenas', '$nombreComercial', '$correoPrincipal', '$copiasCorreo', '506', '$numeroFax', '1')";
        $result = db_query($q, 0);
        return $result;
    } else {
        return false;
    }
}

function companny_updateConsecutive() {
    $env = params_get("env");
    $tipoDocumento = params_get('tipoDocumento');
    $idMasterUser = params_get('idMasterUser');
    $compannyUserId = getComapannyIdUser(params_get('sessionKey'), $idMasterUser);
    if (confirmSessionKey($idMasterUser) != false) {
        $q = "UPDATE `" . $idMasterUser . "_master_consecutive` SET `numeroConsecutivo` = `numeroConsecutivo`+1 WHERE `" . $idMasterUser . "_master_consecutive`.`idUser` = '" . $compannyUserId . "' and `ENV` = '" . $env . "' and `tipoComprobante` = '" . $tipoDocumento . "'";
        $result = db_query($q, 0);
        return $result;
    } else {
        return false;
    }
}

function companny_getMyConsecutive() {
    $iam = params_get("iam");
    $tipoComprobante = params_get("tipoComprobante");
    $idMasterUser = params_get('idMasterUser');
    $env = params_get('env');
    
        $q = "SELECT COALESCE(MAX(`numeroConsecutivo`), 0) as consecutivo FROM `" . $idMasterUser . "_master_consecutive` as C INNER join " . $idMasterUser . "_master_users as U on C.idUser = U.idUser where U.userName = '" . $iam . "' and C.ENV='".$env."' and C.`tipoComprobante` = '" . $tipoComprobante . "'";
        $result = db_query($q, 2);
        return $result;
}

function confirmSessionKey($idMasterUser) {

    global $compannyUser;

    grace_debug("Confirm the session for this user");
    $q = sprintf("SELECT *
FROM " . $idMasterUser . "_master_sessions
WHERE sessionKey = '%s'", params_get('sessionKey', '')
    );
    $r = db_query($q, 1);

    if ($r == ERROR_DB_NO_RESULTS_FOUND) {
        grace_debug("No results found");
        return false;
    } else {
        # Lets confirm the time frame   
        if (conf_get('sessionLifetime', 'users') != -1) {
            if ((time() - $r->lastAccess) > conf_get('sessionLifetime', 'users')) {
                grace_debug("User last access is to old");
                return false;
            }
            return $r->idUser;
        } else {
            return $r->idUser;
        }
    }
}
