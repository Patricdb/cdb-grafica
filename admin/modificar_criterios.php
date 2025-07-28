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
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Modificar Criterios', 'cdb-grafica' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=cdb_modificar_criterios&tab=bar" class="nav-tab <?php echo ($tab == 'bar') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Bar', 'cdb-grafica' ); ?></a>
            <a href="?page=cdb_modificar_criterios&tab=empleado" class="nav-tab <?php echo ($tab == 'empleado') ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Empleado', 'cdb-grafica' ); ?></a>
        </h2>
        <?php if ( $tab === 'empleado' ) { ?>
            <?php $criterios = cdb_get_criterios_empleado(); ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Grupo', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $criterios as $grupo => $items ) { ?>
                        <?php foreach ( $items as $slug => $info ) { ?>
                            <tr>
                                <td><?php echo esc_html__( $grupo, 'cdb-grafica' ); ?></td>
                                <td><?php echo esc_html( $slug ); ?></td>
                                <td><?php echo esc_html( $info['label'] ); ?></td>
                                <td><?php echo esc_html( $info['descripcion'] ); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
            <?php
            // TODO: implementar edición de criterios existentes.
            // TODO: implementar creación de nuevos criterios.
        } else {
            $criterios = cdb_grafica_get_criterios_organizados( $tab );
            ?>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th><label for="criterio_actual"><?php esc_html_e( 'Criterio a Reemplazar:', 'cdb-grafica' ); ?></label></th>
                        <td>
                            <select name="criterio_actual" id="criterio_actual">
                                <?php foreach ( $criterios as $grupo => $items ) { ?>
                                    <optgroup label="<?php echo esc_attr__( $grupo, 'cdb-grafica' ); ?>">
                                        <?php foreach ( $items as $criterio ) { ?>
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
        <?php } ?>
    </div>
    <?php
}

// Definir grupos de criterios reales
function cdb_grafica_get_criterios_organizados($grafica_tipo) {
    if ($grafica_tipo === 'bar') {
        $grupos = [
                    ];
    } elseif ($grafica_tipo === 'empleado') {
        $criterios = cdb_get_criterios_empleado();
        $grupos    = [];
        foreach ($criterios as $grupo => $items) {
            $labels = [];
            foreach ($items as $info) {
                $labels[] = $info['label'];
            }
            $grupos[$grupo] = $labels;
        }
    } else {
        $grupos = [];
    }
    return $grupos;
}
