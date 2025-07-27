<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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

function cdb_grafica_colores_page() {
    if (isset($_POST['cdb_grafica_colores_nonce']) && wp_verify_nonce($_POST['cdb_grafica_colores_nonce'], 'cdb_guardar_colores')) {
        $colores = [
            'bar_background'      => sanitize_text_field($_POST['bar_background'] ?? ''),
            'bar_border'          => sanitize_text_field($_POST['bar_border'] ?? ''),
            'empleado_background' => sanitize_text_field($_POST['empleado_background'] ?? ''),
            'empleado_border'     => sanitize_text_field($_POST['empleado_border'] ?? ''),
            'ticks_color'         => sanitize_text_field($_POST['ticks_color'] ?? ''),
            'ticks_backdrop'      => sanitize_text_field($_POST['ticks_backdrop'] ?? ''),
        ];
        update_option('cdb_grafica_colores', $colores);
        echo '<div class="updated"><p>Colores actualizados.</p></div>';
    }

    $defaults = [
        'bar_background'      => 'rgba(75, 192, 192, 0.2)',
        'bar_border'          => 'rgba(75, 192, 192, 1)',
        'empleado_background' => 'rgba(75, 192, 192, 0.2)',
        'empleado_border'     => 'rgba(75, 192, 192, 1)',
        'ticks_color'         => '#666666',
        'ticks_backdrop'      => '',
    ];
    $colores = get_option('cdb_grafica_colores', $defaults);

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    $alpha_css_path = plugin_dir_path(__FILE__) . 'color-picker-alpha.css';
    wp_enqueue_style(
        'cdb-color-picker-alpha',
        plugins_url('admin/color-picker-alpha.css', dirname(__FILE__)),
        ['wp-color-picker'],
        filemtime($alpha_css_path)
    );

    $alpha_js_path = plugin_dir_path(__FILE__) . 'color-picker-alpha.js';
    wp_enqueue_script(
        'cdb-color-picker-alpha',
        plugins_url('admin/color-picker-alpha.js', dirname(__FILE__)),
        ['wp-color-picker', 'jquery', 'jquery-ui-slider'],
        filemtime($alpha_js_path),
        true
    );
    ?>
    <div class="wrap">
        <h1>Configurar Colores</h1>
        <form method="post">
            <?php wp_nonce_field('cdb_guardar_colores', 'cdb_grafica_colores_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Bar - Color de fondo</th>
                    <td><input type="text" name="bar_background" value="<?php echo esc_attr($colores['bar_background']); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row">Bar - Color de borde</th>
                    <td><input type="text" name="bar_border" value="<?php echo esc_attr($colores['bar_border']); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row">Empleado - Color de fondo</th>
                    <td><input type="text" name="empleado_background" value="<?php echo esc_attr($colores['empleado_background']); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row">Empleado - Color de borde</th>
                    <td><input type="text" name="empleado_border" value="<?php echo esc_attr($colores['empleado_border']); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row">Ticks - Color</th>
                    <td><input type="text" name="ticks_color" value="<?php echo esc_attr($colores['ticks_color']); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row">Ticks - Color de fondo</th>
                    <td><input type="text" name="ticks_backdrop" value="<?php echo esc_attr($colores['ticks_backdrop']); ?>" class="cdb-color-field" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($){
        $('.cdb-color-field').cdbColorPickerAlpha({alpha: true});
    });
    </script>
    <?php
}
