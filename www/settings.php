<?php

# Declare it as global, but never use it as global
global $config;

#####################################################################################
#
# Database
#
#####################################################################################
# Database name
$config['db']['name'] = "crlibreapi";
# Database password
//$config['db']['pwd'] = "g[H6gmrn6pb1";
$config['db']['pwd'] = '1PdeUpreble';
# Database user name
$config['db']['user'] = "root";
# Database host
$config['db']['host'] = "localhost";
##############################################################################
#
# Crypto Keys
#
##############################################################################
$config['crypto']['key'] = "LkWfgWGQ/XhSd+ML13PEJsuecTHUPs9quAWGs1fMC9o=";
##############################################################################
#
# print alerts
# false or true
#
##############################################################################
$config['boot']['alert'] = "false";

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
$config['core']['compannyIMG'] = "https://ci5.googleusercontent.com/proxy/_QIqHChNc1E3UNogRhfwdy7yVY-GlrLhchnwXq1g2VKnVK2OM6ATEQnE9GMgPPpMlyRJxaRHM0goO7JPzkvWvCtsA3xdQl6FZ3lRcvQO_Yt2jrbUcrwwg6PJLcFjpsBHPvT2RDFWdXDt_22yLbub-h5RYqg0eUWDTgZAeCINMBwfAEBtB0LS2ein7din1iPdZB7hoKLGR3h2LQueZQebU0Kd2oMeal-mGoTrSjMFWCIGmhw=s0-d-e1-ft#https://scontent.fsyq2-1.fna.fbcdn.net/v/t1.0-9/33379063_741113822944661_9042709073681711104_n.png?_nc_cat=0&oh=9ac1b60c1f7ab20eb174b29d85a50470&oe=5C0C2666";

# Time in seconds for the lifetime of a session, after this time, the user must
# log back in, if you dont want to use the session life set the value in -1, li this
#  $config['users']['sessionLifetime'] = -1;

$config['users']['sessionLifetime'] = -1;

/*******************************************************************************
 * You should not need to touch anything beyond this point
 */
 


# List of core modules
$config['modules']['core']     = array('cala','db', 'users', 'files', 'geoloc', 'wirez', 'crypto');
# List of core modules to load always, you can overide this list
$config['modules']['coreLoad'] = array('cala', 'db', 'users', 'crypto');

# Core modules location
$config['modules']['corePath']    = $config['modules']['coreInstall'] . "modules/";
# Contributed modules
$config['modules']['contribPath'] = $config['modules']['coreInstall'] . "contrib/";
# Resources such as 404 not found images and such
$config['core']['resourcesPath'] = $config['modules']['coreInstall'] . 'resources/';

# Location to upload files, USE TRAILING SLASH!!
# Each user will have its own directory within this path
$config['files']['basePath'] = $config['modules']['coreInstall'] . 'files/';

