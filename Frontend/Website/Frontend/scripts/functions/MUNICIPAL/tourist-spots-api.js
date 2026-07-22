// ════════════════════════════════════════════════════════════════════════════════
// MUNICIPAL TOURIST SPOTS - API & UTILITIES (ES Module)
// Scoped to the user's municipality — backend handles filtering by session.
// ════════════════════════════════════════════════════════════════════════════════

if (window.API_CONFIG && typeof window.API_CONFIG.getCsrfToken !== 'function') {
    window.API_CONFIG.getCsrfToken = function () {
        const match = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
        if (match) return decodeURIComponent(match.trim().split('=').slice(1).join('='));
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    };
}

const API_BASE = `${window.API_CONFIG?.BASE_URL || ('http://' + (window.location.hostname || '127.0.0.1') + ':8000')}/api/municipal/tourist-spots`;

function getSpotImageUploadUrl() {
    if (window.TOURIST_SPOT_UPLOAD_URL) return window.TOURIST_SPOT_UPLOAD_URL;
    return new URL('../../api/upload-spot-image.php', window.location.href).href;
}

// ── Map/Form State ──────────────────────────────────────────────────────────
let map, markerCluster;
let modalMap, modalMarker;
const mapLayers = {
    street: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 18 }),
    satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '© Esri', maxZoom: 18 }),
};
let uploadedImages = [];
let pendingSaveData = null;

