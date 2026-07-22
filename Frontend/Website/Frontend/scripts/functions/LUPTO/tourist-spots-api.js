// ════════════════════════════════════════════════════════════════════════════════
// LUPTO TOURIST SPOTS - API & UTILITIES
// ════════════════════════════════════════════════════════════════════════════════

// Minimal guard: api-config.js is always loaded before this file.
// Only patch getCsrfToken in case an older cached version is missing it.
if (window.API_CONFIG && typeof window.API_CONFIG.getCsrfToken !== 'function') {
    window.API_CONFIG.getCsrfToken = function () {
        const match = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
        if (match) return decodeURIComponent(match.trim().split('=').slice(1).join('='));
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    };
}

const API_BASE = `${window.API_CONFIG?.BASE_URL || ('http://' + (window.location.hostname || '127.0.0.1') + ':8000')}/api/tourist-spots`;

function getSpotImageUploadUrl() {
    if (window.TOURIST_SPOT_UPLOAD_URL) {
        return window.TOURIST_SPOT_UPLOAD_URL;
    }
    return new URL('../../api/upload-spot-image.php', window.location.href).href;
}

function withTimeout(promise, ms, label = 'Operation') {
    return Promise.race([
        promise,
        new Promise((_, reject) => {
            setTimeout(() => reject(new Error(`${label} timed out after ${Math.round(ms / 1000)}s`)), ms);
        })
    ]);
}

// ── Map Global Variables
let map, markerCluster;
let modalMap, modalMarker;
const mapLayers = {
    street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18
    }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri',
        maxZoom: 18
    }),
};

// ── Form & Image Variables
let uploadedImages = [];
let pendingSaveData = null;

// Generate a preview URL for a file
function getFilePreviewUrl(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = () => resolve(null);
        reader.readAsDataURL(file);
    });
}


// API CALLS - PROPERLY MAPPED TO LARAVEL ENDPOINTS


export const getSpots = async () => {
    return await window.API_CONFIG.get(`${API_BASE}`);
};

export const getSpot = async (id) => {
    return await window.API_CONFIG.get(`${API_BASE}/${id}`);
};

export const createSpot = async (data) => {
    return await window.API_CONFIG.post(`${API_BASE}`, data);
};

export const updateSpot = async (id, data) => {
    return await window.API_CONFIG.put(`${API_BASE}/${id}`, data);
};

export const deleteSpot = async (id) => {
    return await window.API_CONFIG.delete(`${API_BASE}/${id}`);
};

// Make API functions available on window for global access
window.getSpots = getSpots;
window.getSpot = getSpot;
window.createSpot = createSpot;
window.updateSpot = updateSpot;
window.deleteSpot = deleteSpot;

