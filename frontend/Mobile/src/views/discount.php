<?php
$pageTitle = 'Discounts & Vouchers';
$backRoute = 'profile';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in">
    <!-- Hero Section with User Account Profile & Live Points -->
    <div class="merch-hero" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #0284c7 100%) !important; border: none !important; outline: none !important; border-radius: 24px; padding: 18px 20px; margin-bottom: 18px; box-shadow: 0 8px 24px rgba(30, 58, 138, 0.25) !important; color: #ffffff !important;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 0; text-align: left;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #ffffff; border: 2px solid rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <img id="discount-user-avatar" src="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';">
                </div>
                <div style="min-width: 0;">
                    <div style="font-size: 10.5px; font-weight: 800; color: #7dd3fc; text-transform: uppercase; letter-spacing: 0.5px;">Explorer Account</div>
                    <div id="discount-user-name" style="font-size: 15.5px; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">Tourist Explorer</div>
                </div>
            </div>
            <div style="display:inline-flex; align-items:center; gap:7px; background:#ffffff !important; padding:6px 14px; border-radius:100px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); flex-shrink:0;">
                <i class="fa-solid fa-coins" style="color:#f59e0b; font-size:13px;"></i>
                <strong id="discount-user-pts" style="color:#1e3a8a; font-size:13.5px; font-weight:900;">-- Points</strong>
            </div>
        </div>
        <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.92); line-height: 1.4; text-align: left; font-weight: 500;">
            Redeem your points for exclusive discounts and partner vouchers across La Union.
        </p>
    </div>

    <!-- Search Bar -->
    <div style="margin-bottom: 12px;">
        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
            <input type="text" id="voucher-search-input" placeholder="Search vouchers, merchants, or towns..." oninput="handleVoucherSearch(this.value)" style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 38px; border-radius: 14px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; outline: none; background: #f8fafc; color: #1e293b; transition: all 0.2s;">
            <button id="btn-clear-search" onclick="clearVoucherSearch()" style="display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: #e2e8f0; border: none; border-radius: 50%; width: 22px; height: 22px; color: #64748b; font-size: 11px; cursor: pointer; align-items: center; justify-content: center;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Voucher Status Dedicated Floating Droplist (All, Claimed, Redeemed, Upcoming, Expired) -->
    <div id="floating-status-wrapper" style="position: relative; margin-bottom: 16px; z-index: 98;">
        <!-- Floating Trigger Card -->
        <div id="floating-status-trigger" onclick="toggleFloatingStatusDropdown(event)" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 16px; padding: 10px 14px; box-shadow: 0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05); display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
            <div style="display: flex; flex-direction: column; min-width: 0; text-align: left; flex: 1;">
                <span style="font-size: 9.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1;">Voucher Status</span>
                <span id="floating-status-selected-label" style="font-size: 13px; font-weight: 800; color: #1e3a8a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">All Vouchers</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 8px;">
                <span id="floating-status-selected-badge" style="font-size: 10.5px; font-weight: 800; background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 100px; border: 1px solid #bfdbfe;">0</span>
                <i id="floating-status-chevron" class="fa-solid fa-chevron-down" style="color: #64748b; font-size: 10.5px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);"></i>
            </div>
        </div>

        <!-- Floating Menu Panel (Elevated Floating Card) -->
        <div id="floating-status-menu" class="hide-scrollbar" style="display: none; position: absolute; top: calc(100% + 8px); left: 0; width: 100%; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1.5px solid #cbd5e1; border-radius: 18px; box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.22), 0 4px 12px rgba(0, 0, 0, 0.06); padding: 6px; max-height: 330px; overflow-y: auto; z-index: 1000; opacity: 0; transform: translateY(-8px) scale(0.98); transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1); box-sizing: border-box;">
            <div id="floating-status-items-list" style="display: flex; flex-direction: column; gap: 4px;">
                <!-- Populated dynamically: All Vouchers, Claimed Voucher, Redeemed Voucher, Upcoming Voucher, Expired Voucher -->
            </div>
        </div>
    </div>

    <!-- Mabanag Hall Partner Merchant Spotlight Hero (Shown when Mabanag Hall filter or San Fernando town active) -->
    <div id="mabanag-spotlight-card" style="display: none; background: linear-gradient(135deg, #0f2b66 0%, #1a428a 50%, #2559b3 100%) !important; border-radius: 22px; padding: 18px 20px; margin-bottom: 18px; box-shadow: 0 8px 24px rgba(15, 43, 102, 0.28); color: #ffffff !important; position: relative; overflow: hidden; border: 1.5px solid rgba(255,255,255,0.18);">
        <div style="position: absolute; right: -15px; bottom: -25px; font-size: 110px; color: rgba(255,255,255,0.06); pointer-events: none;">
            <i class="fa-solid fa-landmark"></i>
        </div>
        <div style="display: flex; align-items: flex-start; gap: 14px; position: relative; z-index: 2;">
            <div style="width: 54px; height: 54px; border-radius: 16px; background: #ffffff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.18); overflow: hidden; padding: 4px;">
                <img src="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/SAN-FERNANDO.png" alt="Mabanag Hall" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div style="flex: 1; min-width: 0; text-align: left;">
                <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.18); backdrop-filter: blur(4px); padding: 3px 9px; border-radius: 100px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px; color: #fef08a;">
                    <i class="fa-solid fa-certificate"></i> Official Partner Merchant
                </div>
                <h3 style="margin: 0 0 4px 0; font-size: 17px; font-weight: 900; letter-spacing: -0.2px; color: #ffffff;">Mabanag Hall</h3>
                <p style="margin: 0 0 8px 0; font-size: 11.5px; color: rgba(255,255,255,0.92); line-height: 1.4;">
                    Official Partner Merchant in San Fernando City. Redeem your Explorer Points for partner vouchers and present your unique QR code at checkout.
                </p>
                <div style="display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: rgba(255,255,255,0.9); font-weight: 600;">
                    <div><i class="fa-solid fa-location-dot" style="color: #38bdf8; width: 14px;"></i> City Plaza, San Fernando City, La Union</div>
                    <div><i class="fa-regular fa-clock" style="color: #38bdf8; width: 14px;"></i> Mon - Sun • 8:00 AM - 5:00 PM</div>
                    <div><i class="fa-solid fa-ticket" style="color: #38bdf8; width: 14px;"></i> Present your digital QR claim code to staff at the counter</div>
                </div>
            </div>
        </div>
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

            <!-- QR Code Box (Shown when Claimed - Verified Digital Pass) -->
            <div id="modal-claimed-box" style="display: none; background: #eff6ff !important; border: 1.5px dashed #bfdbfe; border-radius: 18px; padding: 16px; margin-bottom: 14px; text-align: center;">
                <div style="background: #ffffff; border-radius: 14px; padding: 10px; width: 150px; height: 150px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.08); border: 1px solid #e2e8f0;">
                    <img id="modal-qr-img" src="" alt="Voucher QR Code" style="width: 100%; height: 100%; object-fit: contain;" onerror="this.style.display='none'">
                </div>
                
                <!-- Verified Digital Pass Badge -->
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 10.5px; font-weight: 800; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; text-transform: uppercase; letter-spacing: 0.6px;">
                        <i class="fa-solid fa-shield-halved" style="color: #10b981; font-size: 11px;"></i> Verified Digital QR Pass
                    </span>
                </div>

                <!-- Clickable Masked Code Container (Tap to Unmask/Mask) -->
                <div id="modal-qr-mask-container" onclick="window.toggleDiscountVoucherCodeMask()" role="button" tabindex="0" title="Tap to reveal or hide voucher code"
                    style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 14px; padding: 10px 14px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(30,58,138,0.06); display: flex; align-items: center; justify-content: space-between; gap: 10px; cursor: pointer; user-select: none; transition: all 0.2s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="text-align: left; min-width: 0; flex: 1;">
                        <div style="font-size: 9.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 5px; margin-bottom: 2px;">
                            <i class="fa-solid fa-key" style="color: #0284c7; font-size: 10px;"></i>
                            <span id="modal-qr-mask-label">Voucher Code (Tap to reveal)</span>
                        </div>
                        <div id="modal-qr-mask-code" style="font-size: 16px; font-weight: 900; color: #1e3a8a; letter-spacing: 2.5px; font-family: monospace; word-break: break-all;">
                            ••••••••••••
                        </div>
                    </div>
                    <div id="modal-qr-mask-btn" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; transition: all 0.15s ease;">
                        <i class="fa-solid fa-eye" id="modal-qr-mask-icon" style="color: #1e3a8a;"></i>
                    </div>
                </div>

                <p style="margin: 8px 0 0 0; font-size: 11.5px; color: #64748b; line-height: 1.45;">
                    Present this QR code directly to merchant or staff at checkout for instant scanning.
                </p>

                <div id="modal-live-status-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; background: #e0f2fe; color: #0369a1; margin-top: 10px; transition: all 0.3s ease;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout
                </div>
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

