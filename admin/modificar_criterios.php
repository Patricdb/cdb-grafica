<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'inc/shared-functions.php';
// Agregar el menú principal del plugin en el panel de administración
function cdb_grafica_menu() {
    add_menu_page(
        __( 'CdB Gráfica', 'cdb-grafica' ),
        __( 'CdB Gráfica', 'cdb-grafica' ),
        'manage_options',
        'cdb_grafica_menu',
        'cdb_grafica_dashboard_page',
        'dashicons-chart-bar',
        25
    );
}
add_action('admin_menu', 'cdb_grafica_menu');

// Función para renderizar la página principal de CdB Gráfica
function cdb_grafica_dashboard_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Bienvenido a CdB Gráfica', 'cdb-grafica' ); ?></h1>
        <p><?php esc_html_e( 'Desde este panel puedes gestionar las gráficas y modificar los criterios evaluativos.', 'cdb-grafica' ); ?></p>
    </div>
    <?php
}

// Agregar la página de configuración en el menú de administración
function cdb_grafica_modificar_criterios_menu() {
    add_submenu_page(
        'cdb_grafica_menu',
        __( 'Modificar Criterios', 'cdb-grafica' ),
        __( 'Modificar Criterios', 'cdb-grafica' ),
        'manage_options',
        'cdb_modificar_criterios',
        'cdb_grafica_modificar_criterios_page'
    );
}
add_action('admin_menu', 'cdb_grafica_modificar_criterios_menu');

// Página de modificación de criterios con pestañas
function cdb_grafica_modificar_criterios_page() {
    $tab      = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'bar';
    $option   = 'cdb_grafica_criterios_' . $tab;
    $defaults = cdb_grafica_default_criterios($tab);
    $criterios = get_option($option, $defaults);

    if (isset($_POST['cdb_guardar_criterios']) && check_admin_referer('cdb_guardar_criterios')) {
        $data = cdb_grafica_sanitize_criterios($_POST['criterios'] ?? []);
        update_option($option, $data);
        $criterios = $data;
        echo '<div class="updated"><p>' . esc_html__( 'Criterios actualizados.', 'cdb-grafica' ) . '</p></div>';
    } elseif (isset($_POST['cdb_reset_criterios']) && check_admin_referer('cdb_guardar_criterios')) {
        update_option($option, $defaults);
        $criterios = $defaults;
        echo '<div class="updated"><p>' . esc_html__( 'Criterios restablecidos a valores por defecto.', 'cdb-grafica' ) . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Modificar Criterios', 'cdb-grafica' ); ?></h1>
        <p><?php esc_html_e( 'Edita los nombres visibles y descripciones de los criterios. El slug se vincula con columnas de la base de datos, por lo que no deberías cambiarlo sin razón. "Orden" controla la secuencia de aparición en la tabla de calificación.', 'cdb-grafica' ); ?></p>
        <h2 class="nav-tab-wrapper">
            <a href="?page=cdb_modificar_criterios&tab=bar" class="nav-tab <?php echo ($tab == 'bar') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Bar', 'cdb-grafica' ); ?></a>
            <a href="?page=cdb_modificar_criterios&tab=empleado" class="nav-tab <?php echo ($tab == 'empleado') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Empleado', 'cdb-grafica' ); ?></a>
        </h2>
        <form method="post">
            <?php wp_nonce_field('cdb_guardar_criterios'); ?>
            <table class="widefat fixed" style="max-width:800px">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Grupo', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Orden', 'cdb-grafica' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($criterios as $g_index => $grupo) : ?>
                    <?php foreach ($grupo['criterios'] as $c_index => $item) : ?>
                    <tr>
                        <?php if ($c_index === 0) : ?>
                            <td rowspan="<?php echo count($grupo['criterios']); ?>">
                                <input type="text" name="criterios[<?php echo $g_index; ?>][grupo]" value="<?php echo esc_attr($grupo['grupo']); ?>">
                            </td>
                        <?php endif; ?>
                        <td><input type="text" name="criterios[<?php echo $g_index; ?>][criterios][<?php echo $c_index; ?>][slug]" value="<?php echo esc_attr($item['slug']); ?>" readonly></td>
                        <td><input type="text" name="criterios[<?php echo $g_index; ?>][criterios][<?php echo $c_index; ?>][label]" value="<?php echo esc_attr($item['label']); ?>"></td>
                        <td><input type="text" name="criterios[<?php echo $g_index; ?>][criterios][<?php echo $c_index; ?>][descripcion]" value="<?php echo esc_attr($item['descripcion']); ?>"></td>
                        <td><input type="number" name="criterios[<?php echo $g_index; ?>][criterios][<?php echo $c_index; ?>][orden]" value="<?php echo esc_attr($item['orden']); ?>" style="width:60px"></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p>
                <button type="submit" name="cdb_guardar_criterios" class="button button-primary"><?php esc_html_e('Guardar cambios', 'cdb-grafica'); ?></button>
                <button type="submit" name="cdb_reset_criterios" class="button" onclick="return confirm('<?php echo esc_js( __( '¿Restaurar valores por defecto?', 'cdb-grafica' ) ); ?>');">
                    <?php esc_html_e('Restaurar por defecto', 'cdb-grafica'); ?>
                </button>
            </p>
        </form>

        <h2><?php esc_html_e( 'Vista previa de criterios', 'cdb-grafica' ); ?></h2>
        <table class="widefat fixed" style="max-width:800px">
            <tbody>
            <?php $vista = cdb_grafica_get_criterios_organizados( $tab ); ?>
            <?php foreach ( $vista as $grupo_nombre => $items ) : ?>
                <tr>
                    <th colspan="2"><?php echo esc_html( $grupo_nombre ); ?></th>
                </tr>
                <?php foreach ( $items as $info ) : ?>
                    <tr>
                        <td style="padding-left:20px;"><?php echo esc_html( $info['label'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}




function cdb_grafica_sanitize_criterios($data) {
    $out = [];
    foreach ($data as $g) {
        if (empty($g['grupo']) || !isset($g['criterios']) || !is_array($g['criterios'])) {
            continue;
        }
        $grupo = [ 'grupo' => sanitize_text_field($g['grupo']), 'criterios' => [] ];
        foreach ($g['criterios'] as $item) {
            if (empty($item['slug'])) { continue; }
            $grupo['criterios'][] = [
                'slug'        => sanitize_key($item['slug']),
                'label'       => sanitize_text_field($item['label'] ?? ''),
                'descripcion' => sanitize_text_field($item['descripcion'] ?? ''),
                'orden'       => intval($item['orden'] ?? 0),
            ];
        }
        $out[] = $grupo;
    }
    return $out;
}
