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
- En la sección **Gráfica Empleado** puedes ajustar:
  - **Valores aportados por los Empleados – Color de fondo**
  - **Valores aportados por los Empleados – Color de borde**
  - **Valores aportados por los Empleadores – Color de fondo**
  - **Valores aportados por los Empleadores – Color de borde**
  - **Valores aportados por los Tutores – Color de fondo**
  - **Valores aportados por los Tutores – Color de borde**

Cada selector cuenta con un deslizador *Alpha* que permite ajustar la transparencia del color. Los valores se guardan en formato `rgba`, por lo que puedes elegir tonalidades semitransparentes que se aplicarán directamente en las gráficas.

Al guardar los cambios, los nuevos valores se aplican automáticamente a las gráficas del sitio. El color de borde también se utiliza para los *ticks* de la escala, por lo que puedes ajustar su tonalidad desde esta misma pantalla.

## Desinstalación

Al desinstalar el plugin se eliminan las tablas creadas para almacenar los resultados.
