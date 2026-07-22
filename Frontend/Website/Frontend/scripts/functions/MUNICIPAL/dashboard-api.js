(function() {
    /**
     * MUNICIPAL Dashboard API
     * Fetches real-time data from database via Laravel API — scoped to the user's municipality.
     * Matches the LUPTO dashboard pattern with municipal-specific adjustments.
     */

    if (window.__muniDashboardLoaded) {
        console.warn('[MUNI Dashboard] Script already loaded — skipping duplicate execution.');
        return;
    }
    window.__muniDashboardLoaded = true;

    const DASHBOARD_URL = window.API_CONFIG?.MUNICIPAL || 'http://localhost:8000/api/municipal';
    const FETCH_TIMEOUT_MS = 8000;

    let refreshTimer = null;
    const _dashboardCharts = {};
    let currentDashboardData = null;

    // ── Helpers ─────────────────────────────────────────────────────────────────
    function showKpiError() {
        document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value').forEach(el => {
            el.innerHTML = '<span style="color:#EF4444;font-size:12px;font-weight:600;">Error</span>';
        });
    }

    function showKpiLoading() {
        document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value').forEach(el => {
            el.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:12px;color:#9CA3AF;"></i>';
        });
    }

    function hideLoadingOverlay() {
        const overlay = document.getElementById('dashboard-loading-overlay');
        if (overlay) {
            overlay.classList.add('fade-out');
            setTimeout(() => { overlay.remove(); }, 350);
        }
    }

    async function fetchDashboardData() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS);

        try {
            const data = await window.API_CONFIG.get(DASHBOARD_URL + '/dashboard');
            currentDashboardData = data;
            return data;
        } catch (err) {
            if (err.name === 'AbortError') {
                console.error('[MUNI Dashboard] Request timed out after', FETCH_TIMEOUT_MS, 'ms');
            } else {
                console.error('[MUNI Dashboard] Failed to fetch dashboard data:', err);
            }
            throw err;
        } finally {
            clearTimeout(timeoutId);
        }
    }

    // ── Initialize Dashboard ───────────────────────────────────────────────────
    async function initializeDashboard() {
        showKpiLoading();

        try {
            await fetchDashboardData();
        } catch (err) {
            console.error('[MUNI Dashboard] Initialization pre-fetch failed:', err);
            hideLoadingOverlay();
            showKpiError();
            loadKpis();
            initMap();
            initVisitorTrendsChart();
            initCategoryChart();
            initTopSpotsChart();
            loadInsights();
            return;
        }

        hideLoadingOverlay();
        loadKpis();
        initMap();
        initVisitorTrendsChart();
        initCategoryChart();
        initTopSpotsChart();
        loadInsights();
    }

    async function softRefreshDashboard() {
        showKpiLoading();
        try {
            await fetchDashboardData();
        } catch (err) {
            console.error('[MUNI Dashboard] Soft refresh failed:', err);
            showKpiError();
            return;
        }
        loadKpis();
        initMap();
        initVisitorTrendsChart();
        initCategoryChart();
        initTopSpotsChart();
        loadInsights();
    }

    // ── KPI Cards ──────────────────────────────────────────────────────────────
    function loadKpis() {
        const kpiElements = document.querySelectorAll('.lupto-kpi-card');
        if (kpiElements.length === 0) return;

        const data = currentDashboardData;
        const kpis = data?.kpis || {};

        // KPI 0: Total Tourist Spots
        if (kpiElements[0]) {
            const el = kpiElements[0].querySelector('.lupto-kpi-value');
            if (el) el.textContent = kpis.total_tourist_spots ?? kpis.totalSpots ?? 0;
        }
        // KPI 1: Total Open Tourist Spots (approved + not maintenance)
        if (kpiElements[1]) {
            const el = kpiElements[1].querySelector('.lupto-kpi-value');
            if (el) el.textContent = kpis.total_approved_spots ?? kpis.approvedSpots ?? 0;
        }
        // KPI 2: Total Closed Tourist Spots (pending or under maintenance)
        if (kpiElements[2]) {
            const el = kpiElements[2].querySelector('.lupto-kpi-value');
            if (el) el.textContent = kpis.total_pending_spots ?? kpis.pendingSpots ?? 0;
        }
        // KPI 3: Total Monthly Visitors
        if (kpiElements[3]) {
            const el = kpiElements[3].querySelector('.lupto-kpi-value');
            if (el) el.textContent = kpis.total_visits ? Number(kpis.total_visits).toLocaleString() : '0';
        }
        // KPI 4: Average Rating
        if (kpiElements[4]) {
            const el = kpiElements[4].querySelector('.lupto-kpi-value');
            const rating = kpis.average_rating ?? 4.0;
            if (el) el.innerHTML = Number(rating).toFixed(1) + ' <i class="fas fa-star" style="color:#fbbf24;font-size:16px;"></i>';
        }
    }

    // ── Municipality Map ───────────────────────────────────────────────────────
    function initMap() {
        const mapEl = document.getElementById('dashboard-map');
        if (!mapEl) return;

        const data = currentDashboardData;
        const municipalities = data?.municipalities || [];
        const muni = municipalities[0] || { name: 'Your Municipality', latitude: 16.5, longitude: 120.3 };

        if (mapEl._leaflet_map) {
            const map = mapEl._leaflet_map;
            map.eachLayer(layer => { if (layer instanceof L.Marker) map.removeLayer(layer); });
            drawMunicipalityMarker(map, muni);
            return;
        }

        const centerLat = parseFloat(muni.latitude) || 16.5;
        const centerLng = parseFloat(muni.longitude) || 120.3;

        const map = L.map('dashboard-map', {
            minZoom: 10,
            maxZoom: 18
        });
        mapEl._leaflet_map = map;

        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        });
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 18
        });
        streetLayer.addTo(map);

        L.control.layers({ "Street Map": streetLayer, "Satellite": satelliteLayer }).addTo(map);
        map.setView([centerLat, centerLng], 12);

        drawMunicipalityMarker(map, muni);
    }

    function drawMunicipalityMarker(map, muni) {
        const lat = parseFloat(muni.latitude) || 16.5;
        const lng = parseFloat(muni.longitude) || 120.3;
        const count = muni.attraction_count || muni.count || 0;
        const name = muni.name || 'Your Municipality';

        const icon = L.divIcon({
            html: `<div style="background:#DC2626;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:14px;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);">${count}</div>`,
            className: 'custom-div-icon',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const marker = L.marker([lat, lng], { icon }).addTo(map);
        marker.bindPopup(`<strong>${name}</strong><br>Total Attractions: ${count}`);
        marker.bindTooltip(name, {
            permanent: true,
            direction: 'bottom',
            offset: [0, 25],
            opacity: 0.9
        });
    }

    // ── Visitor Trends Chart ───────────────────────────────────────────────────
    function initVisitorTrendsChart() {
        const ctx = document.getElementById('visitorTrendsChart');
        if (!ctx) return;

        const data = currentDashboardData;
        const trendData = data?.visitorTrends || [];

        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const fallback = [1200, 1400, 1300, 1600, 1700, 1850, 1500, 1600, 1700, 1750, 1800, 1700];

        if (_dashboardCharts.trends) _dashboardCharts.trends.destroy();

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

        const chartValues = months.map((_, i) => {
            const record = trendData.find(r => r.month == (i + 1));
            return record ? parseInt(record.visits) || 0 : 0;
        });

        _dashboardCharts.trends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Visitors',
                    data: chartValues.every(v => v === 0) ? fallback : chartValues,
                    borderColor: '#2563EB',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.45,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 10,
                        titleFont: { family: 'Outfit, Inter, sans-serif', size: 13, weight: '600' },
                        bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                        cornerRadius: 8,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F1F5F9', drawTicks: false },
                        ticks: { font: { family: 'Outfit, Inter, sans-serif', size: 11 } }
                    },
                    y: {
                        grid: { color: '#F1F5F9', drawTicks: false },
                        ticks: {
                            callback: value => Number(value).toLocaleString(),
                            font: { family: 'Outfit, Inter, sans-serif', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // ── Category Distribution Chart (Horizontal Bar) ────────────────────────────
    function initCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;

        const data = currentDashboardData;
        const catDist = data?.categoryDistribution || [];

        if (_dashboardCharts.category) _dashboardCharts.category.destroy();

        const ALL_CATEGORIES = [
            'Beach', 'Mountain', 'Waterfalls', 'River', 'Lake', 'Island',
            'Cave', 'Volcano', 'Forest', 'Nature Park', 'Marine Sanctuary',
            'Wildlife Sanctuary', 'Historical', 'Cultural Heritage', 'Religious',
            'Museum', 'Monument', 'Landmark', 'Viewpoint', 'Adventure', 'Hiking',
            'Camping', 'Farm', 'Eco-Tourism', 'Garden', 'Park', 'Recreation',
            'Hot Spring', 'Cold Spring', 'Food Destination', 'Shopping',
            'Festival Venue', 'Resort', 'Other'
        ];

        const PALETTE = [
            '#2563EB','#3B82F6','#0EA5E9','#06B6D4','#14B8A6','#10B981',
            '#22C55E','#84CC16','#EAB308','#F59E0B','#F97316','#EF4444',
            '#EC4899','#D946EF','#A855F7','#8B5CF6','#6366F1','#4F46E5',
            '#0891B2','#0D9488','#059669','#16A34A','#65A30D','#CA8A04',
            '#D97706','#DC2626','#DB2777','#C026D3','#9333EA','#7C3AED',
            '#4338CA','#2563EB','#0284C7','#0369A1'
        ];

        const apiMap = {};
        if (catDist.length) {
            catDist.forEach(r => {
                const cnt = parseInt(r.cnt) || 0;
                String(r.category || '').split(',').forEach(part => {
                    const cat = part.trim();
                    if (cat) apiMap[cat] = (apiMap[cat] || 0) + cnt;
                });
            });
        }

        let labels, values, colours;
        if (catDist.length > 0) {
            const active = ALL_CATEGORIES
                .map((cat, i) => ({ cat, cnt: apiMap[cat] || 0, colour: PALETTE[i] }))
                .filter(item => item.cnt > 0)
                .sort((a, b) => b.cnt - a.cnt);

            if (active.length === 0) {
                const fallback = Object.entries(apiMap).sort((a, b) => b[1] - a[1]);
                labels = fallback.map(([k]) => k);
                values = fallback.map(([, v]) => v);
                colours = labels.map((_, i) => PALETTE[i % PALETTE.length]);
            } else {
                labels = active.map(i => i.cat);
                values = active.map(i => i.cnt);
                colours = active.map(i => i.colour);
            }
        } else {
            labels = ['Beach', 'Mountain', 'Historical', 'Cultural Heritage', 'Park'];
            values = [3, 2, 2, 1, 1];
            colours = labels.map((_, i) => PALETTE[i]);
        }

        const barH = 22;
        const padH = 60;
        ctx.parentElement.style.height = Math.max(200, labels.length * (barH + 6) + padH) + 'px';

        _dashboardCharts.category = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Spots',
                    data: values,
                    backgroundColor: colours,
                    borderRadius: 5,
                    barThickness: barH
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 10,
                        titleFont: { family: 'Outfit, Inter, sans-serif', size: 13, weight: '600' },
                        bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.x} spot${ctx.parsed.x !== 1 ? 's' : ''}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F1F5F9', drawTicks: false },
                        ticks: {
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : '',
                            font: { family: 'Outfit, Inter, sans-serif', size: 11 }
                        },
                        title: {
                            display: true,
                            text: 'Number of Spots',
                            font: { family: 'Outfit, Inter, sans-serif', size: 11 },
                            color: '#64748B'
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit, Inter, sans-serif', size: 11 } }
                    }
                }
            }
        });
    }

    // ── Top Spots by Visits Chart ──────────────────────────────────────────────
    function initTopSpotsChart() {
        const ctx = document.getElementById('topSpotsChart');
        if (!ctx) return;

        const data = currentDashboardData;
        const topSpots = data?.topSpots || [];

        if (_dashboardCharts.topSpots) _dashboardCharts.topSpots.destroy();

        _dashboardCharts.topSpots = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topSpots.length ? topSpots.slice(0, 5).map(s => s.name) : ['Spot A', 'Spot B', 'Spot C', 'Spot D', 'Spot E'],
                datasets: [{
                    label: 'Total Visits',
                    data: topSpots.length ? topSpots.slice(0, 5).map(s => parseInt(s.visits) || 0) : [1200, 950, 780, 650, 500],
                    backgroundColor: ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'],
                    borderRadius: 6,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1E293B',
                        padding: 10,
                        titleFont: { family: 'Outfit, Inter, sans-serif', size: 13, weight: '600' },
                        bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#F1F5F9', drawTicks: false },
                        ticks: {
                            callback: value => Number(value).toLocaleString(),
                            font: { family: 'Outfit, Inter, sans-serif', size: 11 }
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit, Inter, sans-serif', size: 11 } }
                    }
                }
            }
        });
    }

    // ── Smart Insights Cards ───────────────────────────────────────────────────
    function loadInsights() {
        const spots = currentDashboardData?.touristSpots || [];
        const kpis = currentDashboardData?.kpis || {};

        const topEl = document.getElementById('insight-top-spot');
        if (topEl) {
            if (spots.length > 0) {
                const sorted = [...spots].sort((a, b) => (b.visits || 0) - (a.visits || 0));
                const top = sorted[0];
                topEl.textContent = top.name + ` — ${(top.visits || 0).toLocaleString()} visitors this month`;
            } else {
                topEl.textContent = 'No tourist spots yet';
            }
        }

        const needsAttnEl = document.getElementById('insight-needs-attention');
        if (needsAttnEl) {
            const pendingCount = kpis.total_pending_spots ?? 0;
            const maintenanceCount = spots.filter(s => s.is_maintenance).length;
            needsAttnEl.textContent = pendingCount > 0
                ? pendingCount + ' spot(s) pending approval'
                : maintenanceCount > 0
                    ? maintenanceCount + ' spot(s) under maintenance'
                    : 'All spots are in good standing';
        }

        const trendEl = document.getElementById('insight-trend');
        if (trendEl) {
            const totalVisits = kpis.total_visits ?? 0;
            const totalSpots = kpis.total_tourist_spots ?? 0;
            trendEl.textContent = totalSpots > 0
                ? totalVisits.toLocaleString() + ' total visits across ' + totalSpots + ' spot(s)'
                : 'No activity data available yet';
        }
    }

    window.softRefreshDashboard = softRefreshDashboard;

    function preWarmMuniTouristSpotsKpis() {
        const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
        window.API_CONFIG.get(`${baseUrl}/api/municipal/tourist-spots`).then(spotsRes => {
            const spots = spotsRes.data || spotsRes || [];
            const vals = {
                total: spots.length,
                open: spots.filter(s => !s.is_maintenance && (s.status || s.operation_status || '') !== 'closed').length,
                closed: spots.filter(s => s.is_maintenance || (s.status || s.operation_status || '') === 'closed').length,
                visits: Number(spots.reduce((sum, s) => sum + (parseInt(s.visits) || 0), 0)).toLocaleString(),
            };
            try { sessionStorage.setItem('ts_kpis_municipal', JSON.stringify(vals)); } catch (e) {}
        }).catch(() => {});
    }

    setTimeout(() => { preWarmMuniTouristSpotsKpis(); }, 1500);

    // ── On DOM Ready ──────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDashboard);
    } else {
        initializeDashboard();
    }
})();
