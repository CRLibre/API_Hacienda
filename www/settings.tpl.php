<?php

# Declare it as global, but never use it as global
global $config;

#####################################################################################
#
# Database
#
#####################################################################################
# Database name
$config['db']['name'] = "{dbName}";
# Database password
$config['db']['pwd'] = "{dbPss}";
# Database user name
$config['db']['user'] = "{dbUser}";
# Database host
$config['db']['host'] = "{dbHost}";
##############################################################################
#
# Crypto Keys
#
##############################################################################
$config['crypto']['key'] = "{cryptoKey}";


##############################################################################
#
# Core and Modules
#
##############################################################################
# Paths USE TRAILING SLASHES!!!
# By default I will assume that core modules and contrib are located in the
# same directory as the Api, but they can be placed anywhere else

# The core installation: This is probably the only one you need to touch
$config['modules']['coreInstall'] = "{apiPath}";

# Name of your site, Not in use really
$config['core']['siteName'] = 'MySite';

# The host name for your site
$config['core']['host'] = "mySite.com";

# Time in seconds for the lifetime of a session, after this time, the user must
# log back in
# In case you need it, -1 allows user to stay logged in
$config['users']['sessionLifetime'] = -1;

# If you want to allow CORS requests
$config['core']['cors'] = true;

##############################################################################
#
# Grace
#
#####################################################################################
# Where do you want me to store the logs? USE TRAILING SLASH!
#Log path
$config['grace']['logPath'] = "../api/errors/";
#If save errors
$config['grace']['errors'] = false;
#If display errors
$config['grace']['display'] = false;

/*******************************************************************************
 * You should not need to touch anything beyond this point
 */
 
# List of core modules
$config['modules']['core'] = array('cala','db', 'users', 'files', 'geoloc', 'wirez', 'crypto');
# List of core modules to load always, you can overide this list
$config['modules']['coreLoad'] = array('cala', 'db', 'users', 'crypto');

# Core modules location
$config['modules']['corePath'] = $config['modules']['coreInstall'] . "modules/";
# Contributed modules
$config['modules']['contribPath'] = $config['modules']['coreInstall'] . "contrib/";
# Resources such as 404 not found images and such
$config['core']['resourcesPath'] = $config['modules']['coreInstall'] . 'resources/';

# Location to upload files, USE TRAILING SLASH!!
# Each user will have its own directory within this path
$config['files']['basePath'] = $config['modules']['coreInstall'] . 'files/';