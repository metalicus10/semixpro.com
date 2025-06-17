import './bootstrap';
import 'flowbite';
import * as simpleDatatables from "simple-datatables";
import Chart from 'chart.js/auto';

if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
    const dataTable = new simpleDatatables.DataTable("#search-table", {
        searchable: true,
        sortable: true
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('stockDoughnut');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: window.stockChartLabels,
                datasets: [{
                    data: window.stockChartData,
                    backgroundColor: window.stockChartColors,
                    borderWidth: 0
                }]
            },
            options: {
                plugins: {
                    legend: { display:false }
                },
                cutout: '60%'
            }
        });
    }
});