// Compress/resize image before upload (huge speedup!)
const compressImage = async (file, maxWidth = 1280, maxHeight = 720, quality = 0.7) => {
    return new Promise((resolve, reject) => {
        if (!file.type.startsWith('image/')) {
            resolve(file);
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                let { width, height } = img;

                // Calculate new dimensions while maintaining aspect ratio
                if (width > maxWidth) {
                    height = (height * maxWidth) / width;
                    width = maxWidth;
                }
                if (height > maxHeight) {
                    width = (width * maxHeight) / height;
                    height = maxHeight;
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (!blob) {
                        resolve(file);
                        return;
                    }
                    const compressedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
};

export const uploadImage = async (file) => {
    let processedFile = file;
    try {
        processedFile = await withTimeout(compressImage(file), 12000, 'Image processing');
    } catch (err) {
        console.warn('[upload] Compression skipped:', err.message);
        processedFile = file;
    }

    const formData = new FormData();
    formData.append('image', processedFile);

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 45000);

    try {
        const response = await fetch(getSpotImageUploadUrl(), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: formData,
            signal: controller.signal
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch {
            throw new Error(`Invalid server response (HTTP ${response.status})`);
        }

        if (!response.ok) {
            throw new Error(data.error || data.message || `Upload failed: HTTP ${response.status}`);
        }

        if (!data.success || !data.photo_url) {
            throw new Error(data.error || 'Upload failed');
        }

        return data;
    } catch (err) {
        if (err.name === 'AbortError') {
            throw new Error('Upload timed out. Check that Laravel is running on port 8000.');
        }
        throw err;
    } finally {
        clearTimeout(timeoutId);
    }
};

// Upload multiple images in parallel
const uploadMultipleImages = async (files) => {
    return Promise.all(files.map(file => uploadImage(file)));
};


// STATUS AND CLASSIFICATION HELPERS


// Database stores: EXIST, EMERGE, POTENTIAL
// Form displays: EXISTING, EMERGING, POTENTIAL
const statusDisplayMap = {
    'EXIST': 'EXISTING',
    'EMERGE': 'EMERGING',
    'POTENTIAL': 'POTENTIAL'
};

const statusReverseMap = {
    'EXISTING': 'EXIST',
    'EMERGING': 'EMERGE',
    'POTENTIAL': 'POTENTIAL'
};

export function getClassificationStyle(status) {
    const styles = {
        'EXIST': { bg: '#10B981', text: '#FFFFFF', label: 'EXISTING' },
        'EMERGE': { bg: '#8B5CF6', text: '#FFFFFF', label: 'EMERGING' },
        'POTENTIAL': { bg: '#F59E0B', text: '#1E293B', label: 'POTENTIAL' },
        'EXISTING': { bg: '#10B981', text: '#FFFFFF', label: 'EXISTING' },
        'EMERGING': { bg: '#8B5CF6', text: '#FFFFFF', label: 'EMERGING' },
        'default': { bg: '#9CA3AF', text: '#FFFFFF', label: 'UNKNOWN' }
    };
    return styles[status] || styles['default'];
}

export function getClassificationBadgeHTML(status) {
    if (!status) return '';
    const style = getClassificationStyle(status);
    return `<span class="tag" style="background:${style.bg};color:${style.text};">${style.label}</span>`;
}

// TOAST NOTIFICATIONS

export function showToast(msg, type = 'success') {
    const colors = {
        success: '#16A34A',
        danger: '#DC2626',
        info: '#4338CA',
        warning: '#F59E0B'
    };
    const icons = {
        success: 'fa-check-circle',
        danger: 'fa-times-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-circle'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
        background: ${colors[type] || '#1E293B'};
        color: white;
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 360px;
        animation: slideIn 0.3s ease;
    `;
    toast.innerHTML = `<i class="fas ${icons[type] || 'fa-bell'}"></i> ${msg}`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ════════════════════════════════════════════════════════════════════════════════
// MAP INITIALIZATION
// ════════════════════════════════════════════════════════════════════════════════

export function getCategoryIcon(categoryStr) {
    if (!categoryStr) return 'map-marker-alt';
    const categories = categoryStr.split(',').map(c => c.trim().toLowerCase());
    const mapping = {
        'beach': 'umbrella-beach',
        'beaches': 'umbrella-beach',
        'mountain': 'mountain',
        'mountains': 'mountain',
        'mountains & hiking': 'mountain',
        'waterfall': 'water',
        'waterfalls': 'water',
        'river': 'water',
        'lake': 'water',
        'island': 'umbrella-beach',
        'cave': 'mountain',
        'volcano': 'mountain',
        'forest': 'tree',
        'nature park': 'tree',
        'marine sanctuary': 'fish',
        'wildlife sanctuary': 'paw',
        'historical': 'landmark',
        'cultural heritage': 'landmark',
        'religious': 'church',
        'museum': 'museum',
        'monument': 'monument',
        'landmark': 'landmark',
        'viewpoint': 'binoculars',
        'adventure': 'hiking',
        'hiking': 'hiking',
        'camping': 'campground',
        'farm': 'seedling',
        'eco-tourism': 'leaf',
        'garden': 'seedling',
        'park': 'tree',
        'recreation': 'bicycle',
        'hot spring': 'hot-tub-person',
        'cold spring': 'snowflake',
        'food destination': 'utensils',
        'shopping': 'shopping-cart',
        'festival venue': 'masks-theater',
        'resort': 'hotel',
        'resorts': 'hotel',
        'other': 'star'
    };
    for (const cat of categories) {
        if (mapping[cat]) {
            return mapping[cat];
        }
    }
    return 'map-marker-alt';
}

export function getCategoryColor(categoryStr) {
    if (!categoryStr) return '#3B82F6';
    const cat = categoryStr.split(',')[0].trim().toLowerCase();
    const colors = {
        'beach': '#0EA5E9',
        'beaches': '#0EA5E9',
        'waterfalls': '#06B6D4',
        'waterfall': '#06B6D4',
        'nature park': '#10B981',
        'forest': '#059669',
        'cultural heritage': '#F59E0B',
        'historical': '#D97706',
        'museum': '#8B5CF6',
        'religious': '#EC4899',
        'farm': '#84CC16',
        'eco-tourism': '#10B981',
        'cold spring': '#06B6D4',
        'hot spring': '#EF4444',
        'resort': '#6366F1'
    };
    return colors[cat] || '#3B82F6';
}

export function initMap(spotsData, municipalData) {
    if (!document.getElementById('touristMap')) return;

    let bounds;
    if (municipalData && municipalData.length > 0) {
        bounds = L.latLngBounds(municipalData.map(m => [m.latitude, m.longitude])).pad(0.08);
    } else {
        bounds = L.latLngBounds([[16.2, 120.2], [16.8, 120.5]]);
    }

    if (map) {
        // Clear all layers on active map
        if (markerCluster) {
            markerCluster.clearLayers();
        }
        // Remove non-tile layers
        map.eachLayer(layer => {
            if (layer !== mapLayers.street && layer !== mapLayers.satellite) {
                map.removeLayer(layer);
            }
        });
    } else {
        map = L.map('touristMap', { minZoom: 10, maxBoundsViscosity: 1.0 });
        mapLayers.street.addTo(map);
    }

    // Save map instance on the DOM element for the SPA router
    document.getElementById('touristMap')._leaflet_map = map;

    map.fitBounds(bounds);
    markerCluster = L.markerClusterGroup();
    map.addLayer(markerCluster);

    // Function to render spot markers for a specific municipality
    function showMunicipalitySpots(muniName) {
        markerCluster.clearLayers();
        const spots = spotsData.filter(s =>
            s.latitude && s.longitude &&
            s.municipality_name &&
            s.municipality_name.toLowerCase().trim() === muniName.toLowerCase().trim()
        );

        spots.forEach(s => {
            const iconColor = getCategoryColor(s.category);
            const icon = L.divIcon({
                className: '',
                html: `<div style="background:${iconColor};width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);"><i class="fas fa-${getCategoryIcon(s.category)}" style="font-size:13px;"></i></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28]
            });
            L.marker([s.latitude, s.longitude], { icon })
                .bindPopup(`<strong>${s.name}</strong><br><small>${s.category}</small>`)
                .addTo(markerCluster);
        });
    }

    // Municipality markers
    municipalData.forEach(m => {
        const icon = L.divIcon({
            className: '',
            html: `<div class="muni-badge" style="background:#DC2626;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3);font-weight:700;font-size:13px;cursor:pointer;">${m.spots}</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 36]
        });

        const marker = L.marker([m.latitude, m.longitude], { icon, zIndexOffset: 1000 })
            .bindTooltip(m.name, { permanent: true, direction: 'bottom', offset: [0, 20], opacity: .9 })
            .bindPopup(`
                <div style="font-family:'Inter', sans-serif; padding: 4px; min-width: 150px;">
                    <h4 style="margin:0 0 4px 0; color:#1E293B; font-size:14px; font-weight:700;">${m.name}</h4>
                    <p style="margin:0 0 8px 0; color:#6B7280; font-size:12px;"><i class="fas fa-map-marker-alt"></i> La Union</p>
                    <div style="border-top:1px solid #E2E8F0; padding-top:6px; font-size:12px; color:#475569;">
                        <strong>Total Spots:</strong> ${m.spots}
                    </div>
                </div>
            `)
            .addTo(map);

        marker.on('click', function () {
            // Smoothly zoom in to the municipality
            map.setView([m.latitude, m.longitude], 13);
            // Display spots for this municipality
            showMunicipalitySpots(m.name);
        });
    });

    // Add a custom button to reset view to the whole La Union province
    if (!map.resetControlAdded) {
        const ResetControl = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function () {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-custom-control');
                container.innerHTML = `
                    <button title="Reset to La Union Province" style="background: white; border: none; width: 34px; height: 34px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 1px 5px rgba(0,0,0,0.4); transition: background-color 0.2s;">
                        <i class="fas fa-globe-asia" style="color: #3B82F6; font-size: 16px;"></i>
                    </button>
                `;
                container.onclick = function (e) {
                    e.stopPropagation();
                    map.fitBounds(bounds);
                    if (markerCluster) {
                        markerCluster.clearLayers();
                    }
                };
                return container;
            }
        });
        map.addControl(new ResetControl());
        map.resetControlAdded = true;
    }

    setTimeout(() => map.invalidateSize(), 300);
}

// ── Map Layer Toggle
export function setupMapLayerToggle() {
    document.querySelectorAll('.map-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.map-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            Object.values(mapLayers).forEach(l => {
                if (map.hasLayer(l)) map.removeLayer(l);
            });
            mapLayers[this.dataset.view].addTo(map);
        });
    });
}

// ════════════════════════════════════════════════════════════════════════════════
// FILTERING LOGIC
// ════════════════════════════════════════════════════════════════════════════════

export function filterSpots(searchValue = '', municipalityValue = '', selectedCats = [], statusValue = '') {
    let visibleCount = 0;
    const mappedStatus = statusReverseMap[statusValue] || statusValue;

    // Helper: does the spot's category field match any of the selected categories?
    // Handles both single values ("Beach") and comma-separated ("Beach,Mountain").
    function matchesCat(cardCat) {
        if (!selectedCats || selectedCats.length === 0) return true;
        const spotCats = (cardCat || '').split(',').map(s => s.trim());
        return selectedCats.some(fc => spotCats.includes(fc));
    }

    // Filter cards
    document.querySelectorAll('#cardsView .spot-card').forEach(card => {
        const nameMatch = !searchValue || card.dataset.name.includes(searchValue.toLowerCase());
        const muniMatch = !municipalityValue || card.dataset.municipality === municipalityValue;
        const catMatch = matchesCat(card.dataset.category);
        const statusMatch = !statusValue || card.dataset.status === mappedStatus;

        const show = nameMatch && muniMatch && catMatch && statusMatch;
        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });

    // Filter table
    document.querySelectorAll('#tableView tbody tr').forEach(row => {
        const nameMatch = !searchValue || row.dataset.name.includes(searchValue.toLowerCase());
        const muniMatch = !municipalityValue || row.dataset.municipality === municipalityValue;
        const catMatch = matchesCat(row.dataset.category);
        const statusMatch = !statusValue || row.dataset.status === mappedStatus;

        const show = nameMatch && muniMatch && catMatch && statusMatch;
        row.style.display = show ? '' : 'none';
    });

    // Update count
    const countEl = document.getElementById('spotCount');
    if (countEl) countEl.textContent = visibleCount;

    return visibleCount;
}

// ── Dropdown Toggle
export function toggleDropdown(menuId) {
    const menu = document.getElementById(menuId);
    const isOpen = menu.style.display === 'block';
    document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    if (!isOpen) menu.style.display = 'block';
}

// ── View Toggle (Cards/Table)
export function setupViewToggle() {
    document.getElementById('viewCards')?.addEventListener('click', function () {
        this.classList.add('active');
        document.getElementById('viewTable').classList.remove('active');
        document.getElementById('cardsView').style.display = 'grid';
        document.getElementById('tableView').style.display = 'none';
    });

    document.getElementById('viewTable')?.addEventListener('click', function () {
        this.classList.add('active');
        document.getElementById('viewCards').classList.remove('active');
        document.getElementById('cardsView').style.display = 'none';
        document.getElementById('tableView').style.display = 'block';
    });
}

// ── Filter Event Listeners
export function setupFilterListeners() {
    const applyFilters = () => {
        const searchValue = document.getElementById('searchInput')?.value || '';
        const municipalityValue = document.getElementById('filterMunicipality')?.value || '';
        // Multi-cat: collect all checked category checkboxes
        const selectedCats = Array.from(document.querySelectorAll('.cat-filter-chk:checked')).map(c => c.value);
        const statusValue = document.getElementById('filterStatus')?.value || '';
        filterSpots(searchValue, municipalityValue, selectedCats, statusValue);
    };

    document.getElementById('searchInput')?.addEventListener('input', applyFilters);
    document.getElementById('filterMunicipality')?.addEventListener('change', applyFilters);
    document.getElementById('filterStatus')?.addEventListener('change', applyFilters);
    // Category checkboxes fire onCatFilterChange() inline which calls filterSpots(),
    // but also listen here for completeness
    document.querySelectorAll('.cat-filter-chk').forEach(chk => chk.addEventListener('change', applyFilters));
}

// ── Dropdown Event Listeners
export function setupDropdownListeners() {
    // Card dropdowns
    document.querySelectorAll('[id^="card-dropdown-"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const spotId = btn.id.replace('card-dropdown-', '');
            toggleDropdown('card-menu-' + spotId);
        });
    });

    // Table dropdowns
    document.querySelectorAll('[id^="tbl-dropdown-"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const spotId = btn.id.replace('tbl-dropdown-', '');
            toggleDropdown('tbl-menu-' + spotId);
        });
    });

    // Close dropdowns on outside click
    document.addEventListener('click', e => {
        if (!e.target.closest('.card-actions-dropdown') && !e.target.closest('.table-actions-dropdown')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
        }
    });
}

// MODAL FUNCTIONS


window.openSpotModal = async function openSpotModal(spotId) {
    const modal = document.getElementById('spotModal');
    if (!modal) return;

    modal.classList.add('active');
    document.getElementById('modalTitle').textContent = 'Loading...';
    document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';

    try {
        // First try to find spot in local data
        let spot = window.touristSpotsAll?.find(s => s.id == spotId);
        if (!spot) {
            // If not found, fetch from API
            spot = await window.getSpot(spotId);
        }
        document.getElementById('modalTitle').textContent = spot.name;
        const classificationStyle = spot.classification_status ? getClassificationStyle(spot.classification_status) : null;

        const formattedDate = new Date(spot.created_at).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });

        // Format time
        function formatTime(timeStr) {
            if (!timeStr) return 'N/A';
            const [hours, minutes] = timeStr.split(':').map(Number);
            const period = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return `${displayHours}:${String(minutes).padStart(2, '0')} ${period}`;
        }

        document.getElementById('modalBody').innerHTML = `
            ${spot.photo_url ? `
                <div style="height:200px;border-radius:10px;overflow:hidden;margin-bottom:16px;">
                    <img src="${escapeHtml(spot.photo_url)}" alt="${escapeHtml(spot.name)}" 
                         style="width:100%;height:100%;object-fit:cover;"
                         onerror="this.parentElement.style.display='none';">
                </div>
            ` : ''}
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
                <span style="font-size:13px;color:#6B7280;">
                    <i class="fas fa-map-marker-alt"></i> ${escapeHtml(spot.municipality_name)}, La Union
                </span>
                ${classificationStyle ? `
                    <span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;background:${classificationStyle.bg};color:${classificationStyle.text};">${classificationStyle.label}</span>
                ` : ''}
                ${spot.is_maintenance ? `
                    <span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;background:#F59E0B;color:#92400E;"><i class="fas fa-exclamation-triangle"></i> Under Maintenance</span>
                ` : ''}
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                    <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Category</div>
                    <div style="display:flex;flex-wrap:wrap;gap:5px;">
                        ${(spot.category || 'Other').split(',').map(c => c.trim()).filter(Boolean).map(c =>
            `<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#DBEAFE;color:#2563EB;">${escapeHtml(c)}</span>`
        ).join('')}
                    </div>
                </div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                    <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Entry Fee</div>
                    <div style="font-size:14px;font-weight:600;">₱${parseFloat(spot.entrance_fee).toLocaleString()}</div>
                </div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                    <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Opening Time</div>
                    <div style="font-size:14px;font-weight:600;">${formatTime(spot.opening_time)}</div>
                </div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                    <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Closing Time</div>
                    <div style="font-size:14px;font-weight:600;">${formatTime(spot.closing_time)}</div>
                </div>
                ${spot.latitude ? `
                    <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                        <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Latitude</div>
                        <div style="font-size:14px;font-weight:600;"><i class="fas fa-map-pin"></i> ${parseFloat(spot.latitude).toFixed(6)}</div>
                    </div>
                ` : ''}
                ${spot.longitude ? `
                    <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                        <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Longitude</div>
                        <div style="font-size:14px;font-weight:600;"><i class="fas fa-map-pin"></i> ${parseFloat(spot.longitude).toFixed(6)}</div>
                    </div>
                ` : ''}
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;">
                    <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Submitted</div>
                    <div style="font-size:14px;font-weight:600;">${formattedDate}</div>
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:8px;">Description</div>
                <p style="color:#4B5563;line-height:1.6;margin:0;">${escapeHtml(spot.description) || 'No description provided.'}</p>
            </div>
        `;
    } catch (err) {
        console.error(err);
        document.getElementById('modalBody').innerHTML = '<p style="color:#DC2626;">Failed to load spot details.</p>';
        showToast('Failed to load spot details', 'danger');
    }
}

export function closeSpotModal() {
    const modal = document.getElementById('spotModal');
    if (modal) modal.classList.remove('active');
}

export function setupModalListeners() {
    document.getElementById('closeSpotModal')?.addEventListener('click', closeSpotModal);

    document.getElementById('spotModal')?.addEventListener('click', e => {
        if (e.target.id === 'spotModal') closeSpotModal();
    });

    document.querySelectorAll('[data-action="view-spot"]').forEach(item => {
        item.addEventListener('click', () => {
            const spotId = item.dataset.spotId;
            openSpotModal(spotId);
        });
    });
}

// ── Utility: HTML Escape
export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ════════════════════════════════════════════════════════════════════════════════
// IMAGE HANDLING
// ════════════════════════════════════════════════════════════════════════════════

function getUploadAreaEl() {
    return document.getElementById('imageUploadArea');
}

function isValidImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (allowedTypes.includes(file.type)) return true;
    const ext = (file.name.split('.').pop() || '').toLowerCase();
    return ['jpg', 'jpeg', 'png'].includes(ext);
}

window.handleImageSelect = async function (e) {
    console.log('Image select triggered, files:', e.target.files.length);
    const files = Array.from(e.target.files);
    e.stopPropagation();
    // Clear input to allow selecting the same files again
    e.target.value = '';
    await processImageFiles(files);
};

window.handleImageDrop = async function (e) {
    e.preventDefault();
    e.stopPropagation();
    const area = getUploadAreaEl();
    if (area) {
        area.style.borderColor = '#D1D5DB';
        area.style.background = '#F9FAFB';
    }
    const files = Array.from(e.dataTransfer.files);
    console.log('Files dropped:', files.length);
    await processImageFiles(files);
};

window.handleDragOver = function (e) {
    e.preventDefault();
    e.stopPropagation();
    const area = getUploadAreaEl();
    if (area) {
        area.style.borderColor = '#2563EB';
        area.style.background = '#EEF2FF';
    }
};

window.handleDragLeave = function (e) {
    e.preventDefault();
    e.stopPropagation();
    const area = getUploadAreaEl();
    if (area) {
        area.style.borderColor = '#D1D5DB';
        area.style.background = '#F9FAFB';
    }
};

async function processImageFiles(files) {
    // Filter valid files first
    const validFiles = [];
    for (const file of files) {
        if (!isValidImageFile(file)) {
            showToast(`Invalid file type: ${file.name}. Allowed: JPEG, PNG`, 'danger');
            continue;
        }
        if (file.size > 10 * 1024 * 1024) { // Increased limit since we compress
            showToast(`File too large: ${file.name} (max 10MB)`, 'danger');
            continue;
        }
        validFiles.push(file);
    }

    if (validFiles.length === 0) return;

    showToast(`Uploading ${validFiles.length} image(s)...`, 'info');

    const pendingUploads = [];

    // Add files immediately with preview and loading state
    for (const file of validFiles) {
        const previewUrl = await getFilePreviewUrl(file);
        const tempId = `${Date.now()}-${Math.random().toString(36).slice(2)}`;
        uploadedImages.push({
            photo_url: previewUrl,
            filename: file.name,
            isLoading: true,
            id: tempId
        });
        pendingUploads.push({ file, tempId });
    }
    renderImagePreviews();

    try {
        // Upload all images in parallel
        const results = await Promise.allSettled(
            pendingUploads.map(async ({ file, tempId }) => {
                try {
                    const result = await uploadImage(file);
                    return { file, result, tempId, success: true };
                } catch (err) {
                    console.error('[upload] Failed:', file.name, err);
                    return { file, error: err, tempId, success: false };
                }
            })
        );

        // Process results
        let successCount = 0;
        for (const settled of results) {
            if (settled.status !== 'fulfilled') continue;
            const item = settled.value;
            const index = uploadedImages.findIndex(img => img.id === item.tempId);
            if (index === -1) continue;

            if (item.success) {
                uploadedImages[index] = {
                    photo_url: item.result.photo_url,
                    filename: item.result.filename || item.file.name
                };
                successCount++;
            } else {
                uploadedImages.splice(index, 1);
                const filename = item.file?.name || 'file';
                showToast(`Failed to upload ${filename}: ${item.error?.message || 'Unknown error'}`, 'danger');
            }
        }

        renderImagePreviews();
        if (successCount > 0) {
            showToast(`${successCount} image(s) uploaded successfully`, 'success');
        }
    } finally {
        // Safety net: remove any previews stuck in loading state
        const stuckCount = uploadedImages.filter(img => img.isLoading).length;
        if (stuckCount > 0) {
            uploadedImages = uploadedImages.filter(img => !img.isLoading);
            renderImagePreviews();
            showToast('Some uploads did not complete. Please try again.', 'danger');
        }
    }
}

function renderImagePreviews() {
    const container = document.getElementById('imagePreviews');
    if (!container) return;

    container.innerHTML = uploadedImages.map((img, index) => `
        <div style="position:relative;border-radius:8px;overflow:hidden;width:100px;height:100px;border: 2px solid #E5E7EB;">
            <img src="${img.photo_url}" alt="Preview" style="width:100%;height:100%;object-fit:cover;${img.isLoading ? 'filter: brightness(0.7);' : ''}" onerror="this.parentElement.innerHTML='<div style=\\'width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#F3F4F6;color:#9CA3AF;flex-direction:column;\\'><i class=\\'fas fa-exclamation\\'></i><span style=\\'font-size:10px;\\'> Error</span></div>'">
            ${img.isLoading ? `
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.3);z-index:10;">
                    <i class="fas fa-spinner fa-spin" style="font-size:32px;color:white;"></i>
                </div>
            ` : ''}
            <button type="button" onclick="removeImage(${index})" style="position:absolute;top:4px;right:4px;background:#DC2626;color:white;border:none;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;padding:0;${img.isLoading ? 'display:none;' : ''}">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

window.removeImage = function (index) {
    uploadedImages.splice(index, 1);
    renderImagePreviews();
    showToast('Image removed', 'info');
};

// ════════════════════════════════════════════════════════════════════════════════
// FORM HELPERS - BARANGAYS, CATEGORIES, ETC
// ════════════════════════════════════════════════════════════════════════════════

// Municipality coordinates (approximate for La Union)
const municipalityCoordinates = {
    1: { lat: 16.3147, lng: 119.9788 }, // Bacnotan
    2: { lat: 16.6167, lng: 120.3167 }, // San Fernando City
    3: { lat: 16.5500, lng: 120.3333 }, // Bauang
    4: { lat: 16.4833, lng: 120.4167 }, // Naguilian
    5: { lat: 16.3833, lng: 120.2833 }, // Caba
    6: { lat: 16.2833, lng: 120.4833 }, // Tubao
    7: { lat: 16.4167, lng: 120.1000 }, // Balaoan
    8: { lat: 16.3500, lng: 120.5000 }, // Aringay
    9: { lat: 16.4500, lng: 120.5000 }, // Santo Tomas
    10: { lat: 16.3000, lng: 120.5500 }, // Rosario
    11: { lat: 16.2000, lng: 120.4500 }, // Pugo
    12: { lat: 16.5833, lng: 120.6000 }, // Tuba
    13: { lat: 16.6500, lng: 120.5500 }, // Sablan
    14: { lat: 16.5833, lng: 120.3833 }, // Bagulin
    15: { lat: 16.6500, lng: 120.2500 }, // Sudipen
    16: { lat: 16.6833, lng: 120.3500 }, // San Gabriel
    17: { lat: 16.7167, lng: 120.4167 }, // San Juan
    18: { lat: 16.2000, lng: 120.5000 }, // Agoo
    19: { lat: 16.2500, lng: 120.5833 }, // Santa Cruz
    20: { lat: 16.2300, lng: 120.4200 }  // Burgos
};

// Helper to generate barangay entries with default municipality coordinates
function createBarangayList(names, muniId) {
    const coords = municipalityCoordinates[muniId];
    return names.map(name => ({ name, lat: coords.lat, lng: coords.lng }));
}

// Barangays with coordinates (using municipality coordinates as default)
const barangaysByMunicipality = {
    1: createBarangayList(['Allangigan', 'Aludaid', 'Bacsayan', 'Balballosa', 'Bambanay', 'Bugbugcao', 'Caarusipan', 'Cabaroan', 'Cabugnayan', 'Cacapian', 'Caculangan', 'Casilagan', 'Catdongan', 'Dangdangla', 'Dasay', 'Dinanum', 'Duplas', 'Guinguinabang', 'Ili Norte (Poblacion)', 'Ili Sur (Poblacion)', 'Legleg', 'Lubing', 'Nadsaag', 'Nagsabaran', 'Naguirangan', 'Naguituban', 'Nagyubuyuban', 'Oaquing', 'Pacpacac', 'Pagdildilan', 'Panicsican', 'Quidem', 'Santa Rosa', 'Saracat', 'Santo Rosario', 'Taboc', 'Talogtog', 'Urbiztondo'], 1),
    2: createBarangayList(['Abut', 'Apaleng', 'Bacsil', 'Baraoas', 'Bato', 'Biday', 'Bangbangolan', 'Bangcusay', 'Barangay I (Poblacion)', 'Barangay II (Poblacion)', 'Barangay III (Poblacion)', 'Barangay IV (Poblacion)', 'Birunget', 'Bungro', 'Cabarsican', 'Cadaclan', 'Calabugao', 'Camansi', 'Canaoay', 'Carlatan', 'Cabaroan (Negro)', 'Cadapli', 'Dallangayan Este', 'Dallangayan Oeste', 'Dalumpinas Este', 'Dalumpinas Oeste', 'Ilocanos Norte', 'Ilocanos Sur', 'Langcuas', 'Lingsat', 'Madayegdeg', 'Mameltac', 'Masicong', 'Narra Este', 'Narra Oeste', 'Namtutan', 'Pagdaldagan', 'Pagdaraoan', 'Pao Norte', 'Pao Sur', 'Pacpaco', 'Pian', 'Poro', 'Puspus', 'San Agustin', 'San Francisco', 'Sagayad', 'Santiago Norte', 'Santiago Sur', 'San Vicente', 'Saoay', 'Siboan-Otong', 'Tanquigan', 'Tanqui', 'Sevilla'], 2),
    3: createBarangayList(['Acao', 'Bagbag', 'Ballay', 'Baccuit Norte', 'Baccuit Sur', 'Boy-utan', 'Bucayab', 'Cabalayangan', 'Cabisilan', 'Casilagan', 'Central East (Poblacion)', 'Central West (Poblacion)', 'Dili', 'Disso-or', 'Guerrero', 'Jimenez', 'Jimenez West', 'Lower San Agustin', 'Nagrebcan', 'Pagdalagan Sur', 'Paliguasan', 'Palingulang', 'Parian Este', 'Parian Oeste', 'Paringao', 'Payocpoc Norte Este', 'Payocpoc Norte Oeste', 'Payocpoc Sur', 'Pilar', 'Pottot', 'Pudoc', 'Pugo', 'Quinavite', 'Santa Monica', 'Santiago', 'Taberna', 'Upper San Agustin', 'Urayong'], 3),
    4: createBarangayList(['Ambitacay', 'Balawarte', 'Capas', 'Consolacion (Poblacion)', 'San Agustin East', 'San Agustin Norte', 'San Agustin Sur', 'San Antonino', 'San Antonio', 'San Francisco', 'San Isidro', 'San Java Norte', 'San Juan', 'San Jose Norte', 'San Jose Sur', 'San Julian Central', 'San Julian East', 'San Julian Norte', 'San Julian West', 'San Manuel Norte', 'San Manuel Sur', 'San Marcos', 'San Miguel', 'San Nicolas Central (Poblacion)', 'San Nicolas East', 'San Nicolas Norte (Poblacion)', 'San Nicolas Sur (Poblacion)', 'San Nicolas West', 'San Pedro', 'San Roque East', 'San Roque West', 'San Vicente Norte', 'San Vicente Sur', 'Santa Ana', 'Santa Barbara (Poblacion)', 'Santa Fe', 'Santa Maria', 'Santa Monica', 'Santa Rita (Nalinac)', 'Santa Rita East', 'Santa Rita Norte', 'Santa Rita Sur', 'Santa Rita West', 'Nazareno', 'Macalva Central', 'Macalva Norte', 'Macalva Sur', 'Purok'], 4),
    5: createBarangayList(['Alcala (Poblacion)', 'Ayaoan', 'Barangobong', 'Barrientos', 'Bungro', 'Buselbusel', 'Cabalitocan', 'Cantoria No. 1', 'Cantoria No. 2', 'Cantoria No. 3', 'Cantoria No. 4', 'Carisquis', 'Darigayos', 'Magallanes (Poblacion)', 'Magsiping', 'Mamay', 'Nalvo Norte', 'Nalvo Sur', 'Nagrebcan', 'Napaset', 'Oaqui No. 1', 'Oaqui No. 2', 'Oaqui No. 3', 'Oaqui No. 4', 'Pila', 'Pitpitac', 'Rimos No. 1', 'Rimos No. 2', 'Rimos No. 3', 'Rimos No. 4', 'Rimos No. 5', 'Rissing', 'Salcedo (Poblacion)', 'Santo Domingo Norte', 'Santo Domingo Sur', 'Sucoc Norte', 'Sucoc Sur', 'Suyo', 'Tallaoen', 'Victoria (Poblacion)'], 5),
    6: createBarangayList(['Amontoc', 'Apayao', 'Bayabas', 'Balbalayang', 'Bucao', 'Bumbuneg', 'Daking', 'Lacong', 'Lipay Este', 'Lipay Norte', 'Lipay Proper', 'Lipay Sur', 'Lon-oy', 'Poblacion', 'Polipol'], 6),
    7: createBarangayList(['Almeida', 'Antonino', 'Apatut', 'Ar-arampang', 'Baracbac Este', 'Baracbac Oeste', 'Bet-ang', 'Bulbulala', 'Bungol', 'Butubut Este', 'Butubut Norte', 'Butubut Oeste', 'Butubut Sur', 'Cabuaan Oeste (Poblacion)', 'Calliat', 'Camiling', 'Calumbaya', 'Calungbuyan', 'Dr. Camilo Osias Poblacion (Cabuaan Este)', 'Guinaburan', 'Nagsabaran Norte', 'Nagsabaran Sur', 'Nalasin', 'Napaset', 'Pagbennecan', 'Pagleddegan', 'Paraoir', 'Patpata', 'Sablut', 'San Pablo', 'Sinapangan Norte', 'Sinapangan Sur', 'Tallipugo'], 7),
    8: createBarangayList(['Alaska', 'Basca', 'Dulao', 'Gallano', 'Macabato', 'Manga', 'Pangao-aoan East', 'Pangao-aoan West', 'Poblacion', 'Samara', 'San Antonio', 'San Benito Norte', 'San Benito Sur', 'San Eugenio', 'San Juan East', 'San Juan West', 'San Simon East', 'San Simon West', 'Santa Cecilia', 'Santa Lucia', 'Santo Rosario East', 'Santo Rosario West', 'Santa Rita East', 'Santa Rita West'], 8),
    9: createBarangayList(['Alipang', 'Amlang', 'Ambangonan', 'Bacani', 'Bangar', 'Bani', 'Benteng-Sapilang', 'Camp One', 'Carunuan East', 'Carunuan West', 'Casilagan', 'Cataguingtingan', 'Concepcion', 'Damortis', 'Gumot-Nagcolaran', 'Inabaan Norte', 'Inabaan Sur', 'Marcos', 'Nagtagaan', 'Nancamotian', 'Parasapas', 'Poblacion East', 'Poblacion West', 'San Jose', 'Subusub', 'Tabtabungao', 'Tay-ac', 'Tanglag', 'Udiao', 'Vila'], 9),
    10: createBarangayList(['Agtipal', 'Arosip', 'Bacqui', 'Bacsil', 'Bagutot', 'Ballogo', 'Baroro', 'Bitalag', 'Burayoc', 'Bussaoit', 'Cabaroan', 'Cabarsican', 'Cabugao', 'Calautit', 'Carcarmay', 'Casiaman', 'Santa Cruz', 'Galongen', 'Guinabang', 'Legleg', 'Lisqueb', 'Mabanengbeng 1st', 'Mabanengbeng 2nd', 'Maragayap', 'Nagatiran', 'Nangalisan', 'Narra', 'Nagsaraboan', 'Nagsimbaanan', 'Oya-oy', 'Paagan', 'Pagan', 'Pandan', 'Pang-Pang', 'Poblacion', 'Quirino', 'Raois', 'Sagapan', 'Salincob', 'San Martin', 'Santa Rita', 'Sapilang', 'Sayoan', 'Sipulo', 'Ubbog', 'Zaragosa'], 10),
    11: createBarangayList(['Al-alinao Norte', 'Al-alinao Sur', 'Aguioas', 'Ambaracao Norte', 'Ambaracao Sur', 'Angin', 'Baraoas Norte', 'Baraoas Sur', 'Bariquir', 'Bato', 'Balecbec', 'Bancagan', 'Bimmotobot', 'Dal-lipaoen', 'Daramuangan', 'Guesset', 'Gusing Norte', 'Gusing Sur', 'Imelda', 'Lioac Norte', 'Lioac Sur', 'Magungunay', 'Mamat-ing Norte', 'Mamat-ing Sur', 'Natividad (Poblacion)', 'Ortiz (Poblacion)', 'Ribsuan', 'San Antonio', 'San Isidro', 'Sili', 'Suguidan Norte', 'Suguidan Sur', 'Teddingan'], 11),
    12: createBarangayList(['Amallapay', 'Anduyan', 'Caoigue', 'Francia Sur', 'Francia West', 'Garcia', 'Gonzales', 'Halog East', 'Halog West', 'Leones East', 'Leones West', 'Linapew', 'Lloren', 'Magsaysay', 'Pideg', 'Poblacion', 'Rizal', 'Santa Teresa'], 12),
    13: createBarangayList(['Ambalite', 'Ambangonan', 'Cares', 'Cuenca', 'Duplas', 'Maoasoas Norte', 'Maoasoas Sur', 'Palina', 'Poblacion East', 'Poblacion West', 'Saytan', 'San Luis', 'Tavora East', 'Tavora Proper'], 13),
    14: createBarangayList(['Bautista', 'Gana', 'Juan Cartas', 'Las-ud', 'Liquicia', 'Poblacion Norte', 'Poblacion Sur', 'San Carlos', 'San Cornelio', 'San Fermin', 'San Gregorio', 'San Jose', 'Santiago Norte', 'Santiago Sur', 'Sobredillo', 'Urayong', 'Wenceslao'], 14),
    15: createBarangayList(['Ambitacay', 'Bail', 'Balaoc', 'Balsaan', 'Baybay', 'Cabaruan', 'Casilagan', 'Casantaan', 'Cupang', 'Damortis', 'Fernando', 'Linong', 'Lomboy', 'Malabago', 'Namboongan', 'Namonitan', 'Narvacan', 'Patac', 'Poblacion', 'Pongpong', 'Raois', 'Tococ', 'Tubod', 'Ubagan'], 15),
    16: createBarangayList(['Agdeppa', 'Alzate', 'Bangaoilan East', 'Bangaoilan West', 'Barraca', 'Central East No. 1 (Poblacion)', 'Central East No. 2 (Poblacion)', 'Central West No. 1 (Poblacion)', 'Central West No. 2 (Poblacion)', 'Central West No. 3 (Poblacion)', 'Consuegra', 'General Prim East', 'General Prim West', 'General Terrero', 'Luzong Norte', 'Luzong Sur', 'Maria Cristina East', 'Maria Cristina West', 'Mindoro', 'Nagsabaran', 'Nagsidorisan', 'Quintarong', 'Reyna Regente', 'Rissing', 'San Blas', 'San Cristobal', 'Sinapangan Norte', 'Sinapangan Sur', 'Ubbog'], 16),
    17: createBarangayList(['Agpay', 'Bilis', 'Caoayan', 'Dalacdac', 'Delles', 'Imelda', 'Libtong', 'Linuan', 'Lower Tumapoc', 'New Poblacion', 'Old Poblacion', 'Upper Tumapoc'], 17),
    18: createBarangayList(['Alibangsay', 'Baay', 'Cambaly', 'Cardiz', 'Dagup', 'Libbo', 'Suyo (Poblacion)', 'Tagudtud', 'Tio-angan', 'Wallayan'], 18),
    19: createBarangayList(['Corrooy', 'Lettac Norte', 'Lettac Sur', 'Mangaan', 'Paagan', 'Poblacion', 'Puguil', 'Ramot', 'Sasaba', 'Sapdaan', 'Tubaday'], 19),
    20: createBarangayList(['Bigbiga', 'Bulalaan', 'Castro', 'Duplas', 'Ipet', 'Ilocano', 'Maliclico', 'Namaltugan', 'Old Central', 'Poblacion', 'Porporiket', 'San Francisco Norte', 'San Francisco Sur', 'San Jose', 'Sengngat', 'Turod', 'Up-uplas'], 20)
};

window.onMunicipalityChange = function (muniId) {
    const select = document.getElementById('spotBarangay');
    if (!select) return;
    select.innerHTML = '<option value="">— Select Barangay —</option>';
    const barangays = barangaysByMunicipality[parseInt(muniId)] || [];
    barangays.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b.name; // Wait! Because b is an object now!
        opt.textContent = b.name;
        opt.dataset.lat = b.lat;
        opt.dataset.lng = b.lng;
        select.appendChild(opt);
    });
};

window.onBarangayChange = function (barangayName) {
    const select = document.getElementById('spotBarangay');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption && selectedOption.value) {
        const lat = parseFloat(selectedOption.dataset.lat);
        const lng = parseFloat(selectedOption.dataset.lng);

        if (!isNaN(lat) && !isNaN(lng)) {
            document.getElementById('spotLatitude').value = lat.toFixed(6);
            document.getElementById('spotLongitude').value = lng.toFixed(6);

            // Wait for modal map to be initialized first!
            const tryPlaceMarker = () => {
                if (typeof placeOrMoveDraggableMarker === 'function' && modalMap) {
                    placeOrMoveDraggableMarker(lat, lng);
                } else {
                    setTimeout(tryPlaceMarker, 100);
                }
            };
            tryPlaceMarker();
        }
    }
};

// ── Multi-Category Chip Logic
// ── Form Category Dropdown Logic
function initCategoryChips() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function (e) {
        const formDd = document.getElementById('formCatDropdown');
        const formBtn = document.getElementById('formCatDropdownBtn');
        if (formDd && formBtn && !formBtn.contains(e.target) && !formDd.contains(e.target)) {
            formDd.style.display = 'none';
            const chevron = document.getElementById('formCatChevron');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }

        const filterDd = document.getElementById('catFilterDropdown');
        const filterBtn = document.getElementById('catFilterBtn');
        if (filterDd && filterBtn && !filterBtn.contains(e.target) && !filterDd.contains(e.target)) {
            filterDd.style.display = 'none';
            const chevron = document.getElementById('catChevron');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });
}

