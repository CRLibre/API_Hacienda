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

global $module;

$module['cabys'] = array(
    'name' => 'CABYS',
    'description' => 'Módulo para el manejo del Catálogo de Bienes y Servicios (CABYS)',
    'package' => 'CRLibre\API\Modules\CABYS',
    'version' => '1.0.0',
    'author' => 'CRLibre.org',
    'routes' => array(
        'cabys_list' => array(
            'name' => 'Lista de productos/servicios CABYS',
            'description' => 'Obtiene una lista paginada de productos y servicios del catálogo CABYS',
            'auth' => false
        ),
        'cabys_detail' => array(
            'name' => 'Detalle de producto/servicio CABYS',
            'description' => 'Obtiene el detalle de un producto o servicio específico del catálogo CABYS',
            'auth' => false
        )
    ),
    'install' => array(
        'SQL' => array(
            'file' => 'sql/install.sql'
        )
    )
);
