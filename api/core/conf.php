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

/**
 * @page This page is for...
 */

/**
 * Get config parameters
 */
function conf_get($what, $whom, $def = false)
{
    global $config;

    if (isset($config[$whom][$what]))
        return $config[$whom][$what];
    else
        return $def;
}

/**
 * Set config parameters
 */
function conf_set($what, $whom, $val = false, $override = false)
{
    global $config;

    if (isset($config[$whom][$what]) && $override)
        $config[$whom][$what] = $val;
    else
        $config[$whom][$what] = $val;

    return $config[$whom][$what];
}

function conf_getAll()
{
    global $config;
    return $config;
}
