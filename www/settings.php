<?php

# Declare it as global, but never use it as global
global $config;

#####################################################################################
#
# Database
#
#####################################################################################
# Database name
$config['db']['name'] = "crlibre_dbuser";
# Database password
//$config['db']['pwd'] = "g[H6gmrn6pb1";
$config['db']['pwd'] = 'S3syyZNw7woqFrm';
# Database user name
$config['db']['user'] = "crlibre_api_user";
# Database host
$config['db']['host'] = "localhost";

##############################################################################
#
# Core and Modules
#
##############################################################################

# Paths USE TRAILING SLASHES!!!
# By default I will assume that core modules and contrib are located in the
# same directory as the Api, but they can be placed anywhere else

# The core installation: This is probably the only one you need to touch
$config['modules']['coreInstall'] = "../api/";

# Name of your site, Not in use really
$config['core']['siteName'] = 'MySite';

# The host name for your site
$config['core']['host'] = "mySite.com";

# Time in seconds for the lifetime of a session, after this time, the user must
# log back in
$config['users']['sessionLifetime'] = 1000;

/*******************************************************************************
 * You should not need to touch anything beyond this point
 */
 


# List of core modules
$config['modules']['core']     = array('cala', 'db', 'users', 'files', 'geoloc', 'wirez');
# List of core modules to load always, you can overide this list
$config['modules']['coreLoad'] = array('cala', 'db', 'users');

# Core modules location
$config['modules']['corePath']    = $config['modules']['coreInstall'] . "modules/";
# Contributed modules
$config['modules']['contribPath'] = $config['modules']['coreInstall'] . "contrib/";
# Resources such as 404 not found images and such
$config['core']['resourcesPath'] = $config['modules']['coreInstall'] . 'resources/';

# Location to upload files, USE TRAILING SLASH!!
# Each user will have its own directory within this path
$config['files']['basePath'] = $config['modules']['coreInstall'] . 'files/';

