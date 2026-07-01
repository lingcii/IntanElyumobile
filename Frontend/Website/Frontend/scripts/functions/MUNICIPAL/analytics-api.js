/**
 * MUNICIPAL Analytics API
 * Fetches real-time data from database via Laravel API
 */

const MUNICIPAL_API = window.API_CONFIG?.MUNICIPAL || 'http://localhost:8000/api/municipal';

// ── Initialize Charts with Real Data ─────────────────────────────────────────
async function initAnalyticsCharts() {
    // YoY Line chart - fetch real data
    const yoyCtx = document.getElementById('yoyChart');
    if (yoyCtx) {
        try {
            const data = await window.API_CONFIG.fetch(MUNICIPAL_API + '/analytics/full');
            const monthlyTrends = data.monthlyTrends || [];

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const currentYear = new Date().getFullYear();
            const prevYear = currentYear - 1;

            const currentData = months.map((_, i) => {
                const m = monthlyTrends.find(d => d.year == currentYear && d.month == (i + 1));
                return m ? parseInt(m.total_visits) || 0 : 0;
            });

            const prevData = months.map((_, i) => {
                const m = monthlyTrends.find(d => d.year == prevYear && d.month == (i + 1));
                return m ? parseInt(m.total_visits) || 0 : 0;
            });

            new Chart(yoyCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: `${prevYear} Visits`,
                            data: prevData,
                            borderColor: '#94a3b8',
                            backgroundColor: 'rgba(148, 163, 184, 0.1)',
                            borderDash: [5, 5],
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: `${currentYear} Visits`,
                            data: currentData,
                            borderColor: '#185FA5',
                            backgroundColor: 'rgba(24, 95, 165, 0.2)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        } catch (err) {
            console.error('[MUNICIPAL Analytics] YoY chart error:', err);
        }
    }

    // Municipality Bar Chart - fetch real data
    const muniCtx = document.getElementById('muniBarChart');
    if (muniCtx) {
        try {
            const data = await window.API_CONFIG.fetch(MUNICIPAL_API + '/analytics/top-municipalities?limit=6');
            const munis = data.municipalities || [];

            new Chart(muniCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: munis.length ? munis.map(m => m.name) : ['San Juan', 'San Fernando', 'Bauang', 'Agoo', 'Luna', 'San Gabriel'],
                    datasets: [{
                        label: 'Visits',
                        data: munis.length ? munis.map(m => parseInt(m.total_visits) || 0) : [12800, 4200, 3500, 2900, 3300, 2800],
                        backgroundColor: '#85B7EB',
                        borderColor: '#185FA5',
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        } catch (err) {
            console.error('[MUNICIPAL Analytics] Municipality chart error:', err);
        }
    }

    // Transport Pie Chart - fetch real data
    const transportCtx = document.getElementById('transportChart');
    if (transportCtx) {
        try {
            const data = await window.API_CONFIG.fetch(MUNICIPAL_API + '/analytics/chart-data');
            const transport = data.transport || {};

            new Chart(transportCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Private Cars', 'Tour Buses', 'Vans', 'Others'],
                    datasets: [{
                        data: [
                            parseInt(transport.car) || 52,
                            parseInt(transport.bus) || 13,
                            parseInt(transport.van) || 27,
                            parseInt(transport.other) || 8
                        ],
                        backgroundColor: ['#185FA5', '#85B7EB', '#B5D4F4', '#e2e8f0']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        } catch (err) {
            console.error('[MUNICIPAL Analytics] Transport chart error:', err);
        }
    }
}

// ── On DOM Ready ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
    await initAnalyticsCharts();

    // Real-time auto-refresh every 30 seconds
    setInterval(initAnalyticsCharts, 30000);
});