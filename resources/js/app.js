import './bootstrap';
import 'flowbite';
import * as simpleDatatables from "simple-datatables";
import Chart from 'chart.js/auto';
import dayjs from "dayjs";
import isSameOrAfter from "dayjs/plugin/isSameOrAfter";
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
import isBetween from 'dayjs/plugin/isBetween';
import timezone from 'dayjs/plugin/timezone';
import utc from 'dayjs/plugin/utc';
import customParseFormat from "dayjs/plugin/customParseFormat";
window.dayjs = dayjs;
dayjs.extend(isSameOrAfter);
dayjs.extend(isSameOrBefore);
dayjs.extend(isBetween);
dayjs.extend(timezone);
dayjs.extend(utc);
dayjs.extend(customParseFormat);
import interact from 'interactjs';
window.interact = interact;

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
