<?php
/*
 * Copyright (C) 2017-2018 CRLibre <https://crlibre.org>
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

# Declare it as global, but never use it as global
global $config;

#####################################################################################
#
# Database
#
#####################################################################################
# Database name
$config['db']['name'] = "db";
# Database password
$config['db']['pwd'] = 'db';
# Database user name
$config['db']['user'] = "db";
# Database host
$config['db']['host'] = "db";
##############################################################################
#
# Crypto Keys
#
##############################################################################
$config['crypto']['key'] = "TqkIsC8PPPBHmlL3C7Gkc7RPp0t4\/Mb9RZ1wmTxASck=";
##############################################################################
#
# print alerts
# false or true
#
##############################################################################
$config['boot']['alert'] = "false";
##############################################################################
##
## Log errors
## false or true
##
###############################################################################
$config['debug']['print_all']    = false;
$config['debug']['print_absurd'] = false;
$config['debug']['print_debug']  = false;
$config['debug']['print_error']  = false;
##############################################################################
#
# Emails
#
##############################################################################
# Options: "mail" or "smtp".
$config['mail']['type']         = "mail";
# Used in "mail" or "smtp"
$config['mail']['address']      = "";       // for example: info@crlibre.org
$config['mail']['noreply']      = "";       // Optional - for example: no-reply@crlibre.org
# If "smtp" option is actived.
$config['mail']['host']         = "";
$config['mail']['username']     = "";
$config['mail']['password']     = "";
$config['mail']['secure']       = "tls";    // Use tls or ssl
$config['mail']['port']         = 587;

##############################################################################
#
# Core and Modules
#
##############################################################################

# Paths USE TRAILING SLASHES!!!
# By default I will assume that core modules and contrib are located in the
# same directory as the Api, but they can be placed anywhere else

# The core installation: This is probably the only one you need to touch
# IMPORTANTE: La ruta debe finalizar en "/".
# $config['modules']['coreInstall'] = "/var/www/api.posventa.cr/factura/api/";
$config['modules']['coreInstall'] = '/var/www/html/api/';

# Name of your site, Not in use really
$config['core']['siteName'] = 'targua';

# The host name for your site
$config['core']['host'] = "targua.posventa.cr";
$config['core']['compannyIMG'] = "https://scontent.fsyq2-1.fna.fbcdn.net/v/t1.0-9/33379063_741113822944661_9042709073681711104_n.png?_nc_cat=0&oh=9ac1b60c1f7ab20eb174b29d85a50470&oe=5C0C2666";

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
