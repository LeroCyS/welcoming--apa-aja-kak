import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler);

export default function () {
    const el = document.getElementById('trend-chart');
    const raw = document.getElementById('trend-data');
    if (!el || !raw) return;
    const data = JSON.parse(raw.textContent);
    new Chart(el, {
        type: 'line',
        data: {
            labels: data.map((d) => d.label),
            datasets: [{
                data: data.map((d) => d.value),
                borderColor: '#B5523F',
                backgroundColor: 'rgba(212,112,95,0.10)',
                pointBackgroundColor: '#B5523F',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 5,
                fill: true,
                tension: 0.3,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { title: (items) => data[items[0].dataIndex].full, label: (ctx) => ctx.parsed.y + ' cerita' } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, color: '#6E625A', font: { family: 'Nunito' } }, grid: { color: '#EFE9DA' }, border: { display: false } },
                x: { ticks: { color: '#6E625A', font: { family: 'Nunito' } }, grid: { display: false } },
            },
        },
    });
}