let activeStatus = 'All';
let activeCategory = 'All';
let activeMunicipality = 'All';
let searchQuery = '';
let vouchersData = [];
let currentVoucherId = null;
let userPointsBalance = 0;
let isStatusDropdownOpen = false;
let isCatDropdownOpen = false;
let isMuniDropdownOpen = false;

// ── Instant Synchronous Profile Hydration (0ms, Zero Latency) ──
function hydrateDiscountUser() {
    try {
        const cached = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (cached.name) {
            const nameEl = document.getElementById('discount-user-name');
            if (nameEl) nameEl.textContent = cached.name;
        }
        if (cached.avatar) {
            const imgEl = document.getElementById('discount-user-avatar');
            if (imgEl) imgEl.src = window.getFullImageUrl ? window.getFullImageUrl(cached.avatar) : cached.avatar;
        }
        if (cached.points !== undefined || cached.xp !== undefined) {
            const pts = parseInt(cached.points !== undefined ? cached.points : cached.xp) || 0;
            userPointsBalance = pts;
            const ptsEl = document.getElementById('discount-user-pts');
            if (ptsEl) ptsEl.textContent = `${pts.toLocaleString()} Points`;
        }
    } catch (e) { }
}
hydrateDiscountUser();

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
    activeCategory = cat || 'All';

    // 1. Synchronize Categories Drop List select element
    const catSelect = document.getElementById('category-dropdown-select');
    if (catSelect && catSelect.value !== activeCategory) {
        let matched = false;
        for (let i = 0; i < catSelect.options.length; i++) {
            if (catSelect.options[i].value.toLowerCase() === activeCategory.toLowerCase()) {
                catSelect.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched && !['All', 'Claimed', 'History'].includes(activeCategory)) {
            const opt = document.createElement('option');
            opt.value = activeCategory;
            opt.textContent = activeCategory;
            catSelect.appendChild(opt);
            catSelect.value = activeCategory;
        }
    }

    // 2. Synchronize Floating Dropdowns UI items & trigger cards
    populateCategoryDropdown();
    populateMunicipalityDropdown();

    renderDiscounts();
}

function filterMunicipality(muni) {
    activeMunicipality = muni || 'All';
    populateMunicipalityDropdown();
    populateCategoryDropdown();
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

function getAuthUserId() {
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (u && (u.id || u.user_id)) return String(u.id || u.user_id);
    } catch(e) {}
    const tok = localStorage.getItem('intan_elyu_token');
    return (tok && tok.length > 10) ? 'tok_' + tok.substring(0, 12) : null;
}

function getClaimedVouchersKey() {
    const uid = getAuthUserId();
    return uid ? 'intan_elyu_claimed_vouchers_' + uid : 'intan_elyu_claimed_vouchers_guest';
}

function getClaimedVouchers() {
    const uid = getAuthUserId();
    if (!uid) return [];
    try {
        return JSON.parse(localStorage.getItem(getClaimedVouchersKey()) || '[]');
    } catch(e) {
        return [];
    }
}

function setClaimedVouchers(claimedArr) {
    const uid = getAuthUserId();
    if (!uid) return;
    try {
        localStorage.setItem(getClaimedVouchersKey(), JSON.stringify(Array.from(new Set(claimedArr))));
        localStorage.removeItem('intan_elyu_claimed_vouchers'); // clear un-scoped legacy key
    } catch(e) {}
}

