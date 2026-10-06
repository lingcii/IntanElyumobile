<?php
$pageTitle = 'Discounts & Vouchers';
$backRoute = 'dashboard';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in">
    <!-- Hero Section -->
    <div class="merch-hero" style="background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%) !important; border: none !important; outline: none !important; border-radius: 24px; padding: 20px 18px; text-align: center; margin-bottom: 18px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28) !important; color: #ffffff !important;">
        <p style="margin: 0 0 12px; font-size: 13.5px; color: rgba(255, 255, 255, 0.95); line-height: 1.45; max-width: 320px; margin-left: auto; margin-right: auto; font-weight: 600;">
            Redeem your <strong style="color: #ffffff; font-weight: 800;">Explorer Points</strong> for exclusive discounts!
        </p>
        <div style="display:inline-flex; align-items:center; gap:8px; background:#ffffff !important; border:none !important; outline:none !important; padding:7px 18px; border-radius:100px; box-shadow: 0 2px 10px rgba(0,0,0,0.12);">
            <i class="fa-solid fa-coins" style="color:#f59e0b; font-size:14px;"></i>
            <span style="font-size:12px; color:#475569; font-weight:700;">Your Balance:</span>
            <strong id="discount-user-pts" style="color:#203f8d; font-size:14px; font-weight:900;">-- Points</strong>
        </div>
    </div>

    <!-- Search & Town Filters Bar -->
    <div style="margin-bottom: 14px;">
        <div style="position: relative; margin-bottom: 10px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
            <input type="text" id="voucher-search-input" placeholder="Search vouchers, merchants, or towns..." oninput="handleVoucherSearch(this.value)" style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 38px; border-radius: 14px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; outline: none; background: #f8fafc; color: #1e293b; transition: all 0.2s;">
            <button id="btn-clear-search" onclick="clearVoucherSearch()" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; border-radius: 50%; width: 22px; height: 22px; color: #64748b; font-size: 11px; cursor: pointer; align-items: center; justify-content: center;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Municipalities Scrollable Filter -->
        <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;" id="muni-filter-bar">
            <button class="muni-pill active" onclick="filterMunicipality('All')">All Towns</button>
            <!-- Dynamic Municipality Pills -->
        </div>
    </div>

    <!-- Category Filters -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 18px; scrollbar-width: none;" id="discount-filters">
        <button class="discount-cat-btn active" onclick="filterDiscounts('All')">All Deals</button>
        <button class="discount-cat-btn" id="btn-my-claimed" onclick="filterDiscounts('Claimed')">My Vouchers (<span id="claimed-count">0</span>)</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Food & Dining')">Food & Dining</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Activities')">Activities & Surf</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Accommodations')">Accommodations</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Souvenirs')">Gear & Passes</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Upcoming')">Upcoming</button>
    </div>

    <!-- Discounts Grid -->
    <div id="discounts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        <!-- Dynamic Voucher Cards -->
    </div>
</div>



