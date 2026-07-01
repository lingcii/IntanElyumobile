<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'lupto') {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'LUPTO Tourist Spots';

// ── Pull data from Laravel API ─────────────────────────────────────────────
$laravelBase = 'http://127.0.0.1:8000/api';
$cookieStr   = '';
foreach ($_COOKIE as $name => $value) {
    $cookieStr .= $name . '=' . urlencode($value) . '; ';
}

function lutoApiGet(string $url, string $cookieStr): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Cookie: ' . $cookieStr],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    if (!$body) return [];
    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : [];
}

$spotsResponse  = lutoApiGet("{$laravelBase}/tourist-spots", $cookieStr);
$spots          = $spotsResponse['data'] ?? (isset($spotsResponse[0]) ? $spotsResponse : []);

$muniResponse   = lutoApiGet("{$laravelBase}/municipalities", $cookieStr);
$municipalities = $muniResponse['municipalities'] ?? $muniResponse['data'] ?? (isset($muniResponse[0]) ? $muniResponse : []);

// Build spot counts per municipality for the map markers
$muniSpotCounts = [];
foreach ($spots as $s) {
    $mName = $s['municipality_name'] ?? $s['municipality']['name'] ?? '';
    if ($mName) $muniSpotCounts[$mName] = ($muniSpotCounts[$mName] ?? 0) + 1;
}
foreach ($municipalities as &$m) {
    $m['spots'] = $muniSpotCounts[$m['name']] ?? 0;
}
unset($m);

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
                <span class="lupto-kpi-value">20</span>
                <span class="lupto-kpi-trend trend-neutral"><i class="fas fa-minus"></i> No Change</span>
            </div>
            <div class="lupto-kpi-icon bg-blue"><i class="fas fa-city"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="total-spots">
            <div class="lupto-kpi-info">
                <h4>Total Tourist Spots</h4>
                <span class="lupto-kpi-value"><?= count($spots) ?></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +2 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-compass"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="approved-spots">
            <div class="lupto-kpi-info">
                <h4>Total Open Tourist Spots</h4>
                <span class="lupto-kpi-value"><?= count(array_filter($spots, fn($s) => ($s['operation_status'] ?? $s['status'] ?? '') === 'open')) ?></span>
                <span class="lupto-kpi-trend trend-up"><i class="fas fa-arrow-up"></i> +1 today</span>
            </div>
            <div class="lupto-kpi-icon bg-green"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="lupto-kpi-card" data-kpi="pending-spots">
            <div class="lupto-kpi-info">
                <h4>Total Closed Tourist Spots</h4>
                <span class="lupto-kpi-value"><?= count(array_filter($spots, fn($s) => ($s['operation_status'] ?? $s['status'] ?? '') === 'closed')) ?></span>
                <span class="lupto-kpi-trend trend-down"><i class="fas fa-arrow-down"></i> -3 this week</span>
            </div>
            <div class="lupto-kpi-icon bg-orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>

    <!-- LUPTO Full Screen Map Wrapper -->
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
                <button data-action="open-create-form" class="btn btn-primary" style="margin-left: auto;">
                    <i class="fas fa-plus"></i> Add Spot
                </button>
            </div>
        </div>
        
        <div class="map-wrapper">
            <div id="lupto-map" class="lupto-dedicated-map"></div>
            
            <!-- Overlay -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>
            
            <!-- Sidebar -->
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
                    <!-- Content will be dynamically populated -->
                </div>
            </div>
        </div>
    </div>

