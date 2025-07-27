<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Devuelve los criterios y grupos para la gráfica de bares.
 *
 * @return array
 */
function cdb_get_bar_criterios() {
    return [
        'ALB (Ambiente Laboral Básico)' => ['bienvenida', 'companerismo', 'clima_positivo', 'resolucion_de_conflictos', 'cooperacion', 'relacion_superiores', 'inclusion', 'comunicacion', 'reconocimiento', 'celebracion_logros'],
        'EDT (Estructura del Trabajo)'   => ['tamano', 'cooperacion_edt', 'comunicacion_edt', 'roles_definidos', 'actitud', 'equilibrio', 'socializacion', 'diversidad', 'compromiso', 'sinergia'],
        'DPF (Desarrollo Profesional)'   => ['formacion', 'habilidades', 'cursos', 'promociones', 'eventos', 'networking', 'creatividad', 'mentor', 'innovacion', 'retos'],
        'CLB (Condiciones Laborales)'    => ['turnos_justos', 'descansos', 'normativas', 'flexibilidad', 'dias_libres', 'festivos_remunerados', 'incentivos', 'seguro_medico', 'uniformes', 'estabilidad'],
        'AEC (Aspectos Económicos)'      => ['salario', 'propinas', 'bonos', 'incrementos', 'beneficios', 'extras_remuneradas', 'comisiones', 'incentivos_festivos', 'sostenibilidad_economica', 'cumplimiento'],
        'EDG (Efectividad del Grupo)'    => ['liderazgo', 'justicia', 'motivacion', 'claridad', 'feedback', 'escucha_activa', 'planificacion', 'delegacion', 'participacion', 'resolucion_rapida'],
        'TBC (Trabajo con Clientes)'     => ['volumen', 'clientela', 'estilo', 'menu', 'reputacion', 'organizacion', 'horarios_pico', 'tematica', 'exigencia', 'adaptacion_cultural'],
        'SGD (Seguridad en el Trabajo)'  => ['limpieza', 'botiquin', 'normativas_claras', 'ergonomia', 'prevencion', 'emergencias', 'iluminacion', 'climatizacion', 'senalizacion', 'espacio_seguro'],
    ];
}

/**
 * Devuelve los criterios y grupos para la gráfica de empleados.
 *
 * @return array
 */
function cdb_get_empleado_criterios() {
    return [
        'LID (Liderazgo)'    => ['motivacion', 'resolucion', 'organizacion', 'delegacion', 'decision', 'direccion', 'evaluacion', 'planificacion', 'control', 'empatia'],
        'CLI (Cliente)'      => ['cordialidad', 'escucha', 'resolutivo', 'memoria', 'empatia_cli', 'satisfaccion', 'claridad', 'gestion', 'adaptacion', 'fidelidad'],
        'TEC (Técnica)'      => ['menu', 'ingredientes', 'cocteleria', 'vinos', 'cafeteria', 'recomendacion', 'prueba', 'cata', 'protocolo', 'presentacion'],
        'RAP (Rapidez)'      => ['agilidad', 'velocidad', 'montaje', 'reaccion', 'prevision', 'sincronizacion', 'ordenacion', 'imprevistos', 'carga', 'rendimiento'],
        'ORD (Orden)'        => ['limpieza', 'higiene', 'almacenaje', 'productos', 'desinfeccion', 'reabastecimiento', 'clasificacion', 'cuidado', 'reciclaje', 'preparacion'],
        'EQU (Equipo)'       => ['cooperacion', 'interaccion', 'soporte', 'instruccion', 'versatilidad', 'conciliacion', 'fluidez', 'proactividad', 'optimismo', 'adaptabilidad'],
        'CRE (Creatividad)'  => ['originalidad', 'propuestas', 'variedad', 'estilo', 'eventos', 'promocion', 'carta', 'sorpresa', 'diferencia', 'innovacion'],
        'PRO (Profesionalismo)' => ['puntualidad', 'serenidad', 'educacion', 'apariencia', 'integridad', 'feedback', 'compromiso', 'crecimiento', 'disciplina', 'vocacion'],
    ];
}
