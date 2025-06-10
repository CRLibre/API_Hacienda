# Módulo de Ubicaciones

Este módulo proporciona acceso a los datos geográficos de Costa Rica, incluyendo provincias, cantones, distritos y barrios desde la tabla `codificacion_mh`.

## Instalación

No requiere instalación adicional, solo asegúrese de que la tabla `codificacion_mh` exista en su base de datos.

## Endpoints Disponibles

### 1. Obtener Provincias
```
GET /api/contrib/ubicacion/?w=provincias
```
Retorna la lista de todas las provincias.

### 2. Obtener Cantones
```
GET /api/contrib/ubicacion/?w=cantones&provincia={id_provincia}
```
Retorna los cantones de una provincia específica.

**Parámetros requeridos:**
- provincia: ID de la provincia

### 3. Obtener Distritos
```
GET /api/contrib/ubicacion/?w=distritos&provincia={id_provincia}&canton={id_canton}
```
Retorna los distritos de un cantón específico.

**Parámetros requeridos:**
- provincia: ID de la provincia
- canton: ID del cantón

### 4. Obtener Barrios
```
GET /api/contrib/ubicacion/?w=barrios&provincia={id_provincia}&canton={id_canton}&distrito={id_distrito}
```
Retorna los barrios de un distrito específico.

**Parámetros requeridos:**
- provincia: ID de la provincia
- canton: ID del cantón
- distrito: ID del distrito

### 5. Obtener Ubicación Completa
```
GET /api/contrib/ubicacion/?w=ubicacion&provincia={id_provincia}&canton={id_canton}&distrito={id_distrito}&barrio={id_barrio}
```
Retorna información completa de una ubicación específica.

**Parámetros:**
- provincia: ID de la provincia (requerido)
- canton: ID del cantón (opcional)
- distrito: ID del distrito (opcional)
- barrio: ID del barrio (opcional)

## Formato de Respuesta

Todas las respuestas siguen este formato:

### Respuesta Exitosa
```json
{
    "status": "success",
    "data": [
        {
            "id": "1",
            "nombre": "Ejemplo"
            // ... otros campos según el endpoint
        }
    ]
}
```

### Respuesta de Error
```json
{
    "status": "error",
    "message": "Descripción del error"
}
```

## Ejemplos de Uso

### Obtener todas las provincias
```
GET /api/contrib/ubicacion/?w=provincias
```

### Obtener cantones de una provincia
```
GET /api/contrib/ubicacion/?w=cantones&provincia=1
```

### Obtener información completa de una ubicación
```
GET /api/contrib/ubicacion/?w=ubicacion&provincia=1&canton=1&distrito=1&barrio=1
```

## Soporte

Para reportar problemas o solicitar nuevas funcionalidades, por favor crear un issue en el repositorio principal de CRLibre. 