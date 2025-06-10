<?php
/*
 * Copyright (C) 2017-2025 CRLibre <https://crlibre.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @ingroup GlobalVars
 *  @{
 */
//! The global User Variable
global $user;

/** @} */
/** @ingroup Constants
 *  @{
 */
# Some needed constants
//! Not a valid user
define('ERROR_USERS_NO_VALID', '-300');

//! Wrong login information
define('ERROR_USERS_WRONG_LOGIN_INFO', '-301');

//! Wrong session, maybe expired
define('ERROR_USERS_NO_VALID_SESSION', '-302');

//! Access denied to this page
define('ERROR_USERS_ACCESS_DENIED', '-303');

//! User already exists
define('ERROR_USERS_EXISTS', '-304');

//! User bg exists
//! @todo move to wirez
define('SUCCESS_USERS_BG_EXISTS', '301');

//! No secret token or not a valid token
define('ERROR_USERS_NO_TOKEN', '-302');

/** @} */

/**
 * Boot up procedure
 */
function users_bootMeUp()
{
    users_loadCurrentUser();
}

/**
 * Loads the current user
 */
function users_loadCurrentUser()
{
    global $user;
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

function users_init()
{
    $paths = array(
        # Register a new user
        # Method = POST
        # @return idUser
        # r=users_register
        # userName=me // no spaces, no weird symbols, same rules as an email address
        # pwd=123 // plain text, unsafe I know
        # about=json(longTextDescribingMe) // json encoded
        # country=crc  //3 letter code for the country
        array(
            'r'             => 'users_register',
            'action'        => 'users_registerNew',
            'access'        => 'users_openAccess',
            'params'        => array(
                array("key" => "fullName",      "def" => "", "req" => true),
                array("key" => "userName",      "def" => "", "req" => true),
                array("key" => "email",         "def" => "", "req" => true),
                array("key" => "about",         "def" => "", "req" => true),
                array("key" => "country",       "def" => "", "req" => true),
                array("key" => "pwd",           "def" => "", "req" => true)
            ),
            'file'          => 'register.php'
        ),
        # Get a list of users
        # @return A list of users that match your criteria
        # r=users_get_list
        # [&like=me]
        array(
            'r'         => 'users_get_list',
            'action'    => 'users_getList',
            'access'    => 'users_openAccess',
            'params'    => array(array("key" => "like", "def" => "", "req" => false)),
            'file'      => 'getList.php'
        ),
        # Log me in
        # @return sessionId
        # r=users_log_me_in
        # pwd=123
        array(
            'r'         => 'users_log_me_in',
            'action'    => 'users_logMeIn',
            'access'    => 'users_openAccess',
            'params'    => array(array("key" => "pwd", "def" => "", "req" => true)),
            'file'      => 'login.php'
        ),
        # Log me out
        # @return Success|Fail
        # r=users_log_me_out
        array(
            'r'         => 'users_log_me_out',
            'action'    => 'users_logMeOut',
            'access'    => 'users_loggedIn',
            'file'      => 'login.php'
        ),
        # Get/Display the avatar
        # @return An image that can be displayed in the browser
        # r=users_avatar_get
        # &size=25|50|100|250
        # &who=userName
        array(
            'r'         => 'users_avatar_get',
            'action'    => 'users_avatarGet',
            'access'    => 'users_openAccess',
            'params'    => array(
                array("key" => "size", "def" => "25", "req" => false),
                array("key" => "userName", "def" => "", "req" => true)
            ),
            'file'      => 'avatarGet.php'
        ),
        # Upload an avatar
        # @return Success|Fail
        # r=users_avatar_upload
        array(
            'r'         => 'users_avatar_upload',
            'action'    => 'users_avatarUpload',
            'access'    => 'users_loggedIn',
            'file'      => 'avatarGet.php'
        ),
        # Get my details
        # @return I will just return the details about the currently logged in user
        # r=users_get_my_details
        array(
            'r'         => 'users_get_my_details',
            'action'    => 'users_getMyDetails',
            'access'    => 'users_loggedIn',
        ),
        # Password recovery either by email or username
        # @return success|fail
        # r=users_recover_password
        # &userName=username|email@email.com
        array(
            'r'         => 'users_recover_pwd',
            'action'    => 'users_recoverPwd',
            'access'    => 'users_openAccess',
            'params'    => array(
                array("key" => "userName", "def" => "", "req" => true)
            ),
        ),
        # Update profile
        # @return Success or fail
        # r=users_update_profile
        # [&fullName=newName
        # &userName=newName
        # &email=newMail
        # &about=json(somehting about me)
        # &country=crc
        # &pwd=newPwd]
        # All fields are tecnically optional, it would make no sense to make such a call, but it would be valid
        array(
            'r'         => 'users_update_profile',
            'action'    => 'users_updateProfile',
            'access'    => 'users_loggedIn',
        ),
        # Upload a new personal chat background
        # type: post
        # &=users_personal_bg_upload
        array(
            'r'         => 'users_personal_bg_upload',
            'action'    => 'users_personalBgUpload',
            'access'    => 'users_loggedIn',
        ),
        # Get the background for someone
        # &=users_personal_bg_upload
        # &fallBack=path
        # &userName=username
        array(
            'r'         => 'users_personal_bg_get',
            'action'    => 'users_personalBgGet',
            'access'    => 'users_openAccess',
        ),
        # Auto login users, this is useful for when you want to embed this with a CMS or similar
        # @return If the login was succesfull
        # r=login_auto
        # &idUser=id
        # &secret=secretTokenWichShouldBeInSettingsAs 'wirezSecret'
        array(
            'r'         => 'login_auto',
            'action'    => 'users_autoLogin',
            'access'    => 'users_openAccess',
            'params'    => array(
                array("key" => "idUser", "def" => "0", "req" => true),
                array("key" => "token", "def" => "ThisIsNotASecretToken", "req" => true)
            ),
            'file'      => 'autoLogin.php'
        ),
        # Confirm the current session
        # @return success|fail
        # r=users_confirm_session_vilidity
        array(
            'r'         => 'users_confirm_session_vilidity',
            'action'    => 'users_confirmSessionValidity',
            'access'    => 'users_loggedIn',
        ),
    );

    return $paths;
}

/* * ***************************************************************************
 *
 * Access and permissions
 *
 * ************************************************************************** */

/**
 * Dummy function, just call me if you want to grant access to anyone
 */
function users_openAccess()
{
    return true;
}

function users_loggedIn()
{
    global $user;

    grace_debug("Confirm that the user is logged in");

    # The user must exist
    # If no user was loaded it could be that I am running in embeded mode
    if ($user->idUser == 0 && conf_get('embeded', 'core', false) == false)
    {
        grace_debug("User id = 0, this can't be logged in");
        return false;
    }

    # Valid session
    return users_confirmSessionKey();
}

/**
 * Register a new user
 */
function users_registerNew()
{
    global $user;
    grace_debug("Register a user");
    $run = false;
    # Does this account exist?
    $newUserByName = users_load(array('userName' => params_get('userName', '')));
    $newUserByEmail = users_load(array('email' => params_get('email', '')));

    if ($newUserByName->idUser == 0 && $newUserByEmail->idUser == 0)
    {
        grace_debug("New user does not exist");
        $user = _users_register(
                array(
                    "fullName"      => params_get('fullName', ''),
                    "userName"      => params_get('userName', ''),
                    "email"         => params_get('email', ''),
                    "about"         => params_get('about', 'May all beings be at ease'),
                    "country"       => params_get('country', 'crc'),
                    "status"        => 1,
                    "timestamp"     => time(),
                    "lastAccess"    => time(),
                    "pwd"           => params_get('pwd', ''),
                    "avatar"        => 0,
                    "settings"      => 'NULL'
                )
        );

        # Load the user and log it in
        $user = users_loadByName(params_get('userName'));

        return users_logMeIn();
    }
    else
    {
        grace_debug("This user already exists");
        $arrayResp = array(
            "code"      => ERROR_USERS_EXISTS,
            "status"    => "usuario ya existe"
        );

        return $arrayResp;
    }
}

/**
 * Logs users in
 * @todo Do not login blocked users status=0
 */
function users_logMeIn()
{
    //global $user;

    grace_debug("Log in this person");

    $userName = params_get('userName');

    # Is it an email based login?
    if (strpos($userName, '@') > 0)
    {
        grace_debug("email based login");
        $user = users_load(array('email' => $userName));
    }
    else
    {
        grace_debug("username based login");
        $user = users_load(array('userName' => $userName));
    }

    if (password_verify(params_get('pwd', ''), users_deshash($user->pwd)))
    {
        // Create a token
        grace_debug("Able to login");
        return array('sessionKey' => users_generateSessionKey($user->idUser), 'userName' => $user->userName,'idUser'=>$user->idUser);
    }
    else if ($user->pwd == md5_hash(params_get('pwd', '')))
    {
        // Create a token
        grace_debug("Able to login");
        return array('sessionKey' => users_generateSessionKey($user->idUser), 'userName' => $user->userName,'idUser'=>$user->idUser);
    }
    else
    {
     //   grace_debug(sprintf("Not able to login %s | %s", params_get('pwd', ''), users_deshash($user->pwd) . " viene " . $user->pwd));
        return ERROR_USERS_WRONG_LOGIN_INFO;
    }
}

/**
 * Create a basic empty user
 */
function users_createBasic()
{
    $user = (object) array('idUser' => 0, 'pwd' => '');
    return $user;
}

/**
 * Generates a session key
 */
function users_generateSessionKey($idUser)
{
    $q = sprintf("delete from sessions where idUser='" . db_escape($idUser) . "'");
    db_query($q, 0);

    modules_loader("crypto", "crypto.php");
    $sessionKey = crypto_encrypt(password_hash(time() * rand(0, 1000)));

    $q = sprintf("INSERT INTO sessions (idUser, sessionKey, ip, lastAccess) "
            . "VALUES('%s', '%s', '%s', '%s')", db_escape($idUser), $sessionKey, db_escape($_SERVER['REMOTE_ADDR']), time());

    db_query($q, 0);

    return $sessionKey;
}

/**
 * Generates a user hash (for passwords mostly)
 * @todo Use php's function
 */
function users_hash($pwd)
{
    // Turn off all error reporting
    error_reporting(0);
    modules_loader("crypto", "crypto.php");
    $salt;
    if (version_compare(PHP_VERSION, '7.0', '<'))
    {
        $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        $options = array(
            'salt' => $salt,
            'cost' => 12
        );
    }
    else
    {
        $salt = random_bytes(22);
        $options = array(
            'cost' => 12
        );
    }

    $hashPass = password_hash($pwd, PASSWORD_BCRYPT, $options);
    $rsp = base64_encode(crypto_encrypt($hashPass));
    return $rsp;
}

function users_deshash($pwd)
{
    $pwd = base64_decode($pwd);
    modules_loader("crypto", "crypto.php");
    return crypto_desencrypt($pwd);
}

function md5_hash($pwd)
{
    return md5($pwd);
}

/**
 * Loads a user by its unique name
 */
function users_load($by = array())
{
    grace_debug("Loading user");

    # Which params do you want to use?
    # I need one at least
    if (count($by) == 0)
        return false;

    $where = "-";
    foreach ($by as $b => $bb)
    {
        $where .= sprintf(" AND %s = '%s'", db_escape($b), db_escape($bb));
    }

    # Replace the first AND
    $where = trim(str_replace("- AND", " WHERE", $where), ',');

    $q = sprintf("SELECT *
    FROM users
    %s", $where);

    $user = db_query($q, 1);

    # If no user found or erros
    if ($user == ERROR_DB_NO_RESULTS_FOUND || $user == ERROR_DB_ERROR)
    {
        grace_debug("Unable to locate user");
        return users_createBasic();
    }

    return $user;
}

/**
 * Loads a user by its unique name
 * @deprecated use users_load() 
 */
function users_loadByName($userName)
{
    grace_debug("Loading user: " . $userName);

    # This should not happen
    if (trim($userName) == '')
    {
        grace_debug("Requested empty user");
        return users_createBasic();
    }

    $q = sprintf("SELECT * FROM users WHERE userName = '%s'", db_escape($userName));

    $user = db_query($q, 1);

    # If no user found or erros
    if ($user == ERROR_DB_NO_RESULTS_FOUND || $user == ERROR_DB_ERROR)
    {
        grace_debug("Unable to locate user");
        return users_createBasic();
    }

    return $user;
}

/**
 * Confirm the validity of a session
 */
function users_confirmSessionKey()
{
    global $user;

    grace_debug("Confirm the session for this user");
    $q = sprintf("SELECT *
        FROM sessions
        WHERE sessionKey = '%s'
        AND ip = '%s'
        AND idUser = '%s'", db_escape(params_get('sessionKey', '')), db_escape($_SERVER['REMOTE_ADDR']), db_escape($user->idUser)
    );
    $r = db_query($q, 1);

    if ($r == ERROR_DB_NO_RESULTS_FOUND)
    {
        grace_debug("No results found");
        return false;
    }
    else
    {
        # Lets confirm the time frame   
        if (conf_get('sessionLifetime', 'users') != -1)
        {
            if ((time() - $r->lastAccess) > conf_get('sessionLifetime', 'users'))
            {
                grace_debug("User last access is to old");
                return false;
            }

            return $r->idUser;
        }
    }
}

/**
 * Destroys a session
 */
function users_destroySession()
{
    $q = sprintf("DELETE FROM sessions WHERE sessionKey = '%s' AND ip = '%s'", params_get('sessionKey', ''), db_escape($_SERVER['REMOTE_ADDR']));
    db_query($q, 0);
}

/**
 * Updates the last access with a session key
 * @todo Update only if the access was valid
 */
function users_updateLastAccess()
{
    global $user;

    if (params_get('sessionKey', '') != '' && $user->idUser > 0)
    {
        # Update in the session table
        $q = sprintf("UPDATE sessions SET lastAccess = '%s' WHERE sessionKey = '%s'", time(), db_escape(params_get('sessionKey', '')));
        db_query($q, 0);

        # Update in the users table too, this could be a separate process
        $q = sprintf("UPDATE users SET lastAccess = '%s' WHERE idUser = '%s'", time(), db_escape($user->idUser));
        db_query($q, 0);
    }
}

/**
 * Update the user profile
 */
function users_updateProfile()
{
    global $user;

    # Set the idUser to this logged in user
    params_set('idUser', $user->idUser);

    # Set the current password if no new password was sent
    $dets = params_get(false);

    # Does this account exist?
    # Did you request a different username?
    if ($user->userName != $dets['userName'])
    {
        $newUserByName = users_load(array('userName' => $dets['userName']));
        if ($newUserByName->idUser != 0)
        {
            $arrayResp = array(
                "code"      => ERROR_USERS_EXISTS,
                "status"    => "usuario ya existe"
            );
            return $arrayResp;
        }
    }

    # Did you request a different email
    if ($user->email != $dets['email'])
    {
        grace_debug("Requested a new email");
        $newUserByEmail = users_load(array('email' => $dets['email']));
        if ($newUserByEmail->idUser != 0)
        {
            $arrayResp = array(
                "code"      => ERROR_USERS_EXISTS,
                "status"    => "usuario ya existe"
            );
            return $arrayResp;
        }
    }

    $r = _users_update($dets);

    if ($r == 0)
    {
        $arrayResp = array(
            "code"      => ERROR_ERROR,
            "status"    => "error registrando"
        );
        return $arrayResp;
    }

    $arrayResp = array(
        "code"      => SUCCESS_ALL_GOOD,
        "status"    => "registrado con exito"
    );
    return $arrayResp;
}

/**
 * Clean up user names, this function should not be here
 * # @todo create a validation tool for this
 */
function users_cleanName($name)
{
    $name = trim($name);
    return $name;
}

/**
 * Helper function to actually update a user
 */
function _users_update($dets)
{
    global $user;

    # If password is not set, I will keep it the same
    if (!isset($dets['pwd']) || trim($dets['pwd']) == '')
        $dets['pwd'] = 'pwd';
    else
        $dets['pwd'] = "'" .users_hash($dets['pwd']) . "'";

    # Merge the current information about the user and the new information provided
    $newDets = array_replace((array) $user, $dets);

    $q = sprintf("UPDATE users SET `fullName` = '%s',
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
}

/**
 * Get MY details
 */
function users_getMyDetails()
{
    grace_debug("Getting my details");

    $user = users_loadByName(params_get('iam'));
    return $user;
}

/**
 * Upload personal background
 */
function users_personalBgUpload()
{
    global $user;

    grace_debug("Uploading a new personal background");

    modules_loader("files", "module.php");

    # Delete all background images
    $targetDir = files_createPath($user->idUser, "background");
    if (is_dir($targetDir))
    {
        $files = scandir($targetDir);
        foreach ($files as $file)
        {
            if ($file != "." && $file != "..")
                unlink($targetDir . "/" . $file);
        }
    }

    $res = files_upload('background', 'personalBackground');

    if ($res < 0)
        users_returnJson($res);

    return SUCCESS_ALL_GOOD;
}

/**
 * Helper function to actually create a new user and register it in the db
 */
function _users_register($userDets)
{
    $q = sprintf("INSERT INTO users (fullName, userName, email, about, country, status, timestamp, lastAccess, pwd, avatar,settings)
        VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", db_escape($userDets['fullName']), db_escape($userDets['userName']), db_escape($userDets['email']), db_escape(addslashes($userDets['about'])), db_escape($userDets['country']), db_escape($userDets['status']), db_escape($userDets['timestamp']), db_escape($userDets['lastAccess']), users_hash($userDets['pwd']), db_escape($userDets['avatar']), db_escape($userDets['settings'])
    );
    db_query($q, 0);
}

# Create a basic empty user

function _userCreateBasic()
{
    $theUser = (object) array('idUser' => 0, 'pwd' => '');
    return $theUser;
}

/**
 * Gets and returns the user background
 *  @todo Return a default background if none is found
 */
function users_personalBgGet()
{
    modules_loader('files', 'module.php');

    $user = users_loadByName(params_get('userName', ''));

    if ($user->idUser == 0)
        return params_get('fallBack', '');

    $userBg = files_createPath($user->idUser, "background") . "personalBackground";

    $userBgFullName = params_get('fallBack', '');

    # I will just investigate, may not be better, or it may be better than asking the database about this all the time
    if (file_exists($userBg . ".jpg"))
        $userBgFullName = $userBg . ".jpg";
    elseif (file_exists($userBg . ".png"))
        $userBgFullName = $userBg . ".png";
    elseif (file_exists($userBg . ".gif"))
        $userBgFullName = $userBg . "gif";

    files_presentFile($userBgFullName, "img", false);

    # If you are only asking if the background exists
    /*if ($userBgFullName) {
        
    }*/
}

/**
 * Access permissions, verify if they exist
 * @todo Create an admin group
 */
function users_access($perm, $theUser = false)
{
    global $user;

    if ($user == false)
        $theUser = $user;

    # The powers that be
    if ($user->idUser == 1)
        return true;

    grace_debug("Checking for perms: " + $perm);

    #@todo Actually check the perms :)
    return true;
}

/**
 * Generate a new temporary password for recovery
 */
function users_recoverPwd()
{
    //
    global $user;

    # This call probably won't have the user via iam, so, I will load it
    $userName = params_get('userName', '');

    # Is it an email based login?
    if (strpos($userName, '@') > 0)
    {
        grace_debug("email based login");
        $user = users_load(array('email' => $userName));
    }
    else
    {
        grace_debug("username based login");
        $user = users_load(array('userName' => $userName));
    }

    if ($user->idUser == 0)
        return ERROR_BAD_REQUEST;

    # Use mailer
    tools_loadLibrary('mailer.php');

    # Generate a new temporary password

    modules_loader("crypto", "crypto.php");
    $temp = rand(0, 1000) + time();
    $user->pwd = users_hash($temp);

    grace_debug("New tmp pwd: " . $user->pwd);

    # Update account
    if (_users_update((array) $user))
    {
        # Send email
        grace_debug("I will send the email");
        $resp = mailer_sendEmail(array(
            'to'        => $user->email,
            'subject'   => 'Recuperación de Clave ' . conf_get('siteName', 'core', 'Mi Sitio'),
            'replyTo'   => conf_get('noreply', 'mail', 'no-repy@crlibre.org'),
            'message'   => 'Su nueva clave es: ' . $temp
        ));
        if ($resp == true)
            return SUCCESS_ALL_GOOD;
    }

    # If I reached this place there was an error
    return ERROR_ERROR;
}

/**
 * Confirm the validity of this session
 */
function users_confirmSessionValidity()
{
    grace_debug("I will confirm the validity of this session: " . params_get('iam', '') . " -- " . params_get('sessionKey', ''));

    # If I got here I am logged in :)
    return SUCCESS_ALL_GOOD;
}
