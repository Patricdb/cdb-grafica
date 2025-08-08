<?php
/**
 * Funciones públicas reutilizables para cdb-grafica.
 *
 * Estas funciones exponen datos en bruto de la tabla grafica_empleado_results
 * para que otros plugins puedan reutilizarlos. El formateo de los datos se
 * deja a quienes consuman estas funciones.
 *
 * @package cdb-grafica
 */

// Evitar acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtiene la fecha/hora de la última valoración para un empleado.
 *
 * Devuelve una cadena en formato "Y-m-d H:i:s" o null si no existen registros.
 * El resultado se almacena en un transient durante 10 minutos.
 *
 * @param int $empleado_id ID del post del empleado.
 * @return string|null Fecha/hora de la última valoración o null si no hay.
 */
function cdb_grafica_get_last_rating_datetime( int $empleado_id ): ?string {
    $cache_key = 'cdb_grafica_last_rating_' . $empleado_id;
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return $cached ? $cached : null;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    // Permite ajustar condiciones adicionales mediante filtro.
    $args = apply_filters( 'cdb_grafica_last_rating_args', [
        'where'  => '',
        'params' => [],
    ], $empleado_id );

    $where   = 'post_id = %d AND user_role IS NOT NULL';
    $params  = [ $empleado_id ];
    if ( ! empty( $args['where'] ) ) {
        $where .= ' ' . $args['where'];
    }
    if ( ! empty( $args['params'] ) ) {
        $params = array_merge( $params, (array) $args['params'] );
    }

    $query = $wpdb->prepare( "SELECT MAX(created_at) FROM {$table} WHERE {$where}", $params );
    $datetime = $wpdb->get_var( $query );

    $datetime = $datetime ? $datetime : null;

    set_transient( $cache_key, $datetime, 10 * MINUTE_IN_SECONDS );

    return $datetime;
}

/**
 * Calcula la puntuación total de un empleado.
 *
 * Basado en la lógica de renderizar_bloque_grafica_empleado, suma los promedios
 * de cada grupo de criterios ignorando valores 0. El resultado se almacena en
 * un transient durante 10 minutos. Devuelve 0.0 si no existen registros.
 *
 * @param int   $empleado_id ID del post del empleado.
 * @param array $args        Argumentos adicionales para ajustar la consulta.
 * @return float Puntuación total con un decimal.
 */
function cdb_grafica_get_total_score( int $empleado_id, array $args = [] ): float {
    $cache_key = 'cdb_grafica_total_score_' . $empleado_id;
    $cached    = get_transient( $cache_key );

    if ( false !== $cached ) {
        return (float) $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    $defaults = [
        'where'  => '',
        'params' => [],
    ];
    $args = apply_filters( 'cdb_grafica_total_score_args', wp_parse_args( $args, $defaults ), $empleado_id );

    $where  = 'post_id = %d AND user_role IS NOT NULL';
    $params = [ $empleado_id ];
    if ( ! empty( $args['where'] ) ) {
        $where .= ' ' . $args['where'];
    }
    if ( ! empty( $args['params'] ) ) {
        $params = array_merge( $params, (array) $args['params'] );
    }

    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where}", $params ) );

    if ( empty( $results ) ) {
        set_transient( $cache_key, 0.0, 10 * MINUTE_IN_SECONDS );
        return 0.0;
    }

    $criterios = cdb_get_criterios_empleado();
    $grupos    = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $grupos[ $grupo_nombre ] = array_keys( $campos );
    }

    $promedios_globales = [];
    foreach ( $grupos as $campos ) {
        $suma   = 0;
        $cuenta = 0;
        foreach ( $results as $row ) {
            foreach ( $campos as $campo ) {
                if ( isset( $row->$campo ) && 0 != $row->$campo ) {
                    $suma   += (float) $row->$campo;
                    $cuenta += 1;
                }
            }
        }
        $promedios_globales[] = $cuenta > 0 ? round( $suma / $cuenta, 1 ) : 0;
    }

    $total = round( array_sum( $promedios_globales ), 1 );

    set_transient( $cache_key, $total, 10 * MINUTE_IN_SECONDS );

    return $total;
}

