<?php
$pageTitle = 'Discounts & Vouchers';
$backRoute = 'dashboard';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in" style="padding-left: 16px; padding-right: 16px; padding-bottom: 40px; background: radial-gradient(ellipse at 85% 5%, rgba(0, 242, 254, 0.35) 0%, transparent 55%), radial-gradient(ellipse at 15% 45%, rgba(56, 189, 248, 0.3) 0%, transparent 60%), radial-gradient(ellipse at 80% 80%, rgba(63, 125, 183, 0.4) 0%, transparent 60%), linear-gradient(180deg, #1e3a8a 0%, #3f7db7 30%, #0284c7 65%, #06b6d4 90%, #00f2fe 100%) !important; background-attachment: fixed !important; min-height: 100vh; box-sizing: border-box;">
    <!-- Hero Section -->
    <div class="merch-hero" style="background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 24px; padding: 24px; text-align: center; margin-top: 16px; margin-bottom: 20px; box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25);">
        <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(56, 189, 248, 0.2); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #00f2fe; margin: 0 auto 12px;">
            <i class="fa-solid fa-tags"></i>
        </div>
        <h2 style="margin: 0 0 6px; font-size: 22px; font-weight: 800; color: #ffffff;">Discounts & Vouchers</h2>
        <p style="margin: 0 0 14px; font-size: 13px; color: rgba(255, 255, 255, 0.9); line-height: 1.5; max-width: 340px; margin-left: auto; margin-right: auto;">
            Redeem your hard-earned <strong style="color: #00f2fe;">XP</strong> for exclusive dining discounts, surf rentals, resort vouchers, and eco-passes!
        </p>
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.12); border:none !important; outline:none !important; padding:6px 16px; border-radius:100px;">
            <i class="fa-solid fa-bolt" style="color:#fbbf24; font-size:13px;"></i>
            <span style="font-size:12px; color:rgba(255,255,255,0.85); font-weight:600;">Your Balance:</span>
            <strong id="discount-user-pts" style="color:#ffffff; font-size:14px; font-weight:900;">-- XP</strong>
        </div>
    </div>

    <!-- Category Filters -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 20px; scrollbar-width: none;" id="discount-filters">
        <button class="discount-cat-btn active" onclick="filterDiscounts('All')">All Deals</button>
        <button class="discount-cat-btn" id="btn-my-claimed" onclick="filterDiscounts('Claimed')">My Vouchers (<span id="claimed-count">0</span>)</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Food & Dining')">Food & Dining</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Activities')">Activities & Surf</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Accommodations')">Accommodations</button>
        <button class="discount-cat-btn" onclick="filterDiscounts('Souvenirs')">Gear & Passes</button>
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
<div id="voucher-modal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(6,11,25,0.85); align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); opacity:0; transition:opacity 0.3s ease;">
    <div class="voucher-card-anim" style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:24px; padding:24px; width:100%; max-width:360px; box-shadow:0 20px 50px rgba(10,25,60,0.5); text-align:center; position:relative; box-sizing:border-box; transform:scale(0.86) translateY(20px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        <button onclick="closeVoucherModal()" style="position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.12); border:none !important; outline:none !important; border-radius:50%; width:32px; height:32px; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div id="modal-icon-wrap" style="width:64px; height:64px; border-radius:18px; background:rgba(255,255,255,0.12); border:none !important; outline:none !important; display:flex; align-items:center; justify-content:center; font-size:28px; color:#00f2fe; margin:0 auto 14px;">
            <i class="fa-solid fa-ticket"></i>
        </div>

        <span id="modal-category" style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#00f2fe; display:block; margin-bottom:4px;">Food & Dining</span>
        <h3 id="modal-title" style="margin:0 0 8px; font-size:18px; font-weight:800; color:#ffffff;">15% OFF at El Union Coffee</h3>
        <p id="modal-location" style="margin:0 0 8px; font-size:12px; color:rgba(255,255,255,0.85);"><i class="fa-solid fa-location-dot" style="color:#00f2fe; margin-right:4px;"></i>San Juan, La Union</p>

        <!-- Expiry Badge in Modal -->
        <div id="modal-expiry-badge" style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:8px; margin-bottom:14px; font-size:11px; font-weight:700; border:none !important; outline:none !important;">
            <i class="fa-regular fa-clock" style="font-size:10px;"></i>
            <span id="modal-expiry-text">Valid until Aug 15, 2026</span>
        </div>

        <div style="background:rgba(255,255,255,0.08); border:none !important; outline:none !important; border-radius:14px; padding:12px; margin-bottom:16px; text-align:left;">
            <p id="modal-description" style="margin:0; font-size:12px; color:rgba(255,255,255,0.92); line-height:1.5;"></p>
        </div>

        <!-- Voucher Code Box -->
        <div style="background:rgba(255,255,255,0.12); border:none !important; outline:none !important; border-radius:14px; padding:14px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <span style="display:block; font-size:9px; color:rgba(255,255,255,0.7); text-transform:uppercase; font-weight:700;">Promo / Claim Code</span>
                <span id="modal-code" style="font-size:18px; font-weight:900; color:#00f2fe; letter-spacing:1px;">ELYU-COFFEE-15</span>
            </div>
            <button id="btn-copy-voucher" onclick="copyVoucherCode()" style="background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#ffffff; padding:8px 14px; border-radius:10px; font-weight:800; font-size:12px; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;">
                <i class="fa-solid fa-copy" id="copy-btn-icon"></i> <span id="copy-btn-label">Copy</span>
            </button>
        </div>

        <!-- Post-Copy Instructions & Next Steps Banner -->
        <div id="copy-success-banner" style="display:none; background:rgba(52,199,89,0.2); border:none !important; outline:none !important; border-radius:14px; padding:12px; margin-bottom:14px; text-align:left;">
            <div style="display:flex; align-items:center; gap:8px; color:#34c759; font-size:12px; font-weight:800; margin-bottom:4px;">
                <i class="fa-solid fa-circle-check"></i> Code Copied & Saved to My Vouchers!
            </div>
            <p style="margin:0; font-size:11px; color:rgba(255,255,255,0.9); line-height:1.4;">
                Present this promo code or show this screen to staff at checkout to claim your discount.
            </p>
        </div>

        <div id="modal-action-row" style="display:flex; flex-direction:column; gap:8px; margin-top:12px;">
            <button id="modal-redeem-btn" onclick="handleModalRedeem()" style="width:100%; padding:12px; border:none !important; outline:none !important; border-radius:12px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color:#fff; font-size:13px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; box-shadow:none !important;">
                <i class="fa-solid fa-gift"></i> <span id="modal-redeem-btn-label">Redeem for 100 XP</span>
            </button>
            <div style="display:flex; gap:8px;">
                <button onclick="navigateTo('map'); closeVoucherModal();" style="flex:1; padding:10px; border:none !important; outline:none !important; border-radius:10px; background:rgba(255,255,255,0.15); color:#ffffff; font-size:11px; font-weight:800; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <i class="fa-solid fa-map-location-dot"></i> View on Map
                </button>
                <button onclick="closeVoucherModal()" style="flex:1; padding:10px; border:none !important; outline:none !important; border-radius:10px; background:rgba(255,255,255,0.1); color:#fff; font-size:11px; font-weight:800; cursor:pointer;">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.discount-cat-btn {
    background: rgba(255,255,255,0.12);
    border: none !important;
    outline: none !important;
    color: rgba(255,255,255,0.85);
    padding: 8px 16px;
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
    background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%);
    color: #ffffff;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.voucher-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
    border: none !important;
    outline: none !important;
    border-radius: 20px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
    transition: transform 0.2s ease;
}
.voucher-card:active {
    transform: scale(0.98);
}
</style>

