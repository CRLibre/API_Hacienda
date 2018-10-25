<?php

/**
 * Boot up procedure
 */
function version_bootMeUp()
{
    // Just booting up
}

/**
 * Init function
 */
function version_init()
{
    $paths = array(
        array(
            'r'             => 'version',
            'action'        => 'version_API',
            'access'        => 'users_openAccess',
            'file'          => 'version.php'
        )
    );

    return $paths;
}

/**@}*/
/** @}*/