window.toggleFormCatDropdown = function (e) {
    e.stopPropagation();
    const dd = document.getElementById('formCatDropdown');
    const chevron = document.getElementById('formCatChevron');
    if (!dd) return;
    const isVisible = dd.style.display === 'block';
    dd.style.display = isVisible ? 'none' : 'block';
    if (chevron) chevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
};

window.toggleFormCategory = function (itemEl, e) {
    e.stopPropagation();
    const chk = itemEl.querySelector('.form-cat-chk');
    if (chk) {
        chk.checked = !chk.checked;
    }
    syncCategoryHiddenInput();
};

function syncCategoryHiddenInput() {
    const selected = Array.from(document.querySelectorAll('.form-cat-chk:checked'))
        .map(c => c.value);
    document.getElementById('spotCategory').value = selected.join(',');

    const label = document.getElementById('formCatDropdownLabel');
    if (selected.length > 0) {
        label.textContent = selected.join(', ');
        label.style.color = '#1E293B';
    } else {
        label.textContent = 'Select Categories...';
        label.style.color = '#9CA3AF';
    }
}

function setSelectedCategories(categoryStr) {
    document.querySelectorAll('.form-cat-chk').forEach(c => c.checked = false);
    if (!categoryStr) { syncCategoryHiddenInput(); return; }
    const cats = categoryStr.split(',').map(s => s.trim());
    cats.forEach(cat => {
        const chk = document.querySelector(`.form-cat-chk[value="${cat}"]`);
        if (chk) chk.checked = true;
    });
    syncCategoryHiddenInput();
}

