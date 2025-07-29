/* global Chart */
/* jshint esversion: 6 */

import { registerBlockType } from '@wordpress/blocks';

registerBlockType('cdb/grafica-empleado', {
    title: 'Gráfica Empleado',
    icon: 'chart-area',
    category: 'widgets',
    edit: () => {
        return (
            <div>
                <p>El bloque "Gráfica Empleado" está funcionando correctamente.</p>
            </div>
        );
    },
    save: () => null, // Renderizado en PHP
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("El DOM está cargado.");
    const graficaEmpleadoElement = document.getElementById("grafica-empleado");
    if (!graficaEmpleadoElement) {
        console.error("No se encontró el elemento con id 'grafica-empleado'.");
        return;
    }

    const canvas = document.createElement("canvas");
    graficaEmpleadoElement.appendChild(canvas);
    const ctx = canvas.getContext("2d");

    if (graficaEmpleadoElement && ctx) {
        const data = JSON.parse(graficaEmpleadoElement.dataset.valores);
        const colores = JSON.parse(graficaEmpleadoElement.dataset.roleColors || "{}");

        const chartData = {
            labels: data.labels,
            datasets: []
        };

        if (Array.isArray(data.datasets)) {
            data.datasets.forEach((dataset) => {
                const cfg = colores[dataset.role] || {};
                chartData.datasets.push({
                    label: dataset.label,
                    data: dataset.data,
                    backgroundColor: cfg.background || "gray",
                    borderColor: cfg.border || "gray",
                    borderWidth: 2,
                });
            });
        }

        new Chart(ctx, {
            type: "radar",
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: { font: { size: 14 } },
                    },
                },
                scales: {
                    r: {
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            max: 10,
                            min: 0,
                            color: graficaEmpleadoElement.dataset.ticksColor || '#666',
                            backdropColor: graficaEmpleadoElement.dataset.ticksBackdropColor || undefined,
                        },
                    },
                },
            },
        });
        console.log("Gráfica creada correctamente.");
    }
});
