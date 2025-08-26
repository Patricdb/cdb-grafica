<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the plugin's admin menu and submenus.
 */
function cdb_grafica_register_admin_menu() {
    add_menu_page(
        __( 'CdB Gráfica', 'cdb-grafica' ),
        __( 'CdB Gráfica', 'cdb-grafica' ),
        'manage_options',
        'cdb_grafica_menu',
        'cdb_grafica_dashboard_page',
        'dashicons-chart-bar',
        25
    );

    add_submenu_page(
        'cdb_grafica_menu',
        __( 'Modificar Criterios', 'cdb-grafica' ),
        __( 'Modificar Criterios', 'cdb-grafica' ),
        'manage_options',
        'cdb_modificar_criterios',
        'cdb_grafica_modificar_criterios_page'
    );

    add_submenu_page(
        'cdb_grafica_menu',
        __( 'Configurar Colores', 'cdb-grafica' ),
        __( 'Configurar Colores', 'cdb-grafica' ),
        'manage_options',
        'cdb_modificar_colores',
        'cdb_grafica_colores_page'
    );
}
add_action( 'admin_menu', 'cdb_grafica_register_admin_menu' );
