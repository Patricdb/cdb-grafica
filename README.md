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

## Desinstalación

Al desinstalar el plugin se eliminan las tablas creadas para almacenar los resultados.