function isVoucherRedeemed(v) {
    if (!v) return false;
    const vid = String(v.dbId || v.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
    const vcode = (v.code || v.voucher_code || '').toUpperCase().trim();

    if (Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const match = window._touristRedemptions.find(r => {
            const rCode = (r.voucher_code || '').toUpperCase().trim();
            if (vcode && rCode && vcode === rCode) return true;
            const rVid = String(r.voucher_id || '').replace(/^db_/, '');
            if (vid && rVid && vid === rVid) return true;
            return false;
        });
        if (match && ['redeemed', 'used', 'completed'].includes((match.status || '').toLowerCase())) {
            return true;
        }
    }

    const rStatus = (v.redemptionStatus || '').toLowerCase();
    return ['redeemed', 'used', 'completed'].includes(rStatus);
}

function isVoucherExpired(v) {
    if (!v) return false;
    if (v.is_expired === true) return true;
    if (v.status && v.status.toLowerCase() === 'expired') return true;
    if (v.expires) {
        try {
            return new Date(v.expires).getTime() < Date.now();
        } catch(e) {}
    }
    return false;
}

function isVoucherUpcoming(v) {
    if (!v) return false;
    if (v.is_upcoming === true) return true;
    const st = (v.status || '').toLowerCase().trim();
    if (st === 'upcoming' || st === 'scheduled' || st === 'soon') return true;
    const badge = (v.badge || '').toLowerCase().trim();
    if (badge.includes('upcoming') || badge.includes('soon')) return true;
    if (v.valid_from) {
        try {
            const validFromStr = String(v.valid_from).replace(' ', 'T');
            const validFrom = validFromStr.includes('T') ? new Date(validFromStr) : new Date(validFromStr + 'T00:00:00');
            if (validFrom > new Date()) return true;
        } catch(e) {}
    }
    return false;
}

function isVoucherFullyClaimed(v) {
    if (!v) return false;
    if (v.is_out_of_stock === true) return true;
    if (v.remaining_quantity !== null && v.remaining_quantity !== undefined && v.remaining_quantity <= 0) return true;
    return false;
}

function isVoucherClaimed(v) {
    if (!v) return false;
    const vid = String(v.dbId || v.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
    const vcode = (v.code || v.voucher_code || '').toUpperCase().trim();

    // Check directly in in-memory tourist redemptions
    if (Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const found = window._touristRedemptions.some(r => {
            const rVid = String(r.voucher_id || '').replace(/^db_/, '');
            if (vid && rVid && vid === rVid) return true;
            const rCode = (r.voucher_code || '').toUpperCase().trim();
            if (vcode && rCode && vcode === rCode) return true;
            return false;
        });
        if (found) return true;
    }

    const claimed = getClaimedVouchers();
    if (claimed.includes(v.id)) return true;
    if (v.dbId && (claimed.includes('db_' + v.dbId) || claimed.includes(String(v.dbId)))) return true;
    if (v.redemptionStatus && ['claimed', 'redeemed', 'used', 'completed'].includes(v.redemptionStatus.toLowerCase())) return true;
    return false;
}

function getUserRedemptionItems() {
    const redemptions = Array.isArray(window._touristRedemptions) ? window._touristRedemptions : [];
    if (redemptions.length === 0) return [];

    return redemptions.map(r => {
        const rVid = r.voucher_id ? String(r.voucher_id) : null;
        const rCode = r.voucher_code ? String(r.voucher_code).toUpperCase().trim() : '';

        // Match with catalog template for rich metadata if available
        const match = vouchersData.find(v => {
            if (rVid && (String(v.dbId) === rVid || v.id === 'db_' + rVid || v.id === rVid)) return true;
            const vCode = v.code ? String(v.code).toUpperCase().trim() : '';
            if (vCode && rCode && vCode === rCode) return true;
            return false;
        });

        const status = (r.status || 'claimed').toLowerCase();
        const isRedeemed = ['redeemed', 'used', 'completed'].includes(status);
        const title = r.type || (match ? match.title : 'Reward Voucher');
        const partner = r.partner_establishment || (match ? match.partner : 'LUPTO Tourism Partner');
        const location = match ? match.location : 'La Union';
        const category = r.category || (match ? match.category : 'Food & Dining');
        const pointsCost = r.points_cost || (match ? match.pointsCost : 100);
        const description = r.description || (match ? match.description : 'Present this voucher code at merchant checkout.');
        const badge = isRedeemed ? 'REDEEMED' : (match ? match.badge : 'CLAIMED');
        const image = match ? match.image : (r.image || null);
        const code = r.voucher_code || (match ? match.code : 'ELYU-PROMO');

        return {
            id: 'rdm_' + r.id,
            redemptionId: r.id,
            dbId: r.voucher_id || (match ? match.dbId : null),
            title: title,
            category: category,
            partner: partner,
            partner_establishments: match ? match.partner_establishments : [partner],
            location: location,
            municipalities: match ? match.municipalities : [],
            badge: badge,
            discount_type: match ? match.discount_type : (r.discount_type || 'percentage'),
            discount_value: match ? match.discount_value : (r.discount_value || 0),
            pointsCost: pointsCost,
            required_points: pointsCost,
            points: pointsCost,
            code: code,
            image: image,
            valid_from: match ? match.valid_from : null,
            valid_from_formatted: match ? match.valid_from_formatted : null,
            expiration_type: match ? match.expiration_type : 'no_expiry_usable',
            is_no_expiration: match ? match.is_no_expiration : true,
            expires: match ? match.expires : null,
            expires_formatted: isRedeemed ? 'Redeemed & Recorded' : (match ? match.expires_formatted : 'Claimed & Usable'),
            is_expired: match ? match.is_expired : false,
            is_upcoming: false,
            is_out_of_stock: false,
            id_needed: match ? match.id_needed : false,
            terms_and_conditions: r.terms_and_conditions || (match ? match.terms_and_conditions : null),
            status: status,
            redemptionStatus: status,
            redeemedAt: r.redeemed_at,
            createdAt: r.created_at,
            description: description,
            is_mabanag: match ? match.is_mabanag : (partner.toLowerCase().includes('mabanag'))
        };
    });
}

function updateClaimedBadge() {
    const redemptions = getUserRedemptionItems();
    const activeClaimed = redemptions.filter(r => r.redemptionStatus === 'claimed');
    const historyClaimed = redemptions.filter(r => r.redemptionStatus === 'redeemed');

    const countEl = document.getElementById('claimed-count');
    const histEl = document.getElementById('history-count');
    if (countEl) countEl.textContent = activeClaimed.length;
    if (histEl) histEl.textContent = historyClaimed.length;

    if (typeof populateStatusDropdown === 'function') {
        populateStatusDropdown();
    }
}

function syncClaimedVouchersWithData() {
    const uid = getAuthUserId();
    if (!uid) {
        setClaimedVouchers([]);
        return;
    }

    const redemptions = Array.isArray(window._touristRedemptions) ? window._touristRedemptions : [];
    let claimedSet = new Set(getClaimedVouchers());

    if (window._hasFetchedRedemptions && redemptions.length === 0) {
        claimedSet.clear();
    }

    if (redemptions.length > 0) {
        redemptions.forEach(v => {
            const vId = v.voucher_id ? String(v.voucher_id) : null;
            const vCode = v.voucher_code ? String(v.voucher_code).toUpperCase().trim() : '';

            if (vId) {
                claimedSet.add(vId);
                claimedSet.add('db_' + vId);
            }
            if (vCode) {
                claimedSet.add(vCode);
            }

            if (vouchersData.length > 0) {
                const match = vouchersData.find(item => {
                    if (vId && (String(item.dbId) === vId || item.id === 'db_' + vId || item.id === vId)) return true;
                    const itemCode = item.code ? String(item.code).toUpperCase().trim() : '';
                    if (itemCode && vCode && (vCode === itemCode)) return true;
                    return false;
                });

                if (match) {
                    if (v.voucher_code) match.code = v.voucher_code;
                    match.redemptionStatus = (v.status || 'claimed').toLowerCase();
                    if (v.redeemed_at) match.redeemedAt = v.redeemed_at;
                    claimedSet.add(match.id);
                    if (match.dbId) {
                        claimedSet.add('db_' + match.dbId);
                        claimedSet.add(String(match.dbId));
                    }
                }
            }
        });
    }

    setClaimedVouchers(Array.from(claimedSet));
}

async function fetchUserPointsAndRedemptions() {
    const token = localStorage.getItem('intan_elyu_token');
    if (!token) {
        const ptsBadge = document.getElementById('discount-user-pts');
        if (ptsBadge) ptsBadge.textContent = '0 Points';
        return;
    }

    // Pre-hydrate balance immediately from auth_user
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (u.points !== undefined || u.xp !== undefined) {
            userPointsBalance = (u.points !== undefined) ? u.points : (u.xp ?? 0);
            const ptsBadge = document.getElementById('discount-user-pts');
            if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;
        }
    } catch (e) { }

    if (!navigator.onLine) {
        syncClaimedVouchersWithData();
        updateClaimedBadge();
        populateStatusDropdown();
        populateCategoryDropdown();
        renderDiscounts();
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
                window._hasFetchedRedemptions = true;
                window._touristRedemptions = Array.isArray(data.vouchers) ? data.vouchers : [];
                userPointsBalance = (data.points !== undefined) ? data.points : (data.xp ?? 0);
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

                try {
                    const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    u.points = userPointsBalance;
                    localStorage.setItem('auth_user', JSON.stringify(u));
                } catch (e) { }

                syncClaimedVouchersWithData();
                updateClaimedBadge();
                populateStatusDropdown();
                populateCategoryDropdown();
                renderDiscounts();
            }
        }
    } catch(e) {
        console.warn('Could not fetch user points balance:', e);
    }
}

function buildMunicipalityFilterBar() {
    populateMunicipalityDropdown();
}

