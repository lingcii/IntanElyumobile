<?php
$pageTitle = 'Discounts & Vouchers';
$backRoute = 'dashboard';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in" style="padding-left: 16px; padding-right: 16px; padding-bottom: 40px;">
    <!-- Hero Section -->
    <div class="merch-hero" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(15, 23, 42, 0.95)); border: 1px solid rgba(56, 189, 248, 0.2); border-radius: 24px; padding: 24px; text-align: center; margin-top: 16px; margin-bottom: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.4);">
        <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #38bdf8; margin: 0 auto 12px;">
            <i class="fa-solid fa-tags"></i>
        </div>
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #fff;">Discounts & Vouchers</h2>
        <p style="margin: 0; font-size: 13px; color: rgba(148, 163, 184, 0.85); line-height: 1.5; max-width: 340px; margin: 0 auto;">
            Redeem your hard-earned <strong style="color: #38bdf8;">PTS & XP</strong> for exclusive dining discounts, surf rentals, resort vouchers, and eco-passes!
        </p>
    </div>

    <!-- Category Filters -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 20px; scrollbar-width: none;" id="discount-filters">
        <button class="discount-cat-btn active" onclick="filterDiscounts('All')">All Deals</button>
        <button class="discount-cat-btn" id="btn-my-claimed" onclick="filterDiscounts('Claimed')">🎟️ My Vouchers (<span id="claimed-count">0</span>)</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Food & Dining')">🍔 Food & Dining</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Activities')">🏄 Activities & Surf</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Accommodations')">🏨 Accommodations</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Souvenirs')">🎁 Gear & Passes</button>
    </div>

    <!-- Discounts Grid -->
    <div id="discounts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
        <!-- Dynamic Voucher Cards -->
    </div>
</div>

<!-- Claim / Voucher Detail Modal -->
<style>
#voucher-modal.active { opacity: 1 !important; }
#voucher-modal.active .voucher-card-anim { transform: scale(1) translateY(0) !important; opacity: 1 !important; }
</style>
<div id="voucher-modal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.85); align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s ease;">
    <div class="voucher-card-anim" style="background:linear-gradient(135deg, #1e293b, #0f172a); border:1px solid rgba(255,255,255,0.12); border-radius:24px; padding:24px; width:100%; max-width:360px; box-shadow:0 25px 50px rgba(0,0,0,0.5); text-align:center; position:relative; box-sizing:border-box; transform:scale(0.86) translateY(20px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        <button onclick="closeVoucherModal()" style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.08); border:none; border-radius:50%; width:32px; height:32px; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div id="modal-icon-wrap" style="width:64px; height:64px; border-radius:18px; background:rgba(56,189,248,0.15); border:1px solid rgba(56,189,248,0.3); display:flex; align-items:center; justify-content:center; font-size:28px; color:#38bdf8; margin:0 auto 14px;">
            <i class="fa-solid fa-ticket"></i>
        </div>

        <span id="modal-category" style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#38bdf8; display:block; margin-bottom:4px;">Food & Dining</span>
        <h3 id="modal-title" style="margin:0 0 8px; font-size:18px; font-weight:800; color:#fff;">15% OFF at El Union Coffee</h3>
        <p id="modal-location" style="margin:0 0 8px; font-size:12px; color:rgba(148,163,184,0.8);"><i class="fa-solid fa-location-dot" style="color:#ef4444; margin-right:4px;"></i>San Juan, La Union</p>

        <!-- Expiry Badge in Modal -->
        <div id="modal-expiry-badge" style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:8px; margin-bottom:14px; font-size:11px; font-weight:700;">
            <i class="fa-regular fa-clock" style="font-size:10px;"></i>
            <span id="modal-expiry-text">Valid until Aug 15, 2026</span>
        </div>

        <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:14px; padding:12px; margin-bottom:16px; text-align:left;">
            <p id="modal-description" style="margin:0; font-size:12px; color:rgba(248,250,252,0.85); line-height:1.5;"></p>
        </div>

        <!-- Voucher Code Box -->
        <div style="background:rgba(56,189,248,0.08); border:1px dashed #38bdf8; border-radius:14px; padding:14px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <span style="display:block; font-size:9px; color:rgba(148,163,184,0.7); text-transform:uppercase; font-weight:700;">Promo / Claim Code</span>
                <span id="modal-code" style="font-size:18px; font-weight:900; color:#38bdf8; letter-spacing:1px;">ELYU-COFFEE-15</span>
            </div>
            <button id="btn-copy-voucher" onclick="copyVoucherCode()" style="background:#38bdf8; border:none; color:#000; padding:8px 14px; border-radius:10px; font-weight:800; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;">
                <i class="fa-solid fa-copy" id="copy-btn-icon"></i> <span id="copy-btn-label">Copy</span>
            </button>
        </div>

        <!-- Post-Copy Instructions & Next Steps Banner -->
        <div id="copy-success-banner" style="display:none; background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.3); border-radius:14px; padding:12px; margin-bottom:14px; text-align:left;">
            <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-size:12px; font-weight:800; margin-bottom:4px;">
                <i class="fa-solid fa-circle-check"></i> Code Copied & Saved to My Vouchers!
            </div>
            <p style="margin:0; font-size:11px; color:rgba(226,232,240,0.85); line-height:1.4;">
                Present this promo code or show this screen to staff at checkout to claim your discount.
            </p>
        </div>

        <div id="modal-action-row" style="display:flex; gap:8px; margin-top:12px;">
            <button onclick="navigateTo('map'); closeVoucherModal();" style="flex:1; padding:10px; border:1px solid rgba(56,189,248,0.3); border-radius:10px; background:rgba(56,189,248,0.12); color:#38bdf8; font-size:11px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                <i class="fa-solid fa-map-location-dot"></i> View on Map
            </button>
            <button onclick="closeVoucherModal()" style="flex:1; padding:10px; border:none; border-radius:10px; background:rgba(255,255,255,0.08); color:#fff; font-size:11px; font-weight:800; cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

