<?php
// inc/grafica-empleado.php

// ------------------------------------------------------------------
// 1. Registro del bloque "grafica-empleado".
// ------------------------------------------------------------------
function registrar_bloque_grafica_empleado() {
    $asset_file = plugin_dir_path(dirname(__FILE__)) . 'build/empleado/index.asset.php';
    if (!file_exists($asset_file)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Archivo index.asset.php para empleado no encontrado.');
        }
        return;
    }
    $asset_data = include $asset_file;
    if (!isset($asset_data['dependencies']) || !is_array($asset_data['dependencies']) || !isset($asset_data['version'])) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('index.asset.php para empleado tiene una estructura inválida.');
        }
        return;
    }
    $script_path = plugin_dir_path(dirname(__FILE__)) . 'build/empleado/index.js';
    $script_url  = plugins_url('build/empleado/index.js', dirname(__FILE__));
    wp_register_script(
        'cdb-grafica-empleado',
        $script_url,
        $asset_data['dependencies'],
        filemtime($script_path),
        true
    );
    register_block_type('cdb/grafica-empleado', array(
        'editor_script'   => 'cdb-grafica-empleado',
        'render_callback' => 'renderizar_bloque_grafica_empleado',
    ));
}
add_action('init', 'registrar_bloque_grafica_empleado');

