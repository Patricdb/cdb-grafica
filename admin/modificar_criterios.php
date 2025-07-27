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
        <p><?php esc_html_e( 'Edita los nombres visibles y descripciones de los criterios. Los slugs internos no pueden modificarse.', 'cdb-grafica' ); ?></p>
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



function cdb_grafica_default_criterios($tipo) {
    if ($tipo === 'bar') {
        return [
            [ 'grupo' => 'ALB (Ambiente Laboral Básico)', 'criterios' => [
                ['slug'=>'bienvenida','label'=>'bienvenida','descripcion'=>'','orden'=>1],
                ['slug'=>'companerismo','label'=>'companerismo','descripcion'=>'','orden'=>2],
                ['slug'=>'clima_positivo','label'=>'clima_positivo','descripcion'=>'','orden'=>3],
                ['slug'=>'resolucion_de_conflictos','label'=>'resolucion_de_conflictos','descripcion'=>'','orden'=>4],
                ['slug'=>'cooperacion','label'=>'cooperacion','descripcion'=>'','orden'=>5],
                ['slug'=>'relacion_superiores','label'=>'relacion_superiores','descripcion'=>'','orden'=>6],
                ['slug'=>'inclusion','label'=>'inclusion','descripcion'=>'','orden'=>7],
                ['slug'=>'comunicacion','label'=>'comunicacion','descripcion'=>'','orden'=>8],
                ['slug'=>'reconocimiento','label'=>'reconocimiento','descripcion'=>'','orden'=>9],
                ['slug'=>'celebracion_logros','label'=>'celebracion_logros','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'EDT (Estructura del Trabajo)', 'criterios' => [
                ['slug'=>'tamano','label'=>'tamano','descripcion'=>'','orden'=>1],
                ['slug'=>'cooperacion_edt','label'=>'cooperacion_edt','descripcion'=>'','orden'=>2],
                ['slug'=>'comunicacion_edt','label'=>'comunicacion_edt','descripcion'=>'','orden'=>3],
                ['slug'=>'roles_definidos','label'=>'roles_definidos','descripcion'=>'','orden'=>4],
                ['slug'=>'actitud','label'=>'actitud','descripcion'=>'','orden'=>5],
                ['slug'=>'equilibrio','label'=>'equilibrio','descripcion'=>'','orden'=>6],
                ['slug'=>'socializacion','label'=>'socializacion','descripcion'=>'','orden'=>7],
                ['slug'=>'diversidad','label'=>'diversidad','descripcion'=>'','orden'=>8],
                ['slug'=>'compromiso','label'=>'compromiso','descripcion'=>'','orden'=>9],
                ['slug'=>'sinergia','label'=>'sinergia','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'DPF (Desarrollo Profesional)', 'criterios' => [
                ['slug'=>'formacion','label'=>'formacion','descripcion'=>'','orden'=>1],
                ['slug'=>'habilidades','label'=>'habilidades','descripcion'=>'','orden'=>2],
                ['slug'=>'cursos','label'=>'cursos','descripcion'=>'','orden'=>3],
                ['slug'=>'promociones','label'=>'promociones','descripcion'=>'','orden'=>4],
                ['slug'=>'eventos','label'=>'eventos','descripcion'=>'','orden'=>5],
                ['slug'=>'networking','label'=>'networking','descripcion'=>'','orden'=>6],
                ['slug'=>'creatividad','label'=>'creatividad','descripcion'=>'','orden'=>7],
                ['slug'=>'mentor','label'=>'mentor','descripcion'=>'','orden'=>8],
                ['slug'=>'innovacion','label'=>'innovacion','descripcion'=>'','orden'=>9],
                ['slug'=>'retos','label'=>'retos','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'CLB (Condiciones Laborales)', 'criterios' => [
                ['slug'=>'turnos_justos','label'=>'turnos_justos','descripcion'=>'','orden'=>1],
                ['slug'=>'descansos','label'=>'descansos','descripcion'=>'','orden'=>2],
                ['slug'=>'normativas','label'=>'normativas','descripcion'=>'','orden'=>3],
                ['slug'=>'flexibilidad','label'=>'flexibilidad','descripcion'=>'','orden'=>4],
                ['slug'=>'dias_libres','label'=>'dias_libres','descripcion'=>'','orden'=>5],
                ['slug'=>'festivos_remunerados','label'=>'festivos_remunerados','descripcion'=>'','orden'=>6],
                ['slug'=>'incentivos','label'=>'incentivos','descripcion'=>'','orden'=>7],
                ['slug'=>'seguro_medico','label'=>'seguro_medico','descripcion'=>'','orden'=>8],
                ['slug'=>'uniformes','label'=>'uniformes','descripcion'=>'','orden'=>9],
                ['slug'=>'estabilidad','label'=>'estabilidad','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'AEC (Aspectos Económicos)', 'criterios' => [
                ['slug'=>'salario','label'=>'salario','descripcion'=>'','orden'=>1],
                ['slug'=>'propinas','label'=>'propinas','descripcion'=>'','orden'=>2],
                ['slug'=>'bonos','label'=>'bonos','descripcion'=>'','orden'=>3],
                ['slug'=>'incrementos','label'=>'incrementos','descripcion'=>'','orden'=>4],
                ['slug'=>'beneficios','label'=>'beneficios','descripcion'=>'','orden'=>5],
                ['slug'=>'extras_remuneradas','label'=>'extras_remuneradas','descripcion'=>'','orden'=>6],
                ['slug'=>'comisiones','label'=>'comisiones','descripcion'=>'','orden'=>7],
                ['slug'=>'incentivos_festivos','label'=>'incentivos_festivos','descripcion'=>'','orden'=>8],
                ['slug'=>'sostenibilidad_economica','label'=>'sostenibilidad_economica','descripcion'=>'','orden'=>9],
                ['slug'=>'cumplimiento','label'=>'cumplimiento','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'EDG (Efectividad del Grupo)', 'criterios' => [
                ['slug'=>'liderazgo','label'=>'liderazgo','descripcion'=>'','orden'=>1],
                ['slug'=>'justicia','label'=>'justicia','descripcion'=>'','orden'=>2],
                ['slug'=>'motivacion','label'=>'motivacion','descripcion'=>'','orden'=>3],
                ['slug'=>'claridad','label'=>'claridad','descripcion'=>'','orden'=>4],
                ['slug'=>'feedback','label'=>'feedback','descripcion'=>'','orden'=>5],
                ['slug'=>'escucha_activa','label'=>'escucha_activa','descripcion'=>'','orden'=>6],
                ['slug'=>'planificacion','label'=>'planificacion','descripcion'=>'','orden'=>7],
                ['slug'=>'delegacion','label'=>'delegacion','descripcion'=>'','orden'=>8],
                ['slug'=>'participacion','label'=>'participacion','descripcion'=>'','orden'=>9],
                ['slug'=>'resolucion_rapida','label'=>'resolucion_rapida','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'TBC (Trabajo con Clientes)', 'criterios' => [
                ['slug'=>'volumen','label'=>'volumen','descripcion'=>'','orden'=>1],
                ['slug'=>'clientela','label'=>'clientela','descripcion'=>'','orden'=>2],
                ['slug'=>'estilo','label'=>'estilo','descripcion'=>'','orden'=>3],
                ['slug'=>'menu','label'=>'menu','descripcion'=>'','orden'=>4],
                ['slug'=>'reputacion','label'=>'reputacion','descripcion'=>'','orden'=>5],
                ['slug'=>'organizacion','label'=>'organizacion','descripcion'=>'','orden'=>6],
                ['slug'=>'horarios_pico','label'=>'horarios_pico','descripcion'=>'','orden'=>7],
                ['slug'=>'tematica','label'=>'tematica','descripcion'=>'','orden'=>8],
                ['slug'=>'exigencia','label'=>'exigencia','descripcion'=>'','orden'=>9],
                ['slug'=>'adaptacion_cultural','label'=>'adaptacion_cultural','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'SGD (Seguridad en el Trabajo)', 'criterios' => [
                ['slug'=>'limpieza','label'=>'limpieza','descripcion'=>'','orden'=>1],
                ['slug'=>'botiquin','label'=>'botiquin','descripcion'=>'','orden'=>2],
                ['slug'=>'normativas_claras','label'=>'normativas_claras','descripcion'=>'','orden'=>3],
                ['slug'=>'ergonomia','label'=>'ergonomia','descripcion'=>'','orden'=>4],
                ['slug'=>'prevencion','label'=>'prevencion','descripcion'=>'','orden'=>5],
                ['slug'=>'emergencias','label'=>'emergencias','descripcion'=>'','orden'=>6],
                ['slug'=>'iluminacion','label'=>'iluminacion','descripcion'=>'','orden'=>7],
                ['slug'=>'climatizacion','label'=>'climatizacion','descripcion'=>'','orden'=>8],
                ['slug'=>'senalizacion','label'=>'senalizacion','descripcion'=>'','orden'=>9],
                ['slug'=>'espacio_seguro','label'=>'espacio_seguro','descripcion'=>'','orden'=>10],
            ]],
        ];
    } elseif ($tipo === 'empleado') {
        return [
            [ 'grupo' => 'LID (Liderazgo)', 'criterios' => [
                ['slug'=>'motivacion','label'=>'motivacion','descripcion'=>'','orden'=>1],
                ['slug'=>'resolucion','label'=>'resolucion','descripcion'=>'','orden'=>2],
                ['slug'=>'organizacion','label'=>'organizacion','descripcion'=>'','orden'=>3],
                ['slug'=>'delegacion','label'=>'delegacion','descripcion'=>'','orden'=>4],
                ['slug'=>'decision','label'=>'decision','descripcion'=>'','orden'=>5],
                ['slug'=>'direccion','label'=>'direccion','descripcion'=>'','orden'=>6],
                ['slug'=>'evaluacion','label'=>'evaluacion','descripcion'=>'','orden'=>7],
                ['slug'=>'planificacion','label'=>'planificacion','descripcion'=>'','orden'=>8],
                ['slug'=>'control','label'=>'control','descripcion'=>'','orden'=>9],
                ['slug'=>'empatia','label'=>'empatia','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'CLI (Cliente)', 'criterios' => [
                ['slug'=>'cordialidad','label'=>'cordialidad','descripcion'=>'','orden'=>1],
                ['slug'=>'escucha','label'=>'escucha','descripcion'=>'','orden'=>2],
                ['slug'=>'resolutivo','label'=>'resolutivo','descripcion'=>'','orden'=>3],
                ['slug'=>'memoria','label'=>'memoria','descripcion'=>'','orden'=>4],
                ['slug'=>'empatia_cli','label'=>'empatia_cli','descripcion'=>'','orden'=>5],
                ['slug'=>'satisfaccion','label'=>'satisfaccion','descripcion'=>'','orden'=>6],
                ['slug'=>'claridad','label'=>'claridad','descripcion'=>'','orden'=>7],
                ['slug'=>'gestion','label'=>'gestion','descripcion'=>'','orden'=>8],
                ['slug'=>'adaptacion','label'=>'adaptacion','descripcion'=>'','orden'=>9],
                ['slug'=>'fidelidad','label'=>'fidelidad','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'TEC (Técnica)', 'criterios' => [
                ['slug'=>'menu','label'=>'menu','descripcion'=>'','orden'=>1],
                ['slug'=>'ingredientes','label'=>'ingredientes','descripcion'=>'','orden'=>2],
                ['slug'=>'cocteleria','label'=>'cocteleria','descripcion'=>'','orden'=>3],
                ['slug'=>'vinos','label'=>'vinos','descripcion'=>'','orden'=>4],
                ['slug'=>'cafeteria','label'=>'cafeteria','descripcion'=>'','orden'=>5],
                ['slug'=>'recomendacion','label'=>'recomendacion','descripcion'=>'','orden'=>6],
                ['slug'=>'prueba','label'=>'prueba','descripcion'=>'','orden'=>7],
                ['slug'=>'cata','label'=>'cata','descripcion'=>'','orden'=>8],
                ['slug'=>'protocolo','label'=>'protocolo','descripcion'=>'','orden'=>9],
                ['slug'=>'presentacion','label'=>'presentacion','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'RAP (Rapidez)', 'criterios' => [
                ['slug'=>'agilidad','label'=>'agilidad','descripcion'=>'','orden'=>1],
                ['slug'=>'velocidad','label'=>'velocidad','descripcion'=>'','orden'=>2],
                ['slug'=>'montaje','label'=>'montaje','descripcion'=>'','orden'=>3],
                ['slug'=>'reaccion','label'=>'reaccion','descripcion'=>'','orden'=>4],
                ['slug'=>'prevision','label'=>'prevision','descripcion'=>'','orden'=>5],
                ['slug'=>'sincronizacion','label'=>'sincronizacion','descripcion'=>'','orden'=>6],
                ['slug'=>'ordenacion','label'=>'ordenacion','descripcion'=>'','orden'=>7],
                ['slug'=>'imprevistos','label'=>'imprevistos','descripcion'=>'','orden'=>8],
                ['slug'=>'carga','label'=>'carga','descripcion'=>'','orden'=>9],
                ['slug'=>'rendimiento','label'=>'rendimiento','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'ORD (Orden)', 'criterios' => [
                ['slug'=>'limpieza','label'=>'limpieza','descripcion'=>'','orden'=>1],
                ['slug'=>'higiene','label'=>'higiene','descripcion'=>'','orden'=>2],
                ['slug'=>'almacenaje','label'=>'almacenaje','descripcion'=>'','orden'=>3],
                ['slug'=>'productos','label'=>'productos','descripcion'=>'','orden'=>4],
                ['slug'=>'desinfeccion','label'=>'desinfeccion','descripcion'=>'','orden'=>5],
                ['slug'=>'reabastecimiento','label'=>'reabastecimiento','descripcion'=>'','orden'=>6],
                ['slug'=>'clasificacion','label'=>'clasificacion','descripcion'=>'','orden'=>7],
                ['slug'=>'cuidado','label'=>'cuidado','descripcion'=>'','orden'=>8],
                ['slug'=>'reciclaje','label'=>'reciclaje','descripcion'=>'','orden'=>9],
                ['slug'=>'preparacion','label'=>'preparacion','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'EQU (Equipo)', 'criterios' => [
                ['slug'=>'cooperacion','label'=>'cooperacion','descripcion'=>'','orden'=>1],
                ['slug'=>'interaccion','label'=>'interaccion','descripcion'=>'','orden'=>2],
                ['slug'=>'soporte','label'=>'soporte','descripcion'=>'','orden'=>3],
                ['slug'=>'instruccion','label'=>'instruccion','descripcion'=>'','orden'=>4],
                ['slug'=>'versatilidad','label'=>'versatilidad','descripcion'=>'','orden'=>5],
                ['slug'=>'conciliacion','label'=>'conciliacion','descripcion'=>'','orden'=>6],
                ['slug'=>'fluidez','label'=>'fluidez','descripcion'=>'','orden'=>7],
                ['slug'=>'proactividad','label'=>'proactividad','descripcion'=>'','orden'=>8],
                ['slug'=>'optimismo','label'=>'optimismo','descripcion'=>'','orden'=>9],
                ['slug'=>'adaptabilidad','label'=>'adaptabilidad','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'CRE (Creatividad)', 'criterios' => [
                ['slug'=>'originalidad','label'=>'originalidad','descripcion'=>'','orden'=>1],
                ['slug'=>'propuestas','label'=>'propuestas','descripcion'=>'','orden'=>2],
                ['slug'=>'variedad','label'=>'variedad','descripcion'=>'','orden'=>3],
                ['slug'=>'estilo','label'=>'estilo','descripcion'=>'','orden'=>4],
                ['slug'=>'eventos','label'=>'eventos','descripcion'=>'','orden'=>5],
                ['slug'=>'promocion','label'=>'promocion','descripcion'=>'','orden'=>6],
                ['slug'=>'carta','label'=>'carta','descripcion'=>'','orden'=>7],
                ['slug'=>'sorpresa','label'=>'sorpresa','descripcion'=>'','orden'=>8],
                ['slug'=>'diferencia','label'=>'diferencia','descripcion'=>'','orden'=>9],
                ['slug'=>'innovacion','label'=>'innovacion','descripcion'=>'','orden'=>10],
            ]],
            [ 'grupo' => 'PRO (Profesionalismo)', 'criterios' => [
                ['slug'=>'puntualidad','label'=>'puntualidad','descripcion'=>'','orden'=>1],
                ['slug'=>'serenidad','label'=>'serenidad','descripcion'=>'','orden'=>2],
                ['slug'=>'educacion','label'=>'educacion','descripcion'=>'','orden'=>3],
                ['slug'=>'apariencia','label'=>'apariencia','descripcion'=>'','orden'=>4],
                ['slug'=>'integridad','label'=>'integridad','descripcion'=>'','orden'=>5],
                ['slug'=>'feedback','label'=>'feedback','descripcion'=>'','orden'=>6],
                ['slug'=>'compromiso','label'=>'compromiso','descripcion'=>'','orden'=>7],
                ['slug'=>'crecimiento','label'=>'crecimiento','descripcion'=>'','orden'=>8],
                ['slug'=>'disciplina','label'=>'disciplina','descripcion'=>'','orden'=>9],
                ['slug'=>'vocacion','label'=>'vocacion','descripcion'=>'','orden'=>10],
            ]],
        ];
    }
    return [];
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
