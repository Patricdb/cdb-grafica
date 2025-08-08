<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Devuelve la fecha/hora (Y-m-d H:i:s) de la última valoración para un empleado.
 *
 * @param int $empleado_id ID del empleado.
 * @return string|null Fecha/hora de la última valoración o null si no hay registros.
 */
function cdb_grafica_get_last_rating_datetime( int $empleado_id ): ?string {
    if ( $empleado_id <= 0 ) {
        return null;
    }

    $cache_key = "cdb_grafica_last_rating_{$empleado_id}";
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return '' === $cached ? null : $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    $sql_parts = apply_filters( 'cdb_grafica_last_rating_args', [
        'where' => '',
        'order' => '',
    ], $empleado_id );

    $sql = "SELECT MAX(created_at) FROM {$table} WHERE post_id = %d AND user_role IS NOT NULL";
    if ( ! empty( $sql_parts['where'] ) ) {
        $sql .= " AND {$sql_parts['where']}";
    }
    if ( ! empty( $sql_parts['order'] ) ) {
        $sql .= " {$sql_parts['order']}";
    }

    $datetime = $wpdb->get_var( $wpdb->prepare( $sql, $empleado_id ) );

    set_transient( $cache_key, $datetime ? $datetime : '', 600 );

    return $datetime ?: null;
}

/**
 * Devuelve totales de puntuación por rol y detalle opcional por grupos.
 *
 * @param int   $empleado_id ID del empleado.
 * @param array $args        Opciones de consulta: with_detail, bypass_cache.
 * @return array             Puntuaciones por rol y detalle si se solicita.
 */
function cdb_grafica_get_scores_by_role( int $empleado_id, array $args = [] ): array {
    $defaults = [
        'with_detail'  => false,
        'bypass_cache' => false,
    ];
    $args = wp_parse_args( $args, $defaults );

    $resultado_base = [ 'empleado' => null, 'empleador' => null, 'tutor' => null ];

    if ( $empleado_id <= 0 ) {
        if ( $args['with_detail'] ) {
            $resultado_base['detalle'] = [];
        }
        return $resultado_base;
    }

    $transient_key = "cdb_grafica_role_scores_{$empleado_id}";
    if ( ! $args['bypass_cache'] ) {
        $cached = get_transient( $transient_key );
        if ( false !== $cached ) {
            return $cached;
        }
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    $criterios = cdb_get_criterios_empleado();
    $grupos    = [];
    $columnas  = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $grupos[ $grupo_nombre ] = array_keys( $campos );
        foreach ( $campos as $campo_slug => $info ) {
            $columnas[] = $campo_slug;
        }
    }
    $columnas   = array_unique( $columnas );
    $select_cols = implode( ', ', array_merge( $columnas, [ 'created_at', 'user_role' ] ) );

    $roles     = [ 'empleado', 'empleador', 'tutor' ];
    $resultado = $resultado_base;
    $detalle   = [];

    foreach ( $roles as $rol ) {
        $sql_parts = apply_filters( 'cdb_grafica_scores_args', [
            'where' => '',
            'order' => '',
        ], $rol, $empleado_id );

        $sql = "SELECT {$select_cols} FROM {$table} WHERE post_id = %d AND user_role = %s";
        if ( ! empty( $sql_parts['where'] ) ) {
            $sql .= " AND {$sql_parts['where']}";
        }
        if ( ! empty( $sql_parts['order'] ) ) {
            $sql .= " {$sql_parts['order']}";
        }

        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $empleado_id, $rol ) );
        if ( empty( $rows ) ) {
            $resultado[ $rol ] = null;
            if ( $args['with_detail'] ) {
                $detalle[ $rol ] = [ 'grupos' => [], 'total' => null ];
            }
            continue;
        }

        $grupos_data = [];
        foreach ( $rows as $row ) {
            foreach ( $grupos as $grupo_nombre => $campos ) {
                if ( ! isset( $grupos_data[ $grupo_nombre ] ) ) {
                    $grupos_data[ $grupo_nombre ] = [ 'suma' => 0, 'cuenta' => 0 ];
                }
                foreach ( $campos as $campo ) {
                    if ( isset( $row->$campo ) && $row->$campo > 0 ) {
                        $grupos_data[ $grupo_nombre ]['suma']   += floatval( $row->$campo );
                        $grupos_data[ $grupo_nombre ]['cuenta'] += 1;
                    }
                }
            }
        }

        $promedios = [];
        foreach ( $grupos as $grupo_nombre => $campos ) {
            $suma   = $grupos_data[ $grupo_nombre ]['suma'] ?? 0;
            $cuenta = $grupos_data[ $grupo_nombre ]['cuenta'] ?? 0;
            $avg    = $cuenta > 0 ? round( $suma / $cuenta, 1 ) : 0;
            $codigo = strtok( $grupo_nombre, ' ' );
            $promedios[ $codigo ] = $avg;
        }

        $total = round( array_sum( $promedios ), 1 );
        $resultado[ $rol ] = $total;

        if ( $args['with_detail'] ) {
            $detalle[ $rol ] = [ 'grupos' => $promedios, 'total' => $total ];
        }
    }

    if ( $args['with_detail'] ) {
        $resultado['detalle'] = $detalle;
    }

    $ttl = intval( apply_filters( 'cdb_grafica_scores_ttl', 600, $empleado_id, $args ) );
    if ( ! $args['bypass_cache'] ) {
        set_transient( $transient_key, $resultado, $ttl );
    }

    return $resultado;
}

