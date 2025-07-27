/* global Chart */
/* jshint esversion: 6 */

import { registerBlockType } from '@wordpress/blocks';

registerBlockType('cdb/grafica-bar', {
    title: 'Gráfica Bar',
    icon: 'chart-area',
    category: 'widgets',
    edit: () => {
        return (
            <div>
                <p>El bloque "Gráfica Bar" está funcionando correctamente. La configuración de colores ha sido eliminada para mayor estabilidad.</p>
            </div>
        );
    },
    save: () => null, // Renderizado en PHP
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("El DOM está cargado.");
    const graficaBarElement = document.getElementById("grafica-bar");
    if (!graficaBarElement) {
        console.error("No se encontró el elemento con id 'grafica-bar'.");
        return;
    }

    const canvas = document.createElement("canvas");
    graficaBarElement.appendChild(canvas);
    const ctx = canvas.getContext("2d");

    if (graficaBarElement && ctx) {
        const data = JSON.parse(graficaBarElement.dataset.valores);

        const toRGBA = (hex, alpha = 0.2) => {
            const match = /^#?([a-fA-F0-9]{6})$/.exec(hex || "");
            if (match) {
                const intVal = parseInt(match[1], 16);
                const r = (intVal >> 16) & 255;
                const g = (intVal >> 8) & 255;
                const b = intVal & 255;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }
            return hex;
        };

        const chartData = {
            labels: data.labels,
            datasets: [
                {
                    label: `Puntuación Total: ${data.total.toFixed(1)}`, // Limitar a 1 decimal
                    data: data.promedios,
                    backgroundColor: toRGBA(graficaBarElement.dataset.backgroundColor) || "rgba(75, 192, 192, 0.2)",
                    borderColor: graficaBarElement.dataset.borderColor || "rgba(75, 192, 192, 1)",
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