<!-- -- Filter Bar -->
<div class="filter-bar">
    <!-- Left: filter fields -->
    <div class="filter-bar-inner">
        <!-- Search -->
        <div class="filter-field filter-field-search">
            <label class="filter-label"><i class="fas fa-search"></i> Search</label>
            <div class="filter-input-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Spot name or keyword..." class="filter-input">
            </div>
        </div>
    </div>
    <!-- Right: Municipality, Category, Status, count + view toggle -->
    <div class="filter-bar-right">
        <!-- Municipality -->
        <div class="filter-field">
            <label class="filter-label"><i class="fas fa-map-marker-alt"></i> Municipality</label>
            <select id="filterMunicipality" class="filter-select">
                <option value="">All Municipalities</option>
                <?php foreach ($municipalities as $muni): ?>
                    <option value="<?= htmlspecialchars($muni['name']); ?>"><?= htmlspecialchars($muni['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Category Multi-Select Dropdown -->
        <div class="filter-field" style="position:relative;">
            <label class="filter-label"><i class="fas fa-tag"></i> Category</label>
            <div id="catFilterBtn" class="filter-select" style="cursor:pointer;user-select:none;display:flex;align-items:center;justify-content:space-between;gap:6px;min-width:140px;" onclick="toggleCatDropdown(event)">
                <span id="catFilterLabel">All Categories</span>
                <i class="fas fa-chevron-down" style="font-size:10px;color:#9CA3AF;transition:transform .2s;" id="catChevron"></i>
            </div>
            <div id="catFilterDropdown" style="display:none;position:absolute;top:100%;left:0;z-index:999;background:#fff;border:1px solid #E5E7EB;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:8px 0;min-width:180px;margin-top:4px;max-height:240px;overflow-y:auto;">
                <div style="padding:6px 14px;font-size:11px;color:#9CA3AF;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Select categories</div>
                <?php foreach (['Beach','Mountain','Waterfalls','River','Lake','Island','Cave','Volcano','Forest','Nature Park','Marine Sanctuary','Wildlife Sanctuary','Historical','Cultural Heritage','Religious','Museum','Monument','Landmark','Viewpoint','Adventure','Hiking','Camping','Farm','Eco-Tourism','Garden','Park','Recreation','Hot Spring','Cold Spring','Food Destination','Shopping','Festival Venue','Resort','Other'] as $c): ?>
                <label style="display:flex;align-items:center;gap:10px;padding:7px 14px;cursor:pointer;font-size:13px;transition:background .15s;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='transparent'">
                    <input type="checkbox" class="cat-filter-chk" value="<?= $c ?>" onchange="onCatFilterChange()" style="accent-color:#2563EB;width:15px;height:15px;cursor:pointer;">
                    <?= $c ?>
                </label>
                <?php endforeach; ?>
                <div style="border-top:1px solid #F1F5F9;margin:6px 0 2px;"></div>
                <button onclick="clearCatFilter()" style="width:100%;background:none;border:none;padding:7px 14px;text-align:left;font-size:12px;color:#6B7280;cursor:pointer;" onmouseenter="this.style.color='#2563EB'" onmouseleave="this.style.color='#6B7280'"><i class="fas fa-times-circle"></i> Clear selection</button>
            </div>
        </div>
        <!-- Status -->
        <div class="filter-field">
            <label class="filter-label"><i class="fas fa-circle-dot"></i> Status</label>
            <select id="filterStatus" class="filter-select">
                <option value="">All Status</option>
                <option value="EXISTING">EXISTING</option>
                <option value="POTENTIAL">POTENTIAL</option>
                <option value="EMERGING">EMERGING</option>
            </select>
        </div>
        <span class="filter-count"><span id="spotCount"><?= count($spots); ?></span> tourist spot(s)</span>
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

<!-- Add / Edit Spot Modal -->
<div class="modal" id="spotFormModal">
    <div class="modal-content spot-form-modal-content">
        <!-- Modal Header -->
        <div class="sfm-header">
            <div class="sfm-header-left">
                <div class="sfm-header-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div>
                    <h2 id="formModalTitle">Add New Spot</h2>
                    <p class="sfm-header-sub">Fill in the details below to register a tourist spot</p>
                </div>
            </div>
            <button type="button" class="sfm-close-btn" data-action="close-form-modal" aria-label="Close Modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="sfm-body">
            <form id="spotForm">
                <input type="hidden" id="spotId" value="">
                <input type="hidden" id="municipalityId" value="">

                <!-- ── SECTION: Media -->
                <div class="sfm-section">
                    <div class="sfm-section-label">
                        <i class="fas fa-images"></i> Photo Upload
                    </div>
                    <div id="imageUploadArea" class="sfm-upload-area">
                        <div class="sfm-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p class="sfm-upload-title">Click or drag to upload</p>
                        <p class="sfm-upload-sub">JPEG / PNG &middot; Max 5MB per file</p>
                        <input type="file" id="spotImages" accept="image/jpeg,image/png" multiple style="display:none;">
                    </div>
                    <div id="imagePreviews" class="sfm-image-previews"></div>
                </div>

                <!-- ── SECTION: Basic Info -->
                <div class="sfm-section">
                    <div class="sfm-section-label">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>

                    <!-- Title -->
                    <div class="sfm-field">
                        <label class="sfm-label" for="spotName">
                            Spot Name <span class="sfm-required">*</span>
                        </label>
                        <input type="text" id="spotName" class="sfm-input" maxlength="100" required
                               placeholder="e.g., spot name">
                        <div class="sfm-char-count"><span id="nameCharCount">0</span>/100</div>
                    </div>

                    <!-- Category Multi-Select Dropdown in Form -->
                    <div class="sfm-field">
                        <label class="sfm-label">
                            Categories <span class="sfm-required">*</span>
                        </label>
                        <div class="sfm-category-dropdown-wrap" style="position:relative; width:100%;">
                            <div id="formCatDropdownBtn" class="sfm-select" style="cursor:pointer;user-select:none;display:flex;align-items:center;justify-content:space-between;gap:6px;min-height:38px;padding:8px 12px;border:1px solid #E5E7EB;border-radius:8px;background:#fff;" onclick="toggleFormCatDropdown(event)">
                                <span id="formCatDropdownLabel" style="color:#9CA3AF;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:90%;">Select Categories...</span>
                                <i class="fas fa-chevron-down" style="font-size:12px;color:#9CA3AF;transition:transform .2s;" id="formCatChevron"></i>
                            </div>
                            <div id="formCatDropdown" style="display:none;position:absolute;top:100%;left:0;z-index:9999;background:#fff;border:1px solid #E5E7EB;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:8px 0;width:100%;max-height:240px;overflow-y:auto;margin-top:4px;">
                                <div style="padding:4px 14px;font-size:11px;color:#9CA3AF;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Choose one or more categories</div>
                                <?php 
                                $formCategories = [
                                    'Beach' => 'umbrella-beach',
                                    'Mountain' => 'mountain',
                                    'Waterfalls' => 'water',
                                    'River' => 'water',
                                    'Lake' => 'water',
                                    'Island' => 'umbrella-beach',
                                    'Cave' => 'mountain',
                                    'Volcano' => 'mountain',
                                    'Forest' => 'tree',
                                    'Nature Park' => 'tree',
                                    'Marine Sanctuary' => 'fish',
                                    'Wildlife Sanctuary' => 'paw',
                                    'Historical' => 'landmark',
                                    'Cultural Heritage' => 'landmark',
                                    'Religious' => 'church',
                                    'Museum' => 'museum',
                                    'Monument' => 'monument',
                                    'Landmark' => 'landmark',
                                    'Viewpoint' => 'binoculars',
                                    'Adventure' => 'hiking',
                                    'Hiking' => 'hiking',
                                    'Camping' => 'campground',
                                    'Farm' => 'seedling',
                                    'Eco-Tourism' => 'leaf',
                                    'Garden' => 'seedling',
                                    'Park' => 'tree',
                                    'Recreation' => 'bicycle',
                                    'Hot Spring' => 'hot-tub-person',
                                    'Cold Spring' => 'snowflake',
                                    'Food Destination' => 'utensils',
                                    'Shopping' => 'shopping-cart',
                                    'Festival Venue' => 'masks-theater',
                                    'Resort' => 'hotel',
                                    'Other' => 'star'
                                ];
                                foreach ($formCategories as $name => $icon): 
                                ?>
                                <div class="form-cat-item" data-value="<?= $name ?>" onclick="toggleFormCategory(this, event)" style="display:flex;align-items:center;gap:10px;padding:8px 14px;cursor:pointer;transition:background .15s;font-size:14px;user-select:none;" onmouseenter="this.style.background='#F8FAFC'" onmouseleave="this.style.background='transparent'">
                                    <input type="checkbox" class="form-cat-chk" value="<?= $name ?>" style="pointer-events:none;accent-color:#2563EB;width:15px;height:15px;cursor:pointer;">
                                    <i class="fas fa-<?= $icon ?>" style="width:18px;text-align:center;color:#4B5563;font-size:13px;"></i>
                                    <span><?= $name ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" id="spotCategory" required>
                        <div class="sfm-selected-cats" id="selectedCatsDisplay" style="display:none;">
                            <span class="sfm-selected-label">Selected:</span>
                            <span id="selectedCatsList"></span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="sfm-field">
                        <label class="sfm-label" for="spotClassification">
                            Classification Status <span class="sfm-required">*</span>
                        </label>
                        <select id="spotClassification" class="sfm-select" required>
                            <option value="">— Select Status —</option>
                            <option value="EXISTING">EXISTING</option>
                            <option value="EMERGING">EMERGING</option>
                            <option value="POTENTIAL">POTENTIAL</option>
                        </select>
                    </div>
                </div>

                <!-- ── SECTION: Details -->
                <div class="sfm-section">
                    <div class="sfm-section-label">
                        <i class="fas fa-align-left"></i> Spot Details
                    </div>

                    <!-- Entrance Fee -->
                    <div class="sfm-field">
                        <label class="sfm-label">
                            Entrance Fee <span class="sfm-required">*</span>
                        </label>
                        <div class="sfm-fee-row">
                            <label class="sfm-checkbox-label">
                                <input type="checkbox" id="isFree">
                                <span>Free Entry</span>
                            </label>
                            <div class="sfm-fee-input-wrap">
                                <span class="sfm-fee-prefix">₱</span>
                                <input type="number" id="spotFee" class="sfm-input sfm-fee-input"
                                       min="0" step="0.01" value="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Opening + Closing Time -->
                    <div class="sfm-two-col">
                        <div class="sfm-field">
                            <label class="sfm-label" for="spotOpeningTime">
                                <i class="fas fa-clock" style="color:#6B7280;margin-right:3px;"></i> Opening Time
                            </label>
                            <input type="time" id="spotOpeningTime" class="sfm-input">
                        </div>
                        <div class="sfm-field">
                            <label class="sfm-label" for="spotClosingTime">
                                <i class="fas fa-clock" style="color:#6B7280;margin-right:3px;"></i> Closing Time
                            </label>
                            <input type="time" id="spotClosingTime" class="sfm-input">
                        </div>
                    </div>

                    <!-- Under Maintenance -->
                    <div class="sfm-field" id="maintenance-field">
                        <label class="sfm-maintenance-toggle">
                            <input type="checkbox" id="spotIsMaintenance">
                            <span class="sfm-maintenance-icon"><i class="fas fa-tools"></i></span>
                            <span class="sfm-maintenance-text">Under Maintenance</span>
                            <span class="sfm-maintenance-hint">Hides this spot from tourist view</span>
                        </label>
                    </div>

                    <!-- Description -->
                    <div class="sfm-field">
                        <label class="sfm-label" for="spotDescription">
                            Description <span class="sfm-required">*</span>
                        </label>
                        <textarea id="spotDescription" class="sfm-textarea" rows="4"
                                  maxlength="1000" required
                                  placeholder="Describe this tourist spot — its highlights, what makes it unique, activities available…"></textarea>
                        <div class="sfm-char-count"><span id="descCharCount">0</span>/1000</div>
                    </div>
                </div>

                <!-- ── SECTION: Location -->
                <div class="sfm-section">
                    <div class="sfm-section-label">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </div>
                  
                    <!-- Mini Leaflet Map -->
                    <div class="sfm-map-container">
                        <div id="modalMap" style="height:100%;width:100%;"></div>
                        <div class="sfm-map-hint">
                            <i class="fas fa-hand-pointer"></i> Click map or drag pin to set location
                        </div>
                    </div>

                       <!-- Municipality -->
                    <div class="sfm-field">
                        <label class="sfm-label" for="spotMunicipality">
                            Municipality <span class="sfm-required">*</span>
                        </label>
                        <select id="spotMunicipality" class="sfm-select" required>
                            <option value="">— Select Municipality —</option>
                            <?php foreach ($municipalities as $muni): ?>
                                <option value="<?= $muni['id'] ?>"><?= htmlspecialchars($muni['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Barangay + Lat + Lng inline row -->
                    <div class="sfm-location-row">
                        <div class="sfm-location-barangay">
                            <label class="sfm-label" for="spotBarangay">Barangay</label>
                            <select id="spotBarangay" class="sfm-select">
                                <option value="">— Select Barangay —</option>
                            </select>
                        </div>
                        <div class="sfm-location-coord">
                            <label class="sfm-label" for="spotLatitude">
                                <i class="fas fa-globe" style="color:#6B7280;margin-right:3px;"></i> Latitude
                            </label>
                            <input type="number" id="spotLatitude" class="sfm-input" step="any"
                                   placeholder="e.g., 16.3278">
                        </div>
                        <div class="sfm-location-coord">
                            <label class="sfm-label" for="spotLongitude">
                                <i class="fas fa-map" style="color:#6B7280;margin-right:3px;"></i> Longitude
                            </label>
                            <input type="number" id="spotLongitude" class="sfm-input" step="any"
                                   placeholder="e.g., 120.3663">
                        </div>
                    </div>
                    
                </div>

                

                <!-- Footer Actions -->
                <div class="sfm-footer">
                    <button type="button" class="sfm-btn-cancel" data-action="close-form-modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="sfm-btn-save">
                        <i class="fas fa-check-circle"></i> Save Spot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- -- Cards Grid-->
<div class="cards-grid" id="cardsView">
<?php 
foreach ($spots as $spot):
    $desc = substr($spot['description'] ?? '', 0, 100);
    $status = htmlspecialchars($spot['classification_status'] ?? '');
?>
    <div class="spot-card" 
         data-spot-id="<?= $spot['id']; ?>" 
         data-municipality="<?= htmlspecialchars($spot['municipality_name']); ?>" 
         data-category="<?= htmlspecialchars($spot['category']); ?>" 
         data-status="<?= $status; ?>" 
         data-name="<?= htmlspecialchars(strtolower($spot['name'])); ?>">
        <div class="spot-image">
            <?php if (!empty($spot['photo_url'])): ?>
                <img src="<?= htmlspecialchars($spot['photo_url']); ?>"
                     alt="<?= htmlspecialchars($spot['name']); ?>"
                     style="width:100%;height:100%;object-fit:cover;display:block;"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="spot-image-placeholder" style="display:none;">
                    <i class="fas fa-image"></i><span>Image unavailable</span>
                </div>
            <?php else: ?>
                <div class="spot-image-placeholder">
                    <i class="fas fa-image"></i><span>No image yet</span>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-actions-dropdown">
            <button class="dropdown-toggle" id="card-dropdown-<?= $spot['id']; ?>">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="dropdown-menu" id="card-menu-<?= $spot['id']; ?>">
                <button class="dropdown-item" data-action="view-spot" data-spot-id="<?= $spot['id']; ?>">
                    <i class="fas fa-eye" style="color:#3B82F6;"></i> View All Fields
                </button>
                <button class="dropdown-item" data-action="edit-spot" data-spot-id="<?= $spot['id']; ?>">
                    <i class="fas fa-edit" style="color:#F59E0B;"></i> Edit
                </button>
            </div>
        </div>
        <div class="spot-body">
            <h3><?= htmlspecialchars($spot['name']); ?></h3>
            <div class="muni"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($spot['municipality_name']); ?>, La Union</div>
            <div class="tags">
                <?php foreach (array_filter(array_map('trim', explode(',', $spot['category'] ?? 'Other'))) as $cat): ?>
                    <span class="tag" style="background:#DBEAFE;color:#2563EB;"><?= htmlspecialchars($cat); ?></span>
                <?php endforeach; ?>
                <span class="tag" style="background:#F8FAFC;color:#4B5563;">&#x20B1;<?= number_format($spot['entrance_fee']); ?> per person</span>
                <?php if ($spot['classification_status'] ?? null): ?>
                    <span class="tag" style="background:<?php
                        echo match($spot['classification_status']) {
                            'EXIST' => '#10B981',
                            'POTENTIAL' => '#F59E0B',
                            'EMERGE' => '#8B5CF6',
                            default => '#9CA3AF'
                        };
                    ?>;color:<?php
                        echo match($spot['classification_status']) {
                            'POTENTIAL' => '#1E293B',
                            default => '#FFFFFF'
                        };
                    ?>;"><?php 
                        $statusMap = ['EXIST' => 'EXISTING', 'EMERGE' => 'EMERGING', 'POTENTIAL' => 'POTENTIAL'];
                        echo htmlspecialchars($statusMap[$spot['classification_status']] ?? $spot['classification_status']); 
                    ?></span>
                <?php endif; ?>
            </div>
            <p><?= htmlspecialchars($desc); ?><?= strlen($spot['description'] ?? '') > 100 ? '...' : ''; ?></p>
        </div>
    </div>
<?php endforeach; ?>
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
        <tbody>
        <?php foreach ($spots as $spot): ?>
            <tr data-spot-id="<?= $spot['id']; ?>" 
                data-municipality="<?= htmlspecialchars($spot['municipality_name']); ?>" 
                data-category="<?= htmlspecialchars($spot['category']); ?>" 
                data-status="<?= htmlspecialchars($spot['classification_status'] ?? ''); ?>" 
                data-name="<?= htmlspecialchars(strtolower($spot['name'])); ?>">
                <td style="font-family:'Courier New',monospace;color:#6B7280;">TS-<?= str_pad($spot['id'], 4, '0', STR_PAD_LEFT); ?></td>
                <td><strong><?= htmlspecialchars($spot['name']); ?></strong></td>
                <td><?= htmlspecialchars($spot['municipality_name']); ?></td>
                <td><?php
                    $cats = array_filter(array_map('trim', explode(',', $spot['category'] ?? 'Other')));
                    echo implode(' ', array_map(fn($c) => '<span class="tag" style="background:#DBEAFE;color:#2563EB;font-size:11px;">' . htmlspecialchars($c) . '</span>', $cats));
                ?></td>
                <td><?php if ($spot['classification_status'] ?? null): ?>
                    <span class="tag" style="background:<?php
                        echo match($spot['classification_status']) {
                            'EXIST' => '#10B981',
                            'POTENTIAL' => '#F59E0B',
                            'EMERGE' => '#8B5CF6',
                            default => '#9CA3AF'
                        };
                    ?>;color:<?php
                        echo match($spot['classification_status']) {
                            'POTENTIAL' => '#1E293B',
                            default => '#FFFFFF'
                        };
                    ?>;"><?php 
                        $statusMap = ['EXIST' => 'EXISTING', 'EMERGE' => 'EMERGING', 'POTENTIAL' => 'POTENTIAL'];
                        echo htmlspecialchars($statusMap[$spot['classification_status']] ?? $spot['classification_status']); 
                    ?></span>
                <?php endif; ?></td>
                <td>&#x20B1;<?= number_format($spot['entrance_fee']); ?></td>
                <td><?= date('M j, Y', strtotime($spot['created_at'])); ?></td>
                <td style="text-align:right;">
                    <div class="table-actions-dropdown">
                        <button class="dropdown-toggle" id="tbl-dropdown-<?= $spot['id']; ?>">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu" id="tbl-menu-<?= $spot['id']; ?>">
                            <button class="dropdown-item" data-action="view-spot" data-spot-id="<?= $spot['id']; ?>">
                                <i class="fas fa-eye" style="color:#3B82F6;"></i> View All Fields
                            </button>
                            <button class="dropdown-item" data-action="edit-spot" data-spot-id="<?= $spot['id']; ?>">
                                <i class="fas fa-edit" style="color:#F59E0B;"></i> Edit
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Save Confirmation Modal -->
<div class="modal" id="saveConfirmModal" style="z-index: 10002;">
    <div class="modal-content" style="max-width: 420px; border-radius: 16px; overflow: hidden;">
        <div style="background: #DBEAFE; padding: 28px 28px 16px 28px; text-align: center;">
            <div style="width: 56px; height: 56px; background: #2563EB; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <i class="fas fa-save" style="color: white; font-size: 22px;"></i>
            </div>
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #1E3A8A;">Save Tourist Spot</h3>
        </div>
        <div style="padding: 20px 28px 28px 28px;">
            <p style="text-align: center; color: #4B5563; margin: 0 0 24px 0; font-size: 14px;">Are you sure you want to save this?</p>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-outline" data-action="close-save-confirm" style="flex: 1; justify-content: center;">
                    <i class="fas fa-times" style="margin-right: 6px;"></i> No
                </button>
                <button class="btn btn-primary" id="saveConfirmBtn" data-action="confirm-save-spot" style="flex: 1; justify-content: center;">
                    <i class="fas fa-check" style="margin-right: 6px;"></i> Yes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- -- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    // Pass data from PHP to JavaScript — must be defined before map-view-api.js runs
    window.touristSpotsData = <?= json_encode($spots) ?>;
    window.municipalitiesData = <?= json_encode($municipalities) ?>;
</script>
<script src="../../scripts/functions/LUPTO/map-view-api.js?v=<?= time() ?>"></script>

<script type="module">
import { initializeAll } from '../../scripts/functions/LUPTO/tourist-spots-api.js?v=<?= time() ?>';

// -- Data injected from PHP
const spotsData = <?= json_encode($spots) ?>;
const municipalData = <?= json_encode($municipalities) ?>;

// -- Initialize Everything (Event Listeners for Add Spot, etc.)
initializeAll(spotsData, municipalData);
</script>

<!-- Multi-category filter helpers (classic script) -->
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
?>