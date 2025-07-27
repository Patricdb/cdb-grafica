<?php
// Submenu page to modify graph colors
function cdb_grafica_colores_menu() {
    add_submenu_page(
        'cdb_grafica_menu',
        'Configurar Colores',
        'Configurar Colores',
        'manage_options',
        'cdb_modificar_colores',
        'cdb_grafica_colores_page'
    );
}
add_action('admin_menu', 'cdb_grafica_colores_menu');

function cdb_rgba_to_hex_alpha($value) {
    if (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([0-9.]+))?\)/', $value, $m)) {
        $hex   = sprintf('#%02x%02x%02x', $m[1], $m[2], $m[3]);
        $alpha = isset($m[4]) ? floatval($m[4]) : 1;
        return [$hex, $alpha];
    }
    return [$value, 1];
}

function cdb_grafica_colores_page() {
    if (isset($_POST['cdb_grafica_colores_nonce']) && wp_verify_nonce($_POST['cdb_grafica_colores_nonce'], 'cdb_guardar_colores')) {
        $colores = [
            'bar_background'          => sanitize_text_field($_POST['bar_background'] ?? ''),
            'bar_background_alpha'    => max(0, min(1, floatval($_POST['bar_background_alpha'] ?? '1'))),
            'bar_border'              => sanitize_text_field($_POST['bar_border'] ?? ''),
            'bar_border_alpha'        => max(0, min(1, floatval($_POST['bar_border_alpha'] ?? '1'))),
            'empleado_background'     => sanitize_text_field($_POST['empleado_background'] ?? ''),
            'empleado_background_alpha' => max(0, min(1, floatval($_POST['empleado_background_alpha'] ?? '1'))),
            'empleado_border'         => sanitize_text_field($_POST['empleado_border'] ?? ''),
            'empleado_border_alpha'   => max(0, min(1, floatval($_POST['empleado_border_alpha'] ?? '1'))),
            'ticks_color'             => sanitize_text_field($_POST['ticks_color'] ?? ''),
            'ticks_color_alpha'       => max(0, min(1, floatval($_POST['ticks_color_alpha'] ?? '1'))),
            'ticks_backdrop'          => sanitize_text_field($_POST['ticks_backdrop'] ?? ''),
            'ticks_backdrop_alpha'    => max(0, min(1, floatval($_POST['ticks_backdrop_alpha'] ?? '1'))),
        ];
        update_option('cdb_grafica_colores', $colores);
        echo '<div class="updated"><p>Colores actualizados.</p></div>';
    }

    $defaults = [
        'bar_background'           => '#4bc0c0',
        'bar_background_alpha'     => 0.2,
        'bar_border'               => '#4bc0c0',
        'bar_border_alpha'         => 1,
        'empleado_background'      => '#4bc0c0',
        'empleado_background_alpha'=> 0.2,
        'empleado_border'          => '#4bc0c0',
        'empleado_border_alpha'    => 1,
        'ticks_color'              => '#666666',
        'ticks_color_alpha'        => 1,
        'ticks_backdrop'           => '',
        'ticks_backdrop_alpha'     => 1,
    ];
    $colores = get_option('cdb_grafica_colores', []);
    $colores = wp_parse_args($colores, $defaults);

    // Compatibilidad con valores antiguos en formato rgba
    foreach (['bar_background', 'bar_border', 'empleado_background', 'empleado_border', 'ticks_color', 'ticks_backdrop'] as $field) {
        if (strpos($colores[$field], 'rgb') === 0) {
            [$hex, $alpha] = cdb_rgba_to_hex_alpha($colores[$field]);
            $colores[$field] = $hex;
            $colores[$field . '_alpha'] = $alpha;
        }
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    ?>
    <div class="wrap">
        <h1>Configurar Colores</h1>
        <form method="post">
            <?php wp_nonce_field('cdb_guardar_colores', 'cdb_grafica_colores_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Bar - Color de fondo</th>
                    <td>
                        <input type="text" name="bar_background" value="<?php echo esc_attr($colores['bar_background']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="bar_background_alpha" value="<?php echo esc_attr($colores['bar_background_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Bar - Color de borde</th>
                    <td>
                        <input type="text" name="bar_border" value="<?php echo esc_attr($colores['bar_border']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="bar_border_alpha" value="<?php echo esc_attr($colores['bar_border_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Empleado - Color de fondo</th>
                    <td>
                        <input type="text" name="empleado_background" value="<?php echo esc_attr($colores['empleado_background']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="empleado_background_alpha" value="<?php echo esc_attr($colores['empleado_background_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Empleado - Color de borde</th>
                    <td>
                        <input type="text" name="empleado_border" value="<?php echo esc_attr($colores['empleado_border']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="empleado_border_alpha" value="<?php echo esc_attr($colores['empleado_border_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Ticks - Color</th>
                    <td>
                        <input type="text" name="ticks_color" value="<?php echo esc_attr($colores['ticks_color']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="ticks_color_alpha" value="<?php echo esc_attr($colores['ticks_color_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Ticks - Color de fondo</th>
                    <td>
                        <input type="text" name="ticks_backdrop" value="<?php echo esc_attr($colores['ticks_backdrop']); ?>" class="cdb-color-field" />
                        <input type="number" step="0.05" min="0" max="1" name="ticks_backdrop_alpha" value="<?php echo esc_attr($colores['ticks_backdrop_alpha']); ?>" class="small-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($){
        $('.cdb-color-field').wpColorPicker();
    });
    </script>
    <?php
}
