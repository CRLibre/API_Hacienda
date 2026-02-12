"""
AUTO-PORTED FROM: api/contrib/facturador/companny_user.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def getIdUser(sessionKey):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = "SELECT `idUser` FROM `sessions` WHERE `sessionKey`='" . db_escape($sessionKey) . "'";
        $result = db_query($q, 2);
        $idUser = $result[0]->idUser;
        return $idUser;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_openAccess():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        return true;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_loggedIn():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_registerNew():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_logMeIn(idMasterUser=''):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_createBasic():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $compannyUser = (object) array('idUser' => 0, 'pwd' => '');
        return $compannyUser;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_generateSessionKey(idUser, idMasterUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = sprintf("delete from " . db_escape($idMasterUser) . "_master_sessions where idUser='" . db_escape($idUser) . "'");
        db_query($q, 0);
    
        $sessionKey = password_hash(time() * rand(0, 1000), PASSWORD_DEFAULT);
    
        $q = sprintf("INSERT INTO " . db_escape($idMasterUser) . "_master_sessions (idUser, sessionKey, ip, lastAccess) "
                . "VALUES('%s', '%s', '%s', '%s')", db_escape($idUser), db_escape($sessionKey), db_escape($_SERVER['REMOTE_ADDR']), time());
    
        db_query($q, 0);
    
        return $sessionKey;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_load(idMasterUser, by=array()):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
        grace_debug("Loading user");
    
        # Which params do you want to use?
        # I need one at least
        if (count($by) == 0) {
            return false;
        }
        $where = "-";
        foreach ($by as $b => $bb) {
            $where .= sprintf(" AND %s = '%s'", db_escape($b), db_escape($bb));
        }
        # Replace the first AND
        $where = trim(str_replace("- AND", " WHERE", $where), ',');
    
        $q = sprintf("SELECT *
        FROM " . db_escape($idMasterUser) . "_master_users
        %s", $where);
    
        $compannyUser = db_query($q, 1);
    
        # If no user found or erros
        if ($compannyUser == ERROR_DB_NO_RESULTS_FOUND || $compannyUser == ERROR_DB_ERROR) {
            grace_debug("Unable to locate user");
            return companny_users_createBasic();
        }
    
        return $compannyUser;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_loadByName(idMasterUser, compannyUserName):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
        grace_debug("Loading user: " . $compannyUserName);
    
        # This should not happen
        if (trim($compannyUserName) == '') {
            grace_debug("Requested empty user");
            return companny_users_createBasic();
        }
    
        $q = sprintf("SELECT * FROM " . db_escape($idMasterUser) . "_master_users WHERE userName = '%s'", db_escape($compannyUserName));
    
        $compannyUser = db_query($q, 1);
    
        # If no user found or erros
        if ($compannyUser == ERROR_DB_NO_RESULTS_FOUND || $compannyUser == ERROR_DB_ERROR) {
            grace_debug("Unable to locate user");
            return companny_users_createBasic();
        }
    
        return $compannyUser;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_confirmSessionKey(idMasterUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
        global $compannyUser;
    
        grace_debug("Confirm the session for this user");
        $q = sprintf("SELECT *
            FROM " . db_escape($idMasterUser) . "_master_sessions
            WHERE sessionKey = '%s'
            AND ip = '%s'
            AND idUser = '%s'", db_escape(params_get('sessionKey', '')), db_escape($_SERVER['REMOTE_ADDR']), db_escape($compannyUser->idUser)
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_destroySession(idMasterUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = sprintf("DELETE FROM " . db_escape($idMasterUser) . "_master_sessions WHERE sessionKey = '%s' AND ip = '%s'", db_escape(params_get('sessionKey', '')), db_escape($_SERVER['REMOTE_ADDR']));
        db_query($q, 0);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_updateProfile():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_getMyDetails():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $idMasterUser = params_get("idMasterUser");
        grace_debug("Getting my details");
            $compannyUser = companny_users_loadByName($idMasterUser, params_get('iam'));
            return $compannyUser;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def _companny_users_register(compannyUserDets, idMasterUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $pwd = password_hash($compannyUserDets['pwd'], PASSWORD_DEFAULT);
        $q = sprintf("INSERT INTO " . db_escape($idMasterUser) . "_master_users (idMasterUser,fullName, userName, email, about, country, status, timestamp, lastAccess, pwd, avatar,settings)
            VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", db_escape($idMasterUser), db_escape($compannyUserDets['fullName']), db_escape($compannyUserDets['userName']), db_escape($compannyUserDets['email']), db_escape(addslashes($compannyUserDets['about'])), db_escape($compannyUserDets['country']), db_escape($compannyUserDets['status']), db_escape($compannyUserDets['timestamp']), db_escape($compannyUserDets['lastAccess']), db_escape($pwd), db_escape($compannyUserDets['avatar']), db_escape($compannyUserDets['settings'])
        );
        db_query($q, 0);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_recoverPwd():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_logMeOut():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $idMasterUser = params_get("idMasterUser");
        grace_debug("Log out");
        companny_users_destroySession($idMasterUser);
        params_set('sessionKey', 'longGone');
        return 'good bye';
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def companny_users_confirmSessionValidity():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
        grace_debug("I will confirm the validity of this session: " . params_get('iam', '') . " -- " . params_get('sessionKey', ''));
    
        # If I got here I am logged in :)
        return SUCCESS_ALL_GOOD;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def _companny_users_update(dets, idMasterUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
    
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
            WHERE `idUser` = %s", db_escape(addslashes($newDets['fullName'])), db_escape(users_cleanName($newDets['userName'])), db_escape($newDets['email']), db_escape(addslashes($newDets['about'])), db_escape($newDets['country']), db_escape($newDets['status']), db_escape($newDets['timestamp']), db_escape($newDets['lastAccess']), db_escape($newDets['pwd']), db_escape($newDets['avatar']), db_escape($newDets['idUser'])
        );
    
        return db_query($q, 0);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
