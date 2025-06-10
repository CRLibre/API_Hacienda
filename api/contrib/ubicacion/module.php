<?php
/*
 * Copyright (C) 2017-2024 CRLibre <https://crlibre.org>
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
 * Módulo de ubicaciones.
 * Provee acceso a los datos de provincias, cantones, distritos y barrios de Costa Rica.
 */

/** \addtogroup Core 
 *  @{
 */

/**
 * \defgroup Ubicacion
 * @{
 */

/**
 * Boot up procedure
 */
function ubicacion_bootMeUp() {
    // Incluir el archivo principal del módulo
    include_once("ubicacion.php");
}

/**
 * Initialize module
 */
function ubicacion_init() {
    $paths = array(
        array(
            'r' => 'provincias',
            'action' => 'getProvincias',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'file' => 'ubicacion.php'
        ),
        array(
            'r' => 'cantones',
            'action' => 'getCantones',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "provincia", "def" => "", "req" => true)
            ),
            'file' => 'ubicacion.php'
        ),
        array(
            'r' => 'distritos',
            'action' => 'getDistritos',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "provincia", "def" => "", "req" => true),
                array("key" => "canton", "def" => "", "req" => true)
            ),
            'file' => 'ubicacion.php'
        ),
        array(
            'r' => 'barrios',
            'action' => 'getBarrios',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "provincia", "def" => "", "req" => true),
                array("key" => "canton", "def" => "", "req" => true),
                array("key" => "distrito", "def" => "", "req" => true)
            ),
            'file' => 'ubicacion.php'
        ),
        array(
            'r' => 'ubicacion',
            'action' => 'getUbicacionCompleta',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "provincia", "def" => "", "req" => true),
                array("key" => "canton", "def" => "", "req" => false),
                array("key" => "distrito", "def" => "", "req" => false),
                array("key" => "barrio", "def" => "", "req" => false)
            ),
            'file' => 'ubicacion.php'
        )
    );

    return $paths;
}

/**
 * Get the permissions for this module
 */
function ubicacion_access() {
    $perms = array(
        array(
            'name' => 'Acceder a datos de ubicación',
            'description' => 'Permite consultar provincias, cantones, distritos y barrios',
            'code' => 'ubicacion_access',
            'def' => true,
        ),
    );

    return $perms;
}

/**@}*/
/** @}*/
