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

    <!-- Sliced Floating Droplists: Left = Category Deals | Right = Municipalities -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; position: relative; z-index: 95;">
        <!-- Left: Category Deals Floating Drop List -->
        <div id="floating-cat-wrapper" style="position: relative; z-index: 1;">
            <!-- Floating Trigger Card -->
            <div id="floating-cat-trigger" onclick="event.stopPropagation(); toggleFloatingCategoryDropdown()" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 16px; padding: 11px 12px; box-shadow: 0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05); display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; align-items: center; min-width: 0; flex: 1;">
                    <span id="floating-cat-selected-label" style="font-size: 12.5px; font-weight: 800; color: #1e3a8a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">All Deals</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 6px;">
                    <span id="floating-cat-selected-badge" style="font-size: 10.5px; font-weight: 800; background: #f1f5f9; color: #64748b; padding: 2px 7px; border-radius: 100px;">0</span>
                    <i id="floating-cat-chevron" class="fa-solid fa-chevron-down" style="color: #64748b; font-size: 10.5px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);"></i>
                </div>
            </div>

            <!-- Floating Menu Panel (Elevated Floating Card) -->
            <div id="floating-cat-menu" class="hide-scrollbar" style="display: none; position: absolute; top: calc(100% + 8px); left: 0; width: 100%; min-width: 190px; max-width: calc(100vw - 32px); background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1.5px solid #cbd5e1; border-radius: 18px; box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.22), 0 4px 12px rgba(0, 0, 0, 0.06); padding: 6px; max-height: 310px; overflow-y: auto; z-index: 1000; opacity: 0; transform: translateY(-8px) scale(0.98); transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1); box-sizing: border-box;">
                <div id="floating-cat-items-list" style="display: flex; flex-direction: column; gap: 4px;">
                    <!-- Dynamically populated floating category items -->
                </div>
            </div>

            <!-- Programmatic compatibility select element -->
            <select id="category-dropdown-select" onchange="filterDiscounts(this.value)" style="display: none;">
                <option value="All">All Deals</option>
                <option value="Food & Dining">Food & Dining</option>
                <option value="Activities">Activities & Surf</option>
                <option value="Accommodations">Accommodations</option>
                <option value="Souvenirs">Souvenirs & Pasalubong</option>
                <option value="Mabanag Hall">Mabanag Hall Deals</option>
                <option value="Upcoming">Upcoming Promotions</option>
                <option id="opt-cat-claimed" value="Claimed">My Vouchers</option>
                <option id="opt-cat-history" value="History">Voucher History</option>
            </select>
        </div>

        <!-- Right: Municipalities Floating Drop List -->
        <div id="floating-muni-wrapper" style="position: relative; z-index: 1;">
            <!-- Floating Trigger Card -->
            <div id="floating-muni-trigger" onclick="event.stopPropagation(); toggleFloatingMunicipalityDropdown()" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 16px; padding: 11px 12px; box-shadow: 0 10px 25px -4px rgba(30, 58, 138, 0.10), 0 4px 10px -2px rgba(30, 58, 138, 0.05); display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
                <div style="display: flex; align-items: center; min-width: 0; flex: 1;">
                    <span id="floating-muni-selected-label" style="font-size: 12.5px; font-weight: 800; color: #1e3a8a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">All Towns</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px; flex-shrink: 0; margin-left: 6px;">
                    <span id="floating-muni-selected-badge" style="font-size: 10.5px; font-weight: 800; background: #f1f5f9; color: #64748b; padding: 2px 7px; border-radius: 100px;">0</span>
                    <i id="floating-muni-chevron" class="fa-solid fa-chevron-down" style="color: #64748b; font-size: 10.5px; transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);"></i>
                </div>
            </div>

            <!-- Floating Menu Panel (Elevated Floating Card) -->
            <div id="floating-muni-menu" class="hide-scrollbar" style="display: none; position: absolute; top: calc(100% + 8px); right: 0; width: 100%; min-width: 190px; max-width: calc(100vw - 32px); background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1.5px solid #cbd5e1; border-radius: 18px; box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.22), 0 4px 12px rgba(0, 0, 0, 0.06); padding: 6px; max-height: 310px; overflow-y: auto; z-index: 1000; opacity: 0; transform: translateY(-8px) scale(0.98); transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1); box-sizing: border-box;">
                <div id="floating-muni-items-list" style="display: flex; flex-direction: column; gap: 4px;">
                    <!-- Dynamically populated floating municipality items -->
                </div>
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

