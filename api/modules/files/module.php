<?php
/*
 * Copyright (C) 2017-2020 CRLibre <https://crlibre.org>
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

/** @file module.php
 * Main Files module, this is where it all begins...
 * Not much more to say
 * @todo this may become a library, not a module
 */
#####################################################################
#
# Files (Upload and such)
#
#####################################################################

/** \addtogroup Modules
 *  @{
 */
/**
 * \defgroup Files
 * @{
 */
//! File size is too big
define('ERROR_FILES_TOO_BIG', '-400');

//! File extention not allowed
define('ERROR_FILES_NOT_ALLOWED', '-401');

//! Some error while uploading
define('ERROR_FILES_UPLOAD_ERROR', '-402');

//! Some error while downloading
define('ERROR_FILES_DOWNLOAD_ERROR', '-403');

//! File type not allowed
define('ERROR_FILES_EXT_NOT_ALLOWED', '-404');

//! The requested file was not found
define('ERROR_FILES_NOT_FOUND', '-405');

/**
 * Boot up procedure
 */
function files_bootMeUp()
{
    //
}

/**
 * Init function
 */
function files_init()
{
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
}

/**
 * @brief Creates a file path
 *
 * @param idUser The id of the user
 * @param type The type of file, this will define part of the path
 *
 * @return 
 * */
function filesGetUrl($codigo = '')
{
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

    $q = sprintf("SELECT * FROM files WHERE downloadCode = '%s'", $codigo);
    $file = db_query($q, 1);
    if ($file != ERROR_DB_NO_RESULTS_FOUND)
    {
        $filePath = files_createPath($file->idUser, $file->type) . $file->name;
        return $filePath;
    }

    return false;
}

function files_createPath($idUser, $type)
{
    return sprintf('%s%s/%s/', conf_get('basePath', 'files', '/'), $idUser, $type);
}

/**
 * @brief Creates a download link for each file
 *
 * @param name The name of the file, just the actual name is good
 * @param idUser The id of the user owner of the file
 *
 * @return 
 * */
function files_createDownloadCode($name, $idUser)
{
    return md5($name . "//" . time() . $idUser);
}

/**
 * @brief Upload files in the system and stores them in the db
 *
 * @param type The type of file to upload, this will define part of the path to be stored in
 * @param finalName The final name that you want to use for it
 * @param ext Allowed extentions to be accepted
 * @param maxSize The maximum file size in Mb to be accepted
 * @param del Should it be deleted if it already exists?
 *
 * @return 
 * */
function files_upload($type = 'attach', $finalName = false, $ext = false, $maxSize = 0, $del = true)
{
    global $user;

    # Load the tool
    tools_useTool('ImageResize.php');

    grace_debug("Uploading a file");

    # List of allowed files
    if ($ext == false)
    {
        grace_debug("Using default allowed extentions");
        $ext = conf_get("allowedExt", "files", "jpg,png,gif,P12,XML,Xml,p12,xml");
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
}

/**
 * @bried Saves a file in the db
 *
 * @param The dets of the file, an array with all of them
 */
function files_save($dets)
{
    $q = sprintf("INSERT INTO files (md5, name, timestamp, size, idUser, downloadCode, fileType, type)
        VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $dets['md5'], $dets['name'], $dets['timestamp'], $dets['size'], $dets['idUser'], $dets['downloadCode'], $dets['fileType'], $dets['type']
    );

    db_query($q, 0);

    # Lets find out which file it was
    $q      = sprintf("SELECT idFile FROM files WHERE downloadCode = '%s'", $dets['downloadCode']);
    $idFile = db_query($q, 1);

    return $idFile->idFile;
}

/**
 *  @brief Gets a file from the db
 *
 *  @param idFile The id of the file
 */
function files_load($idFile)
{
    $q = sprintf("SELECT * FROM files WHERE idFile = '%s'", $idFile);
    $file = db_query($q, 1);
    if ($file != ERROR_DB_NO_RESULTS_FOUND)
    {
        $file->path = files_createPath($file->idUser, $file->type) . $file->name;
        return $file;
    }

    return false;
}

/**
 * @brief Present private files to other people
 *
 * @param file The full path to the file
 * @param internal If this is an internal file
 *
 * @return 
 * */
function files_presentFile($file, $internal = true)
{
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
}

/**
 * @brief Gets the mime type according to the extension
 * Thanks to: http://www.thecave.info/php-get-mime-type-from-file-extension/
 *
 * @param file The file's name 
 *
 * @return The Mime Type for said file
 * */
function files_getMimeTypeFromExtention($file)
{
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
}

/**
 * Resizes images to specified dimensions, currently I work with 'max' dimension either height or width
 * I have to give it more options.
 * Each file will have a name exactly like the parent one, but with an appended 'v_size_'
 * before it, or at the end, I don't know.
 * @param filename The full path to the file
 * @param sizes An array with all the sizes that you want
 */
function files_resizeImg($fileName, $sizes = array())
{
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
}

/**
 * @brief Gets the public path for a file
 *
 * @param idFile The id of the file
 * @param size The size of the image you are looking for, applies for images only
 *
 * @return The public path for said file
 *
 * */
function files_getPublicPath($idFile, $size = false)
{
    # Get the details about the file
    $file = files_load($idFile);

    if ($file != false) 
        return "w=files&r=files_view_file&code=" . $file->downloadCode . "&size=" . $size;
    else
        return ERROR_FILES_DOWNLOAD_ERROR;
}

/**
 * @brief View files, given a code I will present them to everyone 
 *
 * @return 'Presents' a file in the browser, an image will be displayed, a zip will be prompted for dowload probably. Or an eror not found.
 * */
function files_viewPublic()
{
    $file = files_loadByCode(params_get("code", ""), params_get("size", 0));
    grace_debug("found file in path: " . $file->path);
    if ($file != ERROR_DB_NO_RESULTS_FOUND)
        files_presentFile($file->path);

    return ERROR_FILES_NOT_FOUND;
}

/**
 * @brief Loads a file by its code and size
 *
 * @param code The unique download code of the file 
 * @param size The size of the file, only valid for images
 *
 * @return The information about the file including its path.
 * */
function files_loadByCode($code, $size = false)
{
    $q = sprintf("SELECT * FROM files WHERE downloadCode = '%s'", $code);
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
}

/**
 * @brief Renames an image with its size included
 *
 * @param fullName The fullname of the image, may include the path
 * @param size The size like 50, 100, or 250, all in px
 *
 * @return 
 * */
function files_renameImgWithSize($fullName, $size)
{
    # Get the parts of this file
    $fileParts = pathinfo($fullName);
    $baseName = $fileParts['filename'];
    return str_replace($baseName, $baseName . "_" . $size, $fullName);
}

/**@}*/
/** @}*/