const barangaysByMunicipality = {
    1: ['Allangigan', 'Aludaid', 'Bacsayan', 'Balballosa', 'Bambanay', 'Bugbugcao', 'Caarusipan', 'Cabaroan', 'Cabugnayan', 'Cacapian', 'Caculangan', 'Casilagan', 'Catdongan', 'Dangdangla', 'Dasay', 'Dinanum', 'Duplas', 'Guinguinabang', 'Ili Norte (Poblacion)', 'Ili Sur (Poblacion)', 'Legleg', 'Lubing', 'Nadsaag', 'Nagsabaran', 'Naguirangan', 'Naguituban', 'Nagyubuyuban', 'Oaquing', 'Pacpacac', 'Pagdildilan', 'Panicsican', 'Quidem', 'Santa Rosa', 'Saracat', 'Santo Rosario', 'Taboc', 'Talogtog', 'Urbiztondo'],
    2: ['Abut', 'Apaleng', 'Bacsil', 'Baraoas', 'Bato', 'Biday', 'Bangbangolan', 'Bangcusay', 'Barangay I (Poblacion)', 'Barangay II (Poblacion)', 'Barangay III (Poblacion)', 'Barangay IV (Poblacion)', 'Birunget', 'Bungro', 'Cabarsican', 'Cadaclan', 'Calabugao', 'Camansi', 'Canaoay', 'Carlatan', 'Cabaroan (Negro)', 'Cadapli', 'Dallangayan Este', 'Dallangayan Oeste', 'Dalumpinas Este', 'Dalumpinas Oeste', 'Ilocanos Norte', 'Ilocanos Sur', 'Langcuas', 'Lingsat', 'Madayegdeg', 'Mameltac', 'Masicong', 'Narra Este', 'Narra Oeste', 'Namtutan', 'Pagdaldagan', 'Pagdaraoan', 'Pao Norte', 'Pao Sur', 'Pacpaco', 'Pian', 'Poro', 'Puspus', 'San Agustin', 'San Francisco', 'Sagayad', 'Santiago Norte', 'Santiago Sur', 'San Vicente', 'Saoay', 'Siboan-Otong', 'Tanquigan', 'Tanqui', 'Sevilla'],
    3: ['Acao', 'Bagbag', 'Ballay', 'Baccuit Norte', 'Baccuit Sur', 'Boy-utan', 'Bucayab', 'Cabalayangan', 'Cabisilan', 'Casilagan', 'Central East (Poblacion)', 'Central West (Poblacion)', 'Dili', 'Disso-or', 'Guerrero', 'Jimenez', 'Jimenez West', 'Lower San Agustin', 'Nagrebcan', 'Pagdalagan Sur', 'Paliguasan', 'Palingulang', 'Parian Este', 'Parian Oeste', 'Paringao', 'Payocpoc Norte Este', 'Payocpoc Norte Oeste', 'Payocpoc Sur', 'Pilar', 'Pottot', 'Pudoc', 'Pugo', 'Quinavite', 'Santa Monica', 'Santiago', 'Taberna', 'Upper San Agustin', 'Urayong'],
    4: ['Ambitacay', 'Balawarte', 'Capas', 'Consolacion (Poblacion)', 'San Agustin East', 'San Agustin Norte', 'San Agustin Sur', 'San Antonino', 'San Antonio', 'San Francisco', 'San Isidro', 'San Java Norte', 'San Juan', 'San Jose Norte', 'San Jose Sur', 'San Julian Central', 'San Julian East', 'San Julian Norte', 'San Julian West', 'San Manuel Norte', 'San Manuel Sur', 'San Marcos', 'San Miguel', 'San Nicolas Central (Poblacion)', 'San Nicolas East', 'San Nicolas Norte (Poblacion)', 'San Nicolas Sur (Poblacion)', 'San Nicolas West', 'San Pedro', 'San Roque East', 'San Roque West', 'San Vicente Norte', 'San Vicente Sur', 'Santa Ana', 'Santa Barbara (Poblacion)', 'Santa Fe', 'Santa Maria', 'Santa Monica', 'Santa Rita (Nalinac)', 'Santa Rita East', 'Santa Rita Norte', 'Santa Rita Sur', 'Santa Rita West', 'Nazareno', 'Macalva Central', 'Macalva Norte', 'Macalva Sur', 'Purok'],
    5: ['Alcala (Poblacion)', 'Ayaoan', 'Barangobong', 'Barrientos', 'Bungro', 'Buselbusel', 'Cabalitocan', 'Cantoria No. 1', 'Cantoria No. 2', 'Cantoria No. 3', 'Cantoria No. 4', 'Carisquis', 'Darigayos', 'Magallanes (Poblacion)', 'Magsiping', 'Mamay', 'Nalvo Norte', 'Nalvo Sur', 'Nagrebcan', 'Napaset', 'Oaqui No. 1', 'Oaqui No. 2', 'Oaqui No. 3', 'Oaqui No. 4', 'Pila', 'Pitpitac', 'Rimos No. 1', 'Rimos No. 2', 'Rimos No. 3', 'Rimos No. 4', 'Rimos No. 5', 'Rissing', 'Salcedo (Poblacion)', 'Santo Domingo Norte', 'Santo Domingo Sur', 'Sucoc Norte', 'Sucoc Sur', 'Suyo', 'Tallaoen', 'Victoria (Poblacion)'],
    6: ['Amontoc', 'Apayao', 'Bayabas', 'Balbalayang', 'Bucao', 'Bumbuneg', 'Daking', 'Lacong', 'Lipay Este', 'Lipay Norte', 'Lipay Proper', 'Lipay Sur', 'Lon-oy', 'Poblacion', 'Polipol'],
    7: ['Almeida', 'Antonino', 'Apatut', 'Ar-arampang', 'Baracbac Este', 'Baracbac Oeste', 'Bet-ang', 'Bulbulala', 'Bungol', 'Butubut Este', 'Butubut Norte', 'Butubut Oeste', 'Butubut Sur', 'Cabuaan Oeste (Poblacion)', 'Calliat', 'Camiling', 'Calumbaya', 'Calungbuyan', 'Dr. Camilo Osias Poblacion (Cabuaan Este)', 'Guinaburan', 'Nagsabaran Norte', 'Nagsabaran Sur', 'Nalasin', 'Napaset', 'Pagbennecan', 'Pagleddegan', 'Paraoir', 'Patpata', 'Sablut', 'San Pablo', 'Sinapangan Norte', 'Sinapangan Sur', 'Tallipugo'],
    8: ['Alaska', 'Basca', 'Dulao', 'Gallano', 'Macabato', 'Manga', 'Pangao-aoan East', 'Pangao-aoan West', 'Poblacion', 'Samara', 'San Antonio', 'San Benito Norte', 'San Benito Sur', 'San Eugenio', 'San Juan East', 'San Juan West', 'San Simon East', 'San Simon West', 'Santa Cecilia', 'Santa Lucia', 'Santo Rosario East', 'Santo Rosario West', 'Santa Rita East', 'Santa Rita West'],
    9: ['Alipang', 'Amlang', 'Ambangonan', 'Bacani', 'Bangar', 'Bani', 'Benteng-Sapilang', 'Camp One', 'Carunuan East', 'Carunuan West', 'Casilagan', 'Cataguingtingan', 'Concepcion', 'Damortis', 'Gumot-Nagcolaran', 'Inabaan Norte', 'Inabaan Sur', 'Marcos', 'Nagtagaan', 'Nancamotian', 'Parasapas', 'Poblacion East', 'Poblacion West', 'San Jose', 'Subusub', 'Tabtabungao', 'Tay-ac', 'Tanglag', 'Udiao', 'Vila'],
    10: ['Agtipal', 'Arosip', 'Bacqui', 'Bacsil', 'Bagutot', 'Ballogo', 'Baroro', 'Bitalag', 'Burayoc', 'Bussaoit', 'Cabaroan', 'Cabarsican', 'Cabugao', 'Calautit', 'Carcarmay', 'Casiaman', 'Santa Cruz', 'Galongen', 'Guinabang', 'Legleg', 'Lisqueb', 'Mabanengbeng 1st', 'Mabanengbeng 2nd', 'Maragayap', 'Nagatiran', 'Nangalisan', 'Narra', 'Nagsaraboan', 'Nagsimbaanan', 'Oya-oy', 'Paagan', 'Pagan', 'Pandan', 'Pang-Pang', 'Poblacion', 'Quirino', 'Raois', 'Sagapan', 'Salincob', 'San Martin', 'Santa Rita', 'Sapilang', 'Sayoan', 'Sipulo', 'Ubbog', 'Zaragosa'],
    11: ['Al-alinao Norte', 'Al-alinao Sur', 'Aguioas', 'Ambaracao Norte', 'Ambaracao Sur', 'Angin', 'Baraoas Norte', 'Baraoas Sur', 'Bariquir', 'Bato', 'Balecbec', 'Bancagan', 'Bimmotobot', 'Dal-lipaoen', 'Daramuangan', 'Guesset', 'Gusing Norte', 'Gusing Sur', 'Imelda', 'Lioac Norte', 'Lioac Sur', 'Magungunay', 'Mamat-ing Norte', 'Mamat-ing Sur', 'Natividad (Poblacion)', 'Ortiz (Poblacion)', 'Ribsuan', 'San Antonio', 'San Isidro', 'Sili', 'Suguidan Norte', 'Suguidan Sur', 'Teddingan'],
    12: ['Amallapay', 'Anduyan', 'Caoigue', 'Francia Sur', 'Francia West', 'Garcia', 'Gonzales', 'Halog East', 'Halog West', 'Leones East', 'Leones West', 'Linapew', 'Lloren', 'Magsaysay', 'Pideg', 'Poblacion', 'Rizal', 'Santa Teresa'],
    13: ['Ambalite', 'Ambangonan', 'Cares', 'Cuenca', 'Duplas', 'Maoasoas Norte', 'Maoasoas Sur', 'Palina', 'Poblacion East', 'Poblacion West', 'Saytan', 'San Luis', 'Tavora East', 'Tavora Proper'],
    14: ['Bautista', 'Gana', 'Juan Cartas', 'Las-ud', 'Liquicia', 'Poblacion Norte', 'Poblacion Sur', 'San Carlos', 'San Cornelio', 'San Fermin', 'San Gregorio', 'San Jose', 'Santiago Norte', 'Santiago Sur', 'Sobredillo', 'Urayong', 'Wenceslao'],
    15: ['Ambitacay', 'Bail', 'Balaoc', 'Balsaan', 'Baybay', 'Cabaruan', 'Casilagan', 'Casantaan', 'Cupang', 'Damortis', 'Fernando', 'Linong', 'Lomboy', 'Malabago', 'Namboongan', 'Namonitan', 'Narvacan', 'Patac', 'Poblacion', 'Pongpong', 'Raois', 'Tococ', 'Tubod', 'Ubagan'],
    16: ['Agdeppa', 'Alzate', 'Bangaoilan East', 'Bangaoilan West', 'Barraca', 'Central East No. 1 (Poblacion)', 'Central East No. 2 (Poblacion)', 'Central West No. 1 (Poblacion)', 'Central West No. 2 (Poblacion)', 'Central West No. 3 (Poblacion)', 'Consuegra', 'General Prim East', 'General Prim West', 'General Terrero', 'Luzong Norte', 'Luzong Sur', 'Maria Cristina East', 'Maria Cristina West', 'Mindoro', 'Nagsabaran', 'Nagsidorisan', 'Quintarong', 'Reyna Regente', 'Rissing', 'San Blas', 'San Cristobal', 'Sinapangan Norte', 'Sinapangan Sur', 'Ubbog'],
    17: ['Agpay', 'Bilis', 'Caoayan', 'Dalacdac', 'Delles', 'Imelda', 'Libtong', 'Linuan', 'Lower Tumapoc', 'New Poblacion', 'Old Poblacion', 'Upper Tumapoc'],
    18: ['Alibangsay', 'Baay', 'Cambaly', 'Cardiz', 'Dagup', 'Libbo', 'Suyo (Poblacion)', 'Tagudtud', 'Tio-angan', 'Wallayan'],
    19: ['Corrooy', 'Lettac Norte', 'Lettac Sur', 'Mangaan', 'Paagan', 'Poblacion', 'Puguil', 'Ramot', 'Sasaba', 'Sapdaan', 'Tubaday'],
    20: ['Bigbiga', 'Bulalaan', 'Castro', 'Duplas', 'Ipet', 'Ilocano', 'Maliclico', 'Namaltugan', 'Old Central', 'Poblacion', 'Porporiket', 'San Francisco Norte', 'San Francisco Sur', 'San Jose', 'Sengngat', 'Turod', 'Up-uplas']
};