<div id="voucher-modal" onclick="if (event.target === this) closeVoucherModal();" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(15,23,42,0.68); align-items:center; justify-content:center; padding:18px; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s ease;">
    <div class="voucher-card-anim" style="background: #ffffff !important; border: none !important; outline:none !important; border-radius:24px; padding:0 !important; width:100%; max-width:375px; max-height:86vh; display:flex; flex-direction:column; overflow:hidden; box-shadow: 0 16px 40px rgba(10, 25, 60, 0.4) !important; text-align:center; position:relative; box-sizing:border-box; transform:scale(0.88) translateY(20px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        
        <!-- Modal Top Header Banner (Royal Blue) -->
        <div style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; padding: 14px 18px; flex-shrink: 0; display: flex; justify-content: space-between; align-items: center; border: none !important; outline: none !important;">
            <div style="text-align: left;">
                <h4 style="margin: 0; font-size: 16px; font-weight: 800; color: #ffffff; display: flex; align-items: center; gap: 8px; letter-spacing: -0.2px;">
                    <i class="fa-solid fa-ticket" style="color: #00f2fe; font-size: 15px;"></i> Voucher Details
                </h4>
                <p style="margin: 2px 0 0 0; font-size: 11px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">
                    Exclusive Partner Reward
                </p>
            </div>
            <button onclick="closeVoucherModal()" style="background: #ffffff !important; border: none !important; outline: none !important; color: #1e3a8a !important; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; flex-shrink: 0; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.92)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-xmark" style="color: #1e3a8a !important; font-size: 14px;"></i>
            </button>
        </div>

        <!-- Middle Body Area (Pure White Background) -->
        <div class="hide-scrollbar" style="background: #ffffff !important; flex: 1; min-height: 0; overflow-y: auto; padding: 18px 18px 14px 18px; text-align: center; color: #0f172a !important;">
            <!-- Big Logo Icon -->
            <div id="modal-icon-wrap" style="width: 68px; height: 68px; border-radius: 20px; background: #ffffff; border: 1.5px solid #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 28px; color: #1e3a8a; margin: 0 auto 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.06); overflow: hidden;">
                <i class="fa-solid fa-ticket" style="color: #1e3a8a !important;"></i>
            </div>

            <span id="modal-category" style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; color: #0284c7; display: block; margin-bottom: 4px;">Food & Dining</span>
            <h3 id="modal-title" style="margin: 0 0 6px; font-size: 18px; font-weight: 900; color: #0f172a; line-height: 1.25;">15% OFF at El Union Coffee</h3>
            
            <p id="modal-partner" style="margin: 0 0 4px; font-size: 13px; color: #1e3a8a; font-weight: 700;">
                <i class="fa-solid fa-store" style="color: #0284c7; margin-right: 5px;"></i><span id="modal-partner-name">El Union Coffee</span>
            </p>

            <p id="modal-location" style="margin: 0 0 10px; font-size: 12px; color: #64748b; font-weight: 600;">
                <i class="fa-solid fa-location-dot" style="color: #ef4444; margin-right: 5px;"></i><span id="modal-location-name">San Juan, La Union</span>
            </p>

            <!-- ID Needed Warning Banner -->
            <div id="modal-id-notice" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 10px 14px; margin-bottom: 12px; text-align: left;">
                <div style="display: flex; align-items: center; gap: 8px; color: #dc2626; font-size: 12px; font-weight: 800;">
                    <i class="fa-solid fa-id-card"></i> Valid ID Required Upon Redemption
                </div>
                <p style="margin: 3px 0 0 0; font-size: 11px; color: #991b1b; line-height: 1.35;">
                    Please present a valid government, employee, or student ID when claiming at the establishment.
                </p>
            </div>

            <!-- Expiry & Stock Badges Row in Modal -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px;">
                <div id="modal-expiry-badge" style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe;">
                    <i id="modal-expiry-icon" class="fa-regular fa-clock" style="font-size: 10px; color: #2563eb;"></i>
                    <span id="modal-expiry-text">Valid until Aug 15, 2026</span>
                </div>
                <div id="modal-stock-badge" style="display: none; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                    <i class="fa-solid fa-fire" style="font-size: 10px; color: #d97706;"></i>
                    <span id="modal-stock-text">Limited Quantity</span>
                </div>
            </div>

            <!-- Description Box -->
            <div style="background: #f8fafc !important; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; margin-bottom: 12px; text-align: left;">
                <div style="font-size: 10.5px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                    Description & Privileges
                </div>
                <p id="modal-description" style="margin: 0; font-size: 12.5px; color: #334155; line-height: 1.45;"></p>
            </div>

            <!-- Terms and Conditions Box -->
            <div id="modal-terms-box" style="display: none; background: #f8fafc !important; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 14px; margin-bottom: 12px; text-align: left;">
                <div style="font-size: 10.5px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-file-contract" style="color: #2563eb;"></i> Terms & Conditions
                </div>
                <p id="modal-terms-text" style="margin: 0; font-size: 11.5px; color: #475569; line-height: 1.4; white-space: pre-line;"></p>
            </div>

            <!-- QR Code & Voucher Code Box (Shown when Claimed) -->
            <div id="modal-claimed-box" style="display: none; background: #eff6ff !important; border: 1.5px dashed #bfdbfe; border-radius: 18px; padding: 16px; margin-bottom: 14px; text-align: center;">
                <div style="background: #ffffff; border-radius: 14px; padding: 10px; width: 150px; height: 150px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">
                    <img id="modal-qr-img" src="" alt="Voucher QR Code" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.style.display='none'">
                </div>
                
                <div style="margin-bottom: 8px;">
                    <span style="display: block; font-size: 10.5px; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">Your Unique Claim Code</span>
                    <span id="modal-code" style="font-size: 20px; font-weight: 900; color: #1e3a8a; letter-spacing: 1.5px; word-break: break-all;">ELYU-PROMO</span>
                </div>

                <button id="btn-copy-voucher" onclick="copyVoucherCode()" style="background: #ffffff !important; border: 1px solid #bfdbfe !important; color: #1e3a8a !important; padding: 9px 18px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                    <i class="fa-solid fa-copy" id="copy-btn-icon" style="color: #1e3a8a !important;"></i> <span id="copy-btn-label">Copy Voucher Code</span>
                </button>
                <p style="margin: 10px 0 0 0; font-size: 11px; color: #64748b; line-height: 1.35;">
                    Present this QR code or alphanumeric code directly to staff at checkout.
                </p>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) -->
        <div id="modal-footer-banner" style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 14px 18px; border-top: none !important; display: flex; flex-direction: column; gap: 8px; border: none !important; outline: none !important;">
            <button id="modal-redeem-btn" onclick="handleModalRedeem()" style="width: 100%; padding: 12px; border: none !important; outline: none !important; border-radius: 12px; background: #ffffff !important; color: #1e3a8a !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Redeem for 100 Points</span>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