function renderDiscounts() {
    const grid = document.getElementById('discounts-grid');
    if (!grid) return;
    updateClaimedBadge();

    // Toggle Mabanag Spotlight Banner
    const spotlightCard = document.getElementById('mabanag-spotlight-card');
    if (spotlightCard) {
        spotlightCard.style.display = (activeCategory === 'Mabanag Hall' || (activeMunicipality && activeMunicipality.toLowerCase() === 'san fernando')) ? 'block' : 'none';
    }

    const claimed = getClaimedVouchers();
    let filtered = [];

    // 1. Status Filter (Dedicated Status Droplist)
    if (activeStatus === 'Claimed') {
        filtered = getUserRedemptionItems().filter(r => r.redemptionStatus === 'claimed');
    } else if (activeStatus === 'Redeemed') {
        filtered = getUserRedemptionItems().filter(r => r.redemptionStatus === 'redeemed');
    } else if (activeStatus === 'Upcoming') {
        filtered = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v));
    } else if (activeStatus === 'Expired') {
        filtered = vouchersData.filter(v => isVoucherExpired(v));
    } else {
        // 'All': Exclude already claimed, redeemed, expired, or upcoming deals from available catalog browsing
        filtered = vouchersData.filter(v => !isVoucherClaimed(v) && !isVoucherRedeemed(v) && !isVoucherExpired(v) && !isVoucherUpcoming(v));
    }

    // 2. Category Filter
    if (activeCategory === 'Upcoming') {
        filtered = filtered.filter(v => (v.is_upcoming || (v.status && v.status.toLowerCase() === 'upcoming')) && !isVoucherExpired(v));
    } else if (activeCategory === 'Mabanag Hall') {
        filtered = filtered.filter(v => v.is_mabanag || (v.partner && v.partner.toLowerCase().includes('mabanag')) || (v.location && v.location.toLowerCase().includes('mabanag')));
    } else if (activeCategory !== 'All') {
        const targetCat = activeCategory.toLowerCase();
        filtered = filtered.filter(v => {
            if (!v.category) return false;
            const c = v.category.toLowerCase();
            return c === targetCat || c.includes(targetCat) || targetCat.includes(c);
        });
    }

    // 3. Municipality Filter
    if (activeMunicipality !== 'All') {
        const targetMuni = activeMunicipality.toLowerCase();
        filtered = filtered.filter(v => {
            if (Array.isArray(v.municipalities) && v.municipalities.length > 0) {
                return v.municipalities.some(m => m.toLowerCase().includes(targetMuni));
            }
            return (v.location || '').toLowerCase().includes(targetMuni);
        });
    }

    // 4. Search Filter
    if (searchQuery) {
        filtered = filtered.filter(v => {
            const haystack = `${v.title} ${v.partner} ${v.location} ${v.description} ${v.code} ${v.category}`.toLowerCase();
            return haystack.includes(searchQuery);
        });
    }

    // 5. Sort order for browsing:
    if (activeStatus !== 'Claimed' && activeStatus !== 'Redeemed' && activeCategory !== 'Claimed' && activeCategory !== 'History') {
        filtered.sort((a, b) => {
            const aExpired = (isVoucherExpired(a) || a.status === 'expired') ? 2 : (isVoucherFullyClaimed(a) ? 1 : 0);
            const bExpired = (isVoucherExpired(b) || b.status === 'expired') ? 2 : (isVoucherFullyClaimed(b) ? 1 : 0);
            const aUpcoming = (a.is_upcoming || a.status === 'upcoming') ? 1 : 0;
            const bUpcoming = (b.is_upcoming || b.status === 'upcoming') ? 1 : 0;

            const aPriority = aExpired > 0 ? (10 + aExpired) : (aUpcoming ? 2 : 0);
            const bPriority = bExpired > 0 ? (10 + bExpired) : (bUpcoming ? 2 : 0);

            return aPriority - bPriority;
        });
    }

    if (filtered.length === 0) {
        let msg = 'No vouchers match your current filters.';
        if (vouchersData.length === 0 && getUserRedemptionItems().length === 0) {
            msg = 'No discounts or vouchers are currently available. Check back soon for exciting deals!';
        } else if (activeStatus === 'Claimed') {
            msg = 'You have no active claimed vouchers right now. Claim reward deals from "All Vouchers" using your Explorer Points!';
        } else if (activeStatus === 'Redeemed') {
            msg = 'No redeemed voucher history yet. Vouchers scanned at checkout by partner merchants will appear here.';
        } else if (activeStatus === 'Upcoming') {
            msg = 'No upcoming vouchers scheduled right now. Check back soon for exciting new promotions and deals opening soon!';
        } else if (activeStatus === 'Expired') {
            msg = 'No expired vouchers found. All promotions are currently active or upcoming!';
        } else if (activeStatus === 'All' && getUserRedemptionItems().length > 0) {
            msg = '🎉 You have claimed all available deals! Select "Claimed Voucher" in Voucher Status above to view your claimed discounts.';
        } else if (activeCategory === 'Mabanag Hall') {
            msg = 'No vouchers for Mabanag Hall match your current filter.';
        } else if (activeCategory === 'Upcoming') {
            msg = 'No upcoming promotions scheduled right now. Check back soon for new discounts!';
        } else if (activeCategory !== 'All') {
            msg = `No vouchers found under the "${activeCategory}" category. Check back soon for new offers!`;
        } else if (searchQuery) {
            msg = `No vouchers found matching "${searchQuery}". Try a different keyword or town.`;
        }
        grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: #ffffff; padding: 36px 20px; font-size: 13px; font-weight:700; background: linear-gradient(135deg, #203f8d 0%, #2b549c 50%, #3568a9 100%); border-radius: 20px; box-shadow: 0 4px 14px rgba(32, 63, 141, 0.28);">${msg}</div>`;
        return;
    }

    let html = '';
    filtered.forEach(v => {
        const isClaimed = isVoucherClaimed(v);
        const isRedeemed = isVoucherRedeemed(v);
        const imgUrl = getVoucherImageUrl(v);
        const expiryInfo = getExpiryInfo(v.expires, v.is_expired, v.is_upcoming, v.valid_from, v.valid_from_formatted, v.is_no_expiration, v.expiration_type);
        const isCardExpired = isVoucherExpired(v) || expiryInfo.isExpired;
        const isCardUpcoming = !isCardExpired && (v.is_upcoming || expiryInfo.isUpcoming);
        const isOutOfStock = isVoucherFullyClaimed(v);

        let actionBtnHtml = '';
        if (isRedeemed) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #dc2626 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display:flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3) !important;">
                    <i class="fa-solid fa-check-double"></i> Redeemed
                </button>
            `;
        } else if (isClaimed) {
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
                <button disabled style="background: #ea580c !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: not-allowed;">
                    <i class="fa-solid fa-ban" style="margin-right:4px;"></i> Fully Claimed
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

        // Badges: Mabanag Partner, ID Needed & Low Stock
        let mabanagBadgeHtml = '';
        if (v.is_mabanag || (v.partner && v.partner.toLowerCase().includes('mabanag'))) {
            mabanagBadgeHtml = `<span style="font-size: 9.5px; font-weight: 800; background: #fef08a !important; color: #854d0e !important; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-landmark"></i> Mabanag Partner</span>`;
        }

        let idBadgeHtml = '';
        if (v.id_needed) {
            idBadgeHtml = `<span style="font-size: 9.5px; font-weight: 800; background: #ef4444 !important; color: #ffffff !important; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-id-card"></i> ID Required</span>`;
        }

        let stockBadgeHtml = '';
        if (v.remaining_quantity !== null && v.remaining_quantity > 0 && v.remaining_quantity <= 5) {
            stockBadgeHtml = `<span style="font-size: 9.5px; font-weight: 800; background: #f59e0b !important; color: #ffffff !important; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px;"><i class="fa-solid fa-fire"></i> Only ${v.remaining_quantity} left</span>`;
        }

        const topBadgeBg = isRedeemed ? '#dc2626' : (isClaimed ? '#10b981' : '#ffffff');
        const topBadgeColor = isRedeemed ? '#ffffff' : (isClaimed ? '#ffffff' : '#203f8d');
        const topBadgeText = isRedeemed ? 'REDEEMED' : (isClaimed ? 'CLAIMED' : v.badge);

        let codeSnippetHtml = '';
        if (isClaimed || isRedeemed) {
            codeSnippetHtml = `
                <div style="display: inline-flex; align-items: center; gap: 5px; background: rgba(0, 0, 0, 0.22); padding: 3px 8px; border-radius: 6px; margin-bottom: 8px;">
                    <i class="fa-solid fa-ticket" style="font-size: 9px; color: #38bdf8;"></i>
                    <span style="font-size: 10.5px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; font-family: monospace;">CODE: ${v.code || 'CODE'}</span>
                </div>
            `;
        }

        html += `
        <div class="voucher-card">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; gap: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.14);">
                        <img src="${imgUrl}" alt="${v.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;" onerror="this.onerror=null; this.src='https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LUPTO.png';">
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <span style="font-size: 11px; font-weight: 900; background: ${topBadgeBg} !important; color: ${topBadgeColor} !important; padding: 4px 10px; border-radius: 8px; text-transform: uppercase; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">${topBadgeText}</span>
                        ${mabanagBadgeHtml}
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
                    <span style="font-size: 10px; font-weight: 700; color: #ffffff;">${isRedeemed ? 'Redeemed & Recorded' : (isClaimed ? 'Claimed & Ready to Use' : expiryInfo.label)}</span>
                </div>
                ${codeSnippetHtml}

                <p style="margin: 0 0 14px; font-size: 12px; color: rgba(255, 255, 255, 0.88); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${v.description}</p>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.18);">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <i class="fa-solid fa-coins" style="color: #fbbf24; font-size: 14px;"></i>
                    <span style="font-size: 14px; font-weight: 900; color: #ffffff;">${v.pointsCost || v.required_points || 100} <span style="font-size: 10px; color: rgba(255, 255, 255, 0.85); font-weight: 600;">Pts</span></span>
                </div>
                ${actionBtnHtml}
            </div>
        </div>
        `;
    });

    grid.innerHTML = html;
}

let liveSyncInterval = null;

function startLiveRedemptionSync(code) {
    stopLiveRedemptionSync();
    if (!code) return;

    const check = async () => {
        if (!navigator.onLine) return; // Ambient check: pause polling while offline
        try {
            const baseUrl = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
            const res = await fetch(`${baseUrl}/api/public/redemptions/${encodeURIComponent(code)}/status`, {
                headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.status === 'success' && data.is_redeemed) {
                    const statusBadge = document.getElementById('modal-live-status-badge');
                    if (statusBadge) {
                        statusBadge.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#15803d; font-size:13px;"></i> Redeemed & Verified at ${data.redeemed_by_partner || 'Partner Merchant'}!`;
                        statusBadge.style.background = '#dcfce7';
                        statusBadge.style.color = '#15803d';
                        statusBadge.style.border = '1px solid #86efac';
                    }
                    if (window.confetti) {
                        window.confetti({ particleCount: 50, spread: 60, origin: { y: 0.6 } });
                    }
                    stopLiveRedemptionSync();
                }
            }
        } catch(e) {}
    };

    setTimeout(check, 1200);
    liveSyncInterval = setInterval(check, 4000);
}

