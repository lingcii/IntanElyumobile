<?php
require_once __DIR__ . '/../../session-bridge.php';
require_once __DIR__ . '/../../laravel-api-bridge.php';
if ($_SESSION['user_role'] !== 'picto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'PICTO Tourist Spots';

ob_start();
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<link rel="stylesheet" href="../../css/LUPTO/tourist-spots.css">
<link rel="stylesheet" href="../../css/LUPTO/dashboard.css">
<link rel="stylesheet" href="../../css/LUPTO/map-view.css">

<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>
  <!-- Summary Cards -->
    <div class="lupto-kpi-grid">
        <div class="lupto-kpi-card" data-kpi="total-municipalities">
            <div class="lupto-kpi-info">
                <h4>Total Municipalities</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-neutral"><i class="fas fa-minus"></i> No Change</span>
            </div>
            <div class="lupto-kpi-icon bg-blue"><i class="fas fa-city"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="total-spots">
            <div class="lupto-kpi-info">
                <h4>Total Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +2 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-compass"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="approved-spots">
            <div class="lupto-kpi-info">
                <h4>Total Open Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +1 today</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="pending-spots">
            <div class="lupto-kpi-info">
                <h4>Total Closed Tourist Spots</h4>
                <span class="lupto-kpi-value"><i class="fas fa-spinner fa-spin" style="font-size:16px;color:#9CA3AF;"></i></span>
                <span class="lupto-kpi-trend trend-down"><i class="fas fa-arrow-down"></i> -3 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>

    <!-- PICTO Full Screen Map Wrapper -->
    <div class="lupto-fullscreen-map-wrapper">
        <div class="lupto-map-controls-panel">
            <h3 class="card-title" style="margin:0;">
                <i class="fas fa-map"></i> La Union Interactive Map
            </h3>
            <div class="map-view-toolbar">
                <div class="map-tabs" aria-label="Map layer switcher">
                    <button class="map-tab active" data-view="street" type="button">
                        <i class="fas fa-map"></i> Street Map
                    </button>
                    <button class="map-tab" data-view="satellite" type="button">
                        <i class="fas fa-satellite"></i> Satellite
                    </button>
                </div>
            </div>
        </div>
        
        <div class="map-wrapper">
            <div id="lupto-map" class="lupto-dedicated-map"></div>
            
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            
            <div class="sidebar-container" id="sidebarContainer" role="dialog" aria-labelledby="sidebarTitle">
                <div class="sidebar-header">
                    <div class="sidebar-header-left">
                        <button class="sidebar-back-btn hidden" id="sidebarBackBtn" aria-label="Go back">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <h3 id="sidebarTitle">Tourist Spots</h3>
                    </div>
                    <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close sidebar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="sidebar-content" id="sidebarContent">
                </div>
            </div>
        </div>
    </div>

<!-- -- Filter Bar -->
<div class="filter-bar">
    <div class="filter-bar-inner">
        <div class="filter-field filter-field-search">
            <label class="filter-label"><i class="fas fa-search"></i> Search</label>
            <div class="filter-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Spot name or keyword..." class="filter-input">
            </div>
        </div>
    </div>
    <div class="filter-bar-right">
        <div class="filter-field">
            <label class="filter-label"><i class="fas fa-map-marker-alt"></i> Municipality</label>
            <select id="filterMunicipality" class="filter-select">
                <option value="">All Municipalities</option>
            </select>
        </div>
        <div class="filter-field" style="position:relative;">
            <label class="filter-label"><i class="fas fa-tag"></i> Category</label>
            <div id="catFilterBtn" class="filter-select" style="cursor:pointer;user-select:none;display:flex;align-items:center;justify-content:space-between;gap:6px;min-width:140px;" onclick="toggleCatDropdown(event)">
                <span id="catFilterLabel">All Categories</span>
                <i class="fas fa-chevron-down" style="font-size:10px;color:#9CA3AF;transition:transform .2s;" id="catChevron"></i>
            </div>
            <div id="catFilterDropdown" style="display:none;position:absolute;top:100%;left:0;z-index:999;background:#fff;border:1px solid #E5E7EB;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:8px 0;min-width:180px;margin-top:4px;max-height:240px;overflow-y:auto;">
                <div style="padding:6px 14px;font-size:11px;color:#9CA3AF;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Select categories</div>
                <?php
                $cats = ['Beach','Mountain','Waterfalls','River','Lake','Island','Cave','Volcano','Forest','Nature Park','Marine Sanctuary','Wildlife Sanctuary','Historical','Cultural Heritage','Religious','Museum','Monument','Landmark','Viewpoint','Adventure','Hiking','Camping','Farm','Eco-Tourism','Garden','Park','Recreation','Hot Spring','Cold Spring','Food Destination','Shopping','Festival Venue','Resort','Other'];
                foreach ($cats as $c): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:7px 14px;cursor:pointer;font-size:13px;transition:background .15s;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='transparent'">
                    <input type="checkbox" class="cat-filter-chk" value="<?= $c ?>" onchange="onCatFilterChange()" style="accent-color:#2563EB;width:15px;height:15px;cursor:pointer;">
                    <?= $c ?>
                </label>
                <?php endforeach; ?>
                <div style="border-top:1px solid #F1F5F9;margin:6px 0 2px;"></div>
                <button onclick="clearCatFilter()" style="width:100%;background:none;border:none;padding:7px 14px;text-align:left;font-size:12px;color:#6B7280;cursor:pointer;" onmouseenter="this.style.color='#2563EB'" onmouseleave="this.style.color='#6B7280'"><i class="fas fa-times-circle"></i> Clear selection</button>
            </div>
        </div>
        <div class="filter-field">
            <label class="filter-label"><i class="fas fa-circle-dot"></i> Status</label>
            <select id="filterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="EXISTING">EXISTING</option>
                <option value="POTENTIAL">POTENTIAL</option>
                <option value="EMERGING">EMERGING</option>
            </select>
        </div>
        <span class="filter-count"><span id="spotCount">0</span> tourist spot(s)</span>
        <div class="view-toggle">
            <button class="active" id="viewCards" title="Card View"><i class="fas fa-th"></i></button>
            <button id="viewTable" title="Table View"><i class="fas fa-list"></i></button>
        </div>
    </div>
</div>

<!-- -- Spot Detail Modal  -->
<div class="modal" id="spotModal">
    <div class="modal-content" style="max-width:680px;">
        <div class="modal-header">
            <h2 id="modalTitle">Spot Details</h2>
            <button class="modal-close" id="closeSpotModal">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center;padding:40px;color:#9CA3AF;">
                <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- -- Cards Grid -->
<div class="cards-grid" id="cardsView">
    <div style="text-align:center;padding:40px;color:#9CA3AF;grid-column:1/-1;">
        <i class="fas fa-spinner fa-spin" style="font-size:24px;"></i>
        <p style="margin-top:12px;">Loading tourist spots...</p>
    </div>
</div>

<!-- -- Table View -->
<div id="tableView" style="display:none; margin-bottom:24px;">
    <table class="data-table">
        <thead>
            <tr>
                <th>Spot ID</th>
                <th>Spot Name</th>
                <th>Municipality</th>
                <th>Category</th>
                <th>Status</th>
                <th>Entry Fee</th>
                <th>Submitted On</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- -- Scripts  -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js" defer></script>

<script>
    window.touristSpotsData = [];
    window.municipalitiesData = [];
</script>
<script src="../../scripts/functions/LUPTO/map-view-api.js?v=<?= time() ?>"></script>

<script type="module">
import { initializeAll } from '../../scripts/functions/LUPTO/tourist-spots-api.js?v=<?= time() ?>';

initializeAll();
</script>

<!-- Multi-category filter helpers -->
<script>
function getSelectedCats() {
    return Array.from(document.querySelectorAll('.cat-filter-chk:checked')).map(c => c.value);
}

function onCatFilterChange() {
    const selected = getSelectedCats();
    const label = document.getElementById('catFilterLabel');
    if (selected.length === 0) {
        label.textContent = 'All Categories';
    } else if (selected.length === 1) {
        label.textContent = selected[0];
    } else {
        label.textContent = selected.length + ' selected';
    }
    const btn = document.getElementById('catFilterBtn');
    btn.style.borderColor = selected.length ? '#2563EB' : '';
    btn.style.color       = selected.length ? '#2563EB' : '';
    document.getElementById('searchInput')?.dispatchEvent(new Event('input'));
}

function clearCatFilter() {
    document.querySelectorAll('.cat-filter-chk').forEach(c => c.checked = false);
    onCatFilterChange();
}

function toggleCatDropdown(e) {
    e.stopPropagation();
    const dd      = document.getElementById('catFilterDropdown');
    const chevron = document.getElementById('catChevron');
    const open    = dd.style.display === 'block';
    dd.style.display = open ? 'none' : 'block';
    chevron.style.transform = open ? '' : 'rotate(180deg)';
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#catFilterBtn') && !e.target.closest('#catFilterDropdown')) {
        const dd      = document.getElementById('catFilterDropdown');
        const chevron = document.getElementById('catChevron');
        if (dd) dd.style.display = 'none';
        if (chevron) chevron.style.transform = '';
    }
});
</script>

<?php
$pageContent = ob_get_clean();
if (is_ajax_request()) {
    if (isset($extraHeadContent)) {
        echo $extraHeadContent;
    }
    echo $pageContent;
    exit;
}
include '../../components/sections.php';