// ── Free Entry Toggle
window.toggleFreeEntry = function () {
    const isFree = document.getElementById('isFree').checked;
    const feeInput = document.getElementById('spotFee');
    if (isFree) {
        feeInput.value = '0';
        feeInput.disabled = true;
    } else {
        feeInput.disabled = false;
        feeInput.focus();
    }
};

// ════════════════════════════════════════════════════════════════════════════════
// FORM OPERATIONS - CREATE/EDIT/SAVE
// ════════════════════════════════════════════════════════════════════════════════

window.openCreateForm = function () {
    uploadedImages = [];
    pendingSaveData = null;
    document.getElementById('formModalTitle').textContent = 'Add New Spot';
    document.getElementById('spotId').value = '';
    document.getElementById('spotName').value = '';
    document.getElementById('nameCharCount').textContent = '0';
    setSelectedCategories('');
    document.getElementById('spotClassification').value = '';
    document.getElementById('spotFee').value = '0';
    document.getElementById('isFree').checked = false;
    document.getElementById('spotFee').disabled = false;
    document.getElementById('spotLatitude').value = '';
    document.getElementById('spotLongitude').value = '';
    document.getElementById('spotDescription').value = '';
    document.getElementById('descCharCount').textContent = '0';
    document.getElementById('spotMunicipality').value = '';
    document.getElementById('spotBarangay').innerHTML = '<option value="">— Select Barangay —</option>';
    document.getElementById('spotOpeningTime').value = '';
    document.getElementById('spotClosingTime').value = '';
    document.getElementById('spotIsMaintenance').checked = false;
    document.getElementById('imagePreviews').innerHTML = '';

    // Hide under maintenance field in add mode
    document.getElementById('maintenance-field').style.display = 'none';

    document.getElementById('spotFormModal').classList.add('active');
    setTimeout(initModalMap, 200);
};