let activeCategory = 'All';
let activeMunicipality = 'All';
let searchQuery = '';
let vouchersData = [];
let currentVoucherId = null;
let userPointsBalance = 0;
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

function getClaimedVouchers() {
    try {
        return JSON.parse(localStorage.getItem('intan_elyu_claimed_vouchers') || '[]');
    } catch(e) {
        return [];
    }
}

function isVoucherRedeemed(v) {
    if (!v) return false;
    const status = (v.redemptionStatus || v.status || '').toLowerCase();
    return ['redeemed', 'used', 'completed', 'expired'].includes(status);
}

function updateClaimedBadge() {
    const claimed = getClaimedVouchers();
    const activeClaimed = vouchersData.filter(v => claimed.includes(v.id) && !isVoucherRedeemed(v));
    const historyClaimed = vouchersData.filter(v => claimed.includes(v.id) && isVoucherRedeemed(v));

    const countEl = document.getElementById('claimed-count');
    const histEl = document.getElementById('history-count');
    if (countEl) countEl.textContent = activeClaimed.length;
    if (histEl) histEl.textContent = historyClaimed.length;

    // Update labels in Categories Dropdown
    const optClaimed = document.getElementById('opt-cat-claimed');
    const optHistory = document.getElementById('opt-cat-history');
    if (optClaimed) optClaimed.textContent = `My Vouchers (${activeClaimed.length})`;
    if (optHistory) optHistory.textContent = `Voucher History (${historyClaimed.length})`;
}

