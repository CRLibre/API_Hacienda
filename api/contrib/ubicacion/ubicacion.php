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

/** @file ubicacion.php
 * Módulo para manejar la información de ubicaciones geográficas.
 * Provee acceso a los datos de provincias, cantones, distritos y barrios.
 */

/**
 * Boot up procedure
 */
function ubicacion_bootMeUp() {
    // Just booting up
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
 * Get all provinces
 */
function getProvincias() {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }
        
        $query = "SELECT DISTINCT idProvincia, nombreProvincia 
                  FROM codificacion_mh 
                  ORDER BY idProvincia";
        
        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener provincias: ' . $db->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($data)) {
            return array('status' => 'error', 'message' => 'No se encontraron provincias en la base de datos');
        }
        
        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}

/**
 * Get cantones by provincia
 */
function getCantones($params) {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }
        
        $idProvincia = $db->real_escape_string($params['provincia']);
        
        $query = "SELECT DISTINCT idCanton, nombreCanton 
                  FROM codificacion_mh 
                  WHERE idProvincia = '$idProvincia' 
                  ORDER BY idCanton";
        
        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener cantones: ' . $db->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($data)) {
            return array('status' => 'error', 'message' => 'No se encontraron cantones para la provincia especificada');
        }
        
        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}

/**
 * Get distritos by canton
 */
function getDistritos($params) {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }
        
        $idProvincia = $db->real_escape_string($params['provincia']);
        $idCanton = $db->real_escape_string($params['canton']);
        
        $query = "SELECT DISTINCT idDistrito, nombreDistrito 
                  FROM codificacion_mh 
                  WHERE idProvincia = '$idProvincia' 
                  AND idCanton = '$idCanton' 
                  ORDER BY idDistrito";
        
        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener distritos: ' . $db->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($data)) {
            return array('status' => 'error', 'message' => 'No se encontraron distritos para el cantón especificado');
        }
        
        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}

/**
 * Get barrios by distrito
 */
function getBarrios($params) {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }
        
        $idProvincia = $db->real_escape_string($params['provincia']);
        $idCanton = $db->real_escape_string($params['canton']);
        $idDistrito = $db->real_escape_string($params['distrito']);
        
        $query = "SELECT DISTINCT idBarrio, nombreBarrio 
                  FROM codificacion_mh 
                  WHERE idProvincia = '$idProvincia' 
                  AND idCanton = '$idCanton' 
                  AND idDistrito = '$idDistrito' 
                  ORDER BY idBarrio";
        
        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener barrios: ' . $db->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($data)) {
            return array('status' => 'error', 'message' => 'No se encontraron barrios para el distrito especificado');
        }
        
        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}

/**
 * Get complete location information
 */
function getUbicacionCompleta($params) {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }
        
        $idProvincia = $db->real_escape_string($params['provincia']);
        $where = "WHERE idProvincia = '$idProvincia'";
        
        if (isset($params['canton'])) {
            $idCanton = $db->real_escape_string($params['canton']);
            $where .= " AND idCanton = '$idCanton'";
        }
        if (isset($params['distrito'])) {
            $idDistrito = $db->real_escape_string($params['distrito']);
            $where .= " AND idDistrito = '$idDistrito'";
        }
        if (isset($params['barrio'])) {
            $idBarrio = $db->real_escape_string($params['barrio']);
            $where .= " AND idBarrio = '$idBarrio'";
        }
        
        $query = "SELECT * FROM codificacion_mh $where";
        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener ubicación: ' . $db->error);
        }
        
        $data = $result->fetch_all(MYSQLI_ASSOC);
        if (empty($data)) {
            return array('status' => 'error', 'message' => 'No se encontró la ubicación especificada');
        }
        
        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
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