window.editSpot = async function (spotId) {
    try {
        // First try to find spot in local data
        let spot = window.touristSpotsAll?.find(s => s.id == spotId);
        if (!spot) {
            // If not found, fetch from API
            spot = await window.getSpot(spotId);
        }
        uploadedImages = spot.images && spot.images.length > 0 ? spot.images : (spot.photo_url ? [{ photo_url: spot.photo_url }] : []);

        document.getElementById('formModalTitle').textContent = 'Edit Spot';
        document.getElementById('spotId').value = spot.id;
        document.getElementById('spotName').value = spot.name;
        document.getElementById('nameCharCount').textContent = spot.name.length;

        setSelectedCategories(spot.category || '');

        // Convert DB status to form display status
        const formStatus = statusDisplayMap[spot.classification_status] || spot.classification_status;
        document.getElementById('spotClassification').value = formStatus;

        document.getElementById('spotFee').value = spot.entrance_fee;
        const isFree = parseFloat(spot.entrance_fee) === 0;
        document.getElementById('isFree').checked = isFree;
        document.getElementById('spotFee').disabled = isFree;
        document.getElementById('spotLatitude').value = spot.latitude || '';
        document.getElementById('spotLongitude').value = spot.longitude || '';
        document.getElementById('spotDescription').value = spot.description || '';
        document.getElementById('descCharCount').textContent = (spot.description || '').length;

        document.getElementById('spotMunicipality').value = spot.municipality_id;
        onMunicipalityChange(spot.municipality_id);
        if (spot.barangay) {
            setTimeout(() => {
                document.getElementById('spotBarangay').value = spot.barangay;
            }, 50);
        }

        document.getElementById('spotOpeningTime').value = spot.opening_time || '';
        document.getElementById('spotClosingTime').value = spot.closing_time || '';
        document.getElementById('spotIsMaintenance').checked = spot.is_maintenance ? true : false;

        // Show under maintenance field in edit mode
        document.getElementById('maintenance-field').style.display = 'block';

        renderImagePreviews();
        document.getElementById('spotFormModal').classList.add('active');
        setTimeout(initModalMap, 200);

        if (spot.latitude && spot.longitude) {
            setTimeout(() => {
                placeOrMoveDraggableMarker(parseFloat(spot.latitude), parseFloat(spot.longitude));
            }, 250);
        }
    } catch (err) {
        console.error(err);
        showToast('Failed to load spot for editing', 'danger');
    }
};

