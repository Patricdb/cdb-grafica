<?php
/**
 * Plugin Name: CDB Gráfica
 * Description: Plugin para manejar gráficas (bar y empleado) y sus tablas.
 * Version: 1.0.0
 * Author: CdB_
 * Author URI: https://proyectocdb.es
 * Text Domain: cdb-grafica
 */

// Versión actual del plugin
define('CDB_GRAFICA_VERSION', '1.0.0');

// Hooks de activación para crear tablas
register_activation_hook(__FILE__, 'grafica_bar_create_table');
register_activation_hook(__FILE__, 'grafica_empleado_create_table');
require_once plugin_dir_path(__FILE__) . 'admin/modificar_criterios.php';
require_once plugin_dir_path(__FILE__) . 'admin/modificar_colores.php';


// Requerir archivos de CPT y gráficas
require_once __DIR__ . '/inc/grafica-bar.php';
require_once __DIR__ . '/inc/grafica-empleado.php';
// require_once __DIR__ . '/inc/shared-functions.php';


// Función global para calcular el promedio de un grupo de campos.
if (!function_exists('calcular_promedio')) {
    function calcular_promedio($campos) {
        $valores = [];
        foreach ($campos as $campo) {
            $valores[] = get_field($campo);
        }
        return array_sum($valores) / count($campos);
    }
}

// Asignar permisos
add_action('init', function() {
    // Asegúrate de que el rol administrador tenga los permisos
    $admin_role = get_role('administrator');
    if ($admin_role && !$admin_role->has_cap('submit_grafica_bar')) {
        $admin_role->add_cap('submit_grafica_bar');
    }
    if ($admin_role && !$admin_role->has_cap('submit_grafica_empleado')) {
        $admin_role->add_cap('submit_grafica_empleado');
    }
});

// Manejar envíos de formularios
add_action('init', 'handle_grafica_bar_submission');
add_action('init', 'handle_grafica_empleado_submission');

add_action('wp_ajax_obtener_grafica', 'cdb_obtener_grafica');
add_action('wp_ajax_nopriv_obtener_grafica', 'cdb_obtener_grafica');

function cdb_obtener_grafica() {
    // Validar el parámetro "type"
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

    if (!$type || !in_array($type, ['bar', 'empleado'])) {
        wp_send_json_error(['message' => 'Tipo inválido.']);
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_' . $type . '_results';

    // Consultar los datos
    $query   = $wpdb->prepare("SELECT * FROM {$table_name}");
    $results = $wpdb->get_results($query, ARRAY_A);

    if (empty($results)) {
        wp_send_json_error(['message' => 'No se encontraron datos.']);
        return;
    }

    // Enviar los datos en formato JSON
    wp_send_json_success(['data' => $results]);
}
