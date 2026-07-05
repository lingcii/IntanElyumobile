(function() {
    /**
     * LUPTO/PICTO Dashboard API
     * Fetches real-time data from database via a single Laravel API endpoint for maximum speed and efficiency.
     */

    // ── Guard against duplicate execution ───────────────────────────────────────
    // If your SPA router re-injects this <script> tag every time the user
    // navigates back to the Dashboard, this prevents multiple setInterval loops
    // from stacking up and hammering the API (this was causing the
    // "auto refresh going crazy" behavior).
    if (window.__luptoDashboardLoaded) {
        console.warn('[Dashboard] Script already loaded — restarting refresh only.');
        if (typeof window.startAutoRefresh === 'function') {
            window.startAutoRefresh();
        }
        return;
    }
    window.__luptoDashboardLoaded = true;

    // Determine the API base prefix dynamically based on the path
    let DASHBOARD_URL = window.API_CONFIG?.LUPTO || 'http://localhost:8000/api/lupto';
    if (window.location.pathname.includes('/PICTO/')) {
        DASHBOARD_URL = window.API_CONFIG?.PITCO || 'http://localhost:8000/api/pitco';
    }

    // Real-time refresh interval (10 seconds)
    let refreshTimer = null;
    const FETCH_TIMEOUT_MS = 8000;

    // ── Chart Storage ───────────────────────────────────────────────────────────
    const _dashboardCharts = {};

    // Cache for the single dashboard payload
    let currentDashboardData = null;

    // ── Helper: show an error state instead of leaving spinners stuck forever ──
    function showKpiError() {
        document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value').forEach(valueEl => {
            valueEl.innerHTML = '<span style="color:#EF4444;font-size:12px;font-weight:600;">Error</span>';
        });
    }

    function showKpiLoading() {
        document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value').forEach(valueEl => {
            valueEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:12px;color:#9CA3AF;"></i>';
        });
    }

    // Helper: Fetch all metrics in a single API request, with a hard timeout
    // so a hung request can never leave the dashboard stuck on spinners forever.
    async function fetchDashboardData() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS);

        try {
            if (!window.API_CONFIG || typeof window.API_CONFIG.get !== 'function') {
                throw new Error('API_CONFIG is not available. Check that api-config.js loaded before dashboard-api.js.');
            }

            const data = await window.API_CONFIG.get(DASHBOARD_URL + '/dashboard', {
                signal: controller.signal
            });

            currentDashboardData = data;
            return data;
        } catch (err) {
            if (err.name === 'AbortError') {
                console.error('[Dashboard] Request timed out after', FETCH_TIMEOUT_MS, 'ms:', DASHBOARD_URL + '/dashboard');
            } else {
                console.error('[Dashboard] Failed to fetch consolidated dashboard data:', err);
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
            // Fetch everything from backend in one query
            await fetchDashboardData();
        } catch (err) {
            console.error('[Dashboard] Initialization pre-fetch failed:', err);
            showKpiError();
            // Still try to render charts/map with fallback data below,
            // but bail out of starting auto-refresh on a broken connection.
            loadKpis();
            initVisitorTrendsChart();
            initCategoryChart();
            initTopMunicipalitiesChart();
            initApprovalStatusChart();
            loadMunicipalitiesData();
            return;
        }

        // Initialize all components with the pre-fetched data
        loadKpis();
        initVisitorTrendsChart();
        initCategoryChart();
        initTopMunicipalitiesChart();
        initApprovalStatusChart();
        loadMunicipalitiesData();

        // Start real-time auto-refresh
        startAutoRefresh();
    }

    // ── Real-time Auto Refresh ───────────────────────────────────────────────────
    function startAutoRefresh() {
        // Auto-refresh disabled per user request to prevent repeated background requests.
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    async function softRefreshDashboard() {
        showKpiLoading();
        try {
            await fetchDashboardData();
        } catch (err) {
            console.error('[Dashboard] Soft refresh failed:', err);
            showKpiError();
            return;
        }
        loadKpis();
        initVisitorTrendsChart();
        initCategoryChart();
        initTopMunicipalitiesChart();
        initApprovalStatusChart();
        loadMunicipalitiesData();
    }

    // ── Load KPIs from Cached Payload ───────────────────────────────────────────────────
    function loadKpis() {
        const kpiElements = document.querySelectorAll('.lupto-kpi-card');
        if (kpiElements.length === 0) return;

        const data = currentDashboardData;
        if (data && data.kpis) {
            const kpis = data.kpis;

            // Update KPI cards with real data
            if (kpiElements[0]) {
                const valueEl = kpiElements[0].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = kpis.total_municipalities ?? kpis.totalTouristSpots ?? 20;
            }
            if (kpiElements[1]) {
                const valueEl = kpiElements[1].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = kpis.total_tourist_spots ?? kpis.totalSpots ?? kpis.totalTouristSpots ?? 0;
            }
            if (kpiElements[2]) {
                const valueEl = kpiElements[2].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = kpis.total_approved_spots ?? kpis.approvedSpots ?? 0;
            }
            if (kpiElements[3]) {
                const valueEl = kpiElements[3].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = kpis.total_pending_spots ?? kpis.pendingSpots ?? 0;
            }
            if (kpiElements[4]) {
                const valueEl = kpiElements[4].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = kpis.total_visits ? Number(kpis.total_visits).toLocaleString() : '0';
            }
        } else {
            // Reset to default/fallback values on error
            if (kpiElements[0]) {
                const valueEl = kpiElements[0].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = '20';
            }
            if (kpiElements[1]) {
                const valueEl = kpiElements[1].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = '0';
            }
            if (kpiElements[2]) {
                const valueEl = kpiElements[2].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = '0';
            }
            if (kpiElements[3]) {
                const valueEl = kpiElements[3].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = '0';
            }
            if (kpiElements[4]) {
                const valueEl = kpiElements[4].querySelector('.lupto-kpi-value');
                if (valueEl) valueEl.textContent = '0';
            }
        }
    }

    // ── Load Municipalities for Map from Cached Payload ───────────────────────────────────────────────
    function loadMunicipalitiesData() {
        const mapContainer = document.getElementById('dashboard-map');
        if (!mapContainer) return;

        const data = currentDashboardData;
        const municipalities = data ? (data.municipalities || []) : [];

        // Use real coordinates from database
        const munis = municipalities.map(m => ({
            name: m.name,
            lat: m.latitude || m.lat || 16.5,
            lng: m.longitude || m.lng || 120.3,
            count: m.attraction_count || m.count || 0
        }));

        if (munis.length > 0) {
            initDashboardMap(munis);
        } else {
            // Fallback to default coordinates
            const defaultMunis = [
                { name: 'San Juan', lat: 16.6644, lng: 120.3208, count: 130 },
                { name: 'San Fernando City', lat: 16.6156, lng: 120.3167, count: 110 },
                { name: 'Bauang', lat: 16.5297, lng: 120.3308, count: 90 }
            ];
            initDashboardMap(defaultMunis);
        }
    }

    // ── Initialize Dashboard Map ─────────────────────────────────────────────────
    function initDashboardMap(municipalities) {
        const mapEl = document.getElementById('dashboard-map');
        if (!mapEl) return;

        // Check if map is already initialized on this DOM element
        if (mapEl._leaflet_map) {
            // Reuse map instance, redraw markers
            const map = mapEl._leaflet_map;
            map.eachLayer(layer => {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });
            drawDashboardMarkers(map, municipalities);
            return;
        }

        let laUnionBounds;
        if (municipalities && municipalities.length > 0) {
            laUnionBounds = L.latLngBounds(municipalities.map(muni => [muni.lat, muni.lng])).pad(0.08);
        } else {
            laUnionBounds = L.latLngBounds([[16.2, 120.2], [16.8, 120.5]]);
        }
        const map = L.map('dashboard-map', {
            maxBounds: laUnionBounds.pad(0.08),
            maxBoundsViscosity: 1.0,
            minZoom: 10,
            worldCopyJump: false
        });

        // Save map instance on DOM element
        mapEl._leaflet_map = map;

        // Define base layers
        const baseLayers = {
            "Street Map": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }),
            "Satellite View": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 18
            })
        };

        // Add default street map layer
        baseLayers["Street Map"].addTo(map);

        // Add Layer switcher control
        L.control.layers(baseLayers).addTo(map);

        map.fitBounds(laUnionBounds);

        drawDashboardMarkers(map, municipalities);
    }

    function drawDashboardMarkers(map, municipalities) {
        municipalities.forEach(muni => {
            const color = '#DC2626';
            const icon = L.divIcon({
                html: `<div style="background:${color}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:14px; border:3px solid white; box-shadow:0 2px 8px rgba(0,0,0,0.3);">${muni.count}</div>`,
                className: 'custom-div-icon',
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            const marker = L.marker([muni.lat, muni.lng], { icon: icon });
            marker.addTo(map);
            marker.bindPopup(`<strong>${muni.name}</strong><br>Total Attractions: ${muni.count}`);
            marker.bindTooltip(muni.name, {
                permanent: true,
                direction: 'bottom',
                offset: [0, 25],
                className: 'muni-tooltip',
                opacity: 0.9
            });
        });
    }

    // ── Visitor Trends Chart (Line) from Cached Payload ───────────────────────────────────────────────
    function initVisitorTrendsChart(skipError = false) {
        const ctx = document.getElementById('visitorTrendsChart');
        if (!ctx) return;

        try {
            const data = currentDashboardData;
            const trendData = data ? (data.visitorTrends || []) : [];

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            if (_dashboardCharts.trends) _dashboardCharts.trends.destroy();

            // Create premium gradient
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

            // Map visitor trends records to the 12 month labels
            const chartValues = months.map((_, index) => {
                const record = trendData.find(r => r.month == (index + 1));
                return record ? parseInt(record.visits) || 0 : 0;
            });

            _dashboardCharts.trends = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Visitors',
                        data: chartValues.every(v => v === 0)
                            ? [32000, 38000, 35000, 42000, 45000, 48000, 41000, 43000, 46000, 48000, 49000, 45200]
                            : chartValues,
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
        } catch (err) {
            if (!skipError) console.error('Failed to load visitor trends:', err);
        }
    }

    // ── Category Chart (Doughnut) from Cached Payload ─────────────────────────────────────────────────
    function initCategoryChart(skipError = false) {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;

        try {
            const data = currentDashboardData;
            const catDist = data ? (data.categoryDistribution || []) : [];

            if (_dashboardCharts.category) _dashboardCharts.category.destroy();

            // All 34 valid categories with distinct colours
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

            // Build a per-category count map.
            // Categories are stored as comma-joined strings (e.g. "Beach,Mountain"),
            // so we split each row's category field and add its cnt to every individual category.
            const apiMap = {};
            catDist.forEach(r => {
                const cnt = parseInt(r.cnt) || 0;
                String(r.category).split(',').forEach(part => {
                    const cat = part.trim();
                    if (cat) apiMap[cat] = (apiMap[cat] || 0) + cnt;
                });
            });

            // Use API data if present, otherwise fall back to sample placeholders
            let labels, values, colours;
            if (catDist.length) {
                // Map all valid categories to their resolved count, keep only those > 0, sort desc
                const active = ALL_CATEGORIES
                    .map((cat, i) => ({ cat, cnt: apiMap[cat] || 0, colour: PALETTE[i] }))
                    .filter(item => item.cnt > 0)
                    .sort((a, b) => b.cnt - a.cnt);

                // If nothing matched (unexpected category names in DB), show all API keys directly
                if (active.length === 0) {
                    const fallback = Object.entries(apiMap)
                        .sort((a, b) => b[1] - a[1]);
                    labels  = fallback.map(([k]) => k);
                    values  = fallback.map(([, v]) => v);
                    colours = labels.map((_, i) => PALETTE[i % PALETTE.length]);
                } else {
                    labels  = active.map(i => i.cat);
                    values  = active.map(i => i.cnt);
                    colours = active.map(i => i.colour);
                }
            } else {
                // Placeholder: show a few sample categories
                labels  = ['Beach', 'Mountain', 'Historical', 'Cultural Heritage', 'Park', 'Religious', 'Landmark'];
                values  = [12, 8, 7, 5, 4, 3, 2];
                colours = labels.map((_, i) => PALETTE[i]);
            }

            // Dynamic canvas height so bars are never cramped
            const barH  = 22;
            const padH  = 60;
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
                    indexAxis: 'y',          // horizontal bars
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            padding: 10,
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 13, weight: '600' },
                            bodyFont:  { family: 'Outfit, Inter, sans-serif', size: 12 },
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
        } catch (err) {
            if (!skipError) console.error('Failed to load category chart:', err);
        }
    }

    // ── Top Municipalities Chart (Bar) from Cached Payload ─────────────────────────────────────────────
    function initTopMunicipalitiesChart(skipError = false) {
        const ctx = document.getElementById('topMunicipalitiesChart');
        if (!ctx) return;

        try {
            const data = currentDashboardData;
            const topMunis = data ? (data.topMunicipalities || []) : [];

            if (_dashboardCharts.municipalities) _dashboardCharts.municipalities.destroy();

            _dashboardCharts.municipalities = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topMunis.length
                        ? topMunis.slice(0, 5).map(m => m.name)
                        : ['San Juan', 'San Fernando', 'Bauang', 'Agoo', 'Luna'],
                    datasets: [{
                        label: 'Number of Visitors',
                        data: topMunis.length
                            ? topMunis.slice(0, 5).map(m => parseInt(m.total_visits) || 0)
                            : [15200, 12800, 9500, 7800, 6200],
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
        } catch (err) {
            if (!skipError) console.error('Failed to load top municipalities chart:', err);
        }
    }

    // ── Approval Status Chart (Placeholder logic - returns early if DOM element is missing) ───────────────────────────────────────────
    async function initApprovalStatusChart(skipError = false) {
        const ctx = document.getElementById('approvalStatusChart');
        if (!ctx) return;

        try {
            const fullData = await window.API_CONFIG.get(DASHBOARD_URL + '/analytics/full');

            const pendingCount = fullData.touristSpots
                ? fullData.touristSpots.filter(s => s.status === 'pending').length
                : 15;

            const approvedCount = fullData.touristSpots
                ? fullData.touristSpots.filter(s => s.status === 'approved').length
                : 5;

            const rejectedCount = fullData.touristSpots
                ? fullData.touristSpots.filter(s => s.status === 'rejected').length
                : 0;

            if (_dashboardCharts.approval) _dashboardCharts.approval.destroy();

            _dashboardCharts.approval = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected'],
                    datasets: [{
                        data: [approvedCount, pendingCount, rejectedCount],
                        backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 14,
                                usePointStyle: true,
                                font: { family: 'Outfit, Inter, sans-serif', size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1E293B',
                            padding: 10,
                            titleFont: { family: 'Outfit, Inter, sans-serif', size: 13, weight: '600' },
                            bodyFont: { family: 'Outfit, Inter, sans-serif', size: 12 },
                            cornerRadius: 8
                        }
                    }
                }
            });
        } catch (err) {
            if (!skipError) console.error('Failed to load approval status chart:', err);
        }
    }

    // Expose control functions globally for the SPA router
    window.startAutoRefresh = startAutoRefresh;
    window.stopAutoRefresh = stopAutoRefresh;
    window.softRefreshDashboard = softRefreshDashboard;

    function preWarmTouristSpotsKpis() {
        const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
        Promise.all([
            window.API_CONFIG.get(`${baseUrl}/api/tourist-spots`),
            window.API_CONFIG.get(`${baseUrl}/api/municipalities`)
        ]).then(([spotsRes, muniRes]) => {
            const spots = spotsRes.data || spotsRes || [];
            const munis = muniRes.municipalities || muniRes.data || muniRes || [];
            const vals = {
                municipalities: munis.length,
                total: spots.length,
                open: spots.filter(s => (s.operation_status || s.status || '') === 'open').length,
                closed: spots.filter(s => (s.operation_status || s.status || '') === 'closed').length,
            };
            try { sessionStorage.setItem('ts_kpis_lupto', JSON.stringify(vals)); } catch (e) {}
        }).catch(() => {});
    }

    setTimeout(() => { if (typeof window.softRefreshDashboard === 'function') preWarmTouristSpotsKpis(); }, 1500);

    // ── On DOM Ready ──────────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeDashboard);
    } else {
        initializeDashboard();
    }
})();