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

function cdb_grafica_colores_page() {
    if (isset($_POST['cdb_grafica_colores_nonce']) && wp_verify_nonce($_POST['cdb_grafica_colores_nonce'], 'cdb_guardar_colores')) {
        $colores = [
            'bar_background'      => sanitize_text_field($_POST['bar_background'] ?? ''),
            'bar_border'          => sanitize_text_field($_POST['bar_border'] ?? ''),
            'empleado_background' => sanitize_text_field($_POST['empleado_background'] ?? ''),
            'empleado_border'     => sanitize_text_field($_POST['empleado_border'] ?? ''),
        ];
        update_option('cdb_grafica_colores', $colores);
        echo '<div class="updated"><p>Colores actualizados.</p></div>';
    }

    $defaults = [
        'bar_background'      => 'rgba(75, 192, 192, 0.2)',
        'bar_border'          => 'rgba(75, 192, 192, 1)',
        'empleado_background' => 'rgba(75, 192, 192, 0.2)',
        'empleado_border'     => 'rgba(75, 192, 192, 1)',
    ];
    $colores = get_option('cdb_grafica_colores', $defaults);

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
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(document).ready(function($){
        $('.cdb-color-field').wpColorPicker();
    });
    </script>
    <?php
}