<style>
.discount-cat-btn {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.7);
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}
.discount-cat-btn:active {
    transform: scale(0.94);
}
.discount-cat-btn.active {
    background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
    color: #0f172a;
    border-color: transparent;
    box-shadow: 0 4px 14px rgba(56, 189, 248, 0.3);
}

.voucher-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.5), rgba(15,23,42,0.7));
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    transition: transform 0.2s ease, border-color 0.2s ease;
}
.voucher-card:active {
    transform: scale(0.98);
}
</style>

<script>
(function() {
let activeCategory = 'All';
let vouchersData = [
    {
        id: 1,
        title: "15% OFF at El Union Coffee",
        category: "Food & Dining",
        partner: "El Union Coffee",
        location: "San Juan, La Union",
        badge: "15% OFF",
        pointsCost: 150,
        icon: "fa-mug-hot",
        color: "#f59e0b",
        code: "ELYU-COFFEE-15",
        expires: "2026-08-15",
        description: "Get 15% discount on all espresso drinks and cold brews at El Union Coffee in Urbiztondo, San Juan."
    },
    {
        id: 2,
        title: "Free 30-Min Surfboard Rental",
        category: "Activities",
        partner: "San Juan Surf School",
        location: "Urbiztondo Beach, San Juan",
        badge: "FREE RENTAL",
        pointsCost: 250,
        icon: "fa-water",
        color: "#38bdf8",
        code: "ELYU-SURF-30M",
        expires: "2026-09-30",
        description: "Enjoy a free 30-minute surfboard rental or extension with any certified instructor lesson."
    },
    {
        id: 3,
        title: "₱100 Food Voucher at Tagpuan",
        category: "Food & Dining",
        partner: "Tagpuan sa San Juan",
        location: "San Juan, La Union",
        badge: "₱100 VOUCHER",
        pointsCost: 200,
        icon: "fa-utensils",
        color: "#ef4444",
        code: "TAGPUAN-100V",
        expires: "2026-08-31",
        description: "₱100 off your total bill when ordering rice bowls or pares at Tagpuan sa San Juan."
    },
    {
        id: 4,
        title: "20% OFF Room Rate at Kahuna",
        category: "Accommodations",
        partner: "Kahuna Beach Resort",
        location: "San Juan, La Union",
        badge: "20% OFF",
        pointsCost: 500,
        icon: "fa-hotel",
        color: "#a855f7",
        code: "KAHUNA-STAY20",
        expires: "2026-10-31",
        description: "Get 20% discount on weekday Deluxe and Ocean View room bookings at Kahuna Beach Resort & Spa."
    },
    {
        id: 5,
        title: "Tangadan Falls Environmental Pass",
        category: "Souvenirs",
        partner: "San Gabriel Tourism",
        location: "San Gabriel, La Union",
        badge: "ECO-PASS",
        pointsCost: 100,
        icon: "fa-ticket",
        color: "#34d399",
        code: "TANGADAN-FREE",
        expires: "2026-12-31",
        description: "Waiver pass for the local eco-tourism environmental fee at Tangadan Waterfalls."
    },
    {
        id: 6,
        title: "Free Grape Picking Basket Entry",
        category: "Activities",
        partner: "Lomboy Farms",
        location: "Bauang, La Union",
        badge: "FREE ENTRY",
        pointsCost: 180,
        icon: "fa-wine-glass-full",
        color: "#ec4899",
        code: "LOMBOY-GRAPES",
        expires: "2026-08-20",
        description: "Free entrance and vineyard tour basket fee at Lomboy Farms in Bauang."
    },
    {
        id: 7,
        title: "25% OFF La Union Souvenir Pass",
        category: "Souvenirs",
        partner: "Provincial Tourism (LUPTO)",
        location: "Mabanag Hall, San Fernando",
        badge: "25% OFF PASS",
        pointsCost: 300,
        icon: "fa-gift",
        color: "#f97316",
        code: "ELYU-PASS-25",
        expires: "2026-11-15",
        description: "Get 25% discount on all official La Union souvenir crafts and products at the Capitol Tourism Center."
    }
];

function getExpiryInfo(dateStr) {
    const now = new Date();
    const expiry = new Date(dateStr + 'T23:59:59');
    const diff = expiry - now;
    const isExpired = diff <= 0;
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    
    let label, color, bgColor;
    if (isExpired) {
        label = 'Expired';
        color = '#ef4444';
        bgColor = 'rgba(239,68,68,0.12)';
    } else if (days <= 3) {
        label = days === 1 ? 'Expires tomorrow' : `Expires in ${days} days`;
        color = '#ef4444';
        bgColor = 'rgba(239,68,68,0.12)';
    } else if (days <= 14) {
        label = `${days} days left`;
        color = '#f59e0b';
        bgColor = 'rgba(245,158,11,0.12)';
    } else {
        const opts = { month: 'short', day: 'numeric', year: 'numeric' };
        label = `Valid until ${expiry.toLocaleDateString('en-US', opts)}`;
        color = '#34d399';
        bgColor = 'rgba(52,211,153,0.1)';
    }
    return { label, color, bgColor, isExpired, days };
}

let currentVoucherId = null;

function filterDiscounts(cat) {
    activeCategory = cat;
    document.querySelectorAll('.discount-cat-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.includes(cat) || (cat === 'All' && btn.textContent === 'All Deals')) {
            btn.classList.add('active');
        }
    });
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

function renderDiscounts() {
    const grid = document.getElementById('discounts-grid');
    if (!grid) return;
    updateClaimedBadge();

    const claimed = getClaimedVouchers();
    let filtered = [];

    if (activeCategory === 'Claimed') {
        filtered = vouchersData.filter(v => claimed.includes(v.id));
    } else if (activeCategory === 'All') {
        filtered = vouchersData;
    } else {
        filtered = vouchersData.filter(v => v.category === activeCategory);
    }

    if (filtered.length === 0) {
        const msg = activeCategory === 'Claimed' 
            ? 'You have not claimed any vouchers yet. Tap "Copy" on any voucher code to save it here!' 
            : 'No vouchers available in this category.';
        grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: rgba(148,163,184,0.6); padding: 40px; font-size: 13px;">${msg}</div>`;
        return;
    }

    let html = '';
    filtered.forEach(v => {
        const isClaimed = claimed.includes(v.id);
        html += `
        <div class="voucher-card">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 18px; color: ${v.color};">
                        <i class="fa-solid ${v.icon}"></i>
                    </div>
                    <span style="font-size: 10px; font-weight: 800; background: rgba(56,189,248,0.12); color: #38bdf8; border: 1px solid rgba(56,189,248,0.2); padding: 3px 8px; border-radius: 8px; text-transform: uppercase;">${v.badge}</span>
                </div>
                <h4 style="margin: 0 0 4px; font-size: 15px; font-weight: 800; color: #fff; line-height: 1.3;">${v.title}</h4>
                <p style="margin: 0 0 8px; font-size: 12px; color: rgba(148,163,184,0.85); font-weight: 500;">
                    <i class="fa-solid fa-store" style="font-size: 10px; margin-right: 4px; color: rgba(148,163,184,0.5);"></i>${v.partner}
                </p>
                <div style="display: inline-flex; align-items: center; gap: 4px; background: ${getExpiryInfo(v.expires).bgColor}; padding: 3px 8px; border-radius: 6px; margin-bottom: 10px;">
                    <i class="fa-regular fa-clock" style="font-size: 9px; color: ${getExpiryInfo(v.expires).color};"></i>
                    <span style="font-size: 10px; font-weight: 700; color: ${getExpiryInfo(v.expires).color};">${getExpiryInfo(v.expires).label}</span>
                </div>
                <p style="margin: 0 0 16px; font-size: 11px; color: rgba(148,163,184,0.6); line-height: 1.4;">${v.description}</p>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.06);">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-gamepad" style="color: #38bdf8; font-size: 13px;"></i>
                    <span style="font-size: 14px; font-weight: 800; color: #38bdf8;">${v.pointsCost} <span style="font-size: 10px; color: rgba(148,163,184,0.6);">PTS</span></span>
                </div>
                <button onclick="${getExpiryInfo(v.expires).isExpired ? '' : 'openVoucherModal(' + v.id + ')'}" ${getExpiryInfo(v.expires).isExpired ? 'disabled' : ''} style="background: ${getExpiryInfo(v.expires).isExpired ? 'rgba(255,255,255,0.06)' : (isClaimed ? 'rgba(52,211,153,0.15)' : 'linear-gradient(135deg, #38bdf8, #0284c7)')}; border: ${isClaimed ? '1px solid rgba(52,211,153,0.3)' : (getExpiryInfo(v.expires).isExpired ? '1px solid rgba(255,255,255,0.08)' : 'none')}; color: ${getExpiryInfo(v.expires).isExpired ? 'rgba(148,163,184,0.5)' : (isClaimed ? '#34d399' : '#0f172a')}; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: ${getExpiryInfo(v.expires).isExpired ? 'not-allowed' : 'pointer'}; box-shadow: ${isClaimed || getExpiryInfo(v.expires).isExpired ? 'none' : '0 4px 12px rgba(56,189,248,0.25)'}; opacity: ${getExpiryInfo(v.expires).isExpired ? '0.6' : '1'};">
                    ${getExpiryInfo(v.expires).isExpired ? '<i class="fa-solid fa-lock" style="margin-right:4px;"></i> Expired' : (isClaimed ? '<i class="fa-solid fa-check" style="margin-right:4px;"></i> Claimed' : 'Redeem Voucher')}
                </button>
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

function openVoucherModal(id) {
    const item = vouchersData.find(v => v.id === id);
    if (!item) return;
    currentVoucherId = id;

    document.getElementById('modal-title').textContent = item.title;
    document.getElementById('modal-category').textContent = item.category;
    document.getElementById('modal-location').innerHTML = `<i class="fa-solid fa-location-dot" style="color:#ef4444; margin-right:4px;"></i>${item.location}`;
    document.getElementById('modal-description').textContent = item.description;
    document.getElementById('modal-code').textContent = item.code;

    // Update expiry badge in modal
    const expiryInfo = getExpiryInfo(item.expires);
    const expiryBadge = document.getElementById('modal-expiry-badge');
    const expiryText = document.getElementById('modal-expiry-text');
    if (expiryBadge) {
        expiryBadge.style.background = expiryInfo.bgColor;
        expiryBadge.style.color = expiryInfo.color;
    }
    if (expiryText) expiryText.textContent = expiryInfo.label;
    
    const iconWrap = document.getElementById('modal-icon-wrap');
    if (iconWrap) {
        iconWrap.innerHTML = `<i class="fa-solid ${item.icon}"></i>`;
        iconWrap.style.color = item.color;
    }

    const banner = document.getElementById('copy-success-banner');
    if (banner) banner.style.display = 'none';

    const copyBtn = document.getElementById('btn-copy-voucher');
    const copyIcon = document.getElementById('copy-btn-icon');
    const copyLabel = document.getElementById('copy-btn-label');
    
    const claimed = getClaimedVouchers();
    const isAlreadyClaimed = claimed.includes(id);

    if (copyBtn) {
        if (isAlreadyClaimed) {
            copyBtn.disabled = true;
            copyBtn.style.background = 'rgba(52,211,153,0.15)';
            copyBtn.style.color = '#34d399';
            copyBtn.style.cursor = 'not-allowed';
            copyBtn.style.opacity = '0.9';
            if (copyLabel) copyLabel.textContent = 'Saved!';
            if (copyIcon) copyIcon.className = 'fa-solid fa-check';
            if (banner) banner.style.display = 'block';
        } else {
            copyBtn.disabled = false;
            copyBtn.style.background = '#38bdf8';
            copyBtn.style.color = '#000';
            copyBtn.style.cursor = 'pointer';
            copyBtn.style.opacity = '1';
            if (copyLabel) copyLabel.textContent = 'Copy';
            if (copyIcon) copyIcon.className = 'fa-solid fa-copy';
        }
    }
    
    const modal = document.getElementById('voucher-modal');
    if (modal) {
        modal.style.display = 'flex';
        void modal.offsetHeight; // force reflow
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
        }, 320);
    }
}

function copyVoucherCode() {
    const code = document.getElementById('modal-code').textContent;
    const btn = document.getElementById('btn-copy-voucher');
    const icon = document.getElementById('copy-btn-icon');
    const banner = document.getElementById('copy-success-banner');

    navigator.clipboard.writeText(code).then(() => {
        document.getElementById('copy-btn-label').textContent = 'Copied!';
        if (icon) icon.className = 'fa-solid fa-check';
        if (btn) {
            btn.disabled = true;
            btn.style.background = 'rgba(255,255,255,0.15)';
            btn.style.color = 'rgba(255,255,255,0.5)';
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.7';
        }
        if (banner) banner.style.display = 'block';

        // Save voucher ID to localStorage
        if (currentVoucherId) {
            let claimed = getClaimedVouchers();
            if (!claimed.includes(currentVoucherId)) {
                claimed.push(currentVoucherId);
                localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
                updateClaimedBadge();
                renderDiscounts();
            }
        }
    }).catch(err => {
        console.error("Copy error:", err);
    });
}

async function fetchLiveDatabaseVouchers() {
    try {
        const baseUrl = (window.backendUrl || '').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/public/vouchers', {
            headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                const categoryMap = {
                    'percentage': 'Food & Dining',
                    'fixed': 'Souvenirs',
                    'General': 'Food & Dining'
                };

                const dbVouchers = data.data.map(v => ({
                    id: 'db_' + v.id,
                    dbId: v.id,
                    title: v.title,
                    category: categoryMap[v.category] || v.category || 'Food & Dining',
                    partner: v.partner || 'LUPTO Admin',
                    location: v.location || 'La Union',
                    badge: v.badge || 'PROMO OFFER',
                    pointsCost: v.pointsCost || 100,
                    icon: 'fa-tags',
                    color: '#38bdf8',
                    code: v.code || 'ELYU-PROMO',
                    expires: v.expires || '2026-12-31',
                    description: v.description || 'Present voucher code at merchant checkout.'
                }));

                const existingDbIds = new Set(vouchersData.map(v => v.dbId).filter(Boolean));
                const newVouchers = dbVouchers.filter(v => !existingDbIds.has(v.dbId));
                vouchersData = [...newVouchers, ...vouchersData];
                renderDiscounts();
            }
        }
    } catch(e) {
        console.warn('Could not load live database vouchers:', e);
    }
}

// Expose global functions
window.filterDiscounts = filterDiscounts;
window.openVoucherModal = openVoucherModal;
window.closeVoucherModal = closeVoucherModal;
window.copyVoucherCode = copyVoucherCode;
window.renderDiscounts = renderDiscounts;

renderDiscounts();
fetchLiveDatabaseVouchers();
})();
</script>
