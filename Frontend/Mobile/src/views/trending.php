<?php
$pageTitle = 'Trending Spots';
$backRoute = 'dashboard';

$municipalityImages = [];
$imgDir = __DIR__ . '/../../../../backend/storage/app/public/municipalities';
if (is_dir($imgDir)) {
    $munis = scandir($imgDir);
    foreach ($munis as $muni) {
        if ($muni === '.' || $muni === '..') continue;
        if (is_dir("$imgDir/$muni")) {
            $files = scandir("$imgDir/$muni");
            foreach ($files as $f) {
                $fLower = strtolower($f);
                if (preg_match('/\.(jpg|jpeg|png|webp|gif)$/', $fLower)) {
                    $municipalityImages[strtoupper($muni)][] = $f;
                }
            }
        }
    }
}
?>
<?php include __DIR__ . '/../components/header.php'; ?>

<link rel="stylesheet" href="assets/css/views/trending.css">
<div class="saved-trips-page-container has-header animate-slide-up" style="padding-left: 16px; padding-right: 16px;">
    <div id="trending-list" style="margin-top: 16px;">
        <p style="text-align:center; color:rgba(255,255,255,0.5); margin-top:40px;">
            <i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i> Loading trending spots...
        </p>
    </div>
</div>

<script>
(function() {
    var backendUrl = window.backendUrl || 'https://intanelyu-production.up.railway.app';

    window.AVAILABLE_MUNI_IMAGES = <?= json_encode($municipalityImages) ?>;

    async function fetchTrending() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const cacheKey = 'trending_spots_' + (token ? token.substring(0, 10) : '');

        await window.useCache(
            cacheKey,
            async () => {
                const res = await fetch(backendUrl + '/api/tourist/dashboard?limit=50', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!res.ok) throw new Error("Failed to fetch trending spots");
                const data = await res.json();
                return data.trending || [];
            },
            (spots) => {
                if (spots) {
                    renderTrending(spots);
                } else {
                    const list = document.getElementById('trending-list');
                    if (list) list.innerHTML = '<p style="text-align:center; color:#999; margin-top:20px;">Failed to load trending spots.</p>';
                }
            },
            false,
            60000 // 1 minute TTL
        );
    }

    function renderTrending(spots) {
        const list = document.getElementById('trending-list');
        if (!list) return;
        if (!spots.length) {
            list.innerHTML = `
                <div style="padding:40px 20px; text-align:center; color:rgba(255,255,255,0.4);">
                    <i class="fa-solid fa-fire-flame-curved" style="font-size:40px; margin-bottom:12px; display:block;"></i>
                    No trending spots right now.
                </div>
            `;
            return;
        }
        let html = '<div class="trending-grid">';
        spots.forEach((dest, i) => {
            const img = window.getDestImage(dest);
            const badgeColor = dest.classification_status === 'EXIST' ? '#34c759' :
                (dest.classification_status === 'EMERGE' ? '#38bdf8' : '#f59e0b');
            const badgeLabel = dest.classification_status === 'EXIST' ? 'EXISTING' :
                (dest.classification_status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL');
            const visits = dest.visits || 0;
            html += `
                <div class="trending-card" style="animation-delay:${i * 0.08}s" onclick="window.viewTrendingDest(${dest.id}, '${dest.name.replace(/'/g, "\\'")}', '${encodeURIComponent(JSON.stringify(dest))}')">
                    ${dest.classification_status ? `<div class="badge" style="background:${badgeColor};">${badgeLabel}</div>` : ''}
                    <i class="fa-solid fa-fire fire-icon"></i>
                    <img src="${img}" alt="${dest.name}" onerror="this.onerror=null; this.style.display='none';">
                    <div class="overlay">
                        <div class="name">${dest.name}</div>
                        <div class="meta">${visits.toLocaleString()} visits</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        list.innerHTML = html;
    }

    window.viewTrendingDest = function(id, name, encodedDest) {
        try {
            const dest = JSON.parse(decodeURIComponent(encodedDest));
            localStorage.setItem('intan_elyu_view_destination', JSON.stringify(dest));
            window.location.href = '?view=map';
        } catch (e) {
            console.error('Failed to view destination:', e);
        }
    };

    fetchTrending();
})();
</script>
