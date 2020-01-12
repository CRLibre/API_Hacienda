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
 * GeoLoc module for Cala.
 * Using tables from MaxMind I can retrieve information about a location.
 * @todo Import the parsed databases from somewhere, like bitbucket
 * @todo Let people request information about a location via web
 * @bug testing bug, this is a bug it self :)
 * http://dev.maxmind.com/geoip/geoip2/geolite2/
 */

/** \addtogroup GlobalVars
 *  @{
 */

global $geoloc_dbs;

/** @}*/

/** \addtogroup Contrib
 *  @{
 */

/**
 * \defgroup GeoLoc
 * @{
 */


/**
 * Boot up procedure
 */
function geoloc_bootMeUp()
{
    global $geoloc_dbs;

    $dbsPath = conf_get('dbLocation', 'geoloc', dirname(__FILE__)) . "/";
    grace_debug("Opening databases in " . $dbsPath);

    $geoloc_dbs['dbB'] = new SQLite3($dbsPath . 'blocks.sqlite');
    $geoloc_dbs['dbL'] = new SQLite3($dbsPath . 'locations.sqlite');

}

/**
 * Init function
 */
function geoloc_init()
{
    $paths = array(
        array(
            'r'         => 'geoloc_get_by_ip',
            'action'    => 'geoloc_locationGetByIp',
            'access'    => 'users_openAccess',
            'params'    => array(array("key" => "", "def" => "", "req" => true))
        ),

        array(
            'r'             => 'geoloc_create_tables',
            'action'        => 'geoloc_createTables',
            'access'        => 'users_access',
            'access_params' => 'createGeoLocTables'
        ),

        array(
            'r'             => 'geoloc_load_blocks',
            'action'        => 'geoloc_loadBlocks',
            'access'        => 'users_access',
            'access_params' => 'createGeoLocTables'
        ),

        array(
            'r'             => 'geoloc_load_locations',
            'action'        => 'geoloc_loadLocations',
            'access'        => 'users_access',
            'access_params' => 'createGeoLocTables'
        ),
    );

    return $paths;

}

/**
 * Get the perms for this module
 */
function geoloc_access()
{
    $perms = array(
        array(
            # A human readable name
            'name'        => 'Create GeoLoc Tables',
            # Something to remember what it is for
            'description' => 'Users can create/populate the GeoLoc tables, this is a very heavy task!',
            # Internal machine name, no spaces, no funny symbols, same rules as a variable
            # Use yourmodule_ prefix
            'code'        => 'geoloc_createTables',
            # Default value in case it is not set
            'def'         => false,
        ),
        array(
            'name'        => 'Get GeoLoc Coordinates',
            'description' => 'Users can request information about a location based on the ip',
            'code'        => 'geoloc_getCoordinates',
            'def'         => 'true',
        )
    );

}

/**
 * Create the tables, NEVER call this from web, it takes a while
 */
function geoloc_createTables()
{
    global $geoloc_dbs;

    grace_debug("Creating tables");

    $geoloc_dbs['dbB']->exec('DROP TABLE IF EXISTS "blocks";');
    $geoloc_dbs['dbB']->exec('CREATE TABLE "blocks" ("startIpNum" INTEGER NOT NULL , "endIpNum" INTEGER NOT NULL , "locId" INTEGER);');
    $geoloc_dbs['dbL']->exec('DROP TABLE IF EXISTS "locations";');
    $geoloc_dbs['dbL']->exec('CREATE TABLE "locations" ("locId" INTEGER PRIMARY KEY  NOT NULL , "country" VARCHAR, "region" VARCHAR, "city" VARCHAR, "postalCode" VARCHAR, "latitude" VARCHAR, "longitude" VARCHAR, "metroCode" VARCHAR, "areaCode" VARCHAR)');

    return "Tables created!";

}

/**
 * Load the locations in the database
 * NEVER call this from web, it takes a while
 */
function geoloc_loadLocations()
{
    global $geoloc_dbs;

    grace_debug("Loading locations");

    $citiesPath = conf_get('dbLocation', 'geoloc', dirname(__FILE__)) . "/GeoLiteCity-Location.csv";

    grace_debug("Looking for locations tables in: " . $citiesPath);

    if (!file_exists($citiesPath))
        return 'Missing file: ' . $blocksPath;

    $fileCities = file($citiesPath);

    # Remove the first 2 elements
    //array_shift($fileCities);
    //array_shift($fileCities);

    $c = 0;
    $cc = 0;

    foreach ($fileCities as $f)
    {
        # Skip first two
        if ($cc > 1)
        {
            # Fix missing values
            $ff = explode(",", $f);
            for ($a = 0; $a < 9; $a++)
            {
                if (!isset($ff[$a]) || $ff[$a] == NULL || $ff[$a] == "\n")
                    $ff[$a] = 0;
            }

            $f = implode(",", $ff);

            if ($c == 0)
            {
                $q = "insert into locations (locId,country,region,city,postalCode,latitude,longitude,metroCode,areaCode) VALUES ";
                $q2 = array();
            }

            //grace_absurd("+");
            $q2[] = "(" . trim($f, "\n") . ")";

            $c++;
            if ($c == 500)
            {
                $query = $q . implode(",", $q2) . ";";
                //grace_absurd("\n.-");
                $geoloc_dbs['dbL']->exec($query);
                $c = 0;
                grace_absurd($cc);
            }
        }
        $cc++;
    }

}