function stopLiveRedemptionSync() {
    if (liveSyncInterval) {
        clearInterval(liveSyncInterval);
        liveSyncInterval = null;
    }
}

function openVoucherModal(id) {
    let item = vouchersData.find(v => v.id === id);
    if (!item && typeof getUserRedemptionItems === 'function') {
        const redemptionItems = getUserRedemptionItems();
        item = redemptionItems.find(v => v.id === id);
    }
    if (!item) return;
    currentVoucherId = id;

    const isAlreadyClaimed = isVoucherClaimed(item) || isVoucherRedeemed(item) || item.redemptionStatus === 'claimed' || item.redemptionStatus === 'redeemed';

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

    // Reset live status badge text
    const statusBadge = document.getElementById('modal-live-status-badge');
    if (statusBadge) {
        statusBadge.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout';
        statusBadge.style.background = '#e0f2fe';
        statusBadge.style.color = '#0369a1';
        statusBadge.style.border = 'none';
    }

    if (isAlreadyClaimed) {
        const claimCode = item.code || 'ELYU-PROMO';
        const isRedeemedOnWeb = isVoucherRedeemed(item);
        if (claimedBox) {
            claimedBox.style.display = 'block';
            window._currentDiscountClaimCode = claimCode;
            window._isDiscountCodeUnmasked = false;
            const codeEl = document.getElementById('modal-qr-mask-code');
            const iconEl = document.getElementById('modal-qr-mask-icon');
            const labelEl = document.getElementById('modal-qr-mask-label');
            const maskBtn = document.getElementById('modal-qr-mask-btn');
            if (codeEl) {
                codeEl.textContent = '••••••••••••';
                codeEl.style.letterSpacing = '2.5px';
                codeEl.style.color = '#1e3a8a';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-eye';
            if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
            if (maskBtn) {
                maskBtn.style.background = '#eff6ff';
                maskBtn.style.borderColor = '#bfdbfe';
            }

            const qrImg = document.getElementById('modal-qr-img');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(claimCode)}`;
                qrImg.style.display = 'block';
                if (isRedeemedOnWeb) {
                    qrImg.style.filter = 'grayscale(1)';
                    qrImg.style.opacity = '0.35';
                } else {
                    qrImg.style.filter = 'none';
                    qrImg.style.opacity = '1';
                }
            }
            if (isRedeemedOnWeb && statusBadge) {
                statusBadge.innerHTML = '<i class="fa-solid fa-check-double" style="color:#15803d; font-size:13px;"></i> Voucher Already Redeemed & Verified';
                statusBadge.style.background = '#dcfce7';
                statusBadge.style.color = '#15803d';
                statusBadge.style.border = '1px solid #86efac';
            }
        }
        if (redeemBtn) redeemBtn.style.display = 'none';
        if (footerBanner) footerBanner.style.display = 'none';
        if (!isRedeemedOnWeb) {
            startLiveRedemptionSync(claimCode);
        }
    } else {
        if (claimedBox) claimedBox.style.display = 'none';
        if (footerBanner) footerBanner.style.display = 'flex';
        if (redeemBtn) {
            redeemBtn.style.display = 'flex';
            if (isExpired) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.6';
                redeemBtn.style.cursor = 'not-allowed';
                redeemBtn.innerHTML = '<i class="fa-solid fa-lock" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Voucher Expired</span>';
            } else if (isOutOfStock) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.6';
                redeemBtn.style.cursor = 'not-allowed';
                redeemBtn.innerHTML = '<i class="fa-solid fa-ban" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Fully Claimed</span>';
            } else if (isUpcoming) {
                redeemBtn.disabled = true;
                redeemBtn.style.opacity = '0.7';
                redeemBtn.style.cursor = 'not-allowed';
                redeemBtn.innerHTML = `<i class="fa-regular fa-clock" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Starts on ${item.valid_from_formatted || 'Soon'}</span>`;
            } else {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                const cost = item.pointsCost || item.required_points || 100;
                redeemBtn.innerHTML = `<i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Redeem for ${cost} Points</span>`;
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
    stopLiveRedemptionSync();
    window._isDiscountCodeUnmasked = false;
    const btn = document.getElementById('modal-redeem-btn');
    if (btn) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = '<i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Redeem Points</span>';
    }
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

window._currentDiscountClaimCode = '';
window._isDiscountCodeUnmasked = false;
window.toggleDiscountVoucherCodeMask = function () {
    if (!window._currentDiscountClaimCode) return;
    window._isDiscountCodeUnmasked = !window._isDiscountCodeUnmasked;
    const codeEl = document.getElementById('modal-qr-mask-code');
    const iconEl = document.getElementById('modal-qr-mask-icon');
    const labelEl = document.getElementById('modal-qr-mask-label');
    const maskBtn = document.getElementById('modal-qr-mask-btn');

    if (window._isDiscountCodeUnmasked) {
        if (codeEl) {
            codeEl.textContent = window._currentDiscountClaimCode;
            codeEl.style.letterSpacing = '1px';
            codeEl.style.color = '#0284c7';
        }
        if (iconEl) iconEl.className = 'fa-solid fa-eye-slash';
        if (labelEl) labelEl.textContent = 'Voucher Code (Tap to hide)';
        if (maskBtn) {
            maskBtn.style.background = '#dbeafe';
            maskBtn.style.borderColor = '#93c5fd';
        }
    } else {
        if (codeEl) {
            codeEl.textContent = '••••••••••••';
            codeEl.style.letterSpacing = '2.5px';
            codeEl.style.color = '#1e3a8a';
        }
        if (iconEl) iconEl.className = 'fa-solid fa-eye';
        if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
        if (maskBtn) {
            maskBtn.style.background = '#eff6ff';
            maskBtn.style.borderColor = '#bfdbfe';
        }
    }
};

function copyVoucherCode() {
    if (typeof showToast === 'function') {
        showToast("Present your digital QR pass directly to merchant staff.");
    }
}

async function handleModalRedeem() {
    let item = vouchersData.find(v => v.id === currentVoucherId);
    if (!item && typeof getUserRedemptionItems === 'function') {
        item = getUserRedemptionItems().find(v => v.id === currentVoucherId);
    }
    if (!item) return;

    // Strict One-Time Claim: prevent duplicate claim and trigger toast
    if (isVoucherClaimed(item) || isVoucherRedeemed(item) || item.redemptionStatus === 'claimed' || item.redemptionStatus === 'redeemed') {
        if (typeof showToast === 'function') {
            showToast("You have already claimed this voucher. Each voucher is valid for one-time claim only.");
        }
        return;
    }

    const expiryInfo = getExpiryInfo(item.expires, item.is_expired, item.is_upcoming, item.valid_from, item.valid_from_formatted, item.is_no_expiration, item.expiration_type);
    if (isVoucherExpired(item) || expiryInfo.isExpired) {
        if (typeof showToast === 'function') showToast("This voucher has expired.");
        return;
    }

    if (item.is_upcoming || expiryInfo.isUpcoming) {
        if (typeof showToast === 'function') showToast(`This voucher is upcoming and will unlock on ${item.valid_from_formatted || 'its start date'}.`);
        return;
    }

    if (!navigator.onLine) {
        if (typeof showToast === 'function') {
            showToast("📍 Voucher claiming requires an active internet connection to generate your live QR claim code.");
        }
        return;
    }

    if (isVoucherFullyClaimed(item)) {
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

            // Save to claimed in user-scoped storage
            let claimed = getClaimedVouchers();
            if (!claimed.includes(item.id)) claimed.push(item.id);
            if (item.dbId) {
                if (!claimed.includes('db_' + item.dbId)) claimed.push('db_' + item.dbId);
                if (!claimed.includes(String(item.dbId))) claimed.push(String(item.dbId));
            }
            setClaimedVouchers(claimed);
            item.redemptionStatus = 'claimed';

            const claimCode = (data.data && data.data.voucher_code) ? data.data.voucher_code : (data.claim_code || item.code);
            item.code = claimCode;

            // Immediately register into window._touristRedemptions
            if (!Array.isArray(window._touristRedemptions)) window._touristRedemptions = [];
            const newRedemptionId = (data.data && data.data.id) ? data.data.id : Date.now();
            window._touristRedemptions.unshift({
                id: newRedemptionId,
                voucher_id: item.dbId || String(item.id).replace('db_', ''),
                type: item.title,
                points_cost: cost,
                voucher_code: claimCode,
                status: 'claimed',
                redeemed_at: null,
                created_at: new Date().toISOString(),
                partner_establishment: item.partner,
                category: item.category
            });

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
            const claimedBox = document.getElementById('modal-claimed-box');
            if (claimedBox) {
                claimedBox.style.display = 'block';
                window._currentDiscountClaimCode = claimCode;
                window._isDiscountCodeUnmasked = false;
                const codeEl = document.getElementById('modal-qr-mask-code');
                const iconEl = document.getElementById('modal-qr-mask-icon');
                const labelEl = document.getElementById('modal-qr-mask-label');
                const maskBtn = document.getElementById('modal-qr-mask-btn');
                if (codeEl) {
                    codeEl.textContent = '••••••••••••';
                    codeEl.style.letterSpacing = '2.5px';
                    codeEl.style.color = '#1e3a8a';
                }
                if (iconEl) iconEl.className = 'fa-solid fa-eye';
                if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
                if (maskBtn) {
                    maskBtn.style.background = '#eff6ff';
                    maskBtn.style.borderColor = '#bfdbfe';
                }

                const qrImg = document.getElementById('modal-qr-img');
                if (qrImg) {
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(claimCode)}`;
                    qrImg.style.display = 'block';
                }
            }
            if (btn) {
                btn.innerHTML = `<i class="fa-solid fa-gift"></i> <span id="modal-redeem-btn-label">Redeem for ${cost} Points</span>`;
                btn.disabled = false;
                btn.style.display = 'none';
            }
            const footerBanner = document.getElementById('modal-footer-banner');
            if (footerBanner) footerBanner.style.display = 'none';

            startLiveRedemptionSync(claimCode);

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
    // Strict deduplication by unique id or code
    const seenKeys = new Set();
    const uniqueRaw = (rawList || []).filter(v => {
        const key = v.id ? String(v.id) : (v.code || v.voucher_code || v.title);
        if (!key || seenKeys.has(key)) return false;
        seenKeys.add(key);
        return true;
    });

    vouchersData = uniqueRaw.map(v => {
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
            remaining_quantity: v.remaining_quantity,
            is_mabanag: (v.is_mabanag !== undefined) ? v.is_mabanag : (v.partner && v.partner.toLowerCase().includes('mabanag'))
        };
    });

    syncClaimedVouchersWithData();
    populateCategoryDropdown();
    buildMunicipalityFilterBar();
    renderDiscounts();
}