<script>
(function() {
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
let vouchersData = [];

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

let userPointsBalance = 0;

async function fetchUserPointsAndRedemptions() {
    const token = localStorage.getItem('intan_elyu_token');
    if (!token) return;

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
                userPointsBalance = data.xp ?? data.points ?? 0;
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} XP`;

                // Sync database claimed vouchers
                if (Array.isArray(data.vouchers)) {
                    let claimed = getClaimedVouchers();
                    data.vouchers.forEach(v => {
                        // find matching item in vouchersData if any
                        const match = vouchersData.find(item => item.code === v.voucher_code || (item.dbId && item.title === v.type));
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
            ? 'You have not claimed any vouchers yet. Redeem vouchers using XP to save them here!' 
            : 'No vouchers available in this category.';
        grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: #ffffff; padding: 36px 20px; font-size: 13px; font-weight:600; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 20px; box-shadow: 0 8px 24px rgba(10,25,60,0.25);">${msg}</div>`;
        return;
    }

    let html = '';
    filtered.forEach(v => {
        const isClaimed = claimed.includes(v.id);
        const imgUrl = getVoucherImageUrl(v);
        html += `
        <div class="voucher-card">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.14); border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
                        <img src="${imgUrl}" alt="${v.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                    </div>
                    <span style="font-size: 10px; font-weight: 800; background: rgba(0,242,254,0.25); color: #ffffff; border: none !important; outline: none !important; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">${v.badge}</span>
                </div>
                <h4 style="margin: 0 0 4px; font-size: 15px; font-weight: 800; color: #ffffff; line-height: 1.3;">${v.title}</h4>
                <p style="margin: 0 0 8px; font-size: 12px; color: rgba(255,255,255,0.85); font-weight: 500;">
                    <i class="fa-solid fa-store" style="font-size: 10px; margin-right: 4px; color: #00f2fe;"></i>${v.partner}
                </p>
                <div style="display: inline-flex; align-items: center; gap: 4px; background: ${getExpiryInfo(v.expires).bgColor}; padding: 3px 8px; border-radius: 6px; margin-bottom: 10px; border: none !important; outline: none !important;">
                    <i class="fa-regular fa-clock" style="font-size: 9px; color: ${getExpiryInfo(v.expires).color};"></i>
                    <span style="font-size: 10px; font-weight: 700; color: ${getExpiryInfo(v.expires).color};">${getExpiryInfo(v.expires).label}</span>
                </div>
                <p style="margin: 0 0 16px; font-size: 11.5px; color: rgba(255,255,255,0.8); line-height: 1.4;">${v.description}</p>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.12);">
                <div style="display: flex; align-items: center; gap: 4px;">
                    <i class="fa-solid fa-bolt" style="color: #fbbf24; font-size: 13px;"></i>
                    <span style="font-size: 14px; font-weight: 800; color: #ffffff;">${v.xpCost || v.pointsCost} <span style="font-size: 10px; color: rgba(255,255,255,0.7);">XP</span></span>
                </div>
                <button onclick="${getExpiryInfo(v.expires).isExpired ? '' : 'openVoucherModal(\'' + v.id + '\')'}" ${getExpiryInfo(v.expires).isExpired ? 'disabled' : ''} style="background: ${getExpiryInfo(v.expires).isExpired ? 'rgba(255,255,255,0.08)' : (isClaimed ? 'rgba(52,199,89,0.25)' : 'linear-gradient(135deg, #00f2fe, #0284c7)')}; border: none !important; outline: none !important; color: #ffffff; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: ${getExpiryInfo(v.expires).isExpired ? 'not-allowed' : 'pointer'}; box-shadow: none !important; opacity: ${getExpiryInfo(v.expires).isExpired ? '0.6' : '1'};">
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
        const modalImg = getVoucherImageUrl(item);
        iconWrap.innerHTML = `<img src="${modalImg}" alt="${item.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 6px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">`;
    }

    const banner = document.getElementById('copy-success-banner');
    if (banner) banner.style.display = 'none';

    const copyBtn = document.getElementById('btn-copy-voucher');
    const copyIcon = document.getElementById('copy-btn-icon');
    const copyLabel = document.getElementById('copy-btn-label');
    
    const claimed = getClaimedVouchers();
    const isAlreadyClaimed = claimed.includes(id);

    const redeemBtn = document.getElementById('modal-redeem-btn');
    const redeemLabel = document.getElementById('modal-redeem-btn-label');

    if (redeemBtn) {
        if (isAlreadyClaimed) {
            redeemBtn.style.display = 'none';
        } else {
            redeemBtn.style.display = 'flex';
            if (redeemLabel) redeemLabel.textContent = `Redeem for ${item.xpCost || item.pointsCost} XP`;
        }
    }

    if (copyBtn) {
        copyBtn.disabled = false;
        copyBtn.style.background = 'linear-gradient(135deg, #00f2fe 0%, #0284c7 100%)';
        copyBtn.style.color = '#ffffff';
        copyBtn.style.border = 'none';
        copyBtn.style.outline = 'none';
        copyBtn.style.cursor = 'pointer';
        copyBtn.style.opacity = '1';
        if (copyLabel) copyLabel.textContent = 'Copy';
        if (copyIcon) copyIcon.className = 'fa-solid fa-copy';
        if (isAlreadyClaimed && banner) banner.style.display = 'block';
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
            btn.style.background = 'rgba(52,211,153,0.2)';
            btn.style.color = '#34d399';
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

async function handleModalRedeem() {
    const item = vouchersData.find(v => v.id === currentVoucherId);
    if (!item) return;

    const token = localStorage.getItem('intan_elyu_token');
    if (!token) {
        // Guest mode fallback
        copyVoucherCode();
        return;
    }

    const cost = item.xpCost || item.pointsCost || 100;
    if (userPointsBalance < cost) {
        if (typeof showToast === 'function') {
            showToast(`Insufficient XP. You need ${cost} XP (Balance: ${userPointsBalance} XP).`);
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
            body: JSON.stringify({ voucher_id: item.dbId || item.id.replace('db_', '') })
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
            if (typeof showToast === 'function') showToast("🎉 Voucher claimed successfully!");
            if (window.confetti) {
                window.confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
            }

            // Save to claimed
            let claimed = getClaimedVouchers();
            if (!claimed.includes(item.id)) {
                claimed.push(item.id);
                localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
            }

            // Refresh points and redemptions
            fetchUserPointsAndRedemptions();
            renderDiscounts();

            // Update modal UI
            if (btn) btn.style.display = 'none';
            const banner = document.getElementById('copy-success-banner');
            if (banner) {
                banner.innerHTML = `
                    <div style="display:flex; align-items:center; gap:8px; color:#34d399; font-size:12px; font-weight:800; margin-bottom:4px;">
                        <i class="fa-solid fa-circle-check"></i> Voucher Redeemed & Saved to My Vouchers!
                    </div>
                    <p style="margin:0; font-size:11px; color:rgba(226,232,240,0.85); line-height:1.4;">
                        Present this code (${item.code}) to merchant staff at checkout to enjoy your discount.
                    </p>
                `;
                banner.style.display = 'block';
            }
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to redeem voucher.");
            if (btn) {
                const cost = item.xpCost || item.pointsCost || 100;
                btn.innerHTML = `<i class="fa-solid fa-gift"></i> Redeem for ${cost} XP`;
                btn.disabled = false;
            }
        }
    } catch(e) {
        console.error("Redemption error:", e);
        if (typeof showToast === 'function') showToast("Network error. Please try again.");
        if (btn) {
            const cost = item.xpCost || item.pointsCost || 100;
            btn.innerHTML = `<i class="fa-solid fa-gift"></i> Redeem for ${cost} XP`;
            btn.disabled = false;
        }
    }
}

async function fetchLiveDatabaseVouchers() {
    try {
        const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
        const res = await fetch(baseUrl + '/api/vouchers', {
            headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
        });
        if (res.ok) {
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                vouchersData = data.data.map(v => {
                    const icon = v.category === 'Activities' ? 'fa-person-hiking' : (v.category === 'Accommodations' ? 'fa-hotel' : (v.category === 'Souvenirs' ? 'fa-gift' : 'fa-utensils'));
                    return {
                        id: 'db_' + v.id,
                        dbId: v.id,
                        title: v.title,
                        category: v.category || 'Food & Dining',
                        partner: v.partner || 'LUPTO Tourism',
                        location: v.location || 'La Union',
                        badge: v.badge || 'PROMO OFFER',
                        pointsCost: v.pointsCost || 100,
                        icon: icon,
                        color: '#38bdf8',
                        code: v.code || 'ELYU-PROMO',
                        expires: v.expires || '2026-12-31',
                        description: v.description || 'Present voucher code at merchant checkout.'
                    };
                });
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
window.handleModalRedeem = handleModalRedeem;
window.renderDiscounts = renderDiscounts;

fetchLiveDatabaseVouchers();
fetchUserPointsAndRedemptions();
})();
</script>
