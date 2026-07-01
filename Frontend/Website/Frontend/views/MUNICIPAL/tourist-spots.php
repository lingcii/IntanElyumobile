<?php
require_once __DIR__ . '/../../session-bridge.php';
// Check role
if ($_SESSION['user_role'] !== 'municipal' && !str_ends_with($_SESSION['user_role'], '_mto')) {
    header('Location: ../../login.php');
    exit;
}

$pageTitle = 'Municipal Tourist Spots';

// ── Pull data from Laravel API ─────────────────────────────────────────────
$laravelBase = 'http://127.0.0.1:8000/api';
$cookieStr   = '';
foreach ($_COOKIE as $name => $value) {
    $cookieStr .= $name . '=' . urlencode($value) . '; ';
}

function muniSpotsApiGet(string $url, string $cookieStr): array {
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

$muniId = $_SESSION['user_municipality_id'] ?? null;

// ── Parallel cURL: fetch municipality + spots simultaneously ─────────────────
$curlOpts = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Cookie: ' . $cookieStr],
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
];
$chMuni  = curl_init("{$laravelBase}/municipalities/{$muniId}");
$chSpots = curl_init("{$laravelBase}/municipal/tourist-spots");
curl_setopt_array($chMuni,  $curlOpts);
curl_setopt_array($chSpots, $curlOpts);

$mh = curl_multi_init();
curl_multi_add_handle($mh, $chMuni);
curl_multi_add_handle($mh, $chSpots);
$running = null;
do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);

$muniResponse  = json_decode(curl_multi_getcontent($chMuni)  ?: '', true) ?? [];
$spotsResponse = json_decode(curl_multi_getcontent($chSpots) ?: '', true) ?? [];
curl_multi_remove_handle($mh, $chMuni);
curl_multi_remove_handle($mh, $chSpots);
curl_multi_close($mh);

$municipality = $muniResponse['municipality'] ?? $muniResponse['data'] ?? $muniResponse;
if (empty($municipality) || !isset($municipality['name'])) {
    $municipality = ['name' => 'Your Municipality', 'id' => $muniId];
}

$spots = $spotsResponse['data'] ?? (isset($spotsResponse[0]) ? $spotsResponse : []);

// De-duplicate by id
$uniqueSpots = [];
foreach ($spots as $spot) {
    $id = $spot['id'] ?? null;
    if ($id && !isset($uniqueSpots[$id])) $uniqueSpots[$id] = $spot;
}
$spots = array_values($uniqueSpots);

// Stats for summary cards
$totalSpots     = count($spots);
$totalVisits    = array_sum(array_column($spots, 'visits'));
$countExist     = count(array_filter($spots, fn($s) => strtoupper($s['classification_status'] ?? '') === 'EXIST'));
$countPotential = count(array_filter($spots, fn($s) => strtoupper($s['classification_status'] ?? '') === 'POTENTIAL'));
$countEmerge    = count(array_filter($spots, fn($s) => strtoupper($s['classification_status'] ?? '') === 'EMERGE'));

ob_start();
?>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <!-- Tourist Spots CSS - use MUNICIPAL CSS if available, else LUPTO -->
    <link rel="stylesheet" href="../../css/LUPTO/tourist-spots.css">
   <link rel="stylesheet" href="../../css/MUNICIPAL/tourist-spots.css">

<?php
$extraHeadContent = ob_get_clean();