// ------------------------------------------------------------------
// 2. Renderizado del bloque: calcular la gráfica y guardar la puntuación total.
// ------------------------------------------------------------------
function renderizar_bloque_grafica_empleado($attributes, $content) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $post_id    = get_the_ID();

    // Recuperar todas las entradas para el post actual
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d", 
        $post_id
    ));

    // Inicializar agrupaciones para calcular promedios
    $grupos = [
        'DIE' => ['direccion'],
        'SAL' => ['camarero'],
        'TES' => ['venta'],
        'ATC' => ['satisfaccion'],
        'TEQ' => ['cooperacion'],
        'ORL' => ['orden'],
        'TEC' => ['cocina_local'],
        'COC' => ['cocinero']
    ];

    // Calcular promedios por grupo
    $promedios = [];
    foreach ($grupos as $grupo_nombre => $campos) {
        $total_grupo = 0;
        $count = 0;
        foreach ($results as $row) {
            foreach ($campos as $campo) {
                // Un valor 0 significa que el criterio se dejó en blanco y no cuenta
                if (isset($row->$campo) && $row->$campo != 0) {
                    $total_grupo += $row->$campo;
                    $count++;
                }
            }
        }
        $promedios[] = $count > 0 ? round($total_grupo / $count, 1) : 0;
    }

    // Calcular la puntuación total
    $total = round(array_sum($promedios), 1);

    // Guardar en meta si es post_type=empleado
    if ($post_id && get_post_type($post_id) === 'empleado') {
        update_post_meta($post_id, 'cdb_puntuacion_total', $total);
    }

    // Datos para la gráfica
    $data = [
        'labels'    => array_keys($grupos),
        'promedios' => $promedios,
        'total'     => $total,
    ];

    // Obtener colores configurados
    $defaults = [
        'empleado_background' => 'rgba(75, 192, 192, 0.2)',
        'empleado_border'     => 'rgba(75, 192, 192, 1)',
        'ticks_color'         => '#666666',
        'ticks_backdrop'      => ''
    ];
    $opts     = get_option('cdb_grafica_colores', $defaults);
    $attributes['backgroundColor']   = $opts['empleado_background'] ?? $defaults['empleado_background'];
    $attributes['borderColor']       = $opts['empleado_border'] ?? $defaults['empleado_border'];
    $attributes['ticksColor']        = $opts['ticks_color'] ?? $defaults['ticks_color'];
    $attributes['ticksBackdropColor'] = $opts['ticks_backdrop'] ?? $defaults['ticks_backdrop'];

    ob_start();
    ?>
    <div id="grafica-empleado"
         data-valores="<?php echo esc_attr(wp_json_encode($data)); ?>"
         data-background-color="<?php echo esc_attr($attributes['backgroundColor']); ?>"
         data-border-color="<?php echo esc_attr($attributes['borderColor']); ?>"
         data-ticks-color="<?php echo esc_attr($attributes['ticksColor']); ?>"
         data-ticks-backdrop-color="<?php echo esc_attr($attributes['ticksBackdropColor']); ?>">
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// 3. Generar la gráfica en el frontend.
// ------------------------------------------------------------------
function generar_grafica_empleado_en_frontend() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const dataElement = document.getElementById('grafica-empleado');
        if (dataElement) {
            const data = JSON.parse(dataElement.dataset.valores);
            const ctx = document.createElement('canvas');
            dataElement.appendChild(ctx);

            const chartData = {
                labels: data.labels,
                datasets: [{
                    label: `Puntuación Total: ${data.total}`,
                    data: data.promedios,
                    backgroundColor: dataElement.dataset.backgroundColor,
                    borderColor: dataElement.dataset.borderColor,
                    borderWidth: 2
                }]
            };

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true } },
                scales: {
                    r: {
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            max: 10,
                            min: 0,
                            color: dataElement.dataset.ticksColor,
                            backdropColor: dataElement.dataset.ticksBackdropColor || undefined
                        },
                        suggestedMin: 0,
                        suggestedMax: 10
                    }
                }
            };

            new Chart(ctx, {
                type: 'radar',
                data: chartData,
                options: options
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'generar_grafica_empleado_en_frontend');

// ------------------------------------------------------------------
// 4. Crear la tabla de resultados con dbDelta.
// ------------------------------------------------------------------
function grafica_empleado_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $charset_collate = $wpdb->get_charset_collate();

    // Sin comentarios en línea dentro de la SQL
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        direccion FLOAT NOT NULL,
        camarero FLOAT NOT NULL,
        venta FLOAT NOT NULL,
        satisfaccion FLOAT NOT NULL,
        cooperacion FLOAT NOT NULL,
        orden FLOAT NOT NULL,
        cocina_local FLOAT NOT NULL,
        cocinero FLOAT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ------------------------------------------------------------------
// 5. Shortcode para el formulario de calificaciones (oculta el formulario si no procede).
// ------------------------------------------------------------------
add_shortcode('grafica_empleado_form', function ($atts) {
    // Atributos del shortcode
    $atts = shortcode_atts(['post_id' => get_the_ID()], $atts);

    // Verificar permiso global
    if (!current_user_can('submit_grafica_empleado')) {
        return '<p>' . esc_html__( 'No tienes permisos para enviar resultados.', 'cdb-grafica' ) . '</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $user_id    = get_current_user_id();
    $user       = wp_get_current_user();
    $roles      = (array) $user->roles;
    $post_id    = intval($atts['post_id']);
    $post       = get_post($post_id);

    if (!$post || $post->post_type !== 'empleado') {
        return '<p>' . esc_html__( 'Este contenido no es un empleado válido.', 'cdb-grafica' ) . '</p>';
    }

    // Determinar si se muestra el formulario o no:
    $puede_calificar = true;
    $mensaje         = '';

// Verificaciones por rol:
if (in_array('empleado', $roles)) {
    // 1) ¿Está intentando calificar a su propio empleado?
    if ($post->post_author == $user_id) {
        $puede_calificar = false;
        $mensaje = 'No puedes calificar a tu propio empleado.';
    } else {
        // 2) Verificar si ambos comparten algún equipo en wp_cdb_experiencia
        $mi_empleado_id = cdb_obtener_empleado_id($user_id);
        if (!$mi_empleado_id) {
            $puede_calificar = false;
            $mensaje = 'No se encontró tu perfil de empleado.';
        } else {
            // Consulta: ¿existe un equipo_id compartido entre "mi_empleado_id" y "$post_id" en wp_cdb_experiencia?
            $existe_equipo_compartido = $wpdb->get_var($wpdb->prepare("
                SELECT 1
                FROM wp_cdb_experiencia e1
                JOIN wp_cdb_experiencia e2 
                      ON e1.equipo_id = e2.equipo_id
                WHERE e1.empleado_id = %d
                  AND e2.empleado_id = %d
                LIMIT 1
            ", $mi_empleado_id, $post_id));

            if (!$existe_equipo_compartido) {
                $puede_calificar = false;
                $mensaje = 'No puedes calificar a un empleado que no pertenece a tu mismo equipo.';
            }
        }
    }
}

if (in_array('empleador', $roles) && $puede_calificar) {
    // 1) Obtener los bares del empleador (autor = $user_id)
    $bares_del_empleador = get_posts([
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'fields'         => 'ids',
        'posts_per_page' => -1
    ]);
    $bares_del_empleador = $bares_del_empleador ?: [];

    // 2) Verificar si el empleado (post_id) tiene cdb_experiencia en alguno de esos bares
    //    Si no existe coincidencia, no puede calificarlo.
    if (empty($bares_del_empleador)) {
        $puede_calificar = false;
        $mensaje = 'No tienes ningún bar registrado para calificar.';
    } else {
        $in_bares = implode(',', array_map('intval', $bares_del_empleador));

        // ¿El empleado (post_id) tiene experiencia en alguno de esos bares?
        $existe_relacion = $wpdb->get_var($wpdb->prepare("
            SELECT 1
            FROM wp_cdb_experiencia
            WHERE empleado_id = %d
              AND bar_id IN ($in_bares)
            LIMIT 1
        ", $post_id));

        if (!$existe_relacion) {
            $puede_calificar = false;
            $mensaje = 'No pertenece a tu equipo.';
        }
    }
}


    // Obtener datos existentes (si alguno)
    $existing_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d AND user_id = %d",
        $post_id,
        $user_id
    ), ARRAY_A);

    // Definir los nombres y descripciones de las características
    $grupos = [
        'DIE (Dirección)' => [
            'direccion'     => ['label' => 'Dirección',     'descripcion' => 'Guiar al equipo hacia los objetivos comunes.'],
        ],
        'SAL (Sala)' => [
            'camarero'          => ['label' => 'Camarero',                'descripcion' => 'Atender y servir a los clientes en sala.'],
        ],
        'TES (Técnica Sala)' => [
            'venta'        => ['label' => 'Venta',       'descripcion' => 'Capacidades comerciales de venta.'],
        ],
        'ATC (Atención al Cliente)' => [
            'satisfaccion'    => ['label' => 'Satisfacción', 'descripcion' => 'Garantizar una experiencia positiva para el cliente.'],
        ],
        'TEQ (Trabajo en Equipo)' => [
            'cooperacion'   => ['label' => 'Cooperación', 'descripcion' => 'Colaborar para lograr objetivos comunes.'],
        ],
        'ORL (Orden y Limpieza)' => [
            'orden'            => ['label' => 'Orden',         'descripcion' => 'Organizar el espacio y tareas de forma eficiente.'],
        ],
        'TEC (Técnica de Cocina)' => [
            'cocina_local'       => ['label' => 'Cocina Local',       'descripcion' => 'Dominar técnicas culinarias locales.'],
        ],
        'COC (Cocina)' => [
            'cocinero'            => ['label' => 'Cocinero',                 'descripcion' => 'Encargarse de la preparación de platos principales.'],
        ],
    ];

    // Encolar estilos y scripts si quieres
    $style_path = plugin_dir_path(dirname(__FILE__)) . 'style.css';
    wp_enqueue_style(
        'plugin-style',
        plugins_url('style.css', dirname(__FILE__)),
        [],
        filemtime($style_path)
    );
    $script_form_path = plugin_dir_path(dirname(__FILE__)) . 'script.js';
    wp_enqueue_script(
        'grafica-empleado-form-script',
        plugins_url('script.js', dirname(__FILE__)),
        ['jquery'],
        filemtime($script_form_path),
        true
    );

    ob_start();
    // Si no puede calificar, mostramos el mensaje y salimos
    if (!$puede_calificar) {
        echo '<p style="color:red; font-weight:bold;">' . esc_html($mensaje) . '</p>';
        return ob_get_clean();
    }

    // Si sí puede calificar, mostramos el formulario
    ?>
    <form method="post" action="">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
        <?php wp_nonce_field('submit_grafica_empleado', 'grafica_empleado_nonce'); ?>

        <?php foreach ($grupos as $grupo_nombre => $campos): ?>
            <div class="accordion">
                <div class="accordion-header">
                    <button type="button" class="accordion-toggle">
                        <?php echo esc_html($grupo_nombre); ?>
                    </button>
                </div>
                <div class="accordion-content" style="display: none;">
                    <?php foreach ($campos as $campo_slug => $campo_info): 
                        $valor_existente = isset($existing_data[$campo_slug]) ? $existing_data[$campo_slug] : '';
                    ?>
                        <label for="<?php echo esc_attr($campo_slug); ?>">
                            <strong><?php echo esc_html($campo_info['label']); ?></strong><br>
                            <em><?php echo esc_html($campo_info['descripcion']); ?></em>
                        </label>
                        <input 
                            type="range" 
                            id="<?php echo esc_attr($campo_slug); ?>"
                            name="<?php echo esc_attr($campo_slug); ?>" 
                            value="<?php echo esc_attr($valor_existente !== '' ? $valor_existente : 0); ?>" 
                            min="0" 
                            max="10" 
                            step="1" 
                            oninput="document.getElementById('<?php echo esc_attr($campo_slug); ?>_output').value = this.value"
                        >
                        <output id="<?php echo esc_attr($campo_slug); ?>_output">
                            <?php echo esc_html($valor_existente !== '' ? $valor_existente : 0); ?>
                        </output>
                    <br>

                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" name="submit_grafica_empleado">Enviar</button>
    </form>
    <?php
    return ob_get_clean();
});

// ------------------------------------------------------------------
// 6. Procesar el envío del formulario "grafica_empleado_form".
//    Repite las validaciones de rol y guarda o actualiza los datos
//    en la tabla personalizada del empleado evaluado.
// ------------------------------------------------------------------
function handle_grafica_empleado_submission() {
    if (isset($_POST['submit_grafica_empleado'])) {
        // Repetir validaciones para seguridad
        if (!isset($_POST['grafica_empleado_nonce']) || !wp_verify_nonce($_POST['grafica_empleado_nonce'], 'submit_grafica_empleado')) {
            wp_die( esc_html__( 'Nonce inválido.', 'cdb-grafica' ) );
        }
        if (!current_user_can('submit_grafica_empleado')) {
            wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'cdb-grafica' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'grafica_empleado_results';
        $user_id    = get_current_user_id();
        $user       = wp_get_current_user();
        $roles      = (array) $user->roles;
        $post_id    = intval($_POST['post_id']);
        $post       = get_post($post_id);

        if (!$post) {
            wp_die( esc_html__( 'Empleado inválido.', 'cdb-grafica' ) );
        }
        if ($post->post_type !== 'empleado') {
            wp_die( esc_html__( 'No es un post de tipo empleado.', 'cdb-grafica' ) );
        }

// Validaciones de rol
if (in_array('empleado', $roles)) {
    // 1) Evitar que un empleado califique su propio empleado
    if ($post->post_author == $user_id) {
        wp_die( esc_html__( 'No puedes calificar a tu propio empleado.', 'cdb-grafica' ) );
    }

    // 2) Verificar si ambos (quien califica y el calificado) comparten equipo en wp_cdb_experiencia
    $mi_empleado_id = cdb_obtener_empleado_id($user_id);
    if (!$mi_empleado_id) {
        wp_die( esc_html__( 'No se encontró tu perfil de empleado.', 'cdb-grafica' ) );
    }

    // Consulta: ¿existe un equipo_id compartido entre "mi_empleado_id" y "$post_id" en wp_cdb_experiencia?
    $existe_equipo_compartido = $wpdb->get_var($wpdb->prepare("
        SELECT 1
        FROM wp_cdb_experiencia e1
        JOIN wp_cdb_experiencia e2 
              ON e1.equipo_id = e2.equipo_id
        WHERE e1.empleado_id = %d
          AND e2.empleado_id = %d
        LIMIT 1
    ", $mi_empleado_id, $post_id));

    if (!$existe_equipo_compartido) {
        wp_die( esc_html__( 'No puedes calificar a un empleado que no pertenece a tu mismo equipo.', 'cdb-grafica' ) );
    }
}

if (in_array('empleador', $roles)) {
    // 1) Bares del empleador (autor = $user_id)
    $bares_del_empleador = get_posts([
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'fields'         => 'ids',
        'posts_per_page' => -1
    ]);
    $bares_del_empleador = $bares_del_empleador ?: [];

    // 2) Verificar si el empleado (post_id) tiene experiencia en alguno de esos bares
    if (empty($bares_del_empleador)) {
        wp_die( esc_html__( 'No tienes bares para calificar a este empleado.', 'cdb-grafica' ) );
    } else {
        $in_bares = implode(',', array_map('intval', $bares_del_empleador));

        $existe_relacion = $wpdb->get_var($wpdb->prepare("
            SELECT 1
            FROM wp_cdb_experiencia
            WHERE empleado_id = %d
              AND bar_id IN ($in_bares)
            LIMIT 1
        ", $post_id));

        if (!$existe_relacion) {
            wp_die( esc_html__( 'No pertenece a tu equipo.', 'cdb-grafica' ) );
        }
    }
}

        // Otros roles sin restricciones

        // Preparar datos
        $data   = ['post_id' => $post_id, 'user_id' => $user_id];
        $fields = $wpdb->get_col("SHOW COLUMNS FROM $table_name");
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $field !== 'id' && $field !== 'created_at') {
                $data[$field] = floatval($_POST[$field]);
            }
        }

        // Insertar o actualizar
        $existing_row = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE post_id = %d AND user_id = %d",
            $post_id,
            $user_id
        ));

        if ($existing_row) {
            $wpdb->update($table_name, $data, ['id' => $existing_row]);
        } else {
            $wpdb->insert($table_name, $data);
        }

        // Redirigir
        wp_redirect(get_permalink($post_id));
        exit;
    }
}