const VOUCHERS_CACHE_KEY = 'intan_elyu_cached_vouchers';
const VOUCHERS_CACHE_TTL = 900000; // 15 mins

function getVoucherImageUrl(v) {
    if (!v) return 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';
    const r2Base = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev';
    if (v.image && typeof v.image === 'string' && v.image.trim() !== '') {
        const clean = v.image.trim();
        if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('data:')) {
            return clean;
        }
        return `${r2Base}/${clean.replace(/^\/+/, '')}`;
    }

    const text = ((v.partner || '') + ' ' + (v.location || '') + ' ' + (v.title || '')).toLowerCase();
    const muniMap = {
        'san fernando': 'SAN-FERNANDO.png',
        'san gabriel': 'SAN-GABRIEL.png',
        'san juan': 'SAN-JUAN.png',
        'santo tomas': 'SANTO-TOMAS.png',
        'agoo': 'AGOO.png',
        'aringay': 'ARINGAY.png',
        'bacnotan': 'BACNOTAN.png',
        'bagulin': 'BAGULIN.png',
        'balaoan': 'BALAOAN.png',
        'bangar': 'BANGAR.png',
        'bauang': 'BAUANG.png',
        'burgos': 'BURGOS.png',
        'caba': 'CABA.png',
        'luna': 'LUNA.png',
        'naguilian': 'NAGUILIAN.png',
        'pugo': 'PUGO.png',
        'rosario': 'ROSARIO.png',
        'santol': 'SANTOL.png',
        'sudipen': 'SUDIPEN.png',
        'tubao': 'TUBAO.png'
    };

    for (const [muni, logo] of Object.entries(muniMap)) {
        if (text.includes(muni)) {
            return `${r2Base}/logo/${logo}`;
        }
    }

    return `${r2Base}/logo/LUPTO.png`;
}

let activeCategory = 'All';
let activeMunicipality = 'All';
let searchQuery = '';
let vouchersData = [];
let currentVoucherId = null;
let userPointsBalance = 0;

function getExpiryInfo(dateStr, isExpiredExplicit, isUpcomingExplicit, validFromStr, validFromFormatted, isNoExpiration, expirationType) {
    if (isExpiredExplicit === true) {
        return {
            label: 'Expired',
            color: '#ef4444',
            bgColor: 'rgba(239, 68, 68, 0.25)',
            isExpired: true,
            isUpcoming: false,
            days: 0,
            icon: 'fa-solid fa-lock'
        };
    }

    // Check No Expiration option from Admin
    if (isNoExpiration === true || (expirationType && expirationType.includes('claim')) || (expirationType && expirationType.includes('usable'))) {
        const text = (expirationType && expirationType.includes('claim')) ? 'Until Claimed' : 'No Expiry';
        return {
            label: text,
            color: '#10b981',
            bgColor: 'rgba(16, 185, 129, 0.25)',
            isExpired: false,
            isUpcoming: false,
            isNoExpiration: true,
            days: 9999,
            icon: 'fa-solid fa-infinity'
        };
    }

    const now = new Date();

    // Check upcoming status
    let isUpcoming = isUpcomingExplicit === true;
    if (validFromStr) {
        let validFrom;
        if (validFromStr.includes('T') || validFromStr.includes(' ')) {
            validFrom = new Date(validFromStr.replace(' ', 'T'));
        } else {
            validFrom = new Date(validFromStr + 'T00:00:00');
        }
        if (validFrom > now) {
            isUpcoming = true;
        }
    }

    if (isUpcoming) {
        return {
            label: validFromFormatted ? `Starts ${validFromFormatted}` : 'Starts Soon',
            color: '#38bdf8',
            bgColor: 'rgba(56, 189, 248, 0.25)',
            isExpired: false,
            isUpcoming: true,
            days: 0,
            icon: 'fa-regular fa-clock'
        };
    }

    let expiry;
    if (dateStr && (dateStr.includes('T') || dateStr.includes(' '))) {
        expiry = new Date(dateStr.replace(' ', 'T'));
    } else if (dateStr) {
        expiry = new Date(dateStr + 'T23:59:59');
    } else {
        return {
            label: 'No Expiry',
            color: '#10b981',
            bgColor: 'rgba(16, 185, 129, 0.25)',
            isExpired: false,
            isUpcoming: false,
            days: 9999,
            icon: 'fa-solid fa-infinity'
        };
    }

    const diff = expiry - now;
    const isExpired = diff <= 0;
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    
    let label, color, bgColor, icon;
    if (isExpired) {
        label = 'Expired';
        color = '#ef4444';
        bgColor = 'rgba(239, 68, 68, 0.25)';
        icon = 'fa-solid fa-lock';
    } else if (days <= 3) {
        label = days === 1 ? 'Expires tomorrow' : `Expires in ${days} days`;
        color = '#f87171';
        bgColor = 'rgba(239, 68, 68, 0.28)';
        icon = 'fa-solid fa-hourglass-end';
    } else if (days <= 14) {
        label = `${days} days left`;
        color = '#fbbf24';
        bgColor = 'rgba(245, 158, 11, 0.25)';
        icon = 'fa-regular fa-clock';
    } else {
        const opts = { month: 'short', day: 'numeric', year: 'numeric' };
        label = `Valid until ${expiry.toLocaleDateString('en-US', opts)}`;
        color = '#38bdf8';
        bgColor = 'rgba(56, 189, 248, 0.2)';
        icon = 'fa-regular fa-calendar';
    }
    return { label, color, bgColor, isExpired, isUpcoming: false, days, icon };
}

