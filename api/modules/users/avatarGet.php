<?php

/**
 *  Gets and returns the user avatar
 *  @todo this is a mess, with the new files module it will change a lot
 */
function users_avatarGet()
{
    modules_loader('files', 'module.php');

    # Get the details about the person
    $user = users_load(array('userName' => params_get('userName', '')));

    if ($user->avatar == "")
    {
        $user->avatar = params_get('fall_back', '');
        files_presentFile($user->avatar, false);
    }
    else
    {
        # Where are they stored?
        $avatarPath = files_createPath($user->idUser, "avatar");

        # Get the file name
        $q = sprintf("SELECT * FROM files WHERE idFile = '%s'", $user->avatar);
        $avatarDets = db_query($q, 1);

        # Change the name according to the requested size
        $user->avatar = $avatarPath . str_replace("avatar_def", "avatar_def_" . params_get('size', '25'), $avatarDets->name);
        if (!file_exists($user->avatar))
            $user->avatar = params_get('fall_back', '');
    }

    files_presentFile($user->avatar, false);
}

/**
 * Upload an avatar
 * @todo Move the avatar resize somewhere else and delete old avatars
 */
function users_avatarUpload()
{
    global $user;

    grace_debug("Uploading a new avatar");

    # Use files
    modules_loader('files', 'module.php');

    $dets = files_upload("avatar", "avatar_def", "jpg,png,gif");

    if ($dets < 0)
        return $dets;

    # Resize to a smaller size
    files_resizeImg($dets['fullPath'], '750');

    # Create the thumbnails
    files_resizeImg($dets['fullPath'], array('250','100','50','25'));

    # Update the information in the user profile
    _users_update(array("avatar" => $dets['idFile']));

    return SUCCESS_ALL_GOOD;
}
