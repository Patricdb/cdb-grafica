<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the plugin's admin menu and submenus.
 */
function cdb_grafica_register_admin_menu() {
    $capability = apply_filters( 'cdb_grafica_admin_capability', 'manage_options' );

    add_menu_page(
        __( 'CdB Gráfica', 'cdb-grafica' ),
        __( 'CdB Gráfica', 'cdb-grafica' ),
        $capability,
        'cdb_grafica_menu',
        'cdb_grafica_dashboard_page',
        'dashicons-chart-bar',
        5
    );

    add_submenu_page(
        'cdb_grafica_menu',
        __( 'Modificar Criterios', 'cdb-grafica' ),
        __( 'Modificar Criterios', 'cdb-grafica' ),
        $capability,
        'cdb_modificar_criterios',
        'cdb_grafica_modificar_criterios_page'
    );

    add_submenu_page(
        'cdb_grafica_menu',
        __( 'Configurar Colores', 'cdb-grafica' ),
        __( 'Configurar Colores', 'cdb-grafica' ),
        $capability,
        'cdb_modificar_colores',
        'cdb_grafica_colores_page'
    );
}
add_action( 'admin_menu', 'cdb_grafica_register_admin_menu' );
