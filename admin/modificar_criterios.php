<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'bar';
    $criterios = cdb_grafica_get_criterios_organizados($tab);
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Modificar Criterios', 'cdb-grafica' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=cdb_modificar_criterios&tab=bar" class="nav-tab <?php echo ($tab == 'bar') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Bar', 'cdb-grafica' ); ?></a>
            <a href="?page=cdb_modificar_criterios&tab=empleado" class="nav-tab <?php echo ($tab == 'empleado') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Empleado', 'cdb-grafica' ); ?></a>
        </h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="criterio_actual"><?php esc_html_e( 'Criterio a Reemplazar:', 'cdb-grafica' ); ?></label></th>
                    <td>
                        <select name="criterio_actual" id="criterio_actual">
                            <?php foreach ($criterios as $grupo => $items) { ?>
                                <optgroup label="<?php echo esc_attr__( $grupo, 'cdb-grafica' ); ?>">
                                    <?php foreach ($items as $criterio) { ?>
                                        <option value="<?php echo esc_attr( $criterio ); ?>">
                                            <?php echo esc_html__( $criterio, 'cdb-grafica' ); ?>
                                        </option>
                                    <?php } ?>
                                </optgroup>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}

// Definir grupos de criterios reales
function cdb_grafica_get_criterios_organizados($grafica_tipo) {
    if ($grafica_tipo === 'bar') {
        return cdb_get_bar_criterios();
    } elseif ($grafica_tipo === 'empleado') {
        return cdb_get_empleado_criterios();
    }
    return [];
}