function populateCategoryDropdown() {
    const catSelect = document.getElementById('category-dropdown-select');
    const floatingList = document.getElementById('floating-cat-items-list');
    if (!floatingList && !catSelect) return;

    let baseList = [];
    if (activeStatus === 'Claimed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => r.redemptionStatus === 'claimed') : [];
    } else if (activeStatus === 'Redeemed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => r.redemptionStatus === 'redeemed') : [];
    } else if (activeStatus === 'Upcoming') {
        baseList = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v));
    } else if (activeStatus === 'Expired') {
        baseList = vouchersData.filter(v => isVoucherExpired(v));
    } else {
        baseList = vouchersData.filter(v => !isVoucherClaimed(v) && !isVoucherRedeemed(v) && !isVoucherExpired(v) && !isVoucherUpcoming(v));
    }

    // Dynamic counts per category
    const catCounts = {};
    baseList.forEach(v => {
        if (v.category) {
            const c = v.category.trim();
            catCounts[c] = (catCounts[c] || 0) + 1;
        }
    });

    const currentVal = activeCategory || (catSelect ? catSelect.value : 'All') || 'All';

    // Compile ordered list of clean categories (NO status duplicates)
    const categoriesList = [
        { value: 'All', label: 'All Deals', count: baseList.length }
    ];

    ['Food & Dining', 'Activities', 'Accommodations', 'Souvenirs'].forEach(cat => {
        categoriesList.push({
            value: cat,
            label: cat,
            count: catCounts[cat] || 0
        });
    });

    Object.keys(catCounts).forEach(cat => {
        if (!['Food & Dining', 'Activities', 'Accommodations', 'Souvenirs', 'All'].includes(cat)) {
            categoriesList.push({
                value: cat,
                label: cat,
                count: catCounts[cat] || 0
            });
        }
    });

    const mabanagCount = baseList.filter(v => v.is_mabanag || (v.partner && v.partner.toLowerCase().includes('mabanag'))).length;
    categoriesList.push({
        value: 'Mabanag Hall',
        label: 'Mabanag Hall Deals',
        count: mabanagCount
    });

    const upcomingCount = baseList.filter(v => (v.is_upcoming || (v.status && v.status.toLowerCase() === 'upcoming')) && !v.is_expired).length;
    categoriesList.push({
        value: 'Upcoming',
        label: 'Upcoming Promotions',
        count: upcomingCount
    });

    // 1. Sync hidden select element for complete compatibility
    if (catSelect) {
        catSelect.innerHTML = categoriesList.map(c => `<option value="${c.value}">${c.label} (${c.count})</option>`).join('');
        catSelect.value = currentVal;
    }

    // 2. Render floating droplist items (clean, elevated, interactive)
    if (floatingList) {
        floatingList.innerHTML = categoriesList.map(c => {
            const isSelected = currentVal.toLowerCase() === c.value.toLowerCase();
            return `
                <div onclick="selectFloatingCategory('${c.value.replace(/'/g, "\\'")}', event)" role="button" tabindex="0" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); ${isSelected ? 'background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; font-weight: 800; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);' : 'background: transparent; color: #1e293b; font-weight: 700;'}" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <span style="font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 6px; pointer-events: none;">${c.label}</span>
                    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; pointer-events: none;">
                        <span style="font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 100px; ${isSelected ? 'background: rgba(255, 255, 255, 0.22); color: #ffffff;' : 'background: #f1f5f9; color: #475569;'}">${c.count}</span>
                        ${isSelected ? '<span style="font-size: 13px; font-weight: 900; line-height: 1;">✓</span>' : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    // 3. Update Trigger Card selected display
    const activeItem = categoriesList.find(c => c.value.toLowerCase() === currentVal.toLowerCase()) || categoriesList[0];
    const triggerLabel = document.getElementById('floating-cat-selected-label');
    const triggerBadge = document.getElementById('floating-cat-selected-badge');
    if (triggerLabel && activeItem) triggerLabel.textContent = activeItem.label;
    if (triggerBadge && activeItem) triggerBadge.textContent = activeItem.count;
}

function populateMunicipalityDropdown() {
    const floatingMuniList = document.getElementById('floating-muni-items-list');
    if (!floatingMuniList) return;

    let baseList = [];
    if (activeStatus === 'Claimed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => r.redemptionStatus === 'claimed') : [];
    } else if (activeStatus === 'Redeemed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => r.redemptionStatus === 'redeemed') : [];
    } else if (activeStatus === 'Upcoming') {
        baseList = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v));
    } else if (activeStatus === 'Expired') {
        baseList = vouchersData.filter(v => isVoucherExpired(v));
    } else {
        baseList = vouchersData.filter(v => !isVoucherClaimed(v) && !isVoucherRedeemed(v) && !isVoucherExpired(v) && !isVoucherUpcoming(v));
    }

    const townSet = new Set();
    vouchersData.forEach(v => {
        if (Array.isArray(v.municipalities) && v.municipalities.length > 0) {
            v.municipalities.forEach(m => {
                if (m && m.trim()) townSet.add(m.trim());
            });
        } else if (v.location && v.location !== 'La Union') {
            const firstPart = v.location.split(',')[0].trim();
            if (firstPart) townSet.add(firstPart);
        }
    });

    const standardTowns = ['Agoo', 'Aringay', 'Bacnotan', 'Bagulin', 'Balaoan', 'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 'Pugo', 'Rosario', 'San Fernando', 'San Gabriel', 'San Juan', 'Santo Tomas', 'Santol', 'Sudipen', 'Tubao'];
    if (townSet.size === 0) {
        standardTowns.forEach(t => townSet.add(t));
    }

    const sortedTowns = Array.from(townSet).sort();

    const getTownCount = (townName) => {
        if (townName === 'All') return baseList.length;
        const target = townName.toLowerCase();
        return baseList.filter(v => {
            if (Array.isArray(v.municipalities) && v.municipalities.length > 0) {
                return v.municipalities.some(m => m.toLowerCase().includes(target));
            }
            return (v.location || '').toLowerCase().includes(target);
        }).length;
    };

    const muniList = [
        { value: 'All', label: 'All Towns', count: baseList.length }
    ];

    sortedTowns.forEach(t => {
        muniList.push({
            value: t,
            label: t,
            count: getTownCount(t)
        });
    });

    const currentMuni = activeMunicipality || 'All';

    floatingMuniList.innerHTML = muniList.map(m => {
        const isSelected = currentMuni.toLowerCase() === m.value.toLowerCase();
        return `
            <div onclick="selectFloatingMunicipality('${m.value.replace(/'/g, "\\'")}', event)" role="button" tabindex="0" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); ${isSelected ? 'background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; font-weight: 800; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);' : 'background: transparent; color: #1e293b; font-weight: 700;'}" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <span style="font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-right: 6px; pointer-events: none;">${m.label}</span>
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; pointer-events: none;">
                    <span style="font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 100px; ${isSelected ? 'background: rgba(255, 255, 255, 0.22); color: #ffffff;' : 'background: #f1f5f9; color: #475569;'}">${m.count}</span>
                    ${isSelected ? '<span style="font-size: 13px; font-weight: 900; line-height: 1;">✓</span>' : ''}
                </div>
            </div>
        `;
    }).join('');

    // Update Right Trigger Card
    const activeItem = muniList.find(m => m.value.toLowerCase() === currentMuni.toLowerCase()) || muniList[0];
    const triggerLabel = document.getElementById('floating-muni-selected-label');
    const triggerBadge = document.getElementById('floating-muni-selected-badge');
    if (triggerLabel && activeItem) triggerLabel.textContent = activeItem.label;
    if (triggerBadge && activeItem) triggerBadge.textContent = activeItem.count;
}

function closeFloatingStatus() {
    isStatusDropdownOpen = false;
    const menu = document.getElementById('floating-status-menu');
    const chevron = document.getElementById('floating-status-chevron');
    const trigger = document.getElementById('floating-status-trigger');
    const wrapper = document.getElementById('floating-status-wrapper');
    if (!menu) return;

    menu.style.opacity = '0';
    menu.style.transform = 'translateY(-8px) scale(0.98)';
    menu.style.pointerEvents = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (trigger) {
        trigger.style.borderColor = '#cbd5e1';
        trigger.style.boxShadow = '0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05)';
    }
    if (wrapper) wrapper.style.zIndex = '98';
    setTimeout(() => {
        if (!isStatusDropdownOpen) {
            menu.style.display = 'none';
        }
    }, 150);
}

function toggleFloatingStatusDropdown(e, forceClose = false) {
    if (e && e.stopPropagation) e.stopPropagation();
    const menu = document.getElementById('floating-status-menu');
    const chevron = document.getElementById('floating-status-chevron');
    const trigger = document.getElementById('floating-status-trigger');
    const wrapper = document.getElementById('floating-status-wrapper');
    if (!menu) return;

    if (forceClose === true || isStatusDropdownOpen) {
        closeFloatingStatus();
    } else {
        closeFloatingCategory();
        closeFloatingMunicipality();

        isStatusDropdownOpen = true;
        if (wrapper) wrapper.style.zIndex = '1002';
        menu.style.display = 'block';
        menu.style.pointerEvents = 'auto';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (trigger) {
            trigger.style.borderColor = '#2563eb';
            trigger.style.boxShadow = '0 12px 28px -4px rgba(37, 99, 235, 0.20), 0 4px 12px -2px rgba(37, 99, 235, 0.10)';
        }
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0) scale(1)';
        });
    }
}

function selectFloatingStatus(statusVal, e) {
    if (e && e.stopPropagation) e.stopPropagation();
    closeFloatingStatus();
    filterStatus(statusVal);
}

function filterStatus(statusVal) {
    activeStatus = statusVal || 'All';
    populateStatusDropdown();
    populateCategoryDropdown();
    populateMunicipalityDropdown();
    renderDiscounts();
}

function populateStatusDropdown() {
    const floatingList = document.getElementById('floating-status-items-list');
    const claimed = getClaimedVouchers();

    const redemptions = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems() : [];
    const countAll = vouchersData.filter(v => !isVoucherClaimed(v) && !isVoucherRedeemed(v) && !isVoucherExpired(v) && !isVoucherUpcoming(v)).length;
    const countClaimed = redemptions.filter(r => r.redemptionStatus === 'claimed').length;
    const countRedeemed = redemptions.filter(r => r.redemptionStatus === 'redeemed').length;
    const countUpcoming = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v)).length;
    const countExpired = vouchersData.filter(v => isVoucherExpired(v)).length;

    const statusList = [
        {
            value: 'All',
            label: 'All Vouchers',
            desc: 'Available reward deals to claim',
            count: countAll
        },
        {
            value: 'Claimed',
            label: 'Claimed Voucher',
            desc: 'Active & ready to scan at checkout',
            count: countClaimed
        },
        {
            value: 'Redeemed',
            label: 'Redeemed Voucher',
            desc: 'Used & verified at merchant store',
            count: countRedeemed
        },
        {
            value: 'Upcoming',
            label: 'Upcoming Voucher',
            desc: 'Scheduled promotions opening soon',
            count: countUpcoming
        },
        {
            value: 'Expired',
            label: 'Expired Voucher',
            desc: 'Promotion validity period ended',
            count: countExpired
        }
    ];

    if (floatingList) {
        floatingList.innerHTML = statusList.map(s => {
            const isSelected = activeStatus.toLowerCase() === s.value.toLowerCase();
            return `
                <div onclick="selectFloatingStatus('${s.value.replace(/'/g, "\\'")}', event)" role="button" tabindex="0" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); ${isSelected ? 'background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; font-weight: 800; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);' : 'background: transparent; color: #1e293b; font-weight: 700;'}" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="display: flex; flex-direction: column; text-align: left; min-width: 0; flex: 1; pointer-events: none;">
                        <span style="font-size: 13px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.25;">${s.label}</span>
                        <span style="font-size: 10.5px; font-weight: 600; ${isSelected ? 'color: rgba(255,255,255,0.85);' : 'color: #64748b;'} line-height: 1.2;">${s.desc}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; pointer-events: none; margin-left: 8px;">
                        <span style="font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 100px; ${isSelected ? 'background: rgba(255, 255, 255, 0.22); color: #ffffff;' : 'background: #f1f5f9; color: #475569;'}">${s.count}</span>
                        ${isSelected ? '<span style="font-size: 13px; font-weight: 900; line-height: 1;">✓</span>' : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    const currentItem = statusList.find(s => s.value.toLowerCase() === activeStatus.toLowerCase()) || statusList[0];
    const triggerLabel = document.getElementById('floating-status-selected-label');
    const triggerBadge = document.getElementById('floating-status-selected-badge');
    if (triggerLabel && currentItem) triggerLabel.textContent = currentItem.label;
    if (triggerBadge && currentItem) triggerBadge.textContent = currentItem.count;
}

function closeFloatingCategory() {
    isCatDropdownOpen = false;
    const menu = document.getElementById('floating-cat-menu');
    const chevron = document.getElementById('floating-cat-chevron');
    const trigger = document.getElementById('floating-cat-trigger');
    const wrapper = document.getElementById('floating-cat-wrapper');
    if (!menu) return;

    menu.style.opacity = '0';
    menu.style.transform = 'translateY(-8px) scale(0.98)';
    menu.style.pointerEvents = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (trigger) {
        trigger.style.borderColor = '#cbd5e1';
        trigger.style.boxShadow = '0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05)';
    }
    if (wrapper) wrapper.style.zIndex = '1';
    setTimeout(() => {
        if (!isCatDropdownOpen) {
            menu.style.display = 'none';
        }
    }, 150);
}

function closeFloatingMunicipality() {
    isMuniDropdownOpen = false;
    const menu = document.getElementById('floating-muni-menu');
    const chevron = document.getElementById('floating-muni-chevron');
    const trigger = document.getElementById('floating-muni-trigger');
    const wrapper = document.getElementById('floating-muni-wrapper');
    if (!menu) return;

    menu.style.opacity = '0';
    menu.style.transform = 'translateY(-8px) scale(0.98)';
    menu.style.pointerEvents = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (trigger) {
        trigger.style.borderColor = '#cbd5e1';
        trigger.style.boxShadow = '0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05)';
    }
    if (wrapper) wrapper.style.zIndex = '1';
    setTimeout(() => {
        if (!isMuniDropdownOpen) {
            menu.style.display = 'none';
        }
    }, 150);
}

function toggleFloatingCategoryDropdown(e, forceClose = false) {
    if (e && e.stopPropagation) e.stopPropagation();
    const menu = document.getElementById('floating-cat-menu');
    const chevron = document.getElementById('floating-cat-chevron');
    const trigger = document.getElementById('floating-cat-trigger');
    const wrapper = document.getElementById('floating-cat-wrapper');
    if (!menu) return;

    if (forceClose === true || isCatDropdownOpen) {
        closeFloatingCategory();
    } else {
        closeFloatingStatus();
        closeFloatingMunicipality();

        isCatDropdownOpen = true;
        if (wrapper) wrapper.style.zIndex = '1001';
        menu.style.display = 'block';
        menu.style.pointerEvents = 'auto';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (trigger) {
            trigger.style.borderColor = '#2563eb';
            trigger.style.boxShadow = '0 12px 28px -4px rgba(37, 99, 235, 0.20), 0 4px 12px -2px rgba(37, 99, 235, 0.10)';
        }
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0) scale(1)';
        });
    }
}

