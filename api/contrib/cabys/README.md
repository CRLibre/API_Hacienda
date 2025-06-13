# Módulo CABYS

Este módulo proporciona acceso al Catálogo de Bienes y Servicios (CABYS) de Costa Rica, permitiendo consultar información sobre productos y servicios, incluyendo sus códigos, descripciones, impuestos y notas explicativas.

## Instalación

1. Asegúrese de tener la base de datos configurada con la tabla `cabys` y sus respectivas columnas.
2. Copie los archivos del módulo en el directorio `api/contrib/cabys/`.
3. El módulo se registrará automáticamente en el sistema.

## Estructura de la Base de Datos

La tabla `cabys` debe contener las siguientes columnas:

```sql
CREATE TABLE cabys (
    cabys VARCHAR(20) PRIMARY KEY,
    descripcion_cabys TEXT NOT NULL,
    impuesto DECIMAL(5,2) NOT NULL,
    nota_explicativa_incluye TEXT,
    nota_explicativa_excluye TEXT
);
```

## Endpoints Disponibles

### 1. Lista de Productos/Servicios CABYS

Obtiene una lista paginada de productos y servicios del catálogo CABYS.

```
GET /api.php?r=cabys_list
```

#### Parámetros

| Parámetro | Tipo    | Requerido | Descripción                                    |
|-----------|---------|-----------|------------------------------------------------|
| search    | string  | No        | Término de búsqueda para filtrar resultados    |
| page      | integer | No        | Número de página (por defecto: 1)              |
| limit     | integer | No        | Cantidad de items por página (por defecto: 50) |

#### Ejemplo de Respuesta

```json
{
    "status": "success",
    "data": [
        {
            "cabys": "1234567890",
            "descripcion_cabys": "Descripción del producto o servicio",
            "impuesto": 13.00,
            "nota_explicativa_incluye": "Incluye...",
            "nota_explicativa_excluye": "Excluye..."
        }
    ],
    "pagination": {
        "total": 100,
        "page": 1,
        "limit": 50,
        "total_pages": 2
    }
}
```

### 2. Detalle de Producto/Servicio CABYS

Obtiene información detallada de un producto o servicio específico por su código CABYS.

```
GET /api.php?r=cabys_detail
```

#### Parámetros

| Parámetro | Tipo   | Requerido | Descripción        |
|-----------|--------|-----------|-------------------|
| codigo    | string | Sí        | Código CABYS     |

#### Ejemplo de Respuesta

```json
{
    "status": "success",
    "data": {
        "cabys": "1234567890",
        "descripcion_cabys": "Descripción del producto o servicio",
        "impuesto": 13.00,
        "nota_explicativa_incluye": "Incluye...",
        "nota_explicativa_excluye": "Excluye..."
    }
}
```

## Códigos de Error

El módulo puede devolver los siguientes mensajes de error:

- `Error de conexión a la base de datos`: Problema al conectar con la base de datos
- `Error al obtener datos CABYS`: Error en la consulta de lista
- `Error al obtener detalle CABYS`: Error en la consulta de detalle
- `Código CABYS no encontrado`: El código especificado no existe
- `El código CABYS es requerido`: Falta el parámetro obligatorio

## Ejemplos de Uso

### Búsqueda de productos
```
GET /api.php?r=cabys_list&search=computadora
```

### Obtener segunda página con 20 items
```
GET /api.php?r=cabys_list&page=2&limit=20
```

### Consultar producto específico
```
GET /api.php?r=cabys_detail&codigo=1234567890
```

## Soporte

Para reportar problemas o sugerir mejoras, por favor crear un issue en el repositorio oficial de [CRLibre](https://github.com/CRLibre/API_Hacienda).

## Licencia

Este módulo es parte del proyecto API_Hacienda y está bajo la licencia GNU Affero General Public License v3.0. 