function filterDiscounts(cat) {
    activeCategory = cat;
    document.querySelectorAll('.discount-cat-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.includes(cat) || (cat === 'All' && btn.textContent.includes('All Deals'))) {
            btn.classList.add('active');
        }
    });
    renderDiscounts();
}

function filterMunicipality(muni) {
    activeMunicipality = muni;
    document.querySelectorAll('.muni-pill').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim() === (muni === 'All' ? 'All Towns' : muni)) {
            btn.classList.add('active');
        }
    });
    renderDiscounts();
}

function handleVoucherSearch(val) {
    searchQuery = (val || '').toLowerCase().trim();
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.style.display = searchQuery ? 'flex' : 'none';
    renderDiscounts();
}

function clearVoucherSearch() {
    const input = document.getElementById('voucher-search-input');
    if (input) input.value = '';
    searchQuery = '';
    const clearBtn = document.getElementById('btn-clear-search');
    if (clearBtn) clearBtn.style.display = 'none';
    renderDiscounts();
}

function getClaimedVouchers() {
    try {
        return JSON.parse(localStorage.getItem('intan_elyu_claimed_vouchers') || '[]');
    } catch(e) {
        return [];
    }
}

function updateClaimedBadge() {
    const claimed = getClaimedVouchers();
    const countEl = document.getElementById('claimed-count');
    if (countEl) countEl.textContent = claimed.length;
}

async function fetchUserPointsAndRedemptions() {
    const token = localStorage.getItem('intan_elyu_token');
    if (!token) {
        const ptsBadge = document.getElementById('discount-user-pts');
        if (ptsBadge) ptsBadge.textContent = '0 Points';
        return;
    }

    try {
        const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/tourist/points/balance', {
            headers: {
                'Accept': 'application/json',
                'ngrok-skip-browser-warning': 'true',
                'Authorization': 'Bearer ' + token
            }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success') {
                userPointsBalance = (data.points !== undefined) ? data.points : (data.xp ?? 0);
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

                if (Array.isArray(data.vouchers)) {
                    let claimed = getClaimedVouchers();
                    data.vouchers.forEach(v => {
                        const match = vouchersData.find(item => 
                            item.code === v.voucher_code || 
                            (v.voucher_code && item.code && v.voucher_code.startsWith(item.code)) || 
                            (item.dbId && item.title === v.type)
                        );
                        if (match && !claimed.includes(match.id)) {
                            claimed.push(match.id);
                        }
                    });
                    localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
                    updateClaimedBadge();
                    renderDiscounts();
                }
            }
        }
    } catch(e) {
        console.warn('Could not fetch user points balance:', e);
    }
}

function buildMunicipalityFilterBar() {
    const bar = document.getElementById('muni-filter-bar');
    if (!bar) return;

    const townSet = new Set();
    vouchersData.forEach(v => {
        if (Array.isArray(v.municipalities) && v.municipalities.length > 0) {
            v.municipalities.forEach(m => townSet.add(m));
        } else if (v.location && v.location !== 'La Union') {
            const firstPart = v.location.split(',')[0].trim();
            if (firstPart) townSet.add(firstPart);
        }
    });

    const towns = Array.from(townSet).sort();
    if (towns.length === 0) return;

    let html = `<button class="muni-pill ${activeMunicipality === 'All' ? 'active' : ''}" onclick="filterMunicipality('All')">All Towns</button>`;
    towns.forEach(t => {
        const isActive = activeMunicipality.toLowerCase() === t.toLowerCase();
        html += `<button class="muni-pill ${isActive ? 'active' : ''}" onclick="filterMunicipality('${t.replace(/'/g, "\\'")}')">${t}</button>`;
    });
    bar.innerHTML = html;
}