function selectFloatingCategory(catValue, e) {
    if (e && e.stopPropagation) e.stopPropagation();
    closeFloatingCategory();
    filterDiscounts(catValue);
}

function toggleFloatingMunicipalityDropdown(e, forceClose = false) {
    if (e && e.stopPropagation) e.stopPropagation();
    const menu = document.getElementById('floating-muni-menu');
    const chevron = document.getElementById('floating-muni-chevron');
    const trigger = document.getElementById('floating-muni-trigger');
    const wrapper = document.getElementById('floating-muni-wrapper');
    if (!menu) return;

    if (forceClose === true || isMuniDropdownOpen) {
        closeFloatingMunicipality();
    } else {
        closeFloatingStatus();
        closeFloatingCategory();

        isMuniDropdownOpen = true;
        if (wrapper) wrapper.style.zIndex = '1001';
        menu.style.display = 'block';
        menu.style.pointerEvents = 'auto';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (trigger) {
            trigger.style.borderColor = '#2563eb';
            trigger.style.boxShadow = '0 12px 28px -4px rgba(37, 99, 235, 0.20), 0 4px 12px -2px rgba(37, 99, 235, 0.10)';
        }
        requestAnimationFrame(() => {
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0) scale(1)';
        });
    }
}

function selectFloatingMunicipality(muniValue, e) {
    if (e && e.stopPropagation) e.stopPropagation();
    closeFloatingMunicipality();
    filterMunicipality(muniValue);
}

