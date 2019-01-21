<?php

global $compannyUser;

function getIdUser($sessionKey) {
    $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . $sessionKey . "'";
    $result = db_query($q, 2);
    $idUser = $result[0]->idUser;
    return $idUser;
}

/**
 * Dummy function, just call me if you want to grant access to anyone
 */
function companny_users_openAccess() {
    return true;
}

function companny_users_loggedIn() {
    $idMasterUser = params_get("idMasterUser");
    global $compannyUser;

    grace_debug("Confirm that the user is logged in");

    # The user must exist
    # If no user was loaded it could be that I am running in embeded mode
    if ($compannyUser->idUser == 0 && conf_get('embeded', 'core', false) == false) {
        grace_debug("User id = 0, this can't be logged in");
        return false;
    }

    # Valid session
    return companny_users_confirmSessionKey($idMasterUser);
}

/**
 * Register a new user
 */
function companny_users_registerNew() {
    grace_debug("TEST");
    $thisSessionkey = params_get("sessionKey");
    $idMasterUser = getIdUser($thisSessionkey);
    $pwd = params_get('pwd');
    global $compannyUser;
    grace_debug("Register a user");
    $run = false;
    # Does this account exist?
    $newUserByName = companny_users_load($idMasterUser, array('userName' => params_get('userName', '')));
    $newUserByEmail = companny_users_load($idMasterUser, array('email' => params_get('email', '')));

    if ($newUserByName->idUser == 0 && $newUserByEmail->idUser == 0) {
        grace_debug("New user does not exist");
        $compannyUser = _companny_users_register(
                array(
            "idMasterUser" => $idMasterUser,
            "fullName" => params_get('fullName', ''),
            "userName" => params_get('userName', ''),
            "email" => params_get('email', ''),
            "about" => params_get('about', 'May all beings be at ease'),
            "country" => params_get('country', 'crc'),
            "status" => 1,
            "timestamp" => time(),
            "lastAccess" => time(),
            "pwd" => $pwd,
            "avatar" => 0,
            "settings" => params_get('settings', '')
                ), $idMasterUser
        );
        # Load the user and log it in
        $compannyUser = companny_users_loadByName(params_get('userName'), $idMasterUser);

        return companny_users_logMeIn($idMasterUser);
    } else {
        grace_debug("This user already exists");
        $arrayResp = array(
            "code" => ERROR_USERS_EXISTS,
            "status" => "usuario ya existe"
        );
        return $arrayResp;
    }
}

/**
 * Logs users in
 * @todo Do not login blocked users status=0
 */
function companny_users_logMeIn($idMasterUser = '') {
    if ($idMasterUser == '') {
        $idMasterUser = params_get("idMasterUser");
    }
    //global $compannyUser;

    grace_debug("Log in this person");

    $compannyUserName = params_get('userName');

    # Is it an email based login?
    if (strpos($compannyUserName, '@') > 0) {
        grace_debug("email based login");
        $compannyUser = companny_users_load($idMasterUser, array('email' => $compannyUserName));
    } else {
        grace_debug("username based login");
        $compannyUser = companny_users_load($idMasterUser, array('userName' => $compannyUserName));
    }
    grace_debug("### VAlidacion: " . $compannyUser->pwd . " Lo que ingresa" . params_get("pwd"));

    if (password_verify(params_get('pwd', ''), $compannyUser->pwd)) {
        // Create a token
        grace_debug("Able to login");
        return array('sessionKey' => companny_users_generateSessionKey($compannyUser->idUser, $idMasterUser), 'userName' => $compannyUser->userName, 'idUser' => $compannyUser->idUser);
    } else if ($compannyUser->pwd == md5(params_get('pwd', ''))) {
        // Create a token
        grace_debug("Able to login");
        return array('sessionKey' => companny_users_generateSessionKey($compannyUser->idUser, $idMasterUser), 'userName' => $compannyUser->userName, 'idUser' => $compannyUser->idUser);
    } else {

        //   grace_debug(sprintf("Not able to login %s | %s", params_get('pwd', ''), companny_users_deshash($compannyUser->pwd) . " viene " . $compannyUser->pwd));
        return ERROR_USERS_WRONG_LOGIN_INFO;
    }
}

/**
 * Create a basic empty user
 */
function companny_users_createBasic() {
    $compannyUser = (object) array('idUser' => 0, 'pwd' => '');
    return $compannyUser;
}

/**
 * Generates a session key
 */
