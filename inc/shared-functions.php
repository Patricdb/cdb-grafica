<?php
/**
 * Funciones compartidas para el plugin cdb-grafica
 *
 * Este archivo centraliza la inyección automática del formulario y la gráfica
 * al final del contenido de los posts de los CPT "bar" y "empleado".
 *
 * @package cdb-grafica
 */

// Evitamos el acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Inyecta el formulario de calificaciones y la gráfica correspondiente
 * al final del contenido de los CPT "bar" y "empleado".
 *
 * En este enfoque se utiliza exclusivamente el shortcode para mostrar la gráfica,
 * dejando inactivo el bloque oficial (se mantiene registrado para usos futuros).
 *
 * @param string $content Contenido original del post.
 * @return string Contenido modificado.
 */
function cdb_inyectar_formulario_y_grafica( $content ) {
    // Verificamos que se trate de una vista singular de "bar" o "empleado".
    if ( is_singular( array( 'bar', 'empleado' ) ) ) {
        $post_type = get_post_type();

        if ( 'bar' === $post_type ) {
            // CPT Bar: inyecta el formulario mediante el shortcode.
            $formulario = do_shortcode( '[grafica_bar_form]' );
            // Se deja inactivo el bloque oficial.
            $grafica = '';
        } elseif ( 'empleado' === $post_type ) {
            // CPT Empleado: inyecta el formulario mediante el shortcode.
            $formulario = do_shortcode( '[grafica_empleado_form]' );
            // Se deja inactivo el bloque oficial.
            $grafica = '';
        } else {
            return $content;
        }

        // Se concatena el formulario (y, en este caso, no se inyecta la gráfica del bloque).
        $content .= $formulario . $grafica;
    }

    return $content;
}
add_filter( 'the_content', 'cdb_inyectar_formulario_y_grafica' );

/**
 * Obtiene los criterios organizados por grupo para el tipo de gráfica
 * indicado. Devuelve una matriz donde cada grupo contiene los slugs de
 * los criterios junto con su etiqueta y descripción.
 *
 * @param string $grafica_tipo Tipo de gráfica ('bar' o 'empleado').
 * @return array[] Array asociativo de grupos y criterios.
 */
function cdb_grafica_get_criterios_organizados( $grafica_tipo ) {
    $option   = 'cdb_grafica_criterios_' . $grafica_tipo;
    $defaults = cdb_grafica_default_criterios( $grafica_tipo );
    $data     = get_option( $option, $defaults );

    $grupos = [];
    foreach ( $data as $grupo ) {
        $lista = [];
        usort( $grupo['criterios'], function ( $a, $b ) {
            return intval( $a['orden'] ) - intval( $b['orden'] );
        } );
        foreach ( $grupo['criterios'] as $crit ) {
            $lista[ $crit['slug'] ] = [
                'label'       => $crit['label'],
                'descripcion' => $crit['descripcion'],
            ];
        }
        $grupos[ $grupo['grupo'] ] = $lista;
    }

    return $grupos;
}
