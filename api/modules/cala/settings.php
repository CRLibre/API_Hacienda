<?php

global $config;

/**
 * This are all located in settings_default.php, they are placed here for documentation
 * and centralization, you should not need to enable them here
 *

 #####################################################################################
 #
 # Database
 #
 #####################################################################################
 # Database name
 $config['db']['name'] = "cala_base";
# Database password
$config['db']['pwd'] = "123";
# Database user name
$config['db']['user'] = "root";
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
$config['modules']['coreInstall'] = "/path/to/the/api/";

# Name of your site, Not in use really
$config['core']['siteName'] = 'My Site';

# The host name for your site
$config['core']['host'] = "example.net";

# Time in seconds for the lifetime of a session, after this time, the user must
# log back in
$config['users']['sessionLifetime'] = 1000;


# Core modules location
$config['modules']['corePath']    = $config['modules']['coreInstall'] . "modules/";
# Contributed modules
$config['modules']['contribPath'] = $config['modules']['coreInstall'] . "/contrib/";

# Where the internal resources are located, like 404 and default avatars and
# so forth. Use trailing slash!
# @todo This could be indicated by the template, if nothing found i would just
# return the indicated path back
$config['core']['resourcesPath'] = $config['modules']['coreInstall'] . '/resources/';
*/

# A private token in order to run cron, but I don't think this will be needed
# Not in use yet, you may ignore this
$config['cron']['cronToken'] = 'ItIsGoodIfThisIsBigAndHasW3irDLeeT3rsAnd$ymb0lz.IniT';

# List of core modules
$config['modules']['core']     = array('cala', 'db', 'users', 'files', 'geoloc', 'wirez','crypto');
# List of core modules to load always, you can overide this list
$config['modules']['coreLoad'] = array('cala', 'db', 'users','crypto');

# Am I running in CLI mode?
$config['core']['cli'] = false;

#####################################################################################
#
# Files, Uploads and such
#
#####################################################################################

# Location to upload files, USE TRAILING SLASH!!
# Each user will have its own directory within this path
#$config['files']['basePath']      = $config['modules']['coreInstall'] . 'files/';
# Maximum upload size in Mb
$config['files']['maxUploadSize'] = 2;
# Default allowed extensions
$config['files']['allowedExt'] = 'jpg,png';