// ── Initialize Modal Map
function initModalMap() {
    if (!document.getElementById('modalMap')) return;

    if (modalMap) {
        modalMap.remove();
        modalMap = null;
        modalMarker = null;
    }

    modalMap = L.map('modalMap', { minZoom: 10, maxZoom: 18 });

    // Dedicated layer instances for the modal map to avoid sharing singletons with main map
    const modalStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 18
    });
    const modalSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri',
        maxZoom: 18
    });

    modalStreet.addTo(modalMap);

    const modalBaseMaps = {
        "Street Map": modalStreet,
        "Satellite Map": modalSatellite
    };
    L.control.layers(modalBaseMaps, null, { position: 'topright' }).addTo(modalMap);

    modalMap.setView([16.5, 120.3], 10);

    [100, 250, 500].forEach(delay => {
        setTimeout(() => {
            if (modalMap) modalMap.invalidateSize();
        }, delay);
    });

    modalMap.on('click', function (e) {
        document.getElementById('spotLatitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('spotLongitude').value = e.latlng.lng.toFixed(6);
        placeOrMoveDraggableMarker(e.latlng.lat, e.latlng.lng);
    });
}

window.placeOrMoveDraggableMarker = function placeOrMoveDraggableMarker(lat, lng) {
    if (!modalMap) return;

    const icon = L.divIcon({
        html: `<div style="background:#2563EB;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:3px solid white;box-shadow:0 3px 10px rgba(37,99,235,.45);cursor:grab;"><i class="fas fa-map-marker-alt" style="font-size:14px;"></i></div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });

    if (!modalMarker) {
        modalMarker = L.marker([lat, lng], { icon, draggable: true });
        modalMarker.addTo(modalMap);
        modalMarker.on('drag', function (e) {
            const pos = e.target.getLatLng();
            document.getElementById('spotLatitude').value = pos.lat.toFixed(6);
            document.getElementById('spotLongitude').value = pos.lng.toFixed(6);
        });
        modalMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            document.getElementById('spotLatitude').value = pos.lat.toFixed(6);
            document.getElementById('spotLongitude').value = pos.lng.toFixed(6);
        });
    } else {
        modalMarker.setLatLng([lat, lng]);
    }
    modalMap.setView([lat, lng], 15);
};

window.updateMapMarkerFromInput = function () {
    const lat = parseFloat(document.getElementById('spotLatitude').value);
    const lng = parseFloat(document.getElementById('spotLongitude').value);

    if (isNaN(lat) || isNaN(lng)) {
        if (modalMarker && modalMap) {
            modalMap.removeLayer(modalMarker);
            modalMarker = null;
        }
        return;
    }
    placeOrMoveDraggableMarker(lat, lng);
};

window.updateMapMarker = window.updateMapMarkerFromInput;

window.useCurrentLocation = function () {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.getElementById('spotLatitude').value = position.coords.latitude;
                document.getElementById('spotLongitude').value = position.coords.longitude;
                updateMapMarker();
                showToast('Location set successfully', 'success');
            },
            () => showToast('Failed to get current location', 'danger')
        );
    } else {
        showToast('Geolocation not supported', 'danger');
    }
};

window.closeFormModal = function () {
    uploadedImages = [];
    pendingSaveData = null;
    document.getElementById('spotFormModal').classList.remove('active');
    if (modalMap) {
        modalMap.remove();
        modalMap = null;
        modalMarker = null;
    }
};

// ════════════════════════════════════════════════════════════════════════════════
// FORM SUBMISSION WITH CONFIRMATION
// ════════════════════════════════════════════════════════════════════════════════

window.submitSpotForm = async function (e) {
    e.preventDefault();

    // ── Loading state on Save Spot button
    const saveBtn = document.getElementById('saveSpotBtn');
    const saveIcon = document.getElementById('saveSpotIcon');
    const saveSpinner = document.getElementById('saveSpotSpinner');
    const saveLabel = document.getElementById('saveSpotLabel');
    if (saveBtn) {
        saveBtn.disabled = true;
        if (saveIcon)    saveIcon.style.display    = 'none';
        if (saveSpinner) saveSpinner.style.display  = 'inline-block';
        if (saveLabel)   saveLabel.textContent       = 'Validating...';
    }

    const resetSaveBtn = () => {
        if (saveBtn) {
            saveBtn.disabled = false;
            if (saveIcon)    saveIcon.style.display    = 'inline-block';
            if (saveSpinner) saveSpinner.style.display  = 'none';
            if (saveLabel)   saveLabel.textContent       = 'Save Spot';
        }
    };

    const categoryValue = document.getElementById('spotCategory').value;
    if (!categoryValue) {
        showToast('Please select at least one category', 'danger');
        resetSaveBtn();
        return;
    }

    const classificationValue = document.getElementById('spotClassification').value;
    if (!classificationValue) {
        showToast('Please select a classification status', 'danger');
        resetSaveBtn();
        return;
    }

    const stillUploading = uploadedImages.some(img => img.isLoading);
    if (stillUploading) {
        showToast('Please wait for all images to finish uploading before saving', 'danger');
        resetSaveBtn();
        return;
    }

    const cleanImages = uploadedImages
        .filter(img => !img.isLoading && img.photo_url && !img.photo_url.startsWith('blob:'))
        .map(img => ({ photo_url: img.photo_url, filename: img.filename || '' }));

    const spotIdValue = document.getElementById('spotId').value;

    // Convert form status to DB status
    const dbStatus = statusReverseMap[classificationValue] || classificationValue;

    pendingSaveData = {
        id: spotIdValue ? parseInt(spotIdValue) : null,
        name: document.getElementById('spotName').value,
        category: categoryValue,
        classification_status: dbStatus,
        entrance_fee: parseFloat(document.getElementById('spotFee').value) || 0,
        latitude: parseFloat(document.getElementById('spotLatitude').value) || null,
        longitude: parseFloat(document.getElementById('spotLongitude').value) || null,
        barangay: document.getElementById('spotBarangay').value || null,
        description: document.getElementById('spotDescription').value,
        municipality_id: parseInt(document.getElementById('spotMunicipality').value),
        images: cleanImages,
        opening_time: document.getElementById('spotOpeningTime').value || null,
        closing_time: document.getElementById('spotClosingTime').value || null,
        is_maintenance: document.getElementById('spotIsMaintenance').checked ? 1 : 0
    };

    console.log('📋 Form data prepared:', pendingSaveData);
    console.log('📸 Images to save:', cleanImages.length);

    resetSaveBtn();
    document.getElementById('saveConfirmModal').classList.add('active');
};

window.closeSaveConfirmModal = function () {
    document.getElementById('saveConfirmModal').classList.remove('active');
};

window.confirmSaveSpot = async function () {
    console.log('💾 Confirming save...');

    if (!pendingSaveData) {
        showToast('No data to save', 'danger');
        return;
    }

    // ── Loading state on the confirm button
    const confirmBtn     = document.getElementById('saveConfirmBtn');
    const confirmIcon    = document.getElementById('confirmBtnIcon');
    const confirmSpinner = document.getElementById('confirmBtnSpinner');
    const confirmLabel   = document.getElementById('confirmBtnLabel');
    const cancelBtn      = document.querySelector('[data-action="close-save-confirm"]');

    const setConfirmLoading = (loading, isEdit = false) => {
        if (!confirmBtn) return;
        confirmBtn.disabled = loading;
        if (cancelBtn) cancelBtn.disabled = loading;
        if (loading) {
            if (confirmIcon)    confirmIcon.style.display    = 'none';
            if (confirmSpinner) confirmSpinner.style.display = 'inline-block';
            if (confirmLabel)   confirmLabel.textContent     = isEdit ? 'Updating...' : 'Saving...';
            confirmBtn.style.opacity = '0.85';
            confirmBtn.style.cursor  = 'not-allowed';
        } else {
            if (confirmIcon)    confirmIcon.style.display    = 'inline-block';
            if (confirmSpinner) confirmSpinner.style.display = 'none';
            if (confirmLabel)   confirmLabel.textContent     = 'Yes, Save';
            confirmBtn.style.opacity = '';
            confirmBtn.style.cursor  = '';
        }
    };

    const isEdit = pendingSaveData.id;
    setConfirmLoading(true, !!isEdit);

    try {
        let res;

        console.log(isEdit ? 'Updating spot...' : 'Creating new spot...');

        if (isEdit) {
            res = await updateSpot(pendingSaveData.id, pendingSaveData);
        } else {
            res = await createSpot(pendingSaveData);
        }

        console.log('✅ Server response:', res);

        if (res && (res.success || res.message)) {
            closeSaveConfirmModal();
            closeFormModal();
            // Show success toast inline — no page reload needed
            showToast(isEdit ? '✅ Spot updated successfully!' : '✅ Spot created successfully!', 'success');
            // Refresh only the data in the active SPA tab (no full page reload)
            if (typeof window.refreshActiveTab === 'function') {
                window.refreshActiveTab();
            }
        } else {
            throw new Error(res.message || 'Unknown error');
        }
    } catch (err) {
        console.error('❌ Save error:', err);
        const errorMsg = err.message || 'Failed to save spot';
        showToast(`❌ Error: ${errorMsg}`, 'danger');
        setConfirmLoading(false);
        closeSaveConfirmModal();
    }
};

// ════════════════════════════════════════════════════════════════════════════════
// INITIALIZE ALL EVENT LISTENERS
// ════════════════════════════════════════════════════════════════════════════════

export async function initializeAll(spotsData, municipalData) {
    loadCachedKpis();

    // If data not provided, fetch from API (lightweight shell pattern)
    if (!spotsData || !spotsData.length || !municipalData || !municipalData.length) {
        try {
            const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
            const [spotsRes, muniRes] = await Promise.all([
                window.API_CONFIG.get(`${baseUrl}/api/tourist-spots`),
                window.API_CONFIG.get(`${baseUrl}/api/municipalities`)
            ]);
            spotsData = spotsRes.data || spotsRes || [];
            municipalData = muniRes.municipalities || muniRes.data || muniRes || [];
        } catch (err) {
            console.error('Failed to fetch tourist spots:', err);
            spotsData = [];
            municipalData = [];
        }
    }

    window.touristSpotsData = spotsData;
    window.municipalitiesData = municipalData;
    window.touristSpotsAll = spotsData;
    window.municipalitiesAll = municipalData;

    // Render cards and table from JS data
    renderCardsGrid(spotsData);
    renderTableRows(spotsData);
    populateMuniDropdowns(municipalData);

    // Update KPIs
    updateKpiCards(spotsData, municipalData);

    console.log('Initializing tourist spots module...');
    console.log('Total spots:', spotsData.length);
    console.log('Total municipalities:', municipalData.length);

    const pendingToast = sessionStorage.getItem('save_success_toast');
    if (pendingToast) {
        showToast(pendingToast, 'success');
        sessionStorage.removeItem('save_success_toast');
    }

    try {
        initMap(spotsData, municipalData);
        setupMapLayerToggle();
    } catch (e) {
        console.error('Leaflet Map initialization failed:', e);
    }
    try { setupViewToggle(); } catch (e) { console.error('setupViewToggle failed:', e); }
    try { setupFilterListeners(); } catch (e) { console.error('setupFilterListeners failed:', e); }
    try { setupDropdownListeners(); } catch (e) { console.error('setupDropdownListeners failed:', e); }
    try { setupModalListeners(); } catch (e) { console.error('setupModalListeners failed:', e); }
    try { initCategoryChips(); } catch (e) { console.error('initCategoryChips failed:', e); }

    // Add Spot
    document.querySelectorAll('[data-action="open-create-form"]').forEach(btn => {
        btn.addEventListener('click', () => openCreateForm());
    });

    // Edit Spot
    document.querySelectorAll('[data-action="edit-spot"]').forEach(btn => {
        btn.addEventListener('click', () => editSpot(btn.dataset.spotId));
    });

    // View Spot
    document.querySelectorAll('[data-action="view-spot"]').forEach(btn => {
        btn.addEventListener('click', () => openSpotModal(btn.dataset.spotId));
    });

    // Close Form Modal
    document.querySelectorAll('[data-action="close-form-modal"]').forEach(el => {
        el.addEventListener('click', closeFormModal);
    });

    // Backdrop close
    document.getElementById('spotFormModal')?.addEventListener('click', e => {
        if (e.target.id === 'spotFormModal') closeFormModal();
    });

    // Form submit
    document.getElementById('spotForm')?.addEventListener('submit', submitSpotForm);

    // Save confirmation
    document.getElementById('saveConfirmModal')?.addEventListener('click', e => {
        if (e.target.id === 'saveConfirmModal') closeSaveConfirmModal();
    });
    document.querySelector('[data-action="close-save-confirm"]')
        ?.addEventListener('click', closeSaveConfirmModal);
    document.querySelector('[data-action="confirm-save-spot"]')
        ?.addEventListener('click', confirmSaveSpot);

    // Current location
    document.querySelector('[data-action="use-current-location"]')
        ?.addEventListener('click', useCurrentLocation);

    // Free entry
    document.getElementById('isFree')
        ?.addEventListener('change', toggleFreeEntry);

    // Municipality change
    document.getElementById('spotMunicipality')
        ?.addEventListener('change', function () { onMunicipalityChange(this.value); });

    // Barangay change
    document.getElementById('spotBarangay')
        ?.addEventListener('change', function () { onBarangayChange(this.value); });

    // Lat/Lng input
    document.getElementById('spotLatitude')
        ?.addEventListener('input', updateMapMarkerFromInput);
    document.getElementById('spotLongitude')
        ?.addEventListener('input', updateMapMarkerFromInput);

    // Character counters
    document.getElementById('spotName')
        ?.addEventListener('input', function () {
            document.getElementById('nameCharCount').textContent = this.value.length;
        });

    document.getElementById('spotDescription')
        ?.addEventListener('input', function () {
            document.getElementById('descCharCount').textContent = this.value.length;
        });

    // Image upload
    const uploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('spotImages');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleImageDrop);
    }
    if (fileInput) {
        fileInput.addEventListener('change', handleImageSelect);
    }

    console.log('Initialization complete!');

    startKpiAutoRefresh();

    // Re-initialize map with fresh data (map was created empty by map-view-api.js)
    setTimeout(() => {
        if (typeof initMapView === 'function') {
            const mapEl = document.getElementById('lupto-map');
            if (mapEl && mapEl._leaflet_map) {
                mapEl._leaflet_map.remove();
                delete mapEl._leaflet_map;
            }
            initMapView();
        }
    }, 300);
}

let kpiRefreshTimer = null;

function startKpiAutoRefresh() {
    stopKpiAutoRefresh();
    kpiRefreshTimer = setInterval(() => {
        softRefreshSpots();
    }, 30000);
}

function stopKpiAutoRefresh() {
    if (kpiRefreshTimer) {
        clearInterval(kpiRefreshTimer);
        kpiRefreshTimer = null;
    }
}

window.startKpiAutoRefresh = startKpiAutoRefresh;
window.stopKpiAutoRefresh = stopKpiAutoRefresh;

function populateMuniDropdowns(municipalData) {
    const filterSelect = document.getElementById('filterMunicipality');
    const formSelect = document.getElementById('spotMunicipality');
    municipalData.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.name;
        opt.textContent = m.name;
        if (filterSelect) filterSelect.appendChild(opt.cloneNode(true));
        if (formSelect) {
            const fOpt = document.createElement('option');
            fOpt.value = m.id;
            fOpt.textContent = m.name;
            formSelect.appendChild(fOpt);
        }
    });
}

function updateKpiCards(spotsData, municipalData) {
    const kpiEls = document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value');
    const vals = {
        municipalities: municipalData.length,
        total: spotsData.length,
        open: spotsData.filter(s => (s.status || '') === 'approved').length,
        closed: spotsData.filter(s => (s.status || '') !== 'approved' && (s.status || '') !== '').length,
    };
    if (kpiEls[0]) kpiEls[0].textContent = vals.municipalities;
    if (kpiEls[1]) kpiEls[1].textContent = vals.total;
    if (kpiEls[2]) kpiEls[2].textContent = vals.open;
    if (kpiEls[3]) kpiEls[3].textContent = vals.closed;
    const spotCount = document.getElementById('spotCount');
    if (spotCount) spotCount.textContent = vals.total;
    try { sessionStorage.setItem('ts_kpis_lupto', JSON.stringify(vals)); } catch (e) {}
}

function loadCachedKpis() {
    try {
        const raw = sessionStorage.getItem('ts_kpis_lupto');
        if (!raw) return;
        const v = JSON.parse(raw);
        const kpiEls = document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value');
        if (kpiEls[0] && kpiEls[0].textContent.trim() === '') return; // already populated
        if (kpiEls[0]) { kpiEls[0].textContent = v.municipalities; kpiEls[0].style.color = '#1E293B'; }
        if (kpiEls[1]) { kpiEls[1].textContent = v.total; kpiEls[1].style.color = '#1E293B'; }
        if (kpiEls[2]) { kpiEls[2].textContent = v.open; kpiEls[2].style.color = '#1E293B'; }
        if (kpiEls[3]) { kpiEls[3].textContent = v.closed; kpiEls[3].style.color = '#1E293B'; }
        const spotCount = document.getElementById('spotCount');
        if (spotCount) spotCount.textContent = v.total;
    } catch (e) {}
}

function renderCardsGrid(spotsData) {
    const grid = document.getElementById('cardsView');
    if (!grid) return;
    let html = '';
    spotsData.forEach(spot => {
        const desc = (spot.description || '').substring(0, 100);
        const status = spot.classification_status || '';
        const statusClass = status === 'EXIST' ? 'EXISTING' : status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL';
        const statusBg = status === 'EXIST' ? '#10B981' : status === 'EMERGE' ? '#8B5CF6' : status === 'POTENTIAL' ? '#F59E0B' : '#9CA3AF';
        const statusColor = status === 'POTENTIAL' ? '#1E293B' : '#FFFFFF';
        const munName = spot.municipality_name || (spot.municipality && spot.municipality.name) || '';
        const cats = (spot.category || 'Other').split(',').map(c => c.trim()).filter(Boolean);
        const catTags = cats.map(c => `<span class="tag" style="background:#DBEAFE;color:#2563EB;">${c}</span>`).join('');
        const photoUrl = spot.photo_url || '';
        html += `<div class="spot-card" data-spot-id="${spot.id}" data-municipality="${munName}" data-category="${spot.category || ''}" data-status="${statusClass}" data-name="${(spot.name || '').toLowerCase()}">`;
        html += `<div class="spot-image">`;
        if (photoUrl) {
            html += `<img src="${escapeHtml(photoUrl)}" alt="${escapeHtml(spot.name || '')}" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="var p=this.parentElement;this.style.display='none';var ph=p.querySelector('.spot-image-placeholder');if(ph)ph.style.display='flex';">`;
            html += `<div class="spot-image-placeholder" style="display:none;"><i class="fas fa-image"></i><span>Image unavailable</span></div>`;
        } else {
            html += `<div class="spot-image-placeholder"><i class="fas fa-image"></i><span>No image yet</span></div>`;
        }
        html += `</div>`;
        html += `<div class="card-actions-dropdown">`;
        html += `<button class="dropdown-toggle" id="card-dropdown-${spot.id}"><i class="fas fa-ellipsis-v"></i></button>`;
        html += `<div class="dropdown-menu" id="card-menu-${spot.id}">`;
        html += `<button class="dropdown-item" data-action="view-spot" data-spot-id="${spot.id}"><i class="fas fa-eye" style="color:#3B82F6;"></i> View All Fields</button>`;
        html += `</div></div>`;
        html += `<div class="spot-body">`;
        html += `<h3>${spot.name || ''}</h3>`;
        html += `<div class="muni"><i class="fas fa-map-marker-alt"></i> ${munName}, La Union</div>`;
        html += `<div class="tags">`;
        html += catTags;
        const fee = Number(spot.entrance_fee || 0).toLocaleString(undefined, {minimumFractionDigits: 0});
        html += `<span class="tag" style="background:#F8FAFC;color:#4B5563;">₱${fee} per person</span>`;
        if (status) {
            html += `<span class="tag" style="background:${statusBg};color:${statusColor};">${statusClass}</span>`;
        }
        html += `</div>`;
        html += `<p>${desc}${(spot.description || '').length > 100 ? '...' : ''}</p>`;
        html += `</div></div>`;
    });
    grid.innerHTML = html;
    document.getElementById('spotCount').textContent = spotsData.length;
}

function renderTableRows(spotsData) {
    const tbody = document.querySelector('#tableView tbody');
    if (!tbody) return;
    let html = '';
    spotsData.forEach(spot => {
        const munName = spot.municipality_name || (spot.municipality && spot.municipality.name) || '';
        const status = spot.classification_status || '';
        const statusClass = status === 'EXIST' ? 'EXISTING' : status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL';
        const statusBg = status === 'EXIST' ? '#10B981' : status === 'EMERGE' ? '#8B5CF6' : status === 'POTENTIAL' ? '#F59E0B' : '#9CA3AF';
        const statusColor = status === 'POTENTIAL' ? '#1E293B' : '#FFFFFF';
        const cats = (spot.category || 'Other').split(',').map(c => c.trim()).filter(Boolean);
        const catTags = cats.map(c => `<span class="tag" style="background:#DBEAFE;color:#2563EB;font-size:11px;">${c}</span>`).join(' ');
        const date = spot.created_at ? new Date(spot.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const fee = Number(spot.entrance_fee || 0).toLocaleString(undefined, {minimumFractionDigits: 0});
        const spotId = String(spot.id).padStart(4, '0');
        html += `<tr data-spot-id="${spot.id}" data-municipality="${munName}" data-category="${spot.category || ''}" data-status="${statusClass}" data-name="${(spot.name || '').toLowerCase()}">`;
        html += `<td style="font-family:'Courier New',monospace;color:#6B7280;">TS-${spotId}</td>`;
        html += `<td><strong>${spot.name || ''}</strong></td>`;
        html += `<td>${munName}</td>`;
        html += `<td>${catTags}</td>`;
        html += `<td>${status ? `<span class="tag" style="background:${statusBg};color:${statusColor};">${statusClass}</span>` : ''}</td>`;
        html += `<td>₱${fee}</td>`;
        html += `<td>${date}</td>`;
        html += `<td style="text-align:right;"><div class="table-actions-dropdown">`;
        html += `<button class="dropdown-toggle" id="tbl-dropdown-${spot.id}"><i class="fas fa-ellipsis-v"></i></button>`;
        html += `<div class="dropdown-menu" id="tbl-menu-${spot.id}">`;
        html += `<button class="dropdown-item" data-action="view-spot" data-spot-id="${spot.id}"><i class="fas fa-eye" style="color:#3B82F6;"></i> View All Fields</button>`;
        html += `</div></div></td></tr>`;
    });
    tbody.innerHTML = html;
}

async function softRefreshSpots() {
    loadCachedKpis();
    const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
    try {
        const [spotsRes, muniRes] = await Promise.all([
            window.API_CONFIG.get(`${baseUrl}/api/tourist-spots`),
            window.API_CONFIG.get(`${baseUrl}/api/municipalities`)
        ]);
        const freshSpots = spotsRes.data || spotsRes || [];
        const freshMunis = muniRes.municipalities || muniRes.data || muniRes || [];
        window.touristSpotsData = freshSpots;
        window.municipalitiesData = freshMunis;
        window.touristSpotsAll = freshSpots;
        window.municipalitiesAll = freshMunis;
        renderCardsGrid(freshSpots);
        renderTableRows(freshSpots);
        updateKpiCards(freshSpots, freshMunis);
        setupDropdownListeners();
        document.querySelectorAll('[data-action="view-spot"]').forEach(btn => {
            btn.addEventListener('click', () => openSpotModal(btn.dataset.spotId));
        });
        const mapEl = document.getElementById('lupto-map');
        if (mapEl && mapEl._leaflet_map) {
            const map = mapEl._leaflet_map;
            map.eachLayer(layer => {
                if (layer instanceof L.Marker || (layer instanceof L.MarkerClusterGroup) || (layer._markers)) {
                    map.removeLayer(layer);
                }
            });
            freshSpots.forEach(spot => {
                const lat = parseFloat(spot.latitude) || 0;
                const lng = parseFloat(spot.longitude) || 0;
                if (!lat || !lng) return;
                const marker = L.marker([lat, lng]);
                const munName = spot.municipality_name || (spot.municipality?.name) || '';
                marker.bindPopup(`<strong>${spot.name}</strong><br>${munName}<br>${spot.category || ''}`);
                marker.addTo(map);
            });
        }
        console.log('✅ Tourist spots refreshed:', freshSpots.length, 'spots');
    } catch (err) {
        console.error('❌ Soft refresh failed:', err);
    }
}

window.softRefreshTouristSpots = softRefreshSpots;