/**
 * Loads the blocks in the database
 * NEVER call this from web, it takes a while
 */
function geoloc_loadBlocks()
{
    global $geoloc_dbs;

    $blocksPath = conf_get('dbLocation', 'geoloc', dirname(__FILE__)) . "/GeoLiteCity-Blocks.csv";

    grace_debug("Looking for blocks tables in: " . $blocksPath);

    if (!file_exists($blocksPath))
        return 'Missing file GeoLiteCity-Blocks.csv' . $blocksPath;

    $fileCities = file($blocksPath);

    # Remove the first 2 elements
    //array_shift($fileCities);
    //array_shift($fileCities);

    $c = 0;
    $cc = 0;

    foreach ($fileCities as $f)
    {
        if ($c == 0)
        {
            $q = "insert into blocks (startIpNum, endIpNum, locId) VALUES ";
            $q2 = array();
        }

        //grace_absurd("+");
        $q2[] = "(" . trim($f, "\n") . ")";

        $c++;
        $cc++;
        if ($c == 500)
        {
            $query = $q . implode(",", $q2) . ";";
            //grace_absurd(".-");
            $geoloc_dbs['dbB']->exec($query);
            $c = 0;
            grace_absurd($cc);
        }

    }

    return "Blocks loaded!";

}

/**
 * If you need to test how it would look with visitors from all over the world,
 * you may use this function, it works just like geoloc_getMeVisitorDetails(), but
 * it creates a random visitor.
 */
function _geoloc_getMeVisitorDetails($ipAddress = "")
{
    global $geoloc_dbs;

    # Select a random block, not all of them or this function would take for ever!
    $locId = rand(1780, 3000);

    // Select the block in order to have a valid ip address
    $q = sprintf("
        SELECT *
        FROM `blocks` b
        WHERE locId = %s
        ", $locId);

    $stmt = $geoloc_dbs['dbB']->prepare($q);

    $result = $stmt->execute();
    $blocks = $result->fetchArray();

    if ($blocks)
    {
        return geoloc_getMeVisitorDetails(long2ip($blocks['startIpNum']));
        /*
        $q = sprintf("
            SELECT *
            FROM `locations`
            WHERE locId = %s
            ", $blocks['locId']);

        $stmt = $geoloc_dbs['dbL']->prepare($q);
        $result = $stmt->execute();
        $details = $result->fetchArray();
        // Add the random address
         */
    }

    # This should never happen
    return false;

}

/**
 * I will tell you the localization based on an ip
 */
function geoloc_getMeVisitorDetails($ipAddress = "")
{
    global $geoloc_dbs;

    # I will use the address of the current visitor if nothing is provided
    if ($ipAddress == "")
        $ipAddress = $_SERVER['REMOTE_ADDR'];

    # If you are running localhost
    if ($ipAddress == '127.0.0.1' || $ipAddress == '0.0.0.0')
    {
        grace_debug("From localhost, I will get a random Ip");
        return _geoloc_getMeVisitorDetails();
    }

    grace_debug("Getting details for IP: " . $ipAddress);

    $ipParts = explode(".", $ipAddress);

    $integerIp = (16777216 * $ipParts[0])
        + (    65536 * $ipParts[1])
        + (      256 * $ipParts[2])
        +              $ipParts[3];

    # Get the block

    # Add all the details in blank
    # @todo add them all in blank
    $details = array();

    // From  http://dev.maxmind.com/geoip/legacy/geolite/
    $q = sprintf("
        SELECT b.locId
        FROM `blocks` b
        WHERE startIpNum <= %s 
        AND endIpNum >= %s
        ", $integerIp, $integerIp);

    $stmt = $geoloc_dbs['dbB']->prepare($q);

    $result = $stmt->execute();
    $blocks = $result->fetchArray();

    if ($blocks)
    {
        $q = sprintf("
            SELECT *
            FROM `locations`
            WHERE locId = %s
            ", $blocks['locId']);

        $stmt = $geoloc_dbs['dbL']->prepare($q);
        $result = $stmt->execute();
        $details = $result->fetchArray();
        $details['ipAddress'] = $ipAddress;
    }

    return $details;
}


/**@}*/
/** @}*/
