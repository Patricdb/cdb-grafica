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

        const chartData = {
            labels: data.labels,
            datasets: [
                {
                    label: `Puntuación Total: ${data.total.toFixed(1)}`, // Limitar a 1 decimal
                    data: data.promedios,
                    backgroundColor: graficaEmpleadoElement.dataset.backgroundColor || "rgba(75, 192, 192, 0.2)",
                    borderColor: graficaEmpleadoElement.dataset.borderColor || "rgba(75, 192, 192, 1)",
                    borderWidth: 2,
                },
            ],
        };

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
                        ticks: { beginAtZero: true, stepSize: 1, max: 10, min: 0 },
                    },
                },
            },
        });
        console.log("Gráfica creada correctamente.");
    }
});