function syncClaimedVouchersWithData() {
    const redemptions = window._touristRedemptions || [];
    let claimed = getClaimedVouchers();
    if (redemptions.length > 0 && vouchersData.length > 0) {
        redemptions.forEach(v => {
            const match = vouchersData.find(item => 
                item.code === v.voucher_code || 
                (v.voucher_code && item.code && v.voucher_code.startsWith(item.code)) || 
                (item.dbId && item.title === v.type)
            );
            if (match) {
                if (v.voucher_code) match.code = v.voucher_code;
                if (v.status) match.redemptionStatus = (v.status || '').toLowerCase();
                if (v.redeemed_at) match.redeemedAt = v.redeemed_at;
                if (!claimed.includes(match.id)) {
                    claimed.push(match.id);
                }
            } else if (v.voucher_code) {
                const dynamicId = 'redeemed_' + (v.id || v.voucher_id || v.voucher_code);
                if (!vouchersData.some(item => item.id === dynamicId || item.code === v.voucher_code)) {
                    vouchersData.push({
                        id: dynamicId,
                        code: v.voucher_code,
                        title: v.type === 'pasalubong_discount' ? '₱50 Pasalubong Discount' : (v.type === 'environmental_fee' ? 'Waived Environmental Fee' : (v.type || 'Tourist Voucher')),
                        partner: v.partner_establishment || 'Official Partner Merchant',
                        location: 'La Union',
                        category: 'Food & Dining',
                        badge: 'PROMO',
                        pointsCost: 100,
                        redemptionStatus: (v.status || 'active').toLowerCase(),
                        redeemedAt: v.redeemed_at,
                        description: 'Official La Union tourist reward voucher.'
                    });
                    if (!claimed.includes(dynamicId)) {
                        claimed.push(dynamicId);
                    }
                }
            }
        });
        localStorage.setItem('intan_elyu_claimed_vouchers', JSON.stringify(claimed));
    }
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

                try {
                    const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                    u.points = userPointsBalance;
                    localStorage.setItem('auth_user', JSON.stringify(u));
                } catch (e) { }

                if (Array.isArray(data.vouchers)) {
                    window._touristRedemptions = data.vouchers;
                    syncClaimedVouchersWithData();
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
    let filtered = [...vouchersData];

    // 1. Category Filter & Claimed / Redeemed Separation
    if (activeCategory === 'Claimed') {
        // Show ONLY active/unredeemed claimed vouchers
        filtered = filtered.filter(v => claimed.includes(v.id) && !isVoucherRedeemed(v));
    } else if (activeCategory === 'History') {
        // Show redeemed / used vouchers in history
        filtered = filtered.filter(v => claimed.includes(v.id) && isVoucherRedeemed(v));
    } else {
        // Claimed and redeemed vouchers are excluded from All Deals and category browsing
        filtered = filtered.filter(v => !claimed.includes(v.id) && !isVoucherRedeemed(v));

        if (activeCategory === 'Upcoming') {
            filtered = filtered.filter(v => (v.is_upcoming || (v.status && v.status.toLowerCase() === 'upcoming')) && !v.is_expired);
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

    // 4. Sort order for browsing:
    if (activeCategory !== 'Claimed' && activeCategory !== 'History') {
        filtered.sort((a, b) => {
            const aExpired = (a.is_expired || a.status === 'expired') ? 2 : (a.is_out_of_stock ? 1 : 0);
            const bExpired = (b.is_expired || b.status === 'expired') ? 2 : (b.is_out_of_stock ? 1 : 0);
            const aUpcoming = (a.is_upcoming || a.status === 'upcoming') ? 1 : 0;
            const bUpcoming = (b.is_upcoming || b.status === 'upcoming') ? 1 : 0;

            const aPriority = aExpired > 0 ? (10 + aExpired) : (aUpcoming ? 2 : 0);
            const bPriority = bExpired > 0 ? (10 + bExpired) : (bUpcoming ? 2 : 0);

            return aPriority - bPriority;
        });
    }

    if (filtered.length === 0) {
        let msg = 'No vouchers match your current filters.';
        if (vouchersData.length === 0) {
            msg = 'No discounts or vouchers are currently available. Check back soon for exciting deals!';
        } else if (activeCategory === 'Claimed') {
            msg = 'You have no active vouchers right now. Claim reward deals using your Explorer Points!';
        } else if (activeCategory === 'History') {
            msg = 'No redeemed voucher history yet. Used vouchers scanned at checkout will appear here.';
        } else if (activeCategory === 'All' && claimed.length > 0) {
            msg = '🎉 You have claimed all available deals! Tap "My Vouchers" above to view your ready-to-use discounts.';
        } else if (activeCategory === 'Mabanag Hall') {
            msg = 'No unredeemed vouchers for Mabanag Hall right now.';
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
        const isClaimed = claimed.includes(v.id);
        const isRedeemed = isVoucherRedeemed(v);
        const imgUrl = getVoucherImageUrl(v);
        const expiryInfo = getExpiryInfo(v.expires, v.is_expired, v.is_upcoming, v.valid_from, v.valid_from_formatted, v.is_no_expiration, v.expiration_type);
        const isCardExpired = v.is_expired || expiryInfo.isExpired;
        const isCardUpcoming = !isCardExpired && (v.is_upcoming || expiryInfo.isUpcoming);
        const isOutOfStock = v.is_out_of_stock || (v.remaining_quantity !== null && v.remaining_quantity <= 0);

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
                    <i class="fa-solid fa-check"></i> Ready to Use
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

        const topBadgeBg = isRedeemed ? '#dc2626' : '#ffffff';
        const topBadgeColor = isRedeemed ? '#ffffff' : '#203f8d';
        const topBadgeText = isRedeemed ? 'REDEEMED' : v.badge;

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
                    <span style="font-size: 10px; font-weight: 700; color: #ffffff;">${isRedeemed ? 'Redeemed & Recorded' : expiryInfo.label}</span>
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

    // Reset live status badge text
    const statusBadge = document.getElementById('modal-live-status-badge');
    if (statusBadge) {
        statusBadge.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout';
        statusBadge.style.background = '#e0f2fe';
        statusBadge.style.color = '#0369a1';
        statusBadge.style.border = 'none';
    }

    // Reset copy button feedback
    const copyBtn = document.getElementById('btn-copy-voucher');
    const copyLabel = document.getElementById('copy-btn-label');
    const copyIcon = document.getElementById('copy-btn-icon');
    if (copyBtn) {
        copyBtn.style.background = '#ffffff';
        copyBtn.style.borderColor = '#bfdbfe';
        copyBtn.style.color = '#1e3a8a';
    }
    if (copyLabel) copyLabel.textContent = 'Copy Voucher Code';
    if (copyIcon) copyIcon.className = 'fa-solid fa-copy';

    if (isAlreadyClaimed) {
        const claimCode = item.code || 'ELYU-PROMO';
        const isRedeemedOnWeb = (item.redemptionStatus || '').toLowerCase() === 'redeemed';
        if (claimedBox) {
            claimedBox.style.display = 'block';
            const codeEl = document.getElementById('modal-code');
            if (codeEl) codeEl.textContent = claimCode;
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

function copyVoucherCode() {
    const codeEl = document.getElementById('modal-code');
    let code = codeEl ? (codeEl.innerText || codeEl.textContent || '').trim() : '';

    if (!code && currentVoucherId) {
        const currentItem = vouchersData.find(v => v.id === currentVoucherId);
        if (currentItem && currentItem.code) {
            code = String(currentItem.code).trim();
        }
    }

    if (!code) {
        if (typeof showToast === 'function') showToast("No voucher code available to copy.");
        return;
    }

    const btn = document.getElementById('btn-copy-voucher');
    const label = document.getElementById('copy-btn-label');
    const icon = document.getElementById('copy-btn-icon');

    const showCopiedSuccess = () => {
        if (label) label.textContent = 'Code Copied!';
        if (icon) icon.className = 'fa-solid fa-check';
        if (btn) {
            btn.style.background = '#dcfce7';
            btn.style.borderColor = '#86efac';
            btn.style.color = '#15803d';
        }
        if (typeof showToast === 'function') showToast("Voucher code copied to clipboard!");

        setTimeout(() => {
            if (label) label.textContent = 'Copy Voucher Code';
            if (icon) icon.className = 'fa-solid fa-copy';
            if (btn) {
                btn.style.background = '#ffffff';
                btn.style.borderColor = '#bfdbfe';
                btn.style.color = '#1e3a8a';
            }
        }, 2500);
    };

    const showCopiedFallback = () => {
        if (typeof showToast === 'function') showToast("Voucher Code: " + code);
    };

    if (typeof window.copyToClipboard === 'function') {
        window.copyToClipboard(code, showCopiedSuccess, showCopiedFallback);
    } else if (navigator.clipboard && window.isSecureContext && typeof navigator.clipboard.writeText === 'function') {
        navigator.clipboard.writeText(code)
            .then(showCopiedSuccess)
            .catch(() => localFallbackCopy(code, showCopiedSuccess, showCopiedFallback));
    } else {
        localFallbackCopy(code, showCopiedSuccess, showCopiedFallback);
    }
}

function localFallbackCopy(text, onSuccess, onError) {
    let ta = null;
    try {
        ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '0';
        ta.style.left = '-9999px';
        ta.style.width = '2em';
        ta.style.height = '2em';
        ta.style.opacity = '0.01';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        ta.setSelectionRange(0, ta.value.length);
        const ok = document.execCommand('copy');
        document.body.removeChild(ta);
        ta = null;
        if (ok) {
            if (typeof onSuccess === 'function') onSuccess();
        } else {
            if (typeof onError === 'function') onError();
        }
    } catch(e) {
        if (ta && ta.parentNode) ta.parentNode.removeChild(ta);
        if (typeof onError === 'function') onError();
    }
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

    const claimed = getClaimedVouchers();
    const availableVouchers = vouchersData.filter(v => !claimed.includes(v.id) && !isVoucherRedeemed(v));

    // Dynamic counts per category
    const catCounts = {};
    availableVouchers.forEach(v => {
        if (v.category) {
            const c = v.category.trim();
            catCounts[c] = (catCounts[c] || 0) + 1;
        }
    });

    const activeClaimed = vouchersData.filter(v => claimed.includes(v.id) && !isVoucherRedeemed(v));
    const historyClaimed = vouchersData.filter(v => claimed.includes(v.id) && isVoucherRedeemed(v));

    const currentVal = activeCategory || (catSelect ? catSelect.value : 'All') || 'All';

    // Compile ordered list of clean categories (NO icons / emojis)
    const categoriesList = [
        { value: 'All', label: 'All Deals', count: availableVouchers.length }
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

    const mabanagCount = availableVouchers.filter(v => v.is_mabanag || (v.partner && v.partner.toLowerCase().includes('mabanag'))).length;
    categoriesList.push({
        value: 'Mabanag Hall',
        label: 'Mabanag Hall Deals',
        count: mabanagCount
    });

    const upcomingCount = availableVouchers.filter(v => (v.is_upcoming || (v.status && v.status.toLowerCase() === 'upcoming')) && !v.is_expired).length;
    categoriesList.push({
        value: 'Upcoming',
        label: 'Upcoming Promotions',
        count: upcomingCount
    });

    categoriesList.push({
        value: 'Claimed',
        label: 'My Vouchers',
        count: activeClaimed.length
    });

    categoriesList.push({
        value: 'History',
        label: 'Voucher History',
        count: historyClaimed.length
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
                <div onclick="event.stopPropagation(); selectFloatingCategory('${c.value.replace(/'/g, "\\'")}')" role="button" tabindex="0" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); ${isSelected ? 'background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; font-weight: 800; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);' : 'background: transparent; color: #1e293b; font-weight: 700;'}" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
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

    const claimed = getClaimedVouchers();
    let baseList = vouchersData;
    if (activeCategory === 'Claimed') {
        baseList = vouchersData.filter(v => claimed.includes(v.id) && !isVoucherRedeemed(v));
    } else if (activeCategory === 'History') {
        baseList = vouchersData.filter(v => claimed.includes(v.id) && isVoucherRedeemed(v));
    } else {
        baseList = vouchersData.filter(v => !claimed.includes(v.id) && !isVoucherRedeemed(v));
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
            <div onclick="event.stopPropagation(); selectFloatingMunicipality('${m.value.replace(/'/g, "\\'")}')" role="button" tabindex="0" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 12px; cursor: pointer; user-select: none; -webkit-tap-highlight-color: transparent; touch-action: manipulation; transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1); ${isSelected ? 'background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; color: #ffffff !important; font-weight: 800; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);' : 'background: transparent; color: #1e293b; font-weight: 700;'}" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
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

function toggleFloatingCategoryDropdown(forceClose = false) {
    const menu = document.getElementById('floating-cat-menu');
    const chevron = document.getElementById('floating-cat-chevron');
    const trigger = document.getElementById('floating-cat-trigger');
    const wrapper = document.getElementById('floating-cat-wrapper');
    if (!menu) return;

    if (forceClose === true || isCatDropdownOpen) {
        closeFloatingCategory();
    } else {
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

function selectFloatingCategory(catValue) {
    closeFloatingCategory();
    filterDiscounts(catValue);
}

function toggleFloatingMunicipalityDropdown(forceClose = false) {
    const menu = document.getElementById('floating-muni-menu');
    const chevron = document.getElementById('floating-muni-chevron');
    const trigger = document.getElementById('floating-muni-trigger');
    const wrapper = document.getElementById('floating-muni-wrapper');
    if (!menu) return;

    if (forceClose === true || isMuniDropdownOpen) {
        closeFloatingMunicipality();
    } else {
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

function selectFloatingMunicipality(muniValue) {
    closeFloatingMunicipality();
    filterMunicipality(muniValue);
}

// Click outside listener to dismiss floating dropdowns
document.addEventListener('click', function(e) {
    const catWrapper = document.getElementById('floating-cat-wrapper');
    if (isCatDropdownOpen && catWrapper && !catWrapper.contains(e.target)) {
        closeFloatingCategory();
    }
    const muniWrapper = document.getElementById('floating-muni-wrapper');
    if (isMuniDropdownOpen && muniWrapper && !muniWrapper.contains(e.target)) {
        closeFloatingMunicipality();
    }
});

// Bind explicit click event listeners to trigger elements
const catTriggerEl = document.getElementById('floating-cat-trigger');
if (catTriggerEl) {
    catTriggerEl.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleFloatingCategoryDropdown();
    });
}
const muniTriggerEl = document.getElementById('floating-muni-trigger');
if (muniTriggerEl) {
    muniTriggerEl.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleFloatingMunicipalityDropdown();
    });
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
window.toggleFloatingCategoryDropdown = toggleFloatingCategoryDropdown;
window.selectFloatingCategory = selectFloatingCategory;
window.toggleFloatingMunicipalityDropdown = toggleFloatingMunicipalityDropdown;
window.selectFloatingMunicipality = selectFloatingMunicipality;
window.closeFloatingCategory = closeFloatingCategory;
window.closeFloatingMunicipality = closeFloatingMunicipality;
window.populateCategoryDropdown = populateCategoryDropdown;
window.populateMunicipalityDropdown = populateMunicipalityDropdown;

// Synchronously populate default dropdown items right away
populateCategoryDropdown();
populateMunicipalityDropdown();

fetchLiveDatabaseVouchers();
fetchUserPointsAndRedemptions();
})();
</script>