function renderDiscounts() {
    const grid = document.getElementById('discounts-grid');
    if (!grid) return;
    updateClaimedBadge();

    const claimed = getClaimedVouchers();
    let filtered = vouchersData;

    // 1. Category Filter
    if (activeCategory === 'Claimed') {
        filtered = filtered.filter(v => claimed.includes(v.id));
    } else if (activeCategory === 'Upcoming') {
        filtered = filtered.filter(v => v.is_upcoming && !v.is_expired);
    } else if (activeCategory !== 'All') {
        filtered = filtered.filter(v => v.category === activeCategory);
    }

    // 2. Municipality Filter
    if (activeMunicipality !== 'All') {
        const targetMuni = activeMunicipality.toLowerCase();
        filtered = filtered.filter(v => {
            if (Array.isArray(v.municipalities) && v.municipalities.length > 0) {
                return v.municipalities.some(m => m.toLowerCase().includes(targetMuni));
            }
            return (v.location || '').toLowerCase().includes(targetMuni);
        });
    }

    // 3. Search Filter
    if (searchQuery) {
        filtered = filtered.filter(v => {
            const haystack = `${v.title} ${v.partner} ${v.location} ${v.description} ${v.code} ${v.category}`.toLowerCase();
            return haystack.includes(searchQuery);
        });
    }

    if (filtered.length === 0) {
        let msg = 'No vouchers match your current filters.';
        if (activeCategory === 'Claimed') {
            msg = 'You have not claimed any vouchers yet. Redeem your Points to store vouchers here!';
        } else if (activeCategory === 'Upcoming') {
            msg = 'No upcoming promotions scheduled right now. Check back soon for new discounts!';
        } else if (searchQuery) {
            msg = `No vouchers found matching "${searchQuery}". Try a different keyword or town.`;
        }
        grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: #ffffff; padding: 36px 20px; font-size: 13px; font-weight:700; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); border-radius: 20px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28);">${msg}</div>`;
        return;
    }

    let html = '';
    filtered.forEach(v => {
        const isClaimed = claimed.includes(v.id);
        const imgUrl = getVoucherImageUrl(v);
        const expiryInfo = getExpiryInfo(v.expires, v.is_expired, v.is_upcoming, v.valid_from, v.valid_from_formatted, v.is_no_expiration, v.expiration_type);
        const isCardExpired = v.is_expired || expiryInfo.isExpired;
        const isCardUpcoming = !isCardExpired && (v.is_upcoming || expiryInfo.isUpcoming);
        const isOutOfStock = v.is_out_of_stock || (v.remaining_quantity !== null && v.remaining_quantity <= 0);

        let actionBtnHtml = '';
        if (isClaimed) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #10b981 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display:flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;">
                    <i class="fa-solid fa-check"></i> Claimed
                </button>
            `;
        } else if (isCardExpired) {
            actionBtnHtml = `
                <button disabled style="background: #64748b !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: not-allowed;">
                    <i class="fa-solid fa-lock" style="margin-right:4px;"></i> Expired
                </button>
            `;
        } else if (isOutOfStock) {
            actionBtnHtml = `
                <button disabled style="background: #dc2626 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: not-allowed;">
                    Fully Claimed
                </button>
            `;
        } else if (isCardUpcoming) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #0284c7 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer;">
                    <i class="fa-regular fa-clock" style="margin-right:4px;"></i> Starts Soon
                </button>
            `;
        } else {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #ffffff !important; border: none !important; color: #203f8d !important; padding: 8px 14px; border-radius: 10px; font-weight: 900; font-size: 12px; cursor: pointer; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;">
                    Redeem Points
                </button>
            `;
        }

        // Badges: ID Needed & Low Stock
        let idBadgeHtml = '';
        if (v.id_needed) {
            idBadgeHtml = `<span style="font-size: 9.5px; font-weight: 800; background: #ef4444 !important; color: #ffffff !important; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-id-card"></i> ID Required</span>`;
        }

        let stockBadgeHtml = '';
        if (v.remaining_quantity !== null && v.remaining_quantity > 0 && v.remaining_quantity <= 5) {
            stockBadgeHtml = `<span style="font-size: 9.5px; font-weight: 800; background: #f59e0b !important; color: #ffffff !important; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-fire"></i> Only ${v.remaining_quantity} left</span>`;
        }

        html += `
        <div class="voucher-card">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; gap: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.14);">
                        <img src="${imgUrl}" alt="${v.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';">
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 900; background: #ffffff !important; color: #203f8d !important; padding: 4px 10px; border-radius: 8px; text-transform: uppercase; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">${v.badge}</span>
                        ${idBadgeHtml}
                        ${stockBadgeHtml}
                    </div>
                </div>

                <h4 style="margin: 0 0 5px; font-size: 15.5px; font-weight: 900; color: #ffffff; line-height: 1.3;">${v.title}</h4>
                
                <p style="margin: 0 0 4px; font-size: 12px; color: rgba(255, 255, 255, 0.95); font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <i class="fa-solid fa-store" style="font-size: 10px; margin-right: 5px; color: #38bdf8;"></i>${v.partner}
                </p>

                <p style="margin: 0 0 8px; font-size: 11.5px; color: rgba(255, 255, 255, 0.85); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <i class="fa-solid fa-location-dot" style="font-size: 10px; margin-right: 5px; color: #f87171;"></i>${v.location}
                </p>

                <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(255, 255, 255, 0.16); padding: 3px 8px; border-radius: 6px; margin-bottom: 10px;">
                    <i class="${expiryInfo.icon}" style="font-size: 9px; color: #ffffff;"></i>
                    <span style="font-size: 10px; font-weight: 700; color: #ffffff;">${expiryInfo.label}</span>
                </div>

                <p style="margin: 0 0 14px; font-size: 12px; color: rgba(255, 255, 255, 0.88); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${v.description}</p>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.18);">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-coins" style="color: #fbbf24; font-size: 14px;"></i>
                    <span style="font-size: 14px; font-weight: 900; color: #ffffff;">${v.pointsCost || v.required_points || 100} <span style="font-size: 10px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">Pts</span></span>
                </div>
                ${actionBtnHtml}
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

function openVoucherModal(id) {
    const item = vouchersData.find(v => v.id === id);
    if (!item) return;
    currentVoucherId = id;

    const claimed = getClaimedVouchers();
    const isAlreadyClaimed = claimed.includes(id);

    // Basic text
    document.getElementById('modal-title').textContent = item.title;
    document.getElementById('modal-category').textContent = item.category;
    document.getElementById('modal-partner-name').textContent = item.partner;
    document.getElementById('modal-location-name').textContent = item.location;
    document.getElementById('modal-description').textContent = item.description;

    // ID Needed Notice
    const idNotice = document.getElementById('modal-id-notice');
    if (idNotice) {
        idNotice.style.display = item.id_needed ? 'block' : 'none';
    }

    // Terms and Conditions
    const termsBox = document.getElementById('modal-terms-box');
    const termsText = document.getElementById('modal-terms-text');
    if (termsBox && termsText) {
        if (item.terms_and_conditions && item.terms_and_conditions.trim() !== '') {
            termsText.textContent = item.terms_and_conditions;
            termsBox.style.display = 'block';
        } else {
            termsBox.style.display = 'none';
        }
    }

    // Expiry Info
    const expiryInfo = getExpiryInfo(item.expires, item.is_expired, item.is_upcoming, item.valid_from, item.valid_from_formatted, item.is_no_expiration, item.expiration_type);
    const isExpired = item.is_expired || expiryInfo.isExpired;
    const isUpcoming = !isExpired && (item.is_upcoming || expiryInfo.isUpcoming);
    const isOutOfStock = item.is_out_of_stock || (item.remaining_quantity !== null && item.remaining_quantity <= 0);

    const expiryBadge = document.getElementById('modal-expiry-badge');
    const expiryText = document.getElementById('modal-expiry-text');
    const expiryIcon = document.getElementById('modal-expiry-icon');
    if (expiryBadge && expiryText) {
        expiryBadge.style.background = expiryInfo.bgColor;
        expiryText.textContent = expiryInfo.label;
        if (expiryIcon) expiryIcon.className = expiryInfo.icon;
    }

    // Stock Badge
    const stockBadge = document.getElementById('modal-stock-badge');
    const stockText = document.getElementById('modal-stock-text');
    if (stockBadge && stockText) {
        if (item.remaining_quantity !== null && item.remaining_quantity > 0 && item.remaining_quantity <= 10) {
            stockText.textContent = `Only ${item.remaining_quantity} left`;
            stockBadge.style.display = 'inline-flex';
        } else {
            stockBadge.style.display = 'none';
        }
    }

    // Icon / Logo
    const iconWrap = document.getElementById('modal-icon-wrap');
    if (iconWrap) {
        const modalImg = getVoucherImageUrl(item);
        iconWrap.innerHTML = `<img src="${modalImg}" alt="${item.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 6px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';">`;
    }

    // Claimed Box & QR Code
    const claimedBox = document.getElementById('modal-claimed-box');
    const redeemBtn = document.getElementById('modal-redeem-btn');
    const redeemLabel = document.getElementById('modal-redeem-btn-label');
    const footerBanner = document.getElementById('modal-footer-banner');

    if (isAlreadyClaimed) {
        if (claimedBox) {
            claimedBox.style.display = 'block';
            const codeEl = document.getElementById('modal-code');
            if (codeEl) codeEl.textContent = item.code;
            const qrImg = document.getElementById('modal-qr-img');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(item.code)}`;
                qrImg.style.display = 'block';
            }
        }
        if (redeemBtn) redeemBtn.style.display = 'none';
        if (footerBanner) footerBanner.style.display = 'none';
    } else {
        if (claimedBox) claimedBox.style.display = 'none';
        if (footerBanner) footerBanner.style.display = 'flex';
        if (redeemBtn) {
            redeemBtn.style.display = 'flex';
            if (isExpired) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.6';
                redeemBtn.style.cursor = 'not-allowed';
                if (redeemLabel) redeemLabel.innerHTML = '<i class="fa-solid fa-lock"></i> Voucher Expired';
            } else if (isOutOfStock) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.6';
                redeemBtn.style.cursor = 'not-allowed';
                if (redeemLabel) redeemLabel.innerHTML = '<i class="fa-solid fa-ban"></i> Fully Claimed';
            } else if (isUpcoming) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.7';
                redeemBtn.style.cursor = 'not-allowed';
                if (redeemLabel) redeemLabel.innerHTML = `<i class="fa-regular fa-clock"></i> Starts on ${item.valid_from_formatted || 'Soon'}`;
            } else {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                if (redeemLabel) redeemLabel.textContent = `Redeem for ${item.pointsCost || item.required_points || 100} Points`;
            }
        }
    }

    const modal = document.getElementById('voucher-modal');
    if (modal) {
        modal.style.display = 'flex';
        void modal.offsetHeight;
        modal.classList.add('active');
    }
}

function closeVoucherModal() {
    const modal = document.getElementById('voucher-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            if (!modal.classList.contains('active')) {
                modal.style.display = 'none';
            }
        }, 300);
    }
}

function copyVoucherCode() {
    const code = document.getElementById('modal-code').textContent;
    const btn = document.getElementById('btn-copy-voucher');
    const label = document.getElementById('copy-btn-label');
    const icon = document.getElementById('copy-btn-icon');

    navigator.clipboard.writeText(code).then(() => {
        if (label) label.textContent = 'Code Copied!';
        if (icon) icon.className = 'fa-solid fa-check';
        if (btn) btn.style.background = '#dcfce7';
        if (typeof showToast === 'function') showToast("Voucher code copied to clipboard!");

        setTimeout(() => {
            if (label) label.textContent = 'Copy Voucher Code';
            if (icon) icon.className = 'fa-solid fa-copy';
            if (btn) btn.style.background = '#ffffff';
        }, 2500);
    }).catch(err => {
        console.error("Copy error:", err);
    });
}

async function handleModalRedeem() {
    const item = vouchersData.find(v => v.id === currentVoucherId);
    if (!item) return;

    const expiryInfo = getExpiryInfo(item.expires, item.is_expired, item.is_upcoming, item.valid_from, item.valid_from_formatted, item.is_no_expiration, item.expiration_type);
    if (item.is_expired || expiryInfo.isExpired) {
        if (typeof showToast === 'function') showToast("This voucher has expired.");
        return;
    }

    if (item.is_upcoming || expiryInfo.isUpcoming) {
        if (typeof showToast === 'function') showToast(`This voucher is upcoming and will unlock on ${item.valid_from_formatted || 'its start date'}.`);
        return;
    }

    if (item.is_out_of_stock || (item.remaining_quantity !== null && item.remaining_quantity <= 0)) {
        if (typeof showToast === 'function') showToast("This voucher is fully claimed.");
        return;
    }

    const token = localStorage.getItem('intan_elyu_token');
    if (!token) {
        if (typeof showToast === 'function') showToast("Please log in to your tourist account to redeem vouchers.");
        navigateTo('auth');
        return;
    }

    const cost = item.pointsCost || item.required_points || 100;
    if (userPointsBalance < cost) {
        if (typeof showToast === 'function') {
            showToast(`Insufficient Points. You need ${cost} Points (Balance: ${userPointsBalance} Points).`);
        }
        return;
    }

    const btn = document.getElementById('modal-redeem-btn');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Redeeming...';
        btn.disabled = true;
    }

    try {
        const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/tourist/points/redeem-voucher', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'ngrok-skip-browser-warning': 'true',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ voucher_id: item.dbId || String(item.id).replace('db_', '') })
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
            if (typeof showToast === 'function') showToast("🎉 Voucher claimed successfully!");
            if (window.confetti) {
                window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            }

            // Save to claimed in localStorage
            let claimed = getClaimedVouchers();
            if (!claimed.includes(item.id)) {
                claimed.push(item.id);
                localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
            }

            // Update user's points
            let storedUser = null;
            try { storedUser = JSON.parse(localStorage.getItem('auth_user') || '{}'); } catch(e) {}
            if (storedUser) {
                const newPoints = data.new_balance !== undefined ? data.new_balance : Math.max(0, (storedUser.points || 0) - cost);
                storedUser.points = newPoints;
                localStorage.setItem('auth_user', JSON.stringify(storedUser));
                userPointsBalance = newPoints;
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;
            }

            window.dashboardNeedsRefresh = true;

            // Invalidate cached vouchers so stock is re-synced
            try { localStorage.removeItem(VOUCHERS_CACHE_KEY); } catch(e) {}

            // Switch modal to Claimed Box immediately
            const claimCode = (data.data && data.data.voucher_code) ? data.data.voucher_code : (data.claim_code || item.code);
            item.code = claimCode;
            
            const claimedBox = document.getElementById('modal-claimed-box');
            if (claimedBox) {
                claimedBox.style.display = 'block';
                const codeEl = document.getElementById('modal-code');
                if (codeEl) codeEl.textContent = claimCode;
                const qrImg = document.getElementById('modal-qr-img');
                if (qrImg) {
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(claimCode)}`;
                    qrImg.style.display = 'block';
                }
            }
            if (btn) btn.style.display = 'none';
            const footerBanner = document.getElementById('modal-footer-banner');
            if (footerBanner) footerBanner.style.display = 'none';

            renderDiscounts();
            fetchUserPointsAndRedemptions();
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to redeem voucher.");
            if (btn) {
                btn.innerHTML = `<i class="fa-solid fa-gift"></i> Redeem for ${cost} Points`;
                btn.disabled = false;
            }
        }
    } catch(e) {
        console.error("Redemption error:", e);
        if (typeof showToast === 'function') showToast("Network error. Please try again.");
        if (btn) {
            btn.innerHTML = `<i class="fa-solid fa-gift"></i> Redeem for ${cost} Points`;
            btn.disabled = false;
        }
    }
}

