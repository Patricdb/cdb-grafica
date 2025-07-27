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

Junto a cada selector de color hay un campo *Alpha* para introducir un valor entre `0` y `1`. Este número controla la transparencia aplicada al color. El plugin combina ambos valores internamente para generar el código `rgba` necesario en las gráficas.

Al guardar los cambios, los nuevos valores se aplican automáticamente a las gráficas del sitio. El color de borde también se utiliza para los *ticks* de la escala, por lo que puedes ajustar su tonalidad desde esta misma pantalla.

## Desinstalación

Al desinstalar el plugin se eliminan las tablas creadas para almacenar los resultados.
