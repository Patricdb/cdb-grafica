<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Comprueba si una ruta es escribible antes de guardar cambios.
 *
 * @param string $path Ruta al archivo que se desea modificar.
 * @return bool True si el archivo es escribible, false en caso contrario.
 */
function cdb_grafica_ensure_writable( $path ) {
    if ( ! is_writable( $path ) ) {
        $message = sprintf(
            __( 'El archivo %s no es escribible.', 'cdb-grafica' ),
            esc_html( $path )
        );

        if ( is_admin() ) {
            add_settings_error( 'cdb_modificar_criterios', 'cdb_error_permisos', $message );
        } else {
            wp_die( $message );
        }
        return false;
    }

    return true;
}

/**
 * Realiza una copia de seguridad manteniendo un número limitado de archivos.
 *
 * El archivo original se copia con un sufijo de fecha y hora antes de ser
 * sobrescrito. Solo se conservarán las copias más recientes para evitar
 * acumulación de archivos en el sistema.
 *
 * @param string $file        Ruta del archivo a respaldar.
 * @param int    $max_backups Número máximo de copias a conservar.
 * @return void
 */
function cdb_grafica_backup_file( $file, $max_backups = 5 ) {
    $dir       = dirname( $file );
    $base      = basename( $file );
    $timestamp = gmdate( 'Ymd-His' );
    $backup    = "$dir/{$base}.{$timestamp}.bak";

    @copy( $file, $backup );

    // Elimina copias antiguas si exceden el máximo permitido.
    $pattern   = $dir . '/' . $base . '.*.bak';
    $files     = glob( $pattern );
    if ( is_array( $files ) && count( $files ) > $max_backups ) {
        usort(
            $files,
            static function ( $a, $b ) {
                return filemtime( $b ) <=> filemtime( $a );
            }
        );
        $old_files = array_slice( $files, $max_backups );
        foreach ( $old_files as $old ) {
            @unlink( $old );
        }
    }
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

// Esta funcionalidad modifica archivos PHP en disco.
// Solo los administradores deben utilizarla y las ediciones concurrentes
// pueden causar conflictos.

// Página de modificación de criterios con pestañas
function cdb_grafica_modificar_criterios_page() {
    $tab        = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'bar';
    $edit_grupo = isset($_GET['edit_grupo']) ? sanitize_text_field(rawurldecode($_GET['edit_grupo'])) : '';
    $edit_slug  = isset($_GET['edit_slug']) ? sanitize_text_field($_GET['edit_slug']) : '';
    $add_new    = isset($_GET['add']) ? sanitize_text_field($_GET['add']) : '';

    if (isset($_POST['cdb_edit_criterio_nonce']) && wp_verify_nonce($_POST['cdb_edit_criterio_nonce'], 'cdb_guardar_criterio')) {
        if (current_user_can('manage_options')) {
            $tipo   = sanitize_text_field($_POST['tipo']);
            $grupo  = sanitize_text_field($_POST['grupo']);
            $slug   = sanitize_text_field($_POST['slug']);
            $label  = sanitize_text_field($_POST['label']);
            $desc   = sanitize_textarea_field($_POST['descripcion']);

            if ($tipo === 'empleado') {
                $criterios = cdb_get_criterios_empleado();
                $file      = plugin_dir_path(__DIR__) . 'inc/criterios-empleado.php';
            } else {
                $criterios = cdb_get_criterios_bar();
                $file      = plugin_dir_path(__DIR__) . 'inc/criterios-bar.php';
            }

            if (isset($criterios[$grupo][$slug])) {
                $criterios[$grupo][$slug]['label']       = $label;
                $criterios[$grupo][$slug]['descripcion'] = $desc;

                $content = "<?php\nif ( ! defined( 'ABSPATH' ) ) {\n    exit;\n}\n\nfunction cdb_get_criterios_{$tipo}() {\n    return " . var_export($criterios, true) . ";\n}\n";

                if ( cdb_grafica_ensure_writable( $file ) ) {
                    // Realizar copia de seguridad antes de escribir.
                    cdb_grafica_backup_file( $file );
                    if ( false === file_put_contents( $file, $content ) ) {
                        add_settings_error( 'cdb_modificar_criterios', 'cdb_error_guardar', __( 'No se pudo guardar el archivo de criterios.', 'cdb-grafica' ) );
                    } else {
                        add_settings_error( 'cdb_modificar_criterios', 'cdb_criterio_actualizado', __( 'Criterio actualizado.', 'cdb-grafica' ), 'updated' );
                    }
                }
            }
        }
    }

    if (isset($_POST['cdb_nuevo_criterio_nonce']) && wp_verify_nonce($_POST['cdb_nuevo_criterio_nonce'], 'cdb_guardar_nuevo_criterio')) {
        if (current_user_can('manage_options')) {
            $tipo  = sanitize_text_field($_POST['tipo']);
            $grupo = sanitize_text_field($_POST['grupo']);
            $slug  = sanitize_title($_POST['slug']);
            $label = sanitize_text_field($_POST['label']);
            $desc  = sanitize_textarea_field($_POST['descripcion']);

            if ($tipo === 'empleado') {
                $criterios = cdb_get_criterios_empleado();
                $file      = plugin_dir_path(__DIR__) . 'inc/criterios-empleado.php';
            } else {
                $criterios = cdb_get_criterios_bar();
                $file      = plugin_dir_path(__DIR__) . 'inc/criterios-bar.php';
            }

            if (!isset($criterios[$grupo])) {
                add_settings_error('cdb_modificar_criterios', 'cdb_grupo_invalido', __('Grupo inválido.', 'cdb-grafica'));
            } elseif (isset($criterios[$grupo][$slug])) {
                add_settings_error('cdb_modificar_criterios', 'cdb_slug_existente', __('El slug ya existe en este grupo.', 'cdb-grafica'));
            } elseif (empty($slug) || empty($label)) {
                add_settings_error('cdb_modificar_criterios', 'cdb_campos_vacios', __('Por favor completa los campos obligatorios.', 'cdb-grafica'));
            } else {
                $criterios[$grupo][$slug] = [
                    'label'       => $label,
                    'descripcion' => $desc,
                ];

                $content = "<?php\nif ( ! defined( 'ABSPATH' ) ) {\n    exit;\n}\n\nfunction cdb_get_criterios_{$tipo}() {\n    return " . var_export($criterios, true) . ";\n}\n";

                if ( cdb_grafica_ensure_writable( $file ) ) {
                    cdb_grafica_backup_file( $file );
                    if ( false === file_put_contents( $file, $content ) ) {
                        add_settings_error('cdb_modificar_criterios', 'cdb_error_guardar', __('No se pudo guardar el archivo de criterios.', 'cdb-grafica'));
                    } else {
                        add_settings_error('cdb_modificar_criterios', 'cdb_criterio_creado', __('Criterio añadido.', 'cdb-grafica'), 'updated');
                    }
                }
            }
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Modificar Criterios', 'cdb-grafica' ); ?></h1>
        <?php settings_errors('cdb_modificar_criterios'); ?>
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
                        <th><?php esc_html_e( 'Acciones', 'cdb-grafica' ); ?></th>
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
                                <td><a class="button" href="?page=cdb_modificar_criterios&tab=empleado&edit_grupo=<?php echo rawurlencode( $grupo ); ?>&edit_slug=<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Editar', 'cdb-grafica' ); ?></a></td>
                            </tr>
                            <?php if ( $edit_grupo === $grupo && $edit_slug === $slug ) { ?>
                                <tr>
                                    <td colspan="5">
                                        <form method="post">
                                            <input type="hidden" name="tipo" value="empleado" />
                                            <input type="hidden" name="grupo" value="<?php echo esc_attr( $grupo ); ?>" />
                                            <input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>" />
                                            <?php wp_nonce_field( 'cdb_guardar_criterio', 'cdb_edit_criterio_nonce' ); ?>
                                            <p>
                                                <label><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></label><br />
                                                <input type="text" name="label" value="<?php echo esc_attr( $info['label'] ); ?>" class="regular-text" />
                                            </p>
                                            <p>
                                                <label><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></label><br />
                                                <textarea name="descripcion" class="large-text" rows="3"><?php echo esc_textarea( $info['descripcion'] ); ?></textarea>
                                            </p>
                                            <?php submit_button( __( 'Guardar', 'cdb-grafica' ) ); ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <?php if ( $add_new && $tab === 'empleado' ) { ?>
                        <tr>
                            <td colspan="5">
                                <form method="post">
                                    <input type="hidden" name="tipo" value="empleado" />
                                    <?php wp_nonce_field( 'cdb_guardar_nuevo_criterio', 'cdb_nuevo_criterio_nonce' ); ?>
                                    <p>
                                        <label><?php esc_html_e( 'Grupo', 'cdb-grafica' ); ?></label><br />
                                        <select name="grupo" required>
                                            <?php foreach ( array_keys( $criterios ) as $g ) { ?>
                                                <option value="<?php echo esc_attr( $g ); ?>"><?php echo esc_html__( $g, 'cdb-grafica' ); ?></option>
                                            <?php } ?>
                                        </select>
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Slug', 'cdb-grafica' ); ?></label><br />
                                        <input type="text" name="slug" class="regular-text" required />
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></label><br />
                                        <input type="text" name="label" class="regular-text" required />
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></label><br />
                                        <textarea name="descripcion" class="large-text" rows="3"></textarea>
                                    </p>
                                    <?php submit_button( __( 'Guardar', 'cdb-grafica' ) ); ?>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="5"><a class="button" href="?page=cdb_modificar_criterios&tab=empleado&add=1"><?php esc_html_e( 'Añadir Criterio', 'cdb-grafica' ); ?></a></td>
                    </tr>
                </tbody>
            </table>
            <?php
            // TODO: implementar edición de criterios existentes.
            // TODO: implementar creación de nuevos criterios.
            ?>
        <?php } else { ?>
            <?php $criterios = cdb_get_criterios_bar(); ?>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Grupo', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Slug', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></th>
                        <th><?php esc_html_e( 'Acciones', 'cdb-grafica' ); ?></th>
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
                                <td><a class="button" href="?page=cdb_modificar_criterios&tab=bar&edit_grupo=<?php echo rawurlencode( $grupo ); ?>&edit_slug=<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Editar', 'cdb-grafica' ); ?></a></td>
                            </tr>
                            <?php if ( $edit_grupo === $grupo && $edit_slug === $slug ) { ?>
                                <tr>
                                    <td colspan="5">
                                        <form method="post">
                                            <input type="hidden" name="tipo" value="bar" />
                                            <input type="hidden" name="grupo" value="<?php echo esc_attr( $grupo ); ?>" />
                                            <input type="hidden" name="slug" value="<?php echo esc_attr( $slug ); ?>" />
                                            <?php wp_nonce_field( 'cdb_guardar_criterio', 'cdb_edit_criterio_nonce' ); ?>
                                            <p>
                                                <label><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></label><br />
                                                <input type="text" name="label" value="<?php echo esc_attr( $info['label'] ); ?>" class="regular-text" />
                                            </p>
                                            <p>
                                                <label><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></label><br />
                                                <textarea name="descripcion" class="large-text" rows="3"><?php echo esc_textarea( $info['descripcion'] ); ?></textarea>
                                            </p>
                                            <?php submit_button( __( 'Guardar', 'cdb-grafica' ) ); ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <?php if ( $add_new && $tab === 'bar' ) { ?>
                        <tr>
                            <td colspan="5">
                                <form method="post">
                                    <input type="hidden" name="tipo" value="bar" />
                                    <?php wp_nonce_field( 'cdb_guardar_nuevo_criterio', 'cdb_nuevo_criterio_nonce' ); ?>
                                    <p>
                                        <label><?php esc_html_e( 'Grupo', 'cdb-grafica' ); ?></label><br />
                                        <select name="grupo" required>
                                            <?php foreach ( array_keys( $criterios ) as $g ) { ?>
                                                <option value="<?php echo esc_attr( $g ); ?>"><?php echo esc_html__( $g, 'cdb-grafica' ); ?></option>
                                            <?php } ?>
                                        </select>
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Slug', 'cdb-grafica' ); ?></label><br />
                                        <input type="text" name="slug" class="regular-text" required />
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Etiqueta', 'cdb-grafica' ); ?></label><br />
                                        <input type="text" name="label" class="regular-text" required />
                                    </p>
                                    <p>
                                        <label><?php esc_html_e( 'Descripción', 'cdb-grafica' ); ?></label><br />
                                        <textarea name="descripcion" class="large-text" rows="3"></textarea>
                                    </p>
                                    <?php submit_button( __( 'Guardar', 'cdb-grafica' ) ); ?>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td colspan="5"><a class="button" href="?page=cdb_modificar_criterios&tab=bar&add=1"><?php esc_html_e( 'Añadir Criterio', 'cdb-grafica' ); ?></a></td>
                    </tr>
                </tbody>
            </table>
            <?php
            // TODO: implementar edición de criterios existentes.
            // TODO: implementar creación de nuevos criterios.
            ?>
        <?php } ?>
    </div>
    <?php
}

// Definir grupos de criterios reales
function cdb_grafica_get_criterios_organizados($grafica_tipo) {
    if ($grafica_tipo === 'bar') {
        $criterios = cdb_get_criterios_bar();
        $grupos    = [];
        foreach ( $criterios as $grupo => $items ) {
            $labels = [];
            foreach ( $items as $info ) {
                $labels[] = $info['label'];
            }
            $grupos[ $grupo ] = $labels;
        }
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
