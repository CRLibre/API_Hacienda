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

/** @ingroup Constants
 *  @{
 */

# Could not find restults in the db
define('ERROR_DB_NO_RESULTS_FOUND', '-200');

# Errors in the query
define('ERROR_DB_ERROR', '-201');

# No db connection
define('ERROR_DB_NOT_CONNECTED', '-202');


# What do you expect to get from the database?
# Retrieve nothing: updates, inserts and so on
define('DB_RETRIEVE_NONE', '201');
# Select where you expect only one result
define('DB_RETRIEVE_ONE',  '202');
# Select where you expect many results
define('DB_RETRIEVE_MANY', '203');

/** @}*/

/* Global Vars */
global $dbConn;

/**
 * Boot up procedure
 */
function db_bootMeUp()
{
    db_Connect();
}

//! Test if the connection is good, just for debug
function db_allGood()
{
    global $dbConn;

    if ($dbConn->connect_error)
    {
        grace_error("DB Connection error: " . $dbConn->connect_error);
        return $dbConn->connect_error;
    }

    return true;
}

function db_Connect()
{
    global $dbConn;

    # Create connection
    grace_debug("config['db']['name']: ");
    grace_debug(conf_get('name', 'db'));
    grace_debug("config['db']['pwd']: ");
    grace_debug(conf_get('pwd', 'db'));
    grace_debug("config['db']['user']: ");
    grace_debug(conf_get('user', 'db'));
    grace_debug("config['db']['host']: ");
    grace_debug(conf_get('host', 'db'));
    @$dbConn = new mysqli(conf_get('host', 'db'), conf_get('user', 'db'), conf_get('pwd', 'db'), conf_get('name', 'db'));

    # Check connection
    if ($dbConn->connect_error)
        grace_error("Connection failed: " . $dbConn->connect_error);
    else
    {
        $dbConn->set_charset("utf8mb4");
        grace_debug("Conneted to Db");
    }
}

/**
 * I make queries
 * @param $q The query to be excecuted
 * @param $return The number of return items you want 0: none, 1: Just one, >1: All that you have
 */
function db_query($q, $return = 1)
{
    global $dbConn;

    grace_debug($q);

    if(db_allGood() === true)
    {
        $r = $dbConn->query($q);
        $result = array();

        if ($dbConn->error)
        {
            $result = ERROR_DB_ERROR;
            grace_error($dbConn->error);
        }
        else
        {
            if ($dbConn->affected_rows > 0)
            {
                if ($return > 0)
                {
                    while ($row = $r->fetch_object())
                    {
                        $result[] = $row;
                    }

                    # If you just need one result
                    if ($return == 1)
                        $result = $result[0];
                }
                else
                    $result = $dbConn->affected_rows;
            }
            else
                $result = ERROR_DB_NO_RESULTS_FOUND;

            return $result;
        }
    }
    else
        return ERROR_DB_NOT_CONNECTED;
}
