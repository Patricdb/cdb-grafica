# CDB Gráfica

Plugin de WordPress para registrar valoraciones de bares y empleados mediante formularios y mostrar los resultados con bloques de Gutenberg.

## Instalación

1. Copia la carpeta del plugin en `wp-content/plugins`.
2. Activa **CDB Gráfica** desde el panel de administración de WordPress.

Al activarlo se crearán automáticamente las tablas personalizadas donde se almacenan las puntuaciones.

## Uso

- Utiliza los shortcodes `[grafica_bar_form]` y `[grafica_empleado_form]` para mostrar los formularios de valoración.
- Inserta los bloques "Gráfica Bar" o "Gráfica Empleado" en el editor para visualizar los promedios.

Si se deja un criterio sin valorar, la puntuación guardada es 0 y no se tendrá en cuenta para el promedio final.

## Configurar Colores

Dentro del menú **CdB Gráfica** se encuentra el submenú **Configurar Colores**. Desde esta página puedes modificar los colores utilizados en las gráficas:

- **Bar – Color de fondo** y **Bar – Color de borde** definen el aspecto del dataset de bares.
- **Empleado – Color de fondo** y **Empleado – Color de borde** controlan el dataset de empleados.

Cada selector cuenta con un deslizador *Alpha* que permite ajustar la transparencia del color. Los valores se guardan en formato `rgba`, por lo que puedes elegir tonalidades semitransparentes que se aplicarán directamente en las gráficas.

Al guardar los cambios, los nuevos valores se aplican automáticamente a las gráficas del sitio. El color de borde también se utiliza para los *ticks* de la escala, por lo que puedes ajustar su tonalidad desde esta misma pantalla.

## Desinstalación

Al desinstalar el plugin se eliminan las tablas creadas para almacenar los resultados.

## API Pública

El plugin expone dos helpers para que otros plugins (p.ej. `cdb-form`) puedan obtener
información de las valoraciones de empleados sin replicar la lógica de las gráficas.

### Obtener fecha de última valoración

```php
string|null cdb_grafica_get_last_rating_datetime( int $empleado_id )
```

Devuelve la fecha/hora (`Y-m-d H:i:s`) de la última valoración registrada para el
empleado o `null` si no existen datos.

### Obtener puntuaciones por rol

```php
array cdb_grafica_get_scores_by_role( int $empleado_id, array $args = [] )
```

Retorna los totales de cada rol (`empleado`, `empleador`, `tutor`) con un decimal.
Si se pasa `['with_detail' => true]` incluye el desglose por grupos.

```php
[
  'empleado'  => 33.1,
  'empleador' => 28.7,
  'tutor'     => null,
  'detalle'   => [
     'empleado' => ['grupos' => ['DIE' => 7.5, 'SAL' => 6.2], 'total' => 33.1],
     'empleador'=> [...]
  ]
]
```

### Transients y filtros

- `cdb_grafica_last_rating_{ID}` guarda la fecha de última valoración.
- `cdb_grafica_role_scores_{ID}` almacena los totales por rol.
- Filtros disponibles: `cdb_grafica_last_rating_args`,
  `cdb_grafica_scores_args`, `cdb_grafica_scores_ttl`.

Tras guardar una valoración se ejecuta el hook `cdb_grafica_after_save` y se
invalidan los transients anteriores.