ob_start();
?>

    <!-- ===== Summary Stats Cards ===== -->
    <div class="stats-grid" style="margin-bottom: 20px;">

        <!-- Total Tourist Spots -->
        <div class="stat-card" style="background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; position:relative; overflow:hidden;">
            <div style="position:absolute; left:0; top:0; width:4px; height:100%; background:#2563EB; border-radius:2px 0 0 2px;"></div>
            <div style="width:46px; height:46px; border-radius:10px; background:#DBEAFE; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-map-marker-alt" style="color:#2563EB; font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Total Tourist Spots</div>
                <div style="font-size:28px; font-weight:800; color:#1E293B; line-height:1;"><?php echo $totalSpots; ?></div>
                <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">In <?php echo htmlspecialchars($municipality['name']); ?></div>
            </div>
        </div>

        <!-- Total Tourist Visits -->
        <div class="stat-card" style="background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; position:relative; overflow:hidden;">
            <div style="position:absolute; left:0; top:0; width:4px; height:100%; background:#16A34A; border-radius:2px 0 0 2px;"></div>
            <div style="width:46px; height:46px; border-radius:10px; background:#DCFCE7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-users" style="color:#16A34A; font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">Total Tourist Visits</div>
                <div style="font-size:28px; font-weight:800; color:#1E293B; line-height:1;"><?php echo number_format($totalVisits); ?></div>
                <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">Recorded visits</div>
            </div>
        </div>

        <!-- EXISTING -->
        <div class="stat-card" style="background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; position:relative; overflow:hidden;">
            <div style="position:absolute; left:0; top:0; width:4px; height:100%; background:#0891B2; border-radius:2px 0 0 2px;"></div>
            <div style="width:46px; height:46px; border-radius:10px; background:#CFFAFE; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-check-circle" style="color:#0891B2; font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">EXISTING</div>
                <div style="font-size:28px; font-weight:800; color:#1E293B; line-height:1;"><?php echo $countExist; ?></div>
                <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">Established spots</div>
            </div>
        </div>

        <!-- POTENTIAL -->
        <div class="stat-card" style="background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; position:relative; overflow:hidden;">
            <div style="position:absolute; left:0; top:0; width:4px; height:100%; background:#D97706; border-radius:2px 0 0 2px;"></div>
            <div style="width:46px; height:46px; border-radius:10px; background:#FEF3C7; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-star" style="color:#D97706; font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">POTENTIAL</div>
                <div style="font-size:28px; font-weight:800; color:#1E293B; line-height:1;"><?php echo $countPotential; ?></div>
                <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">Developing spots</div>
            </div>
        </div>

        <!-- EMERGING -->
        <div class="stat-card" style="background:#fff; border:1px solid #E5E7EB; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; position:relative; overflow:hidden;">
            <div style="position:absolute; left:0; top:0; width:4px; height:100%; background:#7C3AED; border-radius:2px 0 0 2px;"></div>
            <div style="width:46px; height:46px; border-radius:10px; background:#EDE9FE; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fas fa-seedling" style="color:#7C3AED; font-size:20px;"></i>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; color:#6B7280; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:3px;">EMERGING</div>
                <div style="font-size:28px; font-weight:800; color:#1E293B; line-height:1;"><?php echo $countEmerge; ?></div>
                <div style="font-size:11px; color:#9CA3AF; margin-top:2px;">Emerging spots</div>
            </div>
        </div>

    </div>

    <!-- ===== Map Section ===== -->
    <div class="map-section">
        <div class="map-controls">
            <div class="map-tabs">
                <button class="map-tab active" data-view="street"><i class="fas fa-map"></i> Street Map</button>
                <button class="map-tab" data-view="satellite"><i class="fas fa-satellite"></i> Satellite</button>
            </div>
            <div class="map-actions">
                <button onclick="openCreateForm()" class="btn btn-primary"><i class="fas fa-plus"></i> Add Spot</button>
            </div>
        </div>
        <div style="position: relative;">
            <div id="touristMap"></div>
            <div class="map-legend">
                <div style="font-weight: 700; margin-bottom: 8px;">Legend</div>
                <div class="legend-item"><div class="legend-dot" style="background: #DC2626;"></div> Municipality</div>
            </div>
        </div>
    </div>

        <!-- Filter & Controls Bar -->
    <div class="filter-bar">
        <!-- Left: filter fields -->
        <div class="filter-bar-inner">
            <!-- Search -->
            <div class="filter-field filter-field-search">
                <label class="filter-label"><i class="fas fa-search"></i> Search</label>
                <div class="filter-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Spot name or keyword..." class="filter-input" oninput="filterSpots();">
                </div>
            </div>
        </div>
        <!-- Right: Category, Status, count + view toggle -->
        <div class="filter-bar-right">


            <!-- Category Multi-Select Dropdown -->
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

            <!-- Status -->

            <div class="filter-field">
                <label class="filter-label"><i class="fas fa-circle-dot"></i> Status</label>
                <select id="filterStatus" class="filter-select" onchange="filterSpots()">
                    <option value="">All Status</option>
                    <option value="EXISTING">EXISTING</option>
                    <option value="EMERGING">EMERGING</option>
                    <option value="POTENTIAL">POTENTIAL</option>
                </select>
            </div>
            <span class="filter-count"><span id="filterCount"><?php echo $totalSpots; ?></span> spot(s)</span>
            <div class="view-toggle">
                <button class="active" id="viewCards" title="Card View"><i class="fas fa-th"></i></button>
                <button id="viewTable" title="Table View"><i class="fas fa-list"></i></button>
            </div>
        </div>
    </div>


    <!-- Cards Grid -->
    <div class="cards-grid" id="cardsView">
        <?php foreach($spots as $spot): ?>
            <div class="spot-card" data-spot-id="<?php echo $spot['id']; ?>">
                <div class="spot-image">
                    <?php if (!empty($spot['photo_url'])): ?>
                        <img src="<?php echo htmlspecialchars($spot['photo_url']) . '?t=' . time(); ?>" 
                             alt="<?php echo htmlspecialchars($spot['name']); ?>"
                             style="width:100%; height:100%; object-fit:cover; display:block;"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="spot-image-placeholder" style="display:none; width:100%; height:100%; align-items:center; justify-content:center; flex-direction:column; gap:8px; color:#9CA3AF; background:#F3F4F6;">
                            <i class="fas fa-image" style="font-size:32px;"></i>
                            <span style="font-size:11px;">Image unavailable</span>
                        </div>
                    <?php else: ?>
                        <div class="spot-image-placeholder" style="display:flex; width:100%; height:100%; align-items:center; justify-content:center; flex-direction:column; gap:8px; color:#9CA3AF; background:#F3F4F6;">
                            <i class="fas fa-image" style="font-size:32px;"></i>
                            <span style="font-size:11px;">No image yet</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-actions-dropdown">
                    <button class="dropdown-toggle" onclick="toggleDropdown('card-<?php echo $spot['id']; ?>')">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu" id="card-<?php echo $spot['id']; ?>">
                        <button class="dropdown-item" onclick="openModal('<?php echo $spot['id']; ?>')">
                            <i class="fas fa-eye" style="color: #3B82F6;"></i> View All Fields
                        </button>
                        <button class="dropdown-item" onclick="editSpot(<?php echo $spot['id']; ?>)">
                            <i class="fas fa-edit" style="color: #6B7280;"></i> Edit Spot
                        </button>
                        <button class="dropdown-item" onclick="deleteSpot(<?php echo $spot['id']; ?>)">
                            <i class="fas fa-trash" style="color: #EF4444;"></i> Delete
                        </button>
                    </div>
                </div>
                <div class="spot-body">
                    <h3><?php echo htmlspecialchars($spot['name']); ?></h3>
                    <div class="muni"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($spot['municipality_name']); ?>, La Union</div>
                    <div class="tags">
                        <?php foreach (array_filter(array_map('trim', explode(',', $spot['category'] ?? 'Other'))) as $cat): ?>
                            <span class="tag" style="background: #DBEAFE; color: #2563EB;"><?php echo htmlspecialchars($cat); ?></span>
                        <?php endforeach; ?>
                        <span class="tag" style="background: #F8FAFC; color: #4B5563;">₱<?php echo number_format($spot['entrance_fee']); ?> per person</span>
                        <?php if ($spot['classification_status'] ?? null): ?>
                            <?php
                                $statusMap = ['EXIST' => 'EXISTING', 'EMERGE' => 'EMERGING', 'POTENTIAL' => 'POTENTIAL'];
                                $statusColor = match($spot['classification_status']) {
                                    'EXIST' => '#10B981',
                                    'POTENTIAL' => '#F59E0B',
                                    'EMERGE' => '#8B5CF6',
                                    default => '#9CA3AF'
                                };
                                $statusTextColor = $spot['classification_status'] === 'POTENTIAL' ? '#1E293B' : '#FFFFFF';
                            ?>
                            <span class="tag" style="background: <?php echo $statusColor; ?>; color: <?php echo $statusTextColor; ?>;"><?php echo $statusMap[$spot['classification_status']]; ?></span>
                        <?php endif; ?>
                    </div>
                    <p><?php echo substr(htmlspecialchars($spot['description'] ?? ''), 0, 100); ?>...</p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Table View -->
    <div id="tableView" style="display: none;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Spot ID</th>
                    <th>Spot Name</th>
                    <th>Category</th>
                    <th>Entry Fee</th>
                    <th>Submitted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($spots as $spot): ?>
                    <tr data-spot-id="<?php echo $spot['id']; ?>">
                        <td style="font-family: 'Courier New', monospace; color: #6B7280;">TS-<?php echo str_pad($spot['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><strong><?php echo htmlspecialchars($spot['name']); ?></strong></td>
                        <td><?php
                            $cats = array_filter(array_map('trim', explode(',', $spot['category'] ?? 'Other')));
                            echo implode(' ', array_map(fn($c) => '<span class="tag" style="background:#DBEAFE;color:#2563EB;font-size:11px;">' . htmlspecialchars($c) . '</span>', $cats));
                        ?></td>
                        <td>₱<?php echo number_format($spot['entrance_fee']); ?> per person</td>
                        <td><?php echo date('M j, Y', strtotime($spot['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <div class="table-actions-dropdown">
                                <button class="dropdown-toggle" onclick="toggleDropdown('table-<?php echo $spot['id']; ?>')">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu" id="table-<?php echo $spot['id']; ?>">
                                    <button class="dropdown-item" onclick="openModal('<?php echo $spot['id']; ?>')">
                                        <i class="fas fa-eye" style="color: #3B82F6;"></i> View All Fields
                                    </button>
                                    <button class="dropdown-item" onclick="editSpot(<?php echo $spot['id']; ?>)">
                                        <i class="fas fa-edit" style="color: #6B7280;"></i> Edit Spot
                                    </button>
                                    <button class="dropdown-item" onclick="deleteSpot(<?php echo $spot['id']; ?>)">
                                        <i class="fas fa-trash" style="color: #EF4444;"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Spot Detail Modal -->
    <div class="modal" id="spotModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Spot Details</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <p>Spot details will be loaded here...</p>
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
                <button type="button" class="sfm-close-btn" id="sfmCloseBtn" aria-label="Close Modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sfm-body">
                <form id="spotForm">
                    <input type="hidden" id="spotId" value="">

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
                                Spot Title <span class="sfm-required">*</span>
                            </label>
                            <input type="text" id="spotName" class="sfm-input" maxlength="100" required
                                   placeholder="e.g., Tangadan Falls">
                            <div class="sfm-char-count"><span id="nameCharCount">0</span>/100</div>
                        </div>

                        <!-- Category Multi-Select -->
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
                            <label class="sfm-label">Entrance Fee <span class="sfm-required">*</span></label>
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

                        <!-- Operating Hours -->
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
                        <div class="sfm-field">
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

                        <!-- Mini Map -->
                        <div class="sfm-map-container">
                            <div id="modalMap" style="height:100%;width:100%;"></div>
                            <div class="sfm-map-hint">
                                <i class="fas fa-hand-pointer"></i> Click map or drag pin to set location
                            </div>
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
                        <button type="button" class="sfm-btn-cancel" id="sfmCancelBtn">
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
                    <button class="btn btn-outline" id="saveConfirmNoBtn" style="flex: 1; justify-content: center;">
                        <i class="fas fa-times" style="margin-right: 6px;"></i> No
                    </button>
                    <button class="btn btn-primary" id="saveConfirmBtn" style="flex: 1; justify-content: center;">
                        <i class="fas fa-check" style="margin-right: 6px;"></i> Yes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteConfirmModal" style="z-index: 10001;">
        <div class="modal-content" style="max-width: 420px; border-radius: 16px; overflow: hidden;">
            <div style="background: #FEE2E2; padding: 28px 28px 16px 28px; text-align: center;">
                <div style="width: 56px; height: 56px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <i class="fas fa-trash" style="color: white; font-size: 22px;"></i>
                </div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #991B1B;">Delete Tourist Spot</h3>
            </div>
            <div style="padding: 20px 28px 28px 28px;">
                <p style="text-align: center; color: #4B5563; margin: 0 0 6px 0; font-size: 14px;">Are you sure you want to delete</p>
                <p style="text-align: center; font-weight: 700; color: #1E293B; font-size: 15px; margin: 0 0 16px 0;" id="deleteSpotName">"Spot Name"</p>
                <p style="text-align: center; color: #9CA3AF; font-size: 12px; margin: 0 0 24px 0;">This action cannot be undone.</p>
                <div style="display: flex; gap: 12px;">
                    <button class="btn btn-outline" onclick="closeDeleteModal()" style="flex: 1; justify-content: center;">
                        <i class="fas fa-times" style="margin-right: 6px;"></i> Cancel
                    </button>
                    <button class="btn btn-danger" id="deleteConfirmBtn" onclick="confirmDeleteSpot()" style="flex: 1; justify-content: center;">
                        <i class="fas fa-trash" style="margin-right: 6px;"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <!-- Page data (consumed by tourist-spots-page.js) -->
    <script>
        window.spotsData = <?php echo json_encode($spots); ?>;
        window.municipalityData = <?php echo json_encode($municipality); ?>;
    </script>

    <!-- API layer (classic script — must load before page logic) -->
    <script src="../../scripts/functions/MUNICIPAL/tourist-spots-api.js?v=<?= time() ?>"></script>

    <!-- Page logic (classic script — uses window.TouristSpotsAPI) -->
    <script src="../../scripts/functions/MUNICIPAL/tourist-spots-page.js?v=<?= time() ?>"></script>

    <!-- Multi-category filter helpers -->
    <script>
    // ── Category multi-select dropdown helpers ─────────────────────────────
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
        // Tint the button when filters are active
        const btn = document.getElementById('catFilterBtn');
        btn.style.borderColor = selected.length ? '#2563EB' : '';
        btn.style.color       = selected.length ? '#2563EB' : '';
        filterSpots();
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