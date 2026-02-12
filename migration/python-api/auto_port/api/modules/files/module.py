"""
AUTO-PORTED FROM: api/modules/files/module.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

def files_bootMeUp():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        //
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_init():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $paths = array(
            array(
                'r'         => 'filesGetUrl',
                'action'    => 'filesGetUrl',
                'access'    => "users_openAccess",
                'params'    => array(
                    array("key" => "downloadCode", "def" => "", "req" => true)
                )
            ),
            array(
                'r'         => 'files_view_file',
                'action'    => 'files_viewPublic',
                'access'    => "users_openAccess"
            ),
            array(
                'r'         => 'upload',
                'action'    => 'files_upload',
                'access'    => "users_openAccess"
            )
        );
    
        return $paths;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def filesGetUrl(codigo=''):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        /**
         * Esta funcion se puede llamar desde GET POST si se envian los siguientes parametros
         * w=files
         * r=filesGetUrl
         * downloadCode=codigo de descarga del file
         * Tambien se puede llamar desde un metodo de la siguiente manera:
         * modules_loader("files");       <-- Esta funcion importa el modulo
         * filesGetUrl('codigo');  <------------ esta funcion retorna el URL del file codigo es el downloadCode de la db
         * */
        if ($codigo == '')
            $codigo = params_get('downloadCode', '');
    
        $q = sprintf("SELECT * FROM files WHERE downloadCode = '%s'", db_escape($codigo));
        $file = db_query($q, 1);
        if ($file != ERROR_DB_NO_RESULTS_FOUND)
        {
            $filePath = files_createPath($file->idUser, $file->type) . $file->name;
            return $filePath;
        }
    
        return false;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_createPath(idUser, type):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        return sprintf('%s%s/%s/', conf_get('basePath', 'files', '/'), $idUser, $type);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_createDownloadCode(name, idUser):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        return md5($name . "//" . time() . $idUser);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_upload(type='attach', finalName=False, ext=False, maxSize=0, del=True):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        global $user;
    
        # Load the tool
        tools_useTool('ImageResize.php');
    
        grace_debug("Uploading a file");
    
        # List of allowed files
        if ($ext == false)
        {
            grace_debug("Using default allowed extentions");
            $ext = conf_get("allowedExt", "files", "jpg,JPG,jpeg,JPEG,png,PNG,gif,GIF,p12,P12,pfx,PFX,xml,XML,Xml");
        }
    
        # Maximum allowed size
        if ($maxSize == false)
        {
            grace_debug("Using default max upload size");
            $maxSize = conf_get("maxUploadSize", "files", "2");
        }
    
        # Where should I store this file?
        $targetDir = files_createPath($user->idUser, $type);
    
        grace_debug("Saving file to: " . $targetDir);
    
        # Create directory if it does not exist
        if (!file_exists($targetDir))
        {
            mkdir($targetDir, 0777, true);
        }
    
        # Set the new name if one was given
        $finalName = ($finalName == false ?
                basename($_FILES["fileToUpload"]["name"]) :
                $finalName . "." . pathinfo(basename($_FILES["fileToUpload"]["name"]), PATHINFO_EXTENSION));
    
        $targetFile = $targetDir . $finalName;
    
        grace_debug("Uploading file to: " . $targetFile);
    
        $uploadOk = 1;
    
        # Information about the file, I think this may not be that necessary, it only stores the info in the
        # database, but it is never really used for anything
        //$fileInfo = new finfo(FILEINFO_MIME);
        # Check if file already exists, remane if it does
        //! @todo remane files if they already exist in the server
        if (file_exists($targetFile))
        {
            $uploadOk = 0;
        }
    
        # Check file size
        //! @todo depend on the file type attach|avatar|bgd|etc...
        if ($_FILES["fileToUpload"]["size"] > $maxSize * 1000000)
        {
            return ERROR_FILES_TOO_BIG;
        }
    
        # Check allowed extentions
        //! @todo depend on the file type attach|avatar|bgd|etc...
        if ($ext != "*")
        {
            grace_debug("Some extention restrictions apply");
            $ext = explode(",", $ext);
    
            # Get the information about the file
            $fInfo = pathinfo($targetFile);
    
            if (!in_array($fInfo['extension'], $ext))
                return ERROR_FILES_EXT_NOT_ALLOWED;
        }
    
        # Delete it just in case
        if ($del)
        {
            if (file_exists($targetFile))
                unlink($targetFile);
        }
    
        # Try to upload the file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile))
        {
            $downloadCode = files_createDownloadCode($finalName, $user->idUser);
            $idFile = files_Save(
                    array('md5'         => md5($_FILES["fileToUpload"]["tmp_name"]),
                        'name'          => $finalName,
                        'timestamp'     => time(),
                        'size'          => $_FILES["fileToUpload"]["size"],
                        'idUser'        => $user->idUser,
                        'downloadCode'  => $downloadCode,
                        'fileType'      => "",
                        'type'          => $type
            ));
    
            return array('idFile' => $idFile, 'name' => $finalName, 'downloadCode' => $downloadCode);
        }
        else
            return ERROR_FILES_UPLOAD_ERROR;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_save(dets):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = sprintf("INSERT INTO files (md5, name, timestamp, size, idUser, downloadCode, fileType, type)
            VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", db_escape($dets['md5']), db_escape($dets['name']), db_escape($dets['timestamp']), db_escape($dets['size']), db_escape($dets['idUser']), db_escape($dets['downloadCode']), db_escape($dets['fileType']), db_escape($dets['type'])
        );
    
        db_query($q, 0);
    
        # Lets find out which file it was
        $q      = sprintf("SELECT idFile FROM files WHERE downloadCode = '%s'", db_escape($dets['downloadCode']));
        $idFile = db_query($q, 1);
    
        return $idFile->idFile;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_load(idFile):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = sprintf("SELECT * FROM files WHERE idFile = '%s'", db_escape($idFile));
        $file = db_query($q, 1);
        if ($file != ERROR_DB_NO_RESULTS_FOUND)
        {
            $file->path = files_createPath($file->idUser, $file->type) . $file->name;
            return $file;
        }
    
        return false;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_presentFile(file, internal=True):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        if ($internal && !file_exists($file))
            $file = conf_get('resourcesPath', 'core', '') . "404FileNotFound.svg";
    
        $type = files_getMimeTypeFromExtention(basename($file));
        $thisFileName = time() . basename($file);
        header('Content-Type: ' . $type);
        header('Content-Disposition: filename=' . $thisFileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        if ($internal == false)
        {
            echo file_get_contents($file);
            exit;
        }
        else
        {
            ob_clean();
            flush();
            readfile($file);
        }
    
        exit;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_getMimeTypeFromExtention(file):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        # Internal non-complete list of mime types, but the ones we need at least
        $mimeTypes = array(
            "pdf"     => "application/pdf",
            "exe"     => "application/octet-stream",
            "zip"     => "application/zip",
            "docx"    => "application/msword",
            "doc"     => "application/msword",
            "xls"     => "application/vnd.ms-excel",
            "ppt"     => "application/vnd.ms-powerpoint",
            "gif"     => "image/gif",
            "png"     => "image/png",
            "jpeg"    => "image/jpg",
            "jpg"     => "image/jpg",
            "mp3"     => "audio/mpeg",
            "wav"     => "audio/x-wav",
            "mpeg"    => "video/mpeg",
            "mpg"     => "video/mpeg",
            "mpe"     => "video/mpeg",
            "mov"     => "video/quicktime",
            "avi"     => "video/x-msvideo",
            "3gp"     => "video/3gpp",
            "css"     => "text/css",
            "jsc"     => "application/javascript",
            "js"      => "application/javascript",
            "php"     => "text/html",
            "htm"     => "text/html",
            "html"    => "text/html",
            "svg"     => "image/svg+xml",
        );
    
        $extension = explode('.', $file);
        $extension = end($extension);
        $extension = strtolower($extension);
    
        if (array_key_exists($extension, $mimeTypes))
            return $mimeTypes[$extension];
        else
            return "application/octet-stream";
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_resizeImg(fileName, sizes=array()):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        # Base name without extention
        $fileParts = pathinfo($fileName);
        $baseName = $fileParts['filename'];
        # Create the new image
        $newImage = new \Eventviva\ImageResize($fileName);
    
        if (is_array($sizes))
        {
            foreach ($sizes as $size)
            {
                grace_debug("Creating a new version of the image: " . $size);
                $newImage->resizeToMax($size);
                $newImage->save(str_replace($baseName, $baseName . "_" . $size, $fileName));
            }
        }
        else
        {
            grace_debug("Resizing image and keeping the same name");
            $newImage->resizeToMax($sizes);
            $newImage->save($fileName);
        }
    
        return true;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_getPublicPath(idFile, size=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        # Get the details about the file
        $file = files_load($idFile);
    
        if ($file != false) 
            return "w=files&r=files_view_file&code=" . $file->downloadCode . "&size=" . $size;
        else
            return ERROR_FILES_DOWNLOAD_ERROR;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_viewPublic():
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $file = files_loadByCode(params_get("code", ""), params_get("size", 0));
        grace_debug("found file in path: " . $file->path);
        if ($file != ERROR_DB_NO_RESULTS_FOUND)
            files_presentFile($file->path);
    
        return ERROR_FILES_NOT_FOUND;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_loadByCode(code, size=False):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        $q = sprintf("SELECT * FROM files WHERE downloadCode = '%s'", db_escape($code));
        $file = db_query($q, 1);
        if ($file != ERROR_DB_NO_RESULTS_FOUND)
        {
            $file->path = files_createPath($file->idUser, $file->type) . $file->name;
            if ($size)
            {
                $file->path = files_renameImgWithSize($file->path, $size);
                /*
                  $fileParts  = pathinfo($file->path);
                  $baseName   = $fileParts['filename'];
                  $file->path = str_replace($baseName, $baseName . "_" . $size, $file->path);
                 */
            }
    
            return $file;
        }
    
        return false;
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')

def files_renameImgWithSize(fullName, size):
    """
    AUTO-PORTED PHP BODY (verbatim)
    --------------------------------
    
        # Get the parts of this file
        $fileParts = pathinfo($fullName);
        $baseName = $fileParts['filename'];
        return str_replace($baseName, $baseName . "_" . $size, $fullName);
    """
    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')