function companny_users_generateSessionKey($idUser, $idMasterUser) {
    $q = sprintf("delete from " . $idMasterUser . "_master_sessions where idUser='" . $idUser . "'");
    db_query($q, 0);

    $sessionKey = password_hash(time() * rand(0, 1000), PASSWORD_DEFAULT);

    $q = sprintf("INSERT INTO " . $idMasterUser . "_master_sessions (idUser, sessionKey, ip, lastAccess) "
            . "VALUES('%s', '%s', '%s', '%s')", $idUser, $sessionKey, $_SERVER['REMOTE_ADDR'], time());

    db_query($q, 0);

    return $sessionKey;
}

/**
 * Loads a user by its unique name
 */
function companny_users_load($idMasterUser, $by = array()) {

    grace_debug("Loading user");

    # Which params do you want to use?
    # I need one at least
    if (count($by) == 0) {
        return false;
    }
    $where = "-";
    foreach ($by as $b => $bb) {
        $where .= sprintf(" AND %s = '%s'", $b, $bb);
    }
    # Replace the first AND
    $where = trim(str_replace("- AND", " WHERE", $where), ',');

    $q = sprintf("SELECT *
    FROM " . $idMasterUser . "_master_users
    %s", $where);

    $compannyUser = db_query($q, 1);

    # If no user found or erros
    if ($compannyUser == ERROR_DB_NO_RESULTS_FOUND || $compannyUser == ERROR_DB_ERROR) {
        grace_debug("Unable to locate user");
        return companny_users_createBasic();
    }

    return $compannyUser;
}

/**
 * Loads a user by its unique name
 * @deprecated use companny_users_load() 
 */
function companny_users_loadByName($idMasterUser, $compannyUserName) {

    grace_debug("Loading user: " . $compannyUserName);

    # This should not happen
    if (trim($compannyUserName) == '') {
        grace_debug("Requested empty user");
        return companny_users_createBasic();
    }

    $q = sprintf("SELECT * FROM " . $idMasterUser . "_master_users WHERE userName = '%s'", $compannyUserName);

    $compannyUser = db_query($q, 1);

    # If no user found or erros
    if ($compannyUser == ERROR_DB_NO_RESULTS_FOUND || $compannyUser == ERROR_DB_ERROR) {
        grace_debug("Unable to locate user");
        return companny_users_createBasic();
    }

    return $compannyUser;
}

/**
 * Confirm the validity of a session
 */
function companny_users_confirmSessionKey($idMasterUser) {

    global $compannyUser;

    grace_debug("Confirm the session for this user");
    $q = sprintf("SELECT *
        FROM " . $idMasterUser . "_master_sessions
        WHERE sessionKey = '%s'
        AND ip = '%s'
        AND idUser = '%s'", params_get('sessionKey', ''), $_SERVER['REMOTE_ADDR'], $compannyUser->idUser
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

/**
 * Destroys a session
 */
function companny_users_destroySession($idMasterUser) {
    $q = sprintf("DELETE FROM " . $idMasterUser . "_master_sessions WHERE sessionKey = '%s' AND ip = '%s'", params_get('sessionKey', ''), $_SERVER['REMOTE_ADDR']);
    db_query($q, 0);
}

/**
 * Updates the last access with a session key
 * @todo Update only if the access was valid
 */
//function companny_users_updateLastAccess() {
//
//    global $compannyUser;
//
//    if (params_get('sessionKey', '') != '' && $compannyUser->idUser > 0) {
//        # Update in the session table
//        $q = sprintf("UPDATE sessions SET lastAccess = '%s' WHERE sessionKey = '%s'", time(), params_get('sessionKey', ''));
//        db_query($q, 0);
//
//        # Update in the users table too, this could be a separate process
//        $q = sprintf("UPDATE users SET lastAccess = '%s' WHERE idUser = '%s'", time(), $compannyUser->idUser);
//        db_query($q, 0);
//    }
//}

/**
 * Update the user profile
 */
function companny_users_updateProfile() {

    global $compannyUser;

    # Set the idUser to this logged in user
    params_set('idUser', $compannyUser->idUser);

    # Set the current password if no new password was sent
    $dets = params_get(false);

    # Does this account exist?
    # Did you request a different username?
    if ($compannyUser->userName != $dets['userName']) {
        $newUserByName = companny_users_load(array('userName' => $dets['userName']), $idMasterUser);
        if ($newUserByName->idUser != 0) {
            $arrayResp = array(
                "code" => ERROR_USERS_EXISTS,
                "status" => "usuario ya existe"
            );
            return $arrayResp;
        }
    }

    # Did you request a different email
    if ($compannyUser->email != $dets['email']) {
        grace_debug("Requested a new email");
        $newUserByEmail = companny_users_load(array('email' => $dets['email']));
        if ($newUserByEmail->idUser != 0) {
            $arrayResp = array(
                "code" => ERROR_USERS_EXISTS,
                "status" => "usuario ya existe"
            );
            return $arrayResp;
        }
    }

    $r = _companny_users_update($dets);

    if ($r == 0) {
        $arrayResp = array(
            "code" => ERROR_ERROR,
            "status" => "error registrando"
        );
        return $arrayResp;
    }
    $arrayResp = array(
        "code" => SUCCESS_ALL_GOOD,
        "status" => "registrado con exito"
    );
    return $arrayResp;
}

/**
 * Clean up user names, this function should not be here
 * # @todo create a validation tool for this
 */
//function companny_users_cleanName($name) {
//
//    $name = trim($name);
//
//    return $name;
//}

/**
 * Helper function to actually update a user
 */
//function _companny_users_update($dets) {
//
//    global $compannyUser;
//
//    # If password is not set, I will keep it the same
//    if (!isset($dets['pwd']) || trim($dets['pwd']) == '') {
//        $dets['pwd'] = 'pwd';
//    } else {
//        $dets['pwd'] = "'" . password_hash($dets['pwd']) . "'";
//    }
//
//    # Merge the current information about the user and the new information provided
//    $newDets = array_replace((array) $compannyUser, $dets);
//
//    $q = sprintf("UPDATE users SET `fullName` = '%s',
//        `userName` = '%s',
//        `email` = '%s',
//        `about` = '%s',
//        `country` = '%s',
//        `status` = '%s',
//        `timestamp` = '%s',
//        `lastAccess` = '%s',
//        `pwd` = %s,
//        `avatar` = '%s'
//        WHERE `idUser` = %s", addslashes($newDets['fullName']), companny_users_cleanName($newDets['userName']), $newDets['email'], addslashes($newDets['about']), $newDets['country'], $newDets['status'], $newDets['timestamp'], $newDets['lastAccess'], $newDets['pwd'], $newDets['avatar'], $newDets['idUser']
//    );
//
//    return db_query($q, 0);
//}

/**
 * Get MY details
 */
function companny_users_getMyDetails() {
    $idMasterUser = params_get("idMasterUser");
    grace_debug("Getting my details");
        $compannyUser = companny_users_loadByName($idMasterUser, params_get('iam'));
        return $compannyUser;
}

/**
 * Upload personal background
 */
//function companny_users_personalBgUpload() {
//
//    global $compannyUser;
//
//    grace_debug("Uploading a new personal background");
//
//    modules_loader("files", "module.php");
//
//    # Delete all background images
//    $targetDir = files_createPath($compannyUser->idUser, "background");
//    if (is_dir($targetDir)) {
//        $files = scandir($targetDir);
//        foreach ($files as $file) {
//            if ($file != "." && $file != "..") {
//                unlink($targetDir . "/" . $file);
//            }
//        }
//    }
//
//    $res = files_upload('background', 'personalBackground');
//
//    if ($res < 0) {
//        companny_users_returnJson($res);
//    }
//
//    return SUCCESS_ALL_GOOD;
//}

/**
 * Helper function to actually create a new user and register it in the db
 */
function _companny_users_register($compannyUserDets, $idMasterUser) {
    $pwd = password_hash($compannyUserDets['pwd'], PASSWORD_DEFAULT);
    $q = sprintf("INSERT INTO " . $idMasterUser . "_master_users (idMasterUser,fullName, userName, email, about, country, status, timestamp, lastAccess, pwd, avatar,settings)
        VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", $idMasterUser, $compannyUserDets['fullName'], $compannyUserDets['userName'], $compannyUserDets['email'], addslashes($compannyUserDets['about']), $compannyUserDets['country'], $compannyUserDets['status'], $compannyUserDets['timestamp'], $compannyUserDets['lastAccess'], $pwd, $compannyUserDets['avatar'], $compannyUserDets['settings']
    );
    db_query($q, 0);
}

//# Create a basic empty user
//
//function _userCreateBasic() {
//
//    $theUser = (object) array('idUser' => 0, 'pwd' => '');
//    return $theUser;
//}

/**
 * Gets and returns the user background
 *  @todo Return a default background if none is found
 */
//function companny_users_personalBgGet() {
//
//    modules_loader('files', 'module.php');
//
//    $compannyUser = companny_users_loadByName(params_get('userName', ''), $idMasterUser);
//
//    if ($compannyUser->idUser == 0) {
//        return params_get('fallBack', '');
//    }
//
//    $compannyUserBg = files_createPath($compannyUser->idUser, "background") . "personalBackground";
//
//    $compannyUserBgFullName = params_get('fallBack', '');
//
//    # I will just investigate, may not be better, or it may be better than asking the database about this all the time
//    if (file_exists($compannyUserBg . ".jpg")) {
//        $compannyUserBgFullName = $compannyUserBg . ".jpg";
//    } elseif (file_exists($compannyUserBg . ".png")) {
//        $compannyUserBgFullName = $compannyUserBg . ".png";
//    } elseif (file_exists($compannyUserBg . ".gif")) {
//        $compannyUserBgFullName = $compannyUserBg . "gif";
//    }
//
//    files_presentFile($compannyUserBgFullName, "img", false);
//
//    # If you are only asking if the background exists
//    if ($compannyUserBgFullName) {
//        
//    }
//}

/**
 * Access permissions, verify if they exist
 * @todo Create an admin group
 */
//function companny_users_access($perm, $theUser = false) {
//
//    global $compannyUser;
//
//    if ($compannyUser == false) {
//        $theUser = $compannyUser;
//    }
//
//    # The powers that be
//    if ($compannyUser->idUser == 1) {
//        return true;
//    }
//
//    grace_debug("Checking for perms: " + $perm);
//
//    #@todo Actually check the perms :)
//    return true;
//}

/**
 * Generate a new temporary password for recovery
 */
function companny_users_recoverPwd() {
    //
    $idMasterUser = params_get("idMasterUser");
    global $compannyUser;
    # This call probably won't have the user via iam, so, I will load it
    $compannyUserName = params_get('userName', '');

    # Is it an email based login?
    if (strpos($compannyUserName, '@') > 0) {
        grace_debug("email based login");
        $compannyUser = companny_users_load($idMasterUser, array('email' => $compannyUserName));
    } else {
        grace_debug("username based login");
        $compannyUser = companny_users_load($idMasterUser, array('userName' => $compannyUserName));
    }
    if ($compannyUser->idUser == 0) {
        return ERROR_BAD_REQUEST;
    }

    # Use mailer
    tools_loadLibrary('mailer.php');

    # Generate a new temporary password

    $temp = rand(0, 1000) + time();

    $compannyUser->pwd = password_hash($temp, PASSWORD_DEFAULT);
    grace_debug("New tmp pwd: " . $compannyUser->pwd);

    # Update account
    if (_companny_users_update((array) $compannyUser, $idMasterUser)) {
        # Send email
        grace_debug("I will send the email");
        $resp = mailer_sendEmail(array(
            'to' => $compannyUser->email,
            'subject' => 'Recuperación de Clave ' . conf_get('siteName', 'core', 'Mi Sitio'),
            'replyTo' => 'no-repy@' . conf_get("domain", "core", "crlibre.or"),
            'message' => 'Su nueva clave es: ' . $temp
        ));
        if ($resp == true) {
            return SUCCESS_ALL_GOOD;
        }
    }

    # If I reached this place there was an error
    return ERROR_ERROR;
}

function companny_users_logMeOut() {
    $idMasterUser = params_get("idMasterUser");
    grace_debug("Log out");
    companny_users_destroySession($idMasterUser);
    params_set('sessionKey', 'longGone');
    return 'good bye';
}

/**
 * Confirm the validity of this session
 */
function companny_users_confirmSessionValidity() {

    grace_debug("I will confirm the validity of this session: " . params_get('iam', '') . " -- " . params_get('sessionKey', ''));

    # If I got here I am logged in :)
    return SUCCESS_ALL_GOOD;
}

function _companny_users_update($dets, $idMasterUser) {

    global $user;

    # If password is not set, I will keep it the same
    if (!isset($dets['pwd']) || trim($dets['pwd']) == '') {
        $dets['pwd'] = 'pwd';
    } else {
        $dets['pwd'] = "'" . $dets['pwd'] . "'";
    }
    grace_debug("####->" . $newDets['pwd']);

    # Merge the current information about the user and the new information provided
    $newDets = array_replace((array) $user, $dets);

    $q = sprintf("UPDATE " . $idMasterUser . "_master_users SET `fullName` = '%s',
        `userName` = '%s',
        `email` = '%s',
        `about` = '%s',
        `country` = '%s',
        `status` = '%s',
        `timestamp` = '%s',
        `lastAccess` = '%s',
        `pwd` = %s,
        `avatar` = '%s'
        WHERE `idUser` = %s", addslashes($newDets['fullName']), users_cleanName($newDets['userName']), $newDets['email'], addslashes($newDets['about']), $newDets['country'], $newDets['status'], $newDets['timestamp'], $newDets['lastAccess'], $newDets['pwd'], $newDets['avatar'], $newDets['idUser']
    );

    return db_query($q, 0);
}