async function fetchLiveDatabaseVouchers() {
    // 1. Try SWR Cache first for instant render
    let cached = null;
    try {
        const raw = localStorage.getItem(VOUCHERS_CACHE_KEY);
        if (raw) {
            const parsed = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(raw, null) : JSON.parse(raw);
            if (parsed && Array.isArray(parsed.data)) {
                cached = parsed;
                processVouchersData(parsed.data);
            }
        }
    } catch(e) {}

    // Render cached vouchers instantly, but always fetch fresh vouchers in background so other devices see updates immediately

    try {
        const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/vouchers', {
            headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                try {
                    localStorage.setItem(VOUCHERS_CACHE_KEY, JSON.stringify({ data: data.data, timestamp: Date.now() }));
                } catch(e) {}
                processVouchersData(data.data);
            }
        }
    } catch(e) {
        console.warn('Could not load live database vouchers:', e);
    }
}

function processVouchersData(rawList) {
    vouchersData = rawList.map(v => {
        const icon = v.category === 'Activities' ? 'fa-person-hiking' : (v.category === 'Accommodations' ? 'fa-hotel' : (v.category === 'Souvenirs' ? 'fa-gift' : 'fa-utensils'));
        return {
            id: 'db_' + v.id,
            dbId: v.id,
            title: v.title || v.voucher_name,
            category: v.category || 'Food & Dining',
            partner: v.partner || 'LUPTO Tourism',
            partner_establishments: v.partner_establishments || [v.partner || 'LUPTO Tourism'],
            location: v.location || 'La Union',
            municipalities: v.municipalities || [],
            badge: v.badge || 'PROMO',
            discount_type: v.discount_type || 'percentage',
            discount_value: v.discount_value || 0,
            pointsCost: v.pointsCost || v.required_points || 100,
            required_points: v.required_points || v.pointsCost || 100,
            points: v.points || v.pointsCost || v.required_points || 100,
            icon: icon,
            code: v.code || v.voucher_code || 'ELYU-PROMO',
            image: v.image || null,
            valid_from: v.valid_from || null,
            valid_from_formatted: v.valid_from_formatted || null,
            expiration_type: v.expiration_type || 'date',
            is_no_expiration: (v.is_no_expiration !== undefined) ? v.is_no_expiration : false,
            expires: v.expires || null,
            expires_formatted: v.expires_formatted || 'No Expiry',
            is_expired: (v.is_expired !== undefined) ? v.is_expired : false,
            is_upcoming: (v.is_upcoming !== undefined) ? v.is_upcoming : false,
            is_out_of_stock: (v.is_out_of_stock !== undefined) ? v.is_out_of_stock : false,
            id_needed: (v.id_needed !== undefined) ? v.id_needed : false,
            terms_and_conditions: v.terms_and_conditions || null,
            status: v.status || 'active',
            description: v.description || 'Present voucher code at merchant checkout.',
            available_quantity: v.available_quantity,
            remaining_quantity: v.remaining_quantity
        };
    });

    buildMunicipalityFilterBar();
    renderDiscounts();
}

// Expose global functions
window.filterDiscounts = filterDiscounts;
window.filterMunicipality = filterMunicipality;
window.handleVoucherSearch = handleVoucherSearch;
window.clearVoucherSearch = clearVoucherSearch;
window.openVoucherModal = openVoucherModal;
window.closeVoucherModal = closeVoucherModal;
window.copyVoucherCode = copyVoucherCode;
window.handleModalRedeem = handleModalRedeem;
window.renderDiscounts = renderDiscounts;

fetchLiveDatabaseVouchers();
fetchUserPointsAndRedemptions();
})();
</script>
