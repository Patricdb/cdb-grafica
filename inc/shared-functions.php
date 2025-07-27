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

/**
 * Devuelve la configuración de criterios por defecto para el tipo
 * indicado. Esta información es utilizada tanto en el frontend como
 * en las páginas de administración.
 *
 * @param string $tipo Tipo de gráfica ('bar' o 'empleado').
 * @return array Arreglo de grupos y criterios.
 */
function cdb_grafica_default_criterios( $tipo ) {
    if ( 'bar' === $tipo ) {
        return [
            [
                'grupo'    => __( 'Ambiente y Desarrollo', 'cdb-grafica' ),
                'criterios' => [
                    [ 'slug' => 'relacion_superiores', 'label' => 'relacion_superiores', 'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'motivacion',          'label' => 'motivacion',          'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'bienvenida',          'label' => 'bienvenida',          'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'formacion',           'label' => 'formacion',           'descripcion' => '', 'orden' => 4 ],
                ],
            ],
            [
                'grupo'    => __( 'Condiciones del Bar', 'cdb-grafica' ),
                'criterios' => [
                    [ 'slug' => 'salario',        'label' => 'salario',        'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'turnos_justos',  'label' => 'turnos_justos',  'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'espacio_seguro', 'label' => 'espacio_seguro', 'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'reputacion',     'label' => 'reputacion',     'descripcion' => '', 'orden' => 4 ],
                ],
            ],
        ];
    } elseif ( 'empleado' === $tipo ) {
        return [
            [
                'grupo'    => __( 'Desempe\u00f1o', 'cdb-grafica' ),
                'criterios' => [
                    [ 'slug' => 'direccion',    'label' => 'direccion',    'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'camarero',     'label' => 'camarero',     'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'venta',        'label' => 'venta',        'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'satisfaccion', 'label' => 'satisfaccion', 'descripcion' => '', 'orden' => 4 ],
                ],
            ],
            [
                'grupo'    => __( 'Trabajo en Equipo', 'cdb-grafica' ),
                'criterios' => [
                    [ 'slug' => 'cooperacion',  'label' => 'cooperacion',  'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'orden',        'label' => 'orden',        'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'cocina_local', 'label' => 'cocina_local', 'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'cocinero',     'label' => 'cocinero',     'descripcion' => '', 'orden' => 4 ],
                ],
            ],
        ];
    }
    return [];
}