function getFilePreviewUrl(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.onerror = () => resolve(null);
        reader.readAsDataURL(file);
    });
}

// ── API CALLS ────────────────────────────────────────────────────────────────
export const getSpots = async () => await window.API_CONFIG.get(`${API_BASE}`);
export const getSpot = async (id) => await window.API_CONFIG.get(`${API_BASE}/${id}`);
export const createSpot = async (data) => await window.API_CONFIG.post(`${API_BASE}`, data);
export const updateSpot = async (id, data) => await window.API_CONFIG.put(`${API_BASE}/${id}`, data);
export const deleteSpot = async (id) => await window.API_CONFIG.delete(`${API_BASE}/${id}`);

window.getSpots = getSpots;
window.getSpot = getSpot;
window.createSpot = createSpot;
window.updateSpot = updateSpot;
window.deleteSpot = deleteSpot;

// ── Image Upload ─────────────────────────────────────────────────────────────
const compressImage = async (file, maxWidth = 1280, maxHeight = 720, quality = 0.7) => {
    return new Promise((resolve) => {
        if (!file.type.startsWith('image/')) { resolve(file); return; }
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                let { width, height } = img;
                if (width > maxWidth) { height = (height * maxWidth) / width; width = maxWidth; }
                if (height > maxHeight) { width = (width * maxHeight) / height; height = maxHeight; }
                const canvas = document.createElement('canvas');
                canvas.width = width; canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    if (!blob) { resolve(file); return; }
                    resolve(new File([blob], file.name, { type: 'image/jpeg', lastModified: Date.now() }));
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
    try { processedFile = await compressImage(file); } catch (err) { processedFile = file; }
    const formData = new FormData();
    formData.append('image', processedFile);
    const response = await fetch(getSpotImageUploadUrl(), {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }, body: formData
    });
    const text = await response.text();
    let data;
    try { data = JSON.parse(text); } catch { throw new Error(`Invalid server response (HTTP ${response.status})`); }
    if (!response.ok) throw new Error(data.error || data.message || `Upload failed: HTTP ${response.status}`);
    if (!data.success || !data.photo_url) throw new Error(data.error || 'Upload failed');
    return data;
};

// ── CATEGORY HELPERS ─────────────────────────────────────────────────────────
const statusDisplayMap = { 'EXIST': 'EXISTING', 'EMERGE': 'EMERGING', 'POTENTIAL': 'POTENTIAL' };
const statusReverseMap = { 'EXISTING': 'EXIST', 'EMERGING': 'EMERGE', 'POTENTIAL': 'POTENTIAL' };

export function getClassificationStyle(status) {
    const styles = {
        'EXIST': { bg: '#10B981', text: '#FFFFFF', label: 'EXISTING' },
        'EMERGE': { bg: '#8B5CF6', text: '#FFFFFF', label: 'EMERGING' },
        'POTENTIAL': { bg: '#F59E0B', text: '#1E293B', label: 'POTENTIAL' },
        'default': { bg: '#9CA3AF', text: '#FFFFFF', label: 'UNKNOWN' }
    };
    return styles[status] || styles['default'];
}

// ── TOAST ────────────────────────────────────────────────────────────────────
export function showToast(msg, type = 'success') {
    const colors = { success: '#16A34A', danger: '#DC2626', info: '#4338CA', warning: '#F59E0B' };
    const icons = { success: 'fa-check-circle', danger: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;align-items:center;gap:10px;max-width:360px;animation:slideIn 0.3s ease;';
    toast.style.background = colors[type] || '#1E293B';
    toast.style.color = 'white';
    toast.innerHTML = `<i class="fas ${icons[type] || 'fa-bell'}"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.4s'; setTimeout(() => toast.remove(), 400); }, 3000);
}

// ── CATEGORY ICON / COLOR ───────────────────────────────────────────────────
export function getCategoryIcon(catStr) {
    if (!catStr) return 'map-marker-alt';
    const cats = catStr.split(',').map(c => c.trim().toLowerCase());
    const map = { 'beach': 'umbrella-beach', 'mountain': 'mountain', 'waterfall': 'water', 'waterfalls': 'water', 'river': 'water', 'lake': 'water', 'island': 'umbrella-beach', 'cave': 'mountain', 'volcano': 'mountain', 'forest': 'tree', 'nature park': 'tree', 'marine sanctuary': 'fish', 'wildlife sanctuary': 'paw', 'historical': 'landmark', 'cultural heritage': 'landmark', 'religious': 'church', 'museum': 'museum', 'monument': 'monument', 'landmark': 'landmark', 'viewpoint': 'binoculars', 'adventure': 'hiking', 'hiking': 'hiking', 'camping': 'campground', 'farm': 'seedling', 'eco-tourism': 'leaf', 'garden': 'seedling', 'park': 'tree', 'recreation': 'bicycle', 'hot spring': 'hot-tub-person', 'cold spring': 'snowflake', 'food destination': 'utensils', 'shopping': 'shopping-cart', 'festival venue': 'masks-theater', 'resort': 'hotel', 'other': 'star' };
    for (const c of cats) { if (map[c]) return map[c]; }
    return 'map-marker-alt';
}

export function getCategoryColor(catStr) {
    if (!catStr) return '#3B82F6';
    const cat = catStr.split(',')[0].trim().toLowerCase();
    const colors = { 'beach': '#0EA5E9', 'waterfalls': '#06B6D4', 'waterfall': '#06B6D4', 'nature park': '#10B981', 'forest': '#059669', 'cultural heritage': '#F59E0B', 'historical': '#D97706', 'museum': '#8B5CF6', 'religious': '#EC4899', 'farm': '#84CC16', 'eco-tourism': '#10B981', 'cold spring': '#06B6D4', 'hot spring': '#EF4444', 'resort': '#6366F1' };
    return colors[cat] || '#3B82F6';
}

// ── MAIN MAP ─────────────────────────────────────────────────────────────────
export function initMap(spotsData, municipalData) {
    if (!document.getElementById('touristMap')) return;

    const muni = municipalData[0] || window.municipalityData || {};
    const muniLat = parseFloat(muni.latitude) || 16.5;
    const muniLng = parseFloat(muni.longitude) || 120.3;
    const bounds = L.latLngBounds([[muniLat - 0.15, muniLng - 0.15], [muniLat + 0.15, muniLng + 0.15]]);

    if (map) {
        if (markerCluster) markerCluster.clearLayers();
        map.eachLayer(layer => { if (layer !== mapLayers.street && layer !== mapLayers.satellite) map.removeLayer(layer); });
    } else {
        map = L.map('touristMap', { minZoom: 10 });
        mapLayers.street.addTo(map);
    }

    document.getElementById('touristMap')._leaflet_map = map;
    map.fitBounds(bounds);
    markerCluster = L.markerClusterGroup();
    map.addLayer(markerCluster);

    // Municipality marker
    const muniIcon = L.divIcon({
        html: `<div style="background:#DC2626;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3);font-weight:700;font-size:14px;">${spotsData.length}</div>`,
        iconSize: [40, 40], iconAnchor: [20, 20]
    });
    L.marker([muniLat, muniLng], { icon: muniIcon, zIndexOffset: 1000 })
        .bindTooltip(muni.name || 'Your Municipality', { permanent: true, direction: 'bottom', offset: [0, 22], opacity: .9 })
        .addTo(map);

    // Spot markers via cluster
    spotsData.filter(s => s.latitude && s.longitude).forEach(s => {
        const icon = L.divIcon({
            html: `<div style="background:${getCategoryColor(s.category)};width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);"><i class="fas fa-${getCategoryIcon(s.category)}" style="font-size:13px;"></i></div>`,
            iconSize: [28, 28], iconAnchor: [14, 28]
        });
        L.marker([parseFloat(s.latitude), parseFloat(s.longitude)], { icon })
            .bindPopup(`<strong>${s.name}</strong><br><small>${s.category}</small>`)
            .addTo(markerCluster);
    });

    setTimeout(() => map.invalidateSize(), 300);
}

export function setupMapLayerToggle() {
    document.querySelectorAll('.map-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.map-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            Object.values(mapLayers).forEach(l => { if (map && map.hasLayer(l)) map.removeLayer(l); });
            if (map) mapLayers[this.dataset.view].addTo(map);
        });
    });
}

// ── FILTERING ────────────────────────────────────────────────────────────────
export function filterSpots(searchValue = '', selectedCats = [], statusValue = '') {
    let visibleCount = 0;
    const mappedStatus = statusReverseMap[statusValue] || statusValue;

    function matchesCat(cardCat) {
        if (!selectedCats || selectedCats.length === 0) return true;
        const spotCats = (cardCat || '').split(',').map(s => s.trim());
        return selectedCats.some(fc => spotCats.includes(fc));
    }

    document.querySelectorAll('#cardsView .spot-card').forEach(card => {
        const nameMatch = !searchValue || card.dataset.name.includes(searchValue.toLowerCase());
        const catMatch = matchesCat(card.dataset.category);
        const statusMatch = !statusValue || card.dataset.status === mappedStatus;
        const show = nameMatch && catMatch && statusMatch;
        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });

    document.querySelectorAll('#tableView tbody tr').forEach(row => {
        const nameMatch = !searchValue || row.dataset.name.includes(searchValue.toLowerCase());
        const catMatch = matchesCat(row.dataset.category);
        const statusMatch = !statusValue || row.dataset.status === mappedStatus;
        row.style.display = (nameMatch && catMatch && statusMatch) ? '' : 'none';
    });

    const countEl = document.getElementById('spotCount');
    if (countEl) countEl.textContent = visibleCount;
    return visibleCount;
}

export function toggleDropdown(menuId) {
    const menu = document.getElementById(menuId);
    const isOpen = menu.style.display === 'block';
    document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    if (!isOpen) menu.style.display = 'block';
}

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

export function setupFilterListeners() {
    const applyFilters = () => {
        const searchValue = document.getElementById('searchInput')?.value || '';
        const selectedCats = Array.from(document.querySelectorAll('.cat-filter-chk:checked')).map(c => c.value);
        const statusValue = document.getElementById('filterStatus')?.value || '';
        filterSpots(searchValue, selectedCats, statusValue);
    };

    document.getElementById('searchInput')?.addEventListener('input', applyFilters);
    document.getElementById('filterStatus')?.addEventListener('change', applyFilters);
    document.querySelectorAll('.cat-filter-chk').forEach(chk => chk.addEventListener('change', applyFilters));
}

export function setupDropdownListeners() {
    document.querySelectorAll('[id^="card-dropdown-"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const spotId = btn.id.replace('card-dropdown-', '');
            toggleDropdown('card-menu-' + spotId);
        });
    });
    document.querySelectorAll('[id^="tbl-dropdown-"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const spotId = btn.id.replace('tbl-dropdown-', '');
            toggleDropdown('tbl-menu-' + spotId);
        });
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.card-actions-dropdown') && !e.target.closest('.table-actions-dropdown'))
            document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    });
}

// ── SPOT DETAIL MODAL ────────────────────────────────────────────────────────
window.openSpotModal = async function (spotId) {
    const modal = document.getElementById('spotModal');
    if (!modal) return;
    modal.classList.add('active');
    document.getElementById('modalTitle').textContent = 'Loading...';
    document.getElementById('modalBody').innerHTML = '<div style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-spinner fa-spin" style="font-size:24px;"></i></div>';

    try {
        let spot = window.touristSpotsAll?.find(s => s.id == spotId);
        if (!spot) spot = await window.getSpot(spotId);
        document.getElementById('modalTitle').textContent = spot.name;
        const style = spot.classification_status ? getClassificationStyle(spot.classification_status) : null;
        const formattedDate = new Date(spot.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        function fmTime(t) {
            if (!t) return 'N/A';
            const [h, m] = t.split(':').map(Number);
            return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
        }

        document.getElementById('modalBody').innerHTML = `
            ${spot.photo_url ? `<div style="height:200px;border-radius:10px;overflow:hidden;margin-bottom:16px;"><img src="${escapeHtml(spot.photo_url)}" alt="${escapeHtml(spot.name)}" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentElement.style.display='none';"></div>` : ''}
            <div style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
                <span style="font-size:13px;color:#6B7280;"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(spot.municipality_name)}, La Union</span>
                ${style ? `<span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;background:${style.bg};color:${style.text};">${style.label}</span>` : ''}
                ${spot.is_maintenance ? '<span style="font-size:13px;font-weight:700;padding:4px 12px;border-radius:20px;background:#F59E0B;color:#92400E;"><i class="fas fa-exclamation-triangle"></i> Under Maintenance</span>' : ''}
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Category</div><div style="display:flex;flex-wrap:wrap;gap:5px;">${(spot.category || 'Other').split(',').map(c => c.trim()).filter(Boolean).map(c => `<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#DBEAFE;color:#2563EB;">${escapeHtml(c)}</span>`).join('')}</div></div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Entry Fee</div><div style="font-size:14px;font-weight:600;">₱${parseFloat(spot.entrance_fee).toLocaleString()}</div></div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Opening Time</div><div style="font-size:14px;font-weight:600;">${fmTime(spot.opening_time)}</div></div>
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Closing Time</div><div style="font-size:14px;font-weight:600;">${fmTime(spot.closing_time)}</div></div>
                ${spot.latitude ? `<div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Latitude</div><div style="font-size:14px;font-weight:600;"><i class="fas fa-map-pin"></i> ${parseFloat(spot.latitude).toFixed(6)}</div></div>` : ''}
                ${spot.longitude ? `<div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Longitude</div><div style="font-size:14px;font-weight:600;"><i class="fas fa-map-pin"></i> ${parseFloat(spot.longitude).toFixed(6)}</div></div>` : ''}
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Submitted</div><div style="font-size:14px;font-weight:600;">${formattedDate}</div></div>
            </div>
            <div style="margin-bottom:20px;"><div style="font-size:11px;color:#6B7280;font-weight:700;text-transform:uppercase;margin-bottom:8px;">Description</div><p style="color:#4B5563;line-height:1.6;margin:0;">${escapeHtml(spot.description) || 'No description provided.'}</p></div>`;
    } catch (err) {
        document.getElementById('modalBody').innerHTML = '<p style="color:#DC2626;">Failed to load spot details.</p>';
    }
};

export function closeSpotModal() { document.getElementById('spotModal')?.classList.remove('active'); }

export function setupModalListeners() {
    document.getElementById('closeSpotModal')?.addEventListener('click', closeSpotModal);
    document.getElementById('spotModal')?.addEventListener('click', e => { if (e.target.id === 'spotModal') closeSpotModal(); });
    document.querySelectorAll('[data-action="view-spot"]').forEach(item => {
        item.addEventListener('click', () => openSpotModal(item.dataset.spotId));
    });
}

export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ── IMAGE HANDLING ───────────────────────────────────────────────────────────
window.handleImageSelect = async function (e) {
    const files = Array.from(e.target.files);
    e.stopPropagation();
    e.target.value = '';
    await processImageFiles(files);
};

window.handleImageDrop = async function (e) {
    e.preventDefault(); e.stopPropagation();
    const area = document.getElementById('imageUploadArea');
    if (area) { area.style.borderColor = '#D1D5DB'; area.style.background = '#F9FAFB'; }
    await processImageFiles(Array.from(e.dataTransfer.files));
};

window.handleDragOver = function (e) {
    e.preventDefault(); e.stopPropagation();
    const area = document.getElementById('imageUploadArea');
    if (area) { area.style.borderColor = '#2563EB'; area.style.background = '#EEF2FF'; }
};

window.handleDragLeave = function (e) {
    e.preventDefault(); e.stopPropagation();
    const area = document.getElementById('imageUploadArea');
    if (area) { area.style.borderColor = '#D1D5DB'; area.style.background = '#F9FAFB'; }
};

async function processImageFiles(files) {
    const validFiles = [];
    for (const file of files) {
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) { showToast(`Invalid file: ${file.name}`, 'danger'); continue; }
        if (file.size > 10 * 1024 * 1024) { showToast(`File too large: ${file.name}`, 'danger'); continue; }
        validFiles.push(file);
    }
    if (validFiles.length === 0) return;

    for (const file of validFiles) {
        const previewUrl = await getFilePreviewUrl(file);
        uploadedImages.push({ photo_url: previewUrl, filename: file.name, isLoading: true, id: `${Date.now()}-${Math.random().toString(36).slice(2)}` });
    }
    renderImagePreviews();

    const results = await Promise.allSettled(validFiles.map(async (file, idx) => {
        const tempId = uploadedImages[uploadedImages.length - validFiles.length + idx].id;
        try { const r = await uploadImage(file); return { result: r, tempId, success: true }; }
        catch (err) { return { error: err, tempId, success: false }; }
    }));

    let successCount = 0;
    for (const settled of results) {
        if (settled.status !== 'fulfilled') continue;
        const item = settled.value;
        const index = uploadedImages.findIndex(img => img.id === item.tempId);
        if (index === -1) continue;
        if (item.success) {
            uploadedImages[index] = { photo_url: item.result.photo_url, filename: item.result.filename || 'file' };
            successCount++;
        } else uploadedImages.splice(index, 1);
    }
    renderImagePreviews();
    const stuck = uploadedImages.filter(img => img.isLoading);
    if (stuck.length > 0) { uploadedImages = uploadedImages.filter(img => !img.isLoading); renderImagePreviews(); }
    if (successCount > 0) showToast(`${successCount} image(s) uploaded`, 'success');
}

function renderImagePreviews() {
    const container = document.getElementById('imagePreviews');
    if (!container) return;
    container.innerHTML = uploadedImages.map((img, idx) => `
        <div style="position:relative;border-radius:8px;overflow:hidden;width:100px;height:100px;border:2px solid #E5E7EB;">
            <img src="${img.photo_url}" alt="Preview" style="width:100%;height:100%;object-fit:cover;${img.isLoading ? 'filter:brightness(0.7)' : ''}">
            ${img.isLoading ? '<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.3);z-index:10;"><i class="fas fa-spinner fa-spin" style="font-size:24px;color:white;"></i></div>' : ''}
            <button type="button" onclick="removeImage(${idx})" style="position:absolute;top:4px;right:4px;background:#DC2626;color:white;border:none;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;${img.isLoading ? 'display:none' : ''}"><i class="fas fa-times"></i></button>
        </div>`).join('');
}

window.removeImage = function (index) { uploadedImages.splice(index, 1); renderImagePreviews(); };

// ── CATEGORY CHIP FORM LOGIC ─────────────────────────────────────────────────
function initCategoryChips() {
    document.addEventListener('click', function (e) {
        const formDd = document.getElementById('formCatDropdown'), formBtn = document.getElementById('formCatDropdownBtn');
        if (formDd && formBtn && !formBtn.contains(e.target) && !formDd.contains(e.target)) {
            formDd.style.display = 'none';
            const chevron = document.getElementById('formCatChevron');
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
    if (chk) chk.checked = !chk.checked;
    syncCategoryHiddenInput();
};

function syncCategoryHiddenInput() {
    const selected = Array.from(document.querySelectorAll('.form-cat-chk:checked')).map(c => c.value);
    document.getElementById('spotCategory').value = selected.join(',');
    const label = document.getElementById('formCatDropdownLabel');
    if (selected.length > 0) { label.textContent = selected.join(', '); label.style.color = '#1E293B'; }
    else { label.textContent = 'Select Categories...'; label.style.color = '#9CA3AF'; }
}

function setSelectedCategories(catStr) {
    document.querySelectorAll('.form-cat-chk').forEach(c => c.checked = false);
    if (!catStr) { syncCategoryHiddenInput(); return; }
    catStr.split(',').map(s => s.trim()).forEach(cat => {
        const chk = document.querySelector(`.form-cat-chk[value="${cat}"]`);
        if (chk) chk.checked = true;
    });
    syncCategoryHiddenInput();
}

window.toggleFreeEntry = function () {
    const isFree = document.getElementById('isFree').checked;
    const feeInput = document.getElementById('spotFee');
    if (isFree) { feeInput.value = '0'; feeInput.disabled = true; } else { feeInput.disabled = false; feeInput.focus(); }
};

function populateBarangayDropdown(selectedValue) {
    const muniId = window.municipalityData?.id;
    const select = document.getElementById('spotBarangay');
    if (!select || !muniId) return;

    select.innerHTML = '<option value="">— Select Barangay —</option>';
    const barangays = barangaysByMunicipality[parseInt(muniId)] || [];
    barangays.forEach(b => {
        const opt = document.createElement('option');
        opt.value = b;
        opt.textContent = b;
        if (selectedValue && b === selectedValue) opt.selected = true;
        select.appendChild(opt);
    });
}

// ── FORM OPEN / EDIT ─────────────────────────────────────────────────────────
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
    document.getElementById('spotOpeningTime').value = '';
    document.getElementById('spotClosingTime').value = '';
    document.getElementById('spotIsMaintenance').checked = false;
    populateBarangayDropdown();
    document.getElementById('imagePreviews').innerHTML = '';
    document.getElementById('maintenance-field').style.display = 'none';
    document.getElementById('spotFormModal').classList.add('active');
    setTimeout(initModalMap, 200);
};

window.editSpot = async function (spotId) {
    try {
        let spot = window.touristSpotsAll?.find(s => s.id == spotId);
        if (!spot) spot = await window.getSpot(spotId);
        uploadedImages = spot.images && spot.images.length > 0 ? spot.images : (spot.photo_url ? [{ photo_url: spot.photo_url }] : []);

        document.getElementById('formModalTitle').textContent = 'Edit Spot';
        document.getElementById('spotId').value = spot.id;
        document.getElementById('spotName').value = spot.name;
        document.getElementById('nameCharCount').textContent = spot.name.length;
        setSelectedCategories(spot.category || '');
        document.getElementById('spotClassification').value = statusDisplayMap[spot.classification_status] || spot.classification_status;
        document.getElementById('spotFee').value = spot.entrance_fee;
        const isFree = parseFloat(spot.entrance_fee) === 0;
        document.getElementById('isFree').checked = isFree;
        document.getElementById('spotFee').disabled = isFree;
        document.getElementById('spotLatitude').value = spot.latitude || '';
        document.getElementById('spotLongitude').value = spot.longitude || '';
        document.getElementById('spotDescription').value = spot.description || '';
        document.getElementById('descCharCount').textContent = (spot.description || '').length;
        document.getElementById('spotOpeningTime').value = spot.opening_time || '';
        document.getElementById('spotClosingTime').value = spot.closing_time || '';
        document.getElementById('spotIsMaintenance').checked = spot.is_maintenance ? true : false;
        populateBarangayDropdown(spot.barangay);
        document.getElementById('maintenance-field').style.display = 'block';
        renderImagePreviews();
        document.getElementById('spotFormModal').classList.add('active');
        setTimeout(initModalMap, 200);
        if (spot.latitude && spot.longitude) {
            setTimeout(() => placeOrMoveDraggableMarker(parseFloat(spot.latitude), parseFloat(spot.longitude)), 250);
        }
    } catch (err) {
        showToast('Failed to load spot for editing', 'danger');
    }
};

// ── MODAL MAP ────────────────────────────────────────────────────────────────
function initModalMap() {
    if (!document.getElementById('modalMap')) return;
    if (modalMap) { modalMap.remove(); modalMap = null; modalMarker = null; }
    modalMap = L.map('modalMap', { minZoom: 10, maxZoom: 18 });
    const modalStreet = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 18 });
    const modalSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { attribution: '© Esri', maxZoom: 18 });
    modalStreet.addTo(modalMap);
    L.control.layers({ "Street Map": modalStreet, "Satellite Map": modalSatellite }, null, { position: 'topright' }).addTo(modalMap);
    modalMap.setView([16.5, 120.3], 10);

    [100, 250, 500].forEach(d => setTimeout(() => { if (modalMap) modalMap.invalidateSize(); }, d));

    modalMap.on('click', function (e) {
        document.getElementById('spotLatitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('spotLongitude').value = e.latlng.lng.toFixed(6);
        placeOrMoveDraggableMarker(e.latlng.lat, e.latlng.lng);
    });
}

window.placeOrMoveDraggableMarker = function (lat, lng) {
    if (!modalMap) return;
    const icon = L.divIcon({
        html: `<div style="background:#2563EB;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;border:3px solid white;box-shadow:0 3px 10px rgba(37,99,235,.45);cursor:grab;"><i class="fas fa-map-marker-alt" style="font-size:14px;"></i></div>`,
        iconSize: [32, 32], iconAnchor: [16, 32]
    });
    if (!modalMarker) {
        modalMarker = L.marker([lat, lng], { icon, draggable: true }).addTo(modalMap);
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
    } else modalMarker.setLatLng([lat, lng]);
    modalMap.setView([lat, lng], 15);
};

window.updateMapMarkerFromInput = function () {
    const lat = parseFloat(document.getElementById('spotLatitude').value);
    const lng = parseFloat(document.getElementById('spotLongitude').value);
    if (isNaN(lat) || isNaN(lng)) {
        if (modalMarker && modalMap) { modalMap.removeLayer(modalMarker); modalMarker = null; }
        return;
    }
    placeOrMoveDraggableMarker(lat, lng);
};

window.closeFormModal = function () {
    uploadedImages = [];
    pendingSaveData = null;
    document.getElementById('spotFormModal').classList.remove('active');
    if (modalMap) { modalMap.remove(); modalMap = null; modalMarker = null; }
};

// ── FORM SUBMIT ──────────────────────────────────────────────────────────────
window.submitSpotForm = async function (e) {
    e.preventDefault();
    const saveBtn = document.getElementById('saveSpotBtn');
    const saveIcon = document.getElementById('saveSpotIcon');
    const saveSpinner = document.getElementById('saveSpotSpinner');
    const saveLabel = document.getElementById('saveSpotLabel');
    if (saveBtn) { saveBtn.disabled = true; if (saveIcon) saveIcon.style.display = 'none'; if (saveSpinner) saveSpinner.style.display = 'inline-block'; if (saveLabel) saveLabel.textContent = 'Validating...'; }
    const resetSaveBtn = () => { if (saveBtn) { saveBtn.disabled = false; if (saveIcon) saveIcon.style.display = 'inline-block'; if (saveSpinner) saveSpinner.style.display = 'none'; if (saveLabel) saveLabel.textContent = 'Save Spot'; } };

    const catVal = document.getElementById('spotCategory').value;
    if (!catVal) { showToast('Please select at least one category', 'danger'); resetSaveBtn(); return; }
    const classVal = document.getElementById('spotClassification').value;
    if (!classVal) { showToast('Please select a classification status', 'danger'); resetSaveBtn(); return; }

    const stillUploading = uploadedImages.some(img => img.isLoading);
    if (stillUploading) {
        showToast('Please wait for all images to finish uploading before saving', 'danger');
        resetSaveBtn();
        return;
    }

    const cleanImages = uploadedImages
        .filter(img => !img.isLoading && img.photo_url && !img.photo_url.startsWith('blob:'))
        .map(img => ({ photo_url: img.photo_url, filename: img.filename || '' }));

    const spotIdVal = document.getElementById('spotId').value;
    const dbStatus = statusReverseMap[classVal] || classVal;

    pendingSaveData = {
        id: spotIdVal ? parseInt(spotIdVal) : null,
        name: document.getElementById('spotName').value,
        category: catVal,
        classification_status: dbStatus,
        entrance_fee: parseFloat(document.getElementById('spotFee').value) || 0,
        latitude: parseFloat(document.getElementById('spotLatitude').value) || null,
        longitude: parseFloat(document.getElementById('spotLongitude').value) || null,
        barangay: document.getElementById('spotBarangay').value || null,
        description: document.getElementById('spotDescription').value,
        municipality_id: parseInt(document.getElementById('municipalityId').value),
        images: cleanImages,
        opening_time: document.getElementById('spotOpeningTime').value || null,
        closing_time: document.getElementById('spotClosingTime').value || null,
        is_maintenance: document.getElementById('spotIsMaintenance').checked ? 1 : 0
    };

    resetSaveBtn();
    document.getElementById('saveConfirmModal').classList.add('active');
};

window.closeSaveConfirmModal = function () { document.getElementById('saveConfirmModal').classList.remove('active'); };

window.confirmSaveSpot = async function () {
    if (!pendingSaveData) { showToast('No data to save', 'danger'); return; }
    const confirmBtn = document.getElementById('saveConfirmBtn');
    const confirmIcon = document.getElementById('confirmBtnIcon');
    const confirmSpinner = document.getElementById('confirmBtnSpinner');
    const confirmLabel = document.getElementById('confirmBtnLabel');
    if (confirmBtn) {
        confirmBtn.disabled = true;
        if (confirmIcon) confirmIcon.style.display = 'none';
        if (confirmSpinner) confirmSpinner.style.display = 'inline-block';
        if (confirmLabel) confirmLabel.textContent = pendingSaveData.id ? 'Updating...' : 'Saving...';
    }

    try {
        let res;
        if (pendingSaveData.id) res = await updateSpot(pendingSaveData.id, pendingSaveData);
        else res = await createSpot(pendingSaveData);

        if (res && (res.success || res.message)) {
            closeSaveConfirmModal();
            closeFormModal();
            showToast(pendingSaveData.id ? 'Spot updated successfully!' : 'Spot created successfully!', 'success');
            if (typeof window.refreshActiveTab === 'function') window.refreshActiveTab();
        } else throw new Error(res.message || 'Unknown error');
    } catch (err) {
        showToast(`Error: ${err.message || 'Failed to save'}`, 'danger');
        if (confirmBtn) { confirmBtn.disabled = false; if (confirmIcon) confirmIcon.style.display = 'inline-block'; if (confirmSpinner) confirmSpinner.style.display = 'none'; if (confirmLabel) confirmLabel.textContent = 'Yes, Save'; }
        closeSaveConfirmModal();
    }
};

// ── INITIALIZE ALL ───────────────────────────────────────────────────────────
export async function initializeAll(spotsData, municipalData) {
    loadCachedMuniKpis();

    if (!spotsData || !spotsData.length || !municipalData || !municipalData.length) {
        try {
            const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
            const spotsRes = await window.API_CONFIG.get(`${baseUrl}/api/municipal/tourist-spots`);
            spotsData = spotsRes.data || spotsRes || [];
            municipalData = window.municipalityData ? [window.municipalityData] : [{ id: 0, name: 'Your Municipality' }];
        } catch (err) {
            console.error('Failed to fetch municipal tourist spots:', err);
            spotsData = [];
            municipalData = [{ id: 0, name: 'Your Municipality' }];
        }
    }

    window.touristSpotsData = spotsData;
    window.municipalitiesData = municipalData;
    window.touristSpotsAll = spotsData;
    window.municipalitiesAll = municipalData;

    const munName = (municipalData[0] && municipalData[0].name) || 'Your Municipality';
    renderCardsGrid(spotsData, munName);
    renderTableRows(spotsData, munName);
    updateKpiCards(spotsData, municipalData);

    const pendingToast = sessionStorage.getItem('save_success_toast');
    if (pendingToast) { showToast(pendingToast, 'success'); sessionStorage.removeItem('save_success_toast'); }

    try { if (document.getElementById('touristMap')) initMap(spotsData, municipalData); } catch (e) { console.error('Map init failed:', e); }
    try { if (document.getElementById('lupto-map')) initMainMap(spotsData, municipalData); } catch (e) { }
    try { setupMapLayerToggle(); } catch (e) { }
    try { setupViewToggle(); } catch (e) { }
    try { setupFilterListeners(); } catch (e) { }
    try { setupDropdownListeners(); } catch (e) { }
    try { setupModalListeners(); } catch (e) { }
    try { initCategoryChips(); } catch (e) { }

    document.querySelectorAll('[data-action="open-create-form"]').forEach(btn => btn.addEventListener('click', () => openCreateForm()));
    document.querySelectorAll('[data-action="view-spot"]').forEach(btn => btn.addEventListener('click', () => openSpotModal(btn.dataset.spotId)));
    document.querySelectorAll('[data-action="edit-spot"]').forEach(btn => btn.addEventListener('click', () => editSpot(btn.dataset.spotId)));
    document.querySelectorAll('[data-action="close-form-modal"]').forEach(el => el.addEventListener('click', closeFormModal));
    document.getElementById('spotFormModal')?.addEventListener('click', e => { if (e.target.id === 'spotFormModal') closeFormModal(); });
    document.getElementById('spotForm')?.addEventListener('submit', submitSpotForm);
    document.getElementById('saveConfirmModal')?.addEventListener('click', e => { if (e.target.id === 'saveConfirmModal') closeSaveConfirmModal(); });
    document.querySelector('[data-action="close-save-confirm"]')?.addEventListener('click', closeSaveConfirmModal);
    document.querySelector('[data-action="confirm-save-spot"]')?.addEventListener('click', confirmSaveSpot);
    document.getElementById('isFree')?.addEventListener('change', toggleFreeEntry);
    document.getElementById('spotLatitude')?.addEventListener('input', updateMapMarkerFromInput);
    document.getElementById('spotLongitude')?.addEventListener('input', updateMapMarkerFromInput);
    document.getElementById('spotName')?.addEventListener('input', function () { document.getElementById('nameCharCount').textContent = this.value.length; });
    document.getElementById('spotDescription')?.addEventListener('input', function () { document.getElementById('descCharCount').textContent = this.value.length; });

    const uploadArea = document.getElementById('imageUploadArea');
    const fileInput = document.getElementById('spotImages');
    if (uploadArea) {
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleImageDrop);
    }
    if (fileInput) fileInput.addEventListener('change', handleImageSelect);

    startKpiAutoRefresh();
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

function initMainMap(spotsData, municipalData) {
    const muni = municipalData[0] || window.municipalityData || {};
    const muniLat = parseFloat(muni.latitude) || 16.5;
    const muniLng = parseFloat(muni.longitude) || 120.3;
    const name = muni.name || 'Your Municipality';

    const mapEl = document.getElementById('lupto-map');
    if (!mapEl) return;
    if (mapEl._leaflet_map) {
        const map = mapEl._leaflet_map;
        map.eachLayer(layer => { if (layer instanceof L.Marker) map.removeLayer(layer); });
    }

    spotsData.forEach(spot => {
        const lat = parseFloat(spot.latitude) || 0;
        const lng = parseFloat(spot.longitude) || 0;
        if (!lat || !lng) return;
        const marker = L.marker([lat, lng]);
        marker.bindPopup(`<strong>${spot.name}</strong><br>${spot.category || ''}`);
        if (mapEl._leaflet_map) marker.addTo(mapEl._leaflet_map);
    });
}

function updateKpiCards(spotsData, municipalData) {
    const kpiEls = document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value');
    const vals = {
        total: spotsData.length,
        open: spotsData.filter(s => (s.status || '') === 'approved' && !s.is_maintenance).length,
        closed: spotsData.filter(s => s.is_maintenance || (s.status || '') !== 'approved').length,
        visits: Number(spotsData.reduce((sum, s) => sum + (parseInt(s.visits) || 0), 0)).toLocaleString(),
    };
    if (kpiEls[0]) kpiEls[0].textContent = vals.total;
    if (kpiEls[1]) kpiEls[1].textContent = vals.open;
    if (kpiEls[2]) kpiEls[2].textContent = vals.closed;
    if (kpiEls[3]) kpiEls[3].textContent = vals.visits;
    const spotCount = document.getElementById('spotCount');
    if (spotCount) spotCount.textContent = vals.total;
    try { sessionStorage.setItem('ts_kpis_municipal', JSON.stringify(vals)); } catch (e) { }
}

function loadCachedMuniKpis() {
    try {
        const raw = sessionStorage.getItem('ts_kpis_municipal');
        if (!raw) return;
        const v = JSON.parse(raw);
        const kpiEls = document.querySelectorAll('.lupto-kpi-card .lupto-kpi-value');
        if (kpiEls[0] && kpiEls[0].textContent.trim() === '') return;
        if (kpiEls[0]) { kpiEls[0].textContent = v.total; kpiEls[0].style.color = '#1E293B'; }
        if (kpiEls[1]) { kpiEls[1].textContent = v.open; kpiEls[1].style.color = '#1E293B'; }
        if (kpiEls[2]) { kpiEls[2].textContent = v.closed; kpiEls[2].style.color = '#1E293B'; }
        if (kpiEls[3]) { kpiEls[3].textContent = v.visits; kpiEls[3].style.color = '#1E293B'; }
        const spotCount = document.getElementById('spotCount');
        if (spotCount) spotCount.textContent = v.total;
    } catch (e) { }
}

function renderCardsGrid(spotsData, munName) {
    const grid = document.getElementById('cardsView');
    if (!grid) return;
    let html = '';
    spotsData.forEach(spot => {
        const desc = (spot.description || '').substring(0, 100);
        const status = spot.classification_status || '';
        const statusClass = status === 'EXIST' ? 'EXISTING' : status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL';
        const statusBg = status === 'EXIST' ? '#10B981' : status === 'EMERGE' ? '#8B5CF6' : status === 'POTENTIAL' ? '#F59E0B' : '#9CA3AF';
        const statusColor = status === 'POTENTIAL' ? '#1E293B' : '#FFFFFF';
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
        html += `<button class="dropdown-item" data-action="edit-spot" data-spot-id="${spot.id}"><i class="fas fa-pen-to-square" style="color:#F59E0B;"></i> Edit</button>`;
        html += `</div></div>`;
        html += `<div class="spot-body">`;
        html += `<h3>${spot.name || ''}</h3>`;
        html += `<div class="muni"><i class="fas fa-map-marker-alt"></i> ${munName}, La Union</div>`;
        html += `<div class="tags">`;
        html += catTags;
        const fee = Number(spot.entrance_fee || 0).toLocaleString(undefined, { minimumFractionDigits: 0 });
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

function renderTableRows(spotsData, munName) {
    const tbody = document.querySelector('#tableView tbody');
    if (!tbody) return;
    let html = '';
    spotsData.forEach(spot => {
        const status = spot.classification_status || '';
        const statusClass = status === 'EXIST' ? 'EXISTING' : status === 'EMERGE' ? 'EMERGING' : 'POTENTIAL';
        const statusBg = status === 'EXIST' ? '#10B981' : status === 'EMERGE' ? '#8B5CF6' : status === 'POTENTIAL' ? '#F59E0B' : '#9CA3AF';
        const statusColor = status === 'POTENTIAL' ? '#1E293B' : '#FFFFFF';
        const cats = (spot.category || 'Other').split(',').map(c => c.trim()).filter(Boolean);
        const catTags = cats.map(c => `<span class="tag" style="background:#DBEAFE;color:#2563EB;font-size:11px;">${c}</span>`).join(' ');
        const date = spot.created_at ? new Date(spot.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const fee = Number(spot.entrance_fee || 0).toLocaleString(undefined, { minimumFractionDigits: 0 });
        const spotId = String(spot.id).padStart(4, '0');
        html += `<tr data-spot-id="${spot.id}" data-municipality="${munName}" data-category="${spot.category || ''}" data-status="${statusClass}" data-name="${(spot.name || '').toLowerCase()}">`;
        html += `<td style="font-family:'Courier New',monospace;color:#6B7280;">TS-${spotId}</td>`;
        html += `<td><strong>${spot.name || ''}</strong></td>`;
        html += `<td>${catTags}</td>`;
        html += `<td>${status ? `<span class="tag" style="background:${statusBg};color:${statusColor};">${statusClass}</span>` : ''}</td>`;
        html += `<td>₱${fee}</td>`;
        html += `<td>${date}</td>`;
        html += `<td style="text-align:right;"><div class="table-actions-dropdown">`;
        html += `<button class="dropdown-toggle" id="tbl-dropdown-${spot.id}"><i class="fas fa-ellipsis-v"></i></button>`;
        html += `<div class="dropdown-menu" id="tbl-menu-${spot.id}">`;
        html += `<button class="dropdown-item" data-action="view-spot" data-spot-id="${spot.id}"><i class="fas fa-eye" style="color:#3B82F6;"></i> View All Fields</button>`;
        html += `<button class="dropdown-item" data-action="edit-spot" data-spot-id="${spot.id}"><i class="fas fa-pen-to-square" style="color:#F59E0B;"></i> Edit</button>`;
        html += `</div></div></td></tr>`;
    });
    tbody.innerHTML = html;
}

async function softRefreshSpots() {
    loadCachedMuniKpis();
    const baseUrl = window.API_CONFIG?.BASE_URL || (`http://${window.location.hostname || '127.0.0.1'}:8000`);
    try {
        const spotsRes = await window.API_CONFIG.get(`${baseUrl}/api/municipal/tourist-spots`);
        const freshSpots = spotsRes.data || spotsRes || [];
        const freshMuni = window.municipalityData || { name: 'Your Municipality' };
        const munName = freshMuni.name || 'Your Municipality';
        window.touristSpotsData = freshSpots;
        window.municipalitiesData = [freshMuni];
        window.touristSpotsAll = freshSpots;
        window.municipalitiesAll = [freshMuni];
        renderCardsGrid(freshSpots, munName);
        renderTableRows(freshSpots, munName);
        updateKpiCards(freshSpots, [freshMuni]);
        setupDropdownListeners();
        document.querySelectorAll('[data-action="view-spot"]').forEach(btn => {
            btn.addEventListener('click', () => openSpotModal(btn.dataset.spotId));
        });
        document.querySelectorAll('[data-action="edit-spot"]').forEach(btn => {
            btn.addEventListener('click', () => editSpot(btn.dataset.spotId));
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
                marker.bindPopup(`<strong>${spot.name}</strong><br>${spot.category || ''}`);
                marker.addTo(map);
            });
        }
        console.log('✅ Municipal spots refreshed:', freshSpots.length, 'spots');
    } catch (err) {
        console.error('❌ Municipal soft refresh failed:', err);
    }
}

window.softRefreshTouristSpots = softRefreshSpots;
