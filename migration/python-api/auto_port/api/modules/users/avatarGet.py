"""
AUTO-PORTED FROM: api/modules/users/avatarGet.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def users_avatarGet():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        modules_loader('files', 'module.php');
    
        # Get the details about the person
        $user = users_load(array('userName' => params_get('userName', '')));
    
        $transparentGif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    
        if ($user->avatar == "")
        {
            files_presentFile($transparentGif);
        }
        else
        {
            # Where are they stored?
            $avatarPath = files_createPath($user->idUser, "avatar");
    
            # Get the file name
            $q = sprintf("SELECT * FROM files WHERE idFile = '%s'", db_escape($user->avatar));
            $avatarDets = db_query($q, 1);
    
            # Change the name according to the requested size
            $user->avatar = $avatarPath . str_replace("avatar_def", "avatar_def_" . params_get('size', '25'), $avatarDets->name);
        }
    
        if (!file_exists($user->avatar))
            files_presentFile($transparentGif);
        else
            files_presentFile($user->avatar, false);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def users_avatarUpload():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
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
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
