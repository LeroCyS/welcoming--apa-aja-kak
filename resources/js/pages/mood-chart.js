import { Chart, LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler } from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler);

export default function () {
    const el = document.getElementById('mood-trend');
    const raw = document.getElementById('mood-trend-data');
    if (!el || !raw) return;
    const data = JSON.parse(raw.textContent);
    new Chart(el, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                borderColor: '#B5523F',
                backgroundColor: 'rgba(212,112,95,0.12)',
                pointBackgroundColor: '#B5523F',
                pointRadius: 3,
                spanGaps: true,
                fill: true,
                tension: 0.35,
            }],
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => data.moods[ctx.dataIndex] || '' } },
            },
            scales: {
                y: { min: 0, max: 6, ticks: { display: false }, grid: { color: '#EFE9DA' }, border: { display: false } },
                x: { ticks: { color: '#6E625A', maxTicksLimit: 8, font: { family: 'Nunito' } }, grid: { display: false } },
            },
        },
    });
}
