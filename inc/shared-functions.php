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
                'grupo'    => 'ALB (Ambiente Laboral Básico)',
                'criterios' => [
                    [ 'slug' => 'bienvenida',            'label' => 'bienvenida',            'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'companerismo',          'label' => 'companerismo',          'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'clima_positivo',        'label' => 'clima_positivo',        'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'resolucion_de_conflictos','label' => 'resolucion_de_conflictos','descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'cooperacion',           'label' => 'cooperacion',           'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'relacion_superiores',   'label' => 'relacion_superiores',   'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'inclusion',             'label' => 'inclusion',             'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'comunicacion',          'label' => 'comunicacion',          'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'reconocimiento',        'label' => 'reconocimiento',        'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'celebracion_logros',    'label' => 'celebracion_logros',    'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'EDT (Estructura del Trabajo)',
                'criterios' => [
                    [ 'slug' => 'tamano',            'label' => 'tamano',            'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'cooperacion_edt',  'label' => 'cooperacion_edt',  'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'comunicacion_edt', 'label' => 'comunicacion_edt', 'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'roles_definidos',  'label' => 'roles_definidos',  'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'actitud',          'label' => 'actitud',          'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'equilibrio',       'label' => 'equilibrio',       'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'socializacion',    'label' => 'socializacion',    'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'diversidad',       'label' => 'diversidad',       'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'compromiso',       'label' => 'compromiso',       'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'sinergia',         'label' => 'sinergia',         'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'DPF (Desarrollo Profesional)',
                'criterios' => [
                    [ 'slug' => 'formacion',   'label' => 'formacion',   'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'habilidades', 'label' => 'habilidades', 'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'cursos',      'label' => 'cursos',      'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'promociones', 'label' => 'promociones', 'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'eventos',     'label' => 'eventos',     'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'networking',  'label' => 'networking',  'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'creatividad', 'label' => 'creatividad', 'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'mentor',      'label' => 'mentor',      'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'innovacion',  'label' => 'innovacion',  'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'retos',       'label' => 'retos',       'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'CLB (Condiciones Laborales)',
                'criterios' => [
                    [ 'slug' => 'turnos_justos',         'label' => 'turnos_justos',         'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'descansos',             'label' => 'descansos',             'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'normativas',            'label' => 'normativas',            'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'flexibilidad',          'label' => 'flexibilidad',          'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'dias_libres',           'label' => 'dias_libres',           'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'festivos_remunerados',  'label' => 'festivos_remunerados',  'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'incentivos',            'label' => 'incentivos',            'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'seguro_medico',         'label' => 'seguro_medico',         'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'uniformes',             'label' => 'uniformes',             'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'estabilidad',           'label' => 'estabilidad',           'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'AEC (Aspectos Económicos)',
                'criterios' => [
                    [ 'slug' => 'salario',                 'label' => 'salario',                 'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'propinas',                'label' => 'propinas',                'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'bonos',                   'label' => 'bonos',                   'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'incrementos',             'label' => 'incrementos',             'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'beneficios',              'label' => 'beneficios',              'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'extras_remuneradas',      'label' => 'extras_remuneradas',      'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'comisiones',              'label' => 'comisiones',              'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'incentivos_festivos',     'label' => 'incentivos_festivos',     'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'sostenibilidad_economica','label' => 'sostenibilidad_economica','descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'cumplimiento',            'label' => 'cumplimiento',            'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'EDG (Efectividad del Grupo)',
                'criterios' => [
                    [ 'slug' => 'liderazgo',        'label' => 'liderazgo',        'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'justicia',         'label' => 'justicia',         'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'motivacion',       'label' => 'motivacion',       'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'claridad',         'label' => 'claridad',         'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'feedback',         'label' => 'feedback',         'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'escucha_activa',   'label' => 'escucha_activa',   'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'planificacion',    'label' => 'planificacion',    'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'delegacion',       'label' => 'delegacion',       'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'participacion',    'label' => 'participacion',    'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'resolucion_rapida','label' => 'resolucion_rapida','descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'TBC (Trabajo con Clientes)',
                'criterios' => [
                    [ 'slug' => 'volumen',            'label' => 'volumen',            'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'clientela',          'label' => 'clientela',          'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'estilo',             'label' => 'estilo',             'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'menu',               'label' => 'menu',               'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'reputacion',         'label' => 'reputacion',         'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'organizacion',       'label' => 'organizacion',       'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'horarios_pico',      'label' => 'horarios_pico',      'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'tematica',           'label' => 'tematica',           'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'exigencia',          'label' => 'exigencia',          'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'adaptacion_cultural','label' => 'adaptacion_cultural','descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'SGD (Seguridad en el Trabajo)',
                'criterios' => [
                    [ 'slug' => 'limpieza',        'label' => 'limpieza',        'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'botiquin',        'label' => 'botiquin',        'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'normativas_claras','label' => 'normativas_claras','descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'ergonomia',       'label' => 'ergonomia',       'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'prevencion',      'label' => 'prevencion',      'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'emergencias',     'label' => 'emergencias',     'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'iluminacion',     'label' => 'iluminacion',     'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'climatizacion',   'label' => 'climatizacion',   'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'senalizacion',    'label' => 'senalizacion',    'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'espacio_seguro',  'label' => 'espacio_seguro',  'descripcion' => '', 'orden' => 10 ],
                ],
            ],
        ];
    } elseif ( 'empleado' === $tipo ) {
        return [
            [
                'grupo'    => 'LID (Liderazgo)',
                'criterios' => [
                    [ 'slug' => 'motivacion',  'label' => 'motivacion',  'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'resolucion',  'label' => 'resolucion',  'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'organizacion','label' => 'organizacion','descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'delegacion',  'label' => 'delegacion',  'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'decision',    'label' => 'decision',    'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'direccion',   'label' => 'direccion',   'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'evaluacion',  'label' => 'evaluacion',  'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'planificacion','label' => 'planificacion','descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'control',     'label' => 'control',     'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'empatia',     'label' => 'empatia',     'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'CLI (Cliente)',
                'criterios' => [
                    [ 'slug' => 'cordialidad', 'label' => 'cordialidad', 'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'escucha',     'label' => 'escucha',     'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'resolutivo',  'label' => 'resolutivo',  'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'memoria',     'label' => 'memoria',     'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'empatia_cli', 'label' => 'empatia_cli', 'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'satisfaccion','label' => 'satisfaccion','descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'claridad',    'label' => 'claridad',    'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'gestion',    'label' => 'gestion',    'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'adaptacion', 'label' => 'adaptacion', 'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'fidelidad',  'label' => 'fidelidad',  'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'TEC (Técnica)',
                'criterios' => [
                    [ 'slug' => 'menu',           'label' => 'menu',           'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'ingredientes',   'label' => 'ingredientes',   'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'cocteleria',     'label' => 'cocteleria',     'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'vinos',          'label' => 'vinos',          'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'cafeteria',      'label' => 'cafeteria',      'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'recomendacion',  'label' => 'recomendacion',  'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'prueba',         'label' => 'prueba',         'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'cata',           'label' => 'cata',           'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'protocolo',      'label' => 'protocolo',      'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'presentacion',   'label' => 'presentacion',   'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'RAP (Rapidez)',
                'criterios' => [
                    [ 'slug' => 'agilidad',       'label' => 'agilidad',       'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'velocidad',      'label' => 'velocidad',      'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'montaje',        'label' => 'montaje',        'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'reaccion',       'label' => 'reaccion',       'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'prevision',      'label' => 'prevision',      'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'sincronizacion', 'label' => 'sincronizacion', 'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'ordenacion',     'label' => 'ordenacion',     'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'imprevistos',    'label' => 'imprevistos',    'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'carga',          'label' => 'carga',          'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'rendimiento',    'label' => 'rendimiento',    'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'ORD (Orden)',
                'criterios' => [
                    [ 'slug' => 'limpieza',          'label' => 'limpieza',          'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'higiene',           'label' => 'higiene',           'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'almacenaje',        'label' => 'almacenaje',        'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'productos',         'label' => 'productos',         'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'desinfeccion',      'label' => 'desinfeccion',      'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'reabastecimiento',  'label' => 'reabastecimiento',  'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'clasificacion',     'label' => 'clasificacion',     'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'cuidado',           'label' => 'cuidado',           'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'reciclaje',         'label' => 'reciclaje',         'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'preparacion',       'label' => 'preparacion',       'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'EQU (Equipo)',
                'criterios' => [
                    [ 'slug' => 'cooperacion',    'label' => 'cooperacion',    'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'interaccion',    'label' => 'interaccion',    'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'soporte',        'label' => 'soporte',        'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'instruccion',    'label' => 'instruccion',    'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'versatilidad',   'label' => 'versatilidad',   'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'conciliacion',   'label' => 'conciliacion',   'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'fluidez',        'label' => 'fluidez',        'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'proactividad',   'label' => 'proactividad',   'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'optimismo',      'label' => 'optimismo',      'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'adaptabilidad',  'label' => 'adaptabilidad',  'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'CRE (Creatividad)',
                'criterios' => [
                    [ 'slug' => 'originalidad', 'label' => 'originalidad', 'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'propuestas',   'label' => 'propuestas',   'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'variedad',     'label' => 'variedad',     'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'estilo',       'label' => 'estilo',       'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'eventos',      'label' => 'eventos',      'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'promocion',    'label' => 'promocion',    'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'carta',        'label' => 'carta',        'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'sorpresa',     'label' => 'sorpresa',     'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'diferencia',   'label' => 'diferencia',   'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'innovacion',   'label' => 'innovacion',   'descripcion' => '', 'orden' => 10 ],
                ],
            ],
            [
                'grupo'    => 'PRO (Profesionalismo)',
                'criterios' => [
                    [ 'slug' => 'puntualidad', 'label' => 'puntualidad', 'descripcion' => '', 'orden' => 1 ],
                    [ 'slug' => 'serenidad',   'label' => 'serenidad',   'descripcion' => '', 'orden' => 2 ],
                    [ 'slug' => 'educacion',   'label' => 'educacion',   'descripcion' => '', 'orden' => 3 ],
                    [ 'slug' => 'apariencia',  'label' => 'apariencia',  'descripcion' => '', 'orden' => 4 ],
                    [ 'slug' => 'integridad',  'label' => 'integridad',  'descripcion' => '', 'orden' => 5 ],
                    [ 'slug' => 'feedback',    'label' => 'feedback',    'descripcion' => '', 'orden' => 6 ],
                    [ 'slug' => 'compromiso',  'label' => 'compromiso',  'descripcion' => '', 'orden' => 7 ],
                    [ 'slug' => 'crecimiento', 'label' => 'crecimiento', 'descripcion' => '', 'orden' => 8 ],
                    [ 'slug' => 'disciplina',  'label' => 'disciplina',  'descripcion' => '', 'orden' => 9 ],
                    [ 'slug' => 'vocacion',    'label' => 'vocacion',    'descripcion' => '', 'orden' => 10 ],
                ],
            ],
        ];
    }

    return [];
}
