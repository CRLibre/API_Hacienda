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

/** @file cabys.php
 * Módulo para manejar la información del Catálogo de Bienes y Servicios (CABYS).
 */

/**
 * Initialize module
 */
function cabys() {
    $paths = array(
        array(
            'r' => 'cabys_list',
            'action' => 'getCabysList',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "search", "def" => "", "req" => false),
                array("key" => "page", "def" => "1", "req" => false),
                array("key" => "limit", "def" => "50", "req" => false)
            ),
            'file' => 'cabys.php'
        ),
        array(
            'r' => 'cabys_detail',
            'action' => 'getCabysDetail',
            'access' => 'users_openAccess',
            'access_params' => 'accessName',
            'params' => array(
                array("key" => "codigo", "def" => "", "req" => true)
            ),
            'file' => 'cabys.php'
        )
    );

    return $paths;
}

/**
 * Get list of CABYS items with pagination and search
 */
function getCabysList($params = array()) {
    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }

        // Get parameters
        $search = isset($params['search']) ? $db->real_escape_string($params['search']) : '';
        $page = isset($params['page']) ? max(1, intval($params['page'])) : 1;
        $limit = isset($params['limit']) ? max(1, min(100, intval($params['limit']))) : 50;
        $offset = ($page - 1) * $limit;

        // Build query
        $where = "";
        if (!empty($search)) {
            $where = "WHERE cabys LIKE '%$search%' 
                     OR descripcion_cabys LIKE '%$search%'";
        }

        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM cabys $where";
        $countResult = $db->query($countQuery);
        $totalRows = $countResult->fetch_assoc()['total'];

        // Get data
        $query = "SELECT cabys, 
                         descripcion_cabys, 
                         impuesto,
                         nota_explicativa_incluye,
                         nota_explicativa_excluye
                  FROM cabys 
                  $where
                  ORDER BY cabys
                  LIMIT $offset, $limit";

        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener datos CABYS: ' . $db->error);
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        
        return array(
            'status' => 'success',
            'data' => $data,
            'pagination' => array(
                'total' => $totalRows,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalRows / $limit)
            )
        );
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}

/**
 * Get detailed information for a specific CABYS code
 */
function getCabysDetail($params = array()) {
    if (!isset($params['codigo'])) {
        return array('status' => 'error', 'message' => 'El código CABYS es requerido');
    }

    try {
        $db = db_connect();
        if (!$db) {
            return array('status' => 'error', 'message' => 'Error de conexión a la base de datos');
        }

        $codigo = $db->real_escape_string($params['codigo']);

        $query = "SELECT cabys,
                         descripcion_cabys,
                         impuesto,
                         nota_explicativa_incluye,
                         nota_explicativa_excluye
                  FROM cabys
                  WHERE cabys = '$codigo'
                  LIMIT 1";

        $result = $db->query($query);
        if (!$result) {
            return array('status' => 'error', 'message' => 'Error al obtener detalle CABYS: ' . $db->error);
        }

        $data = $result->fetch_assoc();
        if (!$data) {
            return array('status' => 'error', 'message' => 'Código CABYS no encontrado');
        }

        return array('status' => 'success', 'data' => $data);
    } catch (Exception $e) {
        return array('status' => 'error', 'message' => 'Error: ' . $e->getMessage());
    }
}