// Click outside listener to dismiss floating dropdowns
document.addEventListener('click', function(e) {
    const statusWrapper = document.getElementById('floating-status-wrapper');
    if (isStatusDropdownOpen && statusWrapper && !statusWrapper.contains(e.target)) {
        closeFloatingStatus();
    }
    const catWrapper = document.getElementById('floating-cat-wrapper');
    if (isCatDropdownOpen && catWrapper && !catWrapper.contains(e.target)) {
        closeFloatingCategory();
    }
    const muniWrapper = document.getElementById('floating-muni-wrapper');
    if (isMuniDropdownOpen && muniWrapper && !muniWrapper.contains(e.target)) {
        closeFloatingMunicipality();
    }
});

// Expose global functions
window.filterStatus = filterStatus;
window.filterDiscounts = filterDiscounts;
window.filterMunicipality = filterMunicipality;
window.handleVoucherSearch = handleVoucherSearch;
window.clearVoucherSearch = clearVoucherSearch;
window.openVoucherModal = openVoucherModal;
window.closeVoucherModal = closeVoucherModal;
window.copyVoucherCode = copyVoucherCode;
window.handleModalRedeem = handleModalRedeem;
window.renderDiscounts = renderDiscounts;
window.toggleFloatingStatusDropdown = toggleFloatingStatusDropdown;
window.selectFloatingStatus = selectFloatingStatus;
window.closeFloatingStatus = closeFloatingStatus;
window.populateStatusDropdown = populateStatusDropdown;
window.toggleFloatingCategoryDropdown = toggleFloatingCategoryDropdown;
window.selectFloatingCategory = selectFloatingCategory;
window.toggleFloatingMunicipalityDropdown = toggleFloatingMunicipalityDropdown;
window.selectFloatingMunicipality = selectFloatingMunicipality;
window.closeFloatingCategory = closeFloatingCategory;
window.closeFloatingMunicipality = closeFloatingMunicipality;
window.populateCategoryDropdown = populateCategoryDropdown;
window.populateMunicipalityDropdown = populateMunicipalityDropdown;

// Synchronously populate default dropdown items right away
populateStatusDropdown();
populateCategoryDropdown();
populateMunicipalityDropdown();

fetchLiveDatabaseVouchers();
fetchUserPointsAndRedemptions();

window.addEventListener('online', function () {
    fetchLiveDatabaseVouchers();
    fetchUserPointsAndRedemptions();
});
})();
</script>
