<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cdb_get_criterios_empleado() {
    return [
        'DIE' => [
            'label'     => 'Dirección',
            'criterios' => [
                'direccion' => [
                    'label'       => 'Dirección',
                    'descripcion' => 'Guiar al equipo hacia los objetivos comunes.'
                ],
            ],
        ],
        'SAL' => [
            'label'     => 'Sala',
            'criterios' => [
                'camarero' => [
                    'label'       => 'Camarero',
                    'descripcion' => 'Atender y servir a los clientes en sala.'
                ],
            ],
        ],
        'TES' => [
            'label'     => 'Técnica Sala',
            'criterios' => [
                'venta' => [
                    'label'       => 'Venta',
                    'descripcion' => 'Capacidades comerciales de venta.'
                ],
            ],
        ],
        'ATC' => [
            'label'     => 'Atención al Cliente',
            'criterios' => [
                'satisfaccion' => [
                    'label'       => 'Satisfacción',
                    'descripcion' => 'Garantizar una experiencia positiva para el cliente.'
                ],
            ],
        ],
        'TEQ' => [
            'label'     => 'Trabajo en Equipo',
            'criterios' => [
                'cooperacion' => [
                    'label'       => 'Cooperación',
                    'descripcion' => 'Colaborar para lograr objetivos comunes.'
                ],
            ],
        ],
        'ORL' => [
            'label'     => 'Orden y Limpieza',
            'criterios' => [
                'orden' => [
                    'label'       => 'Orden',
                    'descripcion' => 'Organizar el espacio y tareas de forma eficiente.'
                ],
            ],
        ],
        'TEC' => [
            'label'     => 'Técnica de Cocina',
            'criterios' => [
                'cocina_local' => [
                    'label'       => 'Cocina Local',
                    'descripcion' => 'Dominar técnicas culinarias locales.'
                ],
            ],
        ],
        'COC' => [
            'label'     => 'Cocina',
            'criterios' => [
                'cocinero' => [
                    'label'       => 'Cocinero',
                    'descripcion' => 'Encargarse de la preparación de platos principales.'
                ],
            ],
        ],
    ];
}
