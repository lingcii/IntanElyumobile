<?php
$pageTitle = 'Discounts & Vouchers';
$backRoute = 'profile';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in">
    <!-- Offline status badge -->
    <div id="discount-offline-badge" style="display: none; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 7px 12px; border-radius: 12px; font-size: 11px; font-weight: 700; margin-bottom: 14px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
        <i class="fa-solid fa-cloud-arrow-down" style="margin-right: 5px;"></i> Browsing Offline Vouchers (Cached)
    </div>

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
            <input type="text" id="voucher-search-input" placeholder="Search vouchers, merchants, or towns..." oninput="handleVoucherSearch(this.value)"
                style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 38px; border-radius: 14px; border: 1.5px solid #e2e8f0; font-size: 13px; font-weight: 600; outline: none !important; -webkit-tap-highlight-color: transparent !important; box-shadow: none !important; -webkit-box-shadow: none !important; background: #f8fafc; color: #1e293b; transition: all 0.2s;"
                onfocus="this.style.outline='none'; this.style.boxShadow='none'; this.style.borderColor='#1e3a8a';"
                onblur="this.style.outline='none'; this.style.boxShadow='none'; this.style.borderColor='#e2e8f0';">
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

            <!-- Unusable Status Notice (Expired, Out of stock, Upcoming, etc.) -->
            <div id="modal-status-notice" style="display: none; border-radius: 14px; padding: 11px 14px; margin-bottom: 12px; text-align: left;">
                <div id="modal-status-notice-title" style="display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 800;"></div>
                <p id="modal-status-notice-desc" style="margin: 3px 0 0 0; font-size: 11px; line-height: 1.4;"></p>
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
            <style>
                @keyframes qrStampSlam {
                    0% { opacity: 0; transform: scale(2.4) rotate(-22deg); }
                    70% { opacity: 1; transform: scale(0.92) rotate(-12deg); }
                    100% { opacity: 1; transform: scale(1) rotate(-14deg); }
                }
            </style>
            <div id="modal-claimed-box" style="display: none; background: #eff6ff !important; border: 1.5px dashed #bfdbfe; border-radius: 18px; padding: 16px; margin-bottom: 14px; text-align: center; position: relative; overflow: hidden;">
                <div id="modal-qr-stamp-overlay" style="display: none; position: absolute; inset: 0; align-items: center; justify-content: center; pointer-events: none; z-index: 20;">
                    <div id="modal-qr-stamp-badge" style="border: 4px double #dc2626; color: #dc2626; background: rgba(255,255,255,0.95); padding: 10px 26px; border-radius: 14px; font-weight: 900; font-size: 22px; letter-spacing: 3px; text-transform: uppercase; transform: rotate(-14deg); box-shadow: 0 8px 26px rgba(220,38,38,0.28); display: flex; flex-direction: column; align-items: center; line-height: 1.15;">
                        <span style="font-size: 10px; letter-spacing: 2px; font-weight: 800; color: #b91c1c; margin-bottom: 3px;"><i class="fa-solid fa-certificate"></i> VERIFIED</span>
                        <span>REDEEMED</span>
                    </div>
                </div>

                <!-- Dual Countdown Timers (Voucher Expiration & 20-Min QR Validity) -->
                <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px;">
                    <!-- 1. Voucher Expiration Countdown -->
                    <div id="modal-voucher-timer-box" style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(30,58,138,0.05);">
                        <span style="font-size: 11px; font-weight: 800; color: #1e3a8a; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-regular fa-clock" style="color: #2563eb; font-size: 12px;"></i> Voucher Expiration:
                        </span>
                        <span id="modal-voucher-countdown" style="font-size: 12px; font-weight: 900; color: #dc2626; font-family: monospace; letter-spacing: 0.5px;">--:--</span>
                    </div>

                    <!-- 2. QR Code 20-Minute Automatic Refresh Timer -->
                    <div id="modal-qr-timer-box" style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 6px rgba(30,58,138,0.05);">
                        <span style="font-size: 11px; font-weight: 800; color: #0284c7; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-arrows-rotate" style="color: #0284c7; font-size: 11px;"></i> QR Code Valid:
                        </span>
                        <span id="modal-qr-countdown" style="font-size: 12px; font-weight: 900; color: #0284c7; font-family: monospace; letter-spacing: 0.5px;">20m 00s</span>
                    </div>
                </div>

                <div style="background: #ffffff; border-radius: 14px; padding: 10px; width: 150px; height: 150px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; position: relative; overflow: hidden;">
                    <img id="modal-qr-img" src="" alt="Voucher QR Code" style="width: 100%; height: 100%; object-fit: contain; transition: filter 0.3s ease, opacity 0.3s ease;" onerror="this.style.display='none'">
                </div>
                
                <!-- Verified Digital Pass Badge -->
                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 100px; font-size: 10.5px; font-weight: 800; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; text-transform: uppercase; letter-spacing: 0.6px;">
                        <i class="fa-solid fa-shield-halved" style="color: #10b981; font-size: 11px;"></i> Verified Digital QR Pass
                    </span>
                </div>

                <!-- Clickable Masked Code Container (Tap to Unmask/Mask) -->
                <div id="modal-qr-mask-container" onclick="window.toggleDiscountVoucherCodeMask()" role="button" tabindex="0" title="Tap to reveal or hide voucher code"
                    style="background: #ffffff; border: 1.5px solid #bfdbfe; border-radius: 14px; padding: 9px 12px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(30,58,138,0.06); display: flex; align-items: center; justify-content: space-between; gap: 8px; cursor: pointer; user-select: none; transition: all 0.2s ease;"
                    onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <div style="text-align: left; min-width: 0; flex: 1;">
                        <div style="font-size: 8.5px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.2px; display: flex; align-items: center; gap: 4px; margin-bottom: 2px; white-space: nowrap;">
                            <i class="fa-solid fa-key" style="color: #0284c7; font-size: 9px;"></i>
                            <span id="modal-qr-mask-label">Voucher Code (Tap to reveal)</span>
                        </div>
                        <div id="modal-qr-mask-code" style="font-size: 13.5px; font-weight: 900; color: #1e3a8a; letter-spacing: 2px; font-family: monospace; word-break: break-all;">
                            ••••••••••••
                        </div>
                    </div>
                    <div id="modal-qr-mask-btn" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; transition: all 0.15s ease;">
                        <i class="fa-solid fa-eye" id="modal-qr-mask-icon" style="color: #1e3a8a;"></i>
                    </div>
                </div>

                <p id="modal-qr-notice" style="margin: 8px 0 0 0; font-size: 11.5px; color: #64748b; line-height: 1.45;">
                    Present this QR code directly to merchant or staff at checkout for instant scanning.
                </p>

                <div id="modal-live-status-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; background: #e0f2fe; color: #0369a1; margin-top: 10px; transition: all 0.3s ease;">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Ready for scan at checkout
                </div>
            </div>
        </div>

        <!-- Locked Bottom Footer Banner (Royal Blue) -->
        <div id="modal-footer-banner" style="background: linear-gradient(180deg, #1e3a8a 0%, #193375 100%) !important; flex-shrink: 0; padding: 14px 18px; border-top: none !important; display: flex; flex-direction: column; gap: 8px; border: none !important; outline: none !important;">
            <!-- Unclaimed: Single Claim Button -->
            <button id="modal-redeem-btn" onclick="handleModalRedeem()" style="width: 100%; padding: 12px; border: none !important; outline: none !important; border-radius: 12px; background: #ffffff !important; color: #1e3a8a !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18) !important; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                <i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Claim for 100 Points</span>
            </button>

            <!-- Claimed State: Two Dedicated Buttons (Use and Cancel) -->
            <div id="modal-claimed-actions" style="display: none; gap: 10px; width: 100%;">
                <button id="modal-btn-use" onclick="window.handleVoucherUse()" style="flex: 1; padding: 12px; border: none !important; outline: none !important; border-radius: 12px; background: #10b981 !important; color: #ffffff !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35) !important; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-check" style="color: #ffffff !important;"></i> Use
                </button>
                <button id="modal-btn-cancel" onclick="window.handleVoucherCancel()" style="flex: 1; padding: 12px; border: 1.5px solid #fca5a5 !important; outline: none !important; border-radius: 12px; background: #ffffff !important; color: #dc2626 !important; font-size: 13.5px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.15) !important; transition: transform 0.15s ease;" onpointerdown="this.style.transform='scale(0.98)'" onpointerup="this.style.transform='scale(1)'">
                    <i class="fa-solid fa-xmark" style="color: #dc2626 !important;"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
const VOUCHERS_CACHE_KEY = 'intan_elyu_cached_vouchers';
const VOUCHERS_CACHE_TTL = 900000; // 15 mins
const VOUCHERS_SYNC_INTERVAL_MS = 1500;

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
let voucherFetchInFlight = false;
let lastVouchersCatalogSignature = '';

function getDiscountBaseUrl() {
    return (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
}

function getDiscountAuthToken() {
    return localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
}

// ── Guest Mode detection (guests must never see a previous account's cached data) ──
function isDiscountGuest() {
    if (typeof window.isGuestUser === 'function') return window.isGuestUser();
    const tok = getDiscountAuthToken();
    return !tok || tok === 'guest_token' || tok === 'offline_guest_token' ||
        tok.startsWith('guest_') || tok.startsWith('offline_') ||
        localStorage.getItem('intan_elyu_guest') === 'true' ||
        localStorage.getItem('intan_elyu_offline_guest') === 'true';
}

function renderDiscountGuestHero() {
    userPointsBalance = 0;
    window._touristRedemptions = [];
    const nameEl = document.getElementById('discount-user-name');
    if (nameEl) nameEl.textContent = 'Guest Explorer';
    const imgEl = document.getElementById('discount-user-avatar');
    if (imgEl) imgEl.src = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/logo/LOGO.png';
    const ptsEl = document.getElementById('discount-user-pts');
    if (ptsEl) {
        ptsEl.textContent = 'Login now to get your vouchers';
        ptsEl.style.fontSize = '11.5px';
        ptsEl.style.lineHeight = '1.2';
        ptsEl.style.whiteSpace = 'normal';
        ptsEl.style.textAlign = 'left';
        const pill = ptsEl.parentElement;
        if (pill && !pill.dataset.guestBound) {
            pill.dataset.guestBound = '1';
            pill.id = 'discount-guest-login-pill';
            pill.style.cursor = 'pointer';
            pill.style.flexShrink = '1';
            pill.style.maxWidth = '60%';
            pill.style.borderRadius = '14px';
            pill.setAttribute('role', 'button');
            pill.addEventListener('click', () => {
                if (typeof navigateTo === 'function') navigateTo('auth');
            });
        }
    }
}

// ── Instant Synchronous Profile Hydration (0ms, Zero Latency) ──
function hydrateDiscountUser() {
    if (isDiscountGuest()) {
        renderDiscountGuestHero();
        return;
    }
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
    const imageUrl = (path) => window.getFullImageUrl ? window.getFullImageUrl(path) : path;
    if (!v) return imageUrl('logo/LUPTO.png');
    if (v.image && typeof v.image === 'string' && v.image.trim() !== '') {
        return imageUrl(v.image.trim());
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
            return imageUrl(`logo/${logo}`);
        }
    }

    return imageUrl('logo/LUPTO.png');
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
    // Guests share the device with previous accounts: never read/write another user's voucher cache
    if (isDiscountGuest()) return null;
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (u && (u.id || u.user_id)) return String(u.id || u.user_id);
    } catch(e) {}
    const tok = getDiscountAuthToken();
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

    // 1. If this object is already a specific redemption item with redemptionStatus or status:
    const rStatus = (v.redemptionStatus || v.status || '').toLowerCase().trim();
    if (rStatus === 'claimed' || rStatus === 'active') return false; // Strictly active claimed, NOT redeemed!
    if (['redeemed', 'used', 'completed'].includes(rStatus)) return true;

    // 2. If it has a specific unique voucher code (from a redemption):
    const vcode = (v.code || v.voucher_code || '').toUpperCase().trim();
    if (vcode && Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const matchCode = window._touristRedemptions.find(r => {
            const rCode = (r.voucher_code || r.code || '').toUpperCase().trim();
            return rCode && rCode === vcode;
        });
        if (matchCode) {
            return ['redeemed', 'used', 'completed'].includes((matchCode.status || '').toLowerCase());
        }
    }

    // 3. For catalog templates in vouchersData (deals not yet individually claimed):
    const vid = String(v.dbId || v.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
    if (vid && Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const userRedemptionsForThisVoucher = window._touristRedemptions.filter(r => {
            const rVid = String(r.voucher_id || r.dbId || '').replace(/^db_/, '');
            return rVid === vid;
        });
        if (userRedemptionsForThisVoucher.length > 0) {
            // If the user has any active unredeemed claim, it is NOT redeemed
            const hasActiveClaim = userRedemptionsForThisVoucher.some(r => {
                const s = (r.status || '').toLowerCase();
                return s === 'claimed' || s === 'active';
            });
            if (hasActiveClaim) return false;
            // Only considered redeemed if all instances are redeemed
            return userRedemptionsForThisVoucher.every(r => ['redeemed', 'used', 'completed'].includes((r.status || '').toLowerCase()));
        }
    }

    return false;
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

    // 1. Only redemption records may use "active" to mean already claimed.
    // Catalog vouchers also use status=active to mean available for claiming.
    const isRedemptionItem = v.redemptionStatus !== undefined || v.redemptionId !== undefined || String(v.id || '').startsWith('rdm_');
    const rStatus = (v.redemptionStatus || v.status || '').toLowerCase().trim();
    if (isRedemptionItem) {
        if (['redeemed', 'used', 'completed'].includes(rStatus)) return false;
        if (rStatus === 'claimed' || rStatus === 'active') return true;
    }

    // 2. If it has a specific unique voucher code:
    const vcode = (v.code || v.voucher_code || '').toUpperCase().trim();
    if (vcode && Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const matchCode = window._touristRedemptions.find(r => {
            const rCode = (r.voucher_code || r.code || '').toUpperCase().trim();
            return rCode && rCode === vcode;
        });
        if (matchCode) {
            const mst = (matchCode.status || '').toLowerCase();
            return mst === 'claimed' || mst === 'active';
        }
    }

    // 3. For catalog templates in vouchersData:
    const vid = String(v.dbId || v.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
    if (vid && Array.isArray(window._touristRedemptions) && window._touristRedemptions.length > 0) {
        const userRedemptionsForThisVoucher = window._touristRedemptions.filter(r => {
            const rVid = String(r.voucher_id || r.dbId || '').replace(/^db_/, '');
            return rVid === vid;
        });
        if (userRedemptionsForThisVoucher.length > 0) {
            // Check if user currently has an active unredeemed claim
            return userRedemptionsForThisVoucher.some(r => {
                const mst = (r.status || '').toLowerCase();
                return mst === 'claimed' || mst === 'active';
            });
        }
    }

    const claimed = getClaimedVouchers();
    const cleanId = String(v.id || '').replace(/^db_/, '').toLowerCase().trim();
    const cleanDbId = String(v.dbId || '').replace(/^db_/, '').toLowerCase().trim();
    const titleStr = String(v.title || v.voucher_name || '').toLowerCase().trim();
    const codeStr = String(v.code || v.voucher_code || '').toUpperCase().trim();

    if (claimed.some(k => {
        if (!k) return false;
        const sk = String(k).trim();
        const lk = sk.toLowerCase();
        const uk = sk.toUpperCase();
        if (cleanId && (lk === cleanId || lk === 'db_' + cleanId)) return true;
        if (cleanDbId && (lk === cleanDbId || lk === 'db_' + cleanDbId)) return true;
        if (codeStr && (uk === codeStr || uk.startsWith(codeStr + '-'))) return true;
        if (titleStr && lk === titleStr) return true;
        return false;
    })) return true;

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

        const status = (r.status || 'claimed').toLowerCase().trim();
        const isUsed = ['redeemed', 'used', 'completed'].includes(status);
        const isCancelled = status === 'cancelled';
        const vExp = r.voucher_expires_at || r.expires_at || (match ? match.expires : null);
        const isTimeExpired = vExp && (new Date(vExp).getTime() < Date.now());
        const isExpired = (status === 'expired') || (!isUsed && !isCancelled && isTimeExpired);
        const isClaimed = !isUsed && !isCancelled && !isExpired;
        const finalStatus = isUsed ? 'used' : (isCancelled ? 'cancelled' : (isExpired ? 'expired' : 'claimed'));

        const title = r.type || (match ? match.title : 'Reward Voucher');
        const partner = r.partner_establishment || (match ? match.partner : 'LUPTO Tourism Partner');
        const location = match ? match.location : 'La Union';
        const category = r.category || (match ? match.category : 'Food & Dining');
        const pointsCost = r.points_cost || (match ? match.pointsCost : 100);
        const description = r.description || (match ? match.description : 'Present this voucher code at merchant checkout.');
        const badge = isUsed ? 'USED' : (isCancelled ? 'CANCELLED' : (isExpired ? 'EXPIRED' : 'CLAIMED'));
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
            qr_token: r.qr_token || null,
            image: image,
            valid_from: match ? match.valid_from : null,
            valid_from_formatted: match ? match.valid_from_formatted : null,
            expiration_type: match ? match.expiration_type : 'date',
            is_no_expiration: match ? match.is_no_expiration : false,
            expires: vExp,
            voucher_expires_at: vExp,
            qr_expires_at: r.qr_expires_at || null,
            expires_formatted: isUsed ? 'Redeemed & Recorded' : (isCancelled ? 'Claim Cancelled' : (isExpired ? 'Expired' : (match ? match.expires_formatted : 'Claimed & Usable'))),
            is_expired: isExpired,
            is_upcoming: false,
            is_out_of_stock: false,
            id_needed: match ? match.id_needed : false,
            terms_and_conditions: r.terms_and_conditions || (match ? match.terms_and_conditions : null),
            status: finalStatus,
            redemptionStatus: finalStatus,
            isRedeemed: isUsed,
            isUsed: isUsed,
            isCancelled: isCancelled,
            isClaimed: isClaimed,
            claimed_at: r.claimed_at || r.created_at,
            redeemed_at: r.redeemed_at,
            redeemedAt: r.redeemed_at,
            cancelled_at: r.cancelled_at,
            createdAt: r.created_at,
            description: description,
            is_mabanag: match ? match.is_mabanag : (partner.toLowerCase().includes('mabanag'))
        };
    });
}

function updateClaimedBadge() {
    const redemptions = getUserRedemptionItems();
    const activeClaimed = redemptions.filter(r => r.isClaimed);
    const historyClaimed = redemptions.filter(r => r.isUsed);

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
    let claimedSet = (window._hasFetchedRedemptions || redemptions.length > 0) ? new Set() : new Set(getClaimedVouchers());

    if (redemptions.length > 0) {
        redemptions.forEach(v => {
            const vId = v.voucher_id ? String(v.voucher_id) : (v.dbId ? String(v.dbId) : null);
            const vCode = (v.voucher_code || v.code || '').toUpperCase().trim();
            const status = (v.status || '').toLowerCase().trim();
            const title = String(v.type || v.title || v.voucher_name || '').toLowerCase().trim();

            if (status === 'claimed' || status === 'active' || ['redeemed', 'used', 'completed'].includes(status)) {
                if (vId) {
                    const clean = vId.replace(/^db_/, '');
                    claimedSet.add(clean);
                    claimedSet.add('db_' + clean);
                }
                if (vCode) {
                    claimedSet.add(vCode);
                    const prefix = vCode.split('-')[0];
                    if (prefix) claimedSet.add(prefix);
                }
                if (title) {
                    claimedSet.add(title);
                }
            }
        });
    }

    setClaimedVouchers(Array.from(claimedSet));
}

async function fetchUserPointsAndRedemptions() {
    const token = getDiscountAuthToken();
    if (isDiscountGuest()) {
        renderDiscountGuestHero();
        syncClaimedVouchersWithData();
        updateClaimedBadge();
        populateStatusDropdown();
        populateCategoryDropdown();
        renderDiscounts();
        return;
    }
    if (!token) {
        const ptsBadge = document.getElementById('discount-user-pts');
        if (ptsBadge) ptsBadge.textContent = '0 Points';
        return;
    }

    // Pre-hydrate balance immediately from auth_user & cached points_balance_
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        if (u.points !== undefined || u.xp !== undefined) {
            userPointsBalance = (u.points !== undefined) ? u.points : (u.xp ?? 0);
            const ptsBadge = document.getElementById('discount-user-pts');
            if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;
        }
        const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
        const cachedRaw = localStorage.getItem(ptsCacheKey);
        if (cachedRaw) {
            const cachedData = JSON.parse(cachedRaw);
            const dataObj = cachedData.data || cachedData;
            if (Array.isArray(dataObj.vouchers)) {
                window._touristRedemptions = dataObj.vouchers;
                window._hasFetchedRedemptions = true;
            }
        }
    } catch (e) { }

    syncClaimedVouchersWithData();
    updateClaimedBadge();
    populateStatusDropdown();
    populateCategoryDropdown();
    renderDiscounts();

    try {
        const baseUrl = getDiscountBaseUrl();
        const res = await fetch(baseUrl + '/api/tourist/points/balance', {
            cache: 'no-store',
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
                const rawPts = Number(data.points !== undefined ? data.points : (data.xp ?? 0)) || 0;
                const rawXp = Number(data.xp !== undefined ? data.xp : (data.earned_total ?? rawPts)) || 0;
                const redeemedTotal = Number(data.redeemed_total || 0) || window._touristRedemptions.reduce((sum, v) => sum + (Number(v && (v.points_cost || v.pointsCost) || 0) || 0), 0);
                userPointsBalance = (redeemedTotal > 0 && rawPts === rawXp && rawXp > 0)
                    ? Math.max(0, rawXp - redeemedTotal)
                    : rawPts;
                data.points = userPointsBalance;
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

                try {
                    ['auth_user', 'auth_user_backup', 'intan_elyu_user'].forEach(key => {
                        const raw = localStorage.getItem(key);
                        if (raw) {
                            const u = JSON.parse(raw);
                            if (u && typeof u === 'object') {
                                u.points = userPointsBalance;
                                localStorage.setItem(key, JSON.stringify(u));
                            }
                        }
                    });
                    const profKey = 'profile_data_' + token.substring(0, 10);
                    const profRaw = localStorage.getItem(profKey);
                    if (profRaw) {
                        const profObj = JSON.parse(profRaw);
                        if (profObj && typeof profObj === 'object') {
                            if (profObj.user && typeof profObj.user === 'object') profObj.user.points = userPointsBalance;
                            if (profObj.data && profObj.data.user && typeof profObj.data.user === 'object') profObj.data.user.points = userPointsBalance;
                            localStorage.setItem(profKey, JSON.stringify(profObj));
                        }
                    }
                    const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
                    localStorage.setItem(ptsCacheKey, JSON.stringify({ data: data, timestamp: Date.now() }));
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
        filtered = getUserRedemptionItems().filter(r => r.isClaimed);
    } else if (activeStatus === 'Used' || activeStatus === 'Redeemed') {
        filtered = getUserRedemptionItems().filter(r => r.isUsed);
    } else if (activeStatus === 'Cancelled') {
        filtered = getUserRedemptionItems().filter(r => r.isCancelled);
    } else if (activeStatus === 'Upcoming') {
        filtered = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v));
    } else if (activeStatus === 'Expired') {
        filtered = getUserRedemptionItems().filter(r => r.isExpired).concat(vouchersData.filter(v => isVoucherExpired(v)));
    } else {
        // 'All' / Available: Exclude already claimed, redeemed, expired, or upcoming deals from available catalog browsing
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
    if (activeStatus !== 'Claimed' && activeStatus !== 'Redeemed' && activeStatus !== 'Used' && activeStatus !== 'Cancelled' && activeCategory !== 'Claimed' && activeCategory !== 'History') {
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
            msg = 'You have no active claimed vouchers right now. Claim reward deals from "Available Vouchers" using your Explorer Points!';
        } else if (activeStatus === 'Used' || activeStatus === 'Redeemed') {
            msg = 'No used voucher history yet. Vouchers redeemed or scanned at checkout will appear here.';
        } else if (activeStatus === 'Cancelled') {
            msg = 'No cancelled vouchers found.';
        } else if (activeStatus === 'Upcoming') {
            msg = 'No upcoming vouchers scheduled right now. Check back soon for exciting new promotions and deals opening soon!';
        } else if (activeStatus === 'Expired') {
            msg = 'No expired vouchers found. All promotions are currently active or upcoming!';
        } else if (activeStatus === 'All' && getUserRedemptionItems().length > 0) {
            msg = '🎉 You have claimed all available deals! Select "Claimed Vouchers" in Voucher Status above to view your claimed discounts.';
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
        const isClaimed = v.isClaimed !== undefined ? v.isClaimed : (isVoucherClaimed(v) && !isVoucherRedeemed(v));
        const isUsed = v.isUsed !== undefined ? v.isUsed : isVoucherRedeemed(v);
        const isRedeemed = isUsed;
        const isCancelled = v.isCancelled !== undefined ? v.isCancelled : (v.status === 'cancelled');
        const imgUrl = getVoucherImageUrl(v);
        const expiryInfo = getExpiryInfo(v.expires, v.is_expired, v.is_upcoming, v.valid_from, v.valid_from_formatted, v.is_no_expiration, v.expiration_type);
        const isCardExpired = v.isExpired !== undefined ? v.isExpired : (isVoucherExpired(v) || expiryInfo.isExpired);
        const isCardUpcoming = !isCardExpired && (v.is_upcoming || expiryInfo.isUpcoming);
        const isOutOfStock = isVoucherFullyClaimed(v);

        let actionBtnHtml = '';
        if (isUsed) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #dc2626 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display:flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3) !important;">
                    <i class="fa-solid fa-check-double"></i> Used
                </button>
            `;
        } else if (isCancelled) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #64748b !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display:flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3) !important;">
                    <i class="fa-solid fa-xmark"></i> Cancelled
                </button>
            `;
        } else if (isClaimed) {
            actionBtnHtml = `
                <button onclick="openVoucherModal('${v.id}')" style="background: #10b981 !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display:flex; align-items:center; gap:4px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;">
                    <i class="fa-solid fa-qrcode"></i> Claimed
                </button>
            `;
        } else if (isCardExpired) {
            actionBtnHtml = `
                <button onclick="event.stopPropagation(); openVoucherModal('${v.id}')" style="background: #64748b !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3) !important;">
                    <i class="fa-solid fa-lock" style="margin-right:2px;"></i> Expired
                </button>
            `;
        } else if (isOutOfStock) {
            actionBtnHtml = `
                <button onclick="event.stopPropagation(); openVoucherModal('${v.id}')" style="background: #ea580c !important; border: none !important; color: #ffffff !important; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3) !important;">
                    <i class="fa-solid fa-ban" style="margin-right:2px;"></i> Fully Claimed
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
                    Claim
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

        const topBadgeBg = isUsed ? '#dc2626' : (isCancelled ? '#64748b' : (isClaimed ? '#10b981' : '#ffffff'));
        const topBadgeColor = (isUsed || isCancelled || isClaimed) ? '#ffffff' : '#203f8d';
        const topBadgeText = isUsed ? 'USED' : (isCancelled ? 'CANCELLED' : (isClaimed ? 'CLAIMED' : v.badge));

        html += `
        <div class="voucher-card" onclick="openVoucherModal('${v.id}')" style="cursor: pointer;">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; gap: 8px;">
                    <div style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.14);">
                        <img src="${imgUrl}" alt="${v.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;" onerror="this.onerror=null; this.src=window.getFullImageUrl('logo/LUPTO.png');">
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
                    <span style="font-size: 10px; font-weight: 700; color: #ffffff;">${isUsed ? 'Redeemed & Recorded' : (isCancelled ? 'Claim Cancelled' : (isClaimed ? 'Claimed & Ready to Use' : expiryInfo.label))}</span>
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
        if (!navigator.onLine) return; // Ambient check: pause polling while offline
        try {
            const baseUrl = getDiscountBaseUrl();
            const res = await fetch(`${baseUrl}/api/public/redemptions/${encodeURIComponent(code)}/status`, {
                cache: 'no-store',
                headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.status === 'success' && data.is_redeemed) {
                    const statusBadge = document.getElementById('modal-live-status-badge');
                    const qrImg = document.getElementById('modal-qr-img');
                    const stampOverlay = document.getElementById('modal-qr-stamp-overlay');
                    const stampBadge = document.getElementById('modal-qr-stamp-badge');
                    if (statusBadge) {
                        statusBadge.innerHTML = `<i class="fa-solid fa-circle-check" style="color:#15803d; font-size:13px;"></i> Redeemed & Verified at ${data.redeemed_by_partner || 'Partner Merchant'}!`;
                        statusBadge.style.background = '#dcfce7';
                        statusBadge.style.color = '#15803d';
                        statusBadge.style.border = '1px solid #86efac';
                    }
                    if (qrImg) {
                        qrImg.style.filter = 'grayscale(1)';
                        qrImg.style.opacity = '0.3';
                    }
                    if (stampOverlay) {
                        stampOverlay.style.display = 'flex';
                    }
                    if (stampBadge) {
                        stampBadge.style.animation = 'none';
                        void stampBadge.offsetWidth;
                        stampBadge.style.animation = 'qrStampSlam 0.45s cubic-bezier(0.22, 1, 0.36, 1) forwards';
                    }
                    const qrNotice = document.getElementById('modal-qr-notice');
                    if (qrNotice) qrNotice.style.display = 'none';
                    if (window.confetti) {
                        window.confetti({ particleCount: 50, spread: 60, origin: { y: 0.6 } });
                    }
                    stopLiveRedemptionSync();

                    // Move the voucher out of Active immediately, then reconcile
                    // against the authenticated balance endpoint in the background.
                    const normalizedCode = String(code).toUpperCase().trim();
                    const localRedemption = (window._touristRedemptions || []).find(r => {
                        return String(r.voucher_code || r.code || '').toUpperCase().trim() === normalizedCode;
                    });
                    if (localRedemption) {
                        localRedemption.status = 'redeemed';
                        localRedemption.redeemed_at = data.redeemed_at || new Date().toISOString();
                    }
                    syncClaimedVouchersWithData();
                    updateClaimedBadge();
                    populateStatusDropdown();
                    renderDiscounts();
                    fetchUserPointsAndRedemptions();
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
    let item = null;
    if (typeof id === 'string' && id.startsWith('rdm_') && typeof getUserRedemptionItems === 'function') {
        item = getUserRedemptionItems().find(v => v.id === id);
    }
    if (!item) {
        item = vouchersData.find(v => v.id === id);
    }
    if (!item && typeof getUserRedemptionItems === 'function') {
        item = getUserRedemptionItems().find(v => v.id === id);
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
        iconWrap.innerHTML = `<img src="${modalImg}" alt="${item.title}" style="width: 100%; height: 100%; object-fit: contain; padding: 6px;" onerror="this.onerror=null; this.src=window.getFullImageUrl('logo/LUPTO.png');">`;
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

    // Dynamic status notice banner in modal
    const statusNotice = document.getElementById('modal-status-notice');
    const statusNoticeTitle = document.getElementById('modal-status-notice-title');
    const statusNoticeDesc = document.getElementById('modal-status-notice-desc');

    if (statusNotice && statusNoticeTitle && statusNoticeDesc) {
        if (isAlreadyClaimed && isVoucherRedeemed(item)) {
            statusNotice.style.display = 'block';
            statusNotice.style.background = '#fef2f2';
            statusNotice.style.border = '1px solid #fecaca';
            statusNoticeTitle.style.color = '#dc2626';
            statusNoticeTitle.innerHTML = '<i class="fa-solid fa-check-double"></i> Voucher Already Redeemed & Verified';
            statusNoticeDesc.style.color = '#991b1b';
            statusNoticeDesc.textContent = 'This voucher has already been redeemed and verified at merchant checkout. It cannot be used again.';
        } else if (isExpired) {
            statusNotice.style.display = 'block';
            statusNotice.style.background = '#fef2f2';
            statusNotice.style.border = '1px solid #fecaca';
            statusNoticeTitle.style.color = '#dc2626';
            statusNoticeTitle.innerHTML = '<i class="fa-solid fa-lock"></i> Voucher Expired';
            statusNoticeDesc.style.color = '#991b1b';
            statusNoticeDesc.textContent = `This voucher reached its expiration date (${expiryInfo.text || 'validity period'}) and is no longer valid for redemption.`;
        } else if (isOutOfStock) {
            statusNotice.style.display = 'block';
            statusNotice.style.background = '#fff7ed';
            statusNotice.style.border = '1px solid #fed7aa';
            statusNoticeTitle.style.color = '#ea580c';
            statusNoticeTitle.innerHTML = '<i class="fa-solid fa-ban"></i> Out of Stock / Fully Claimed';
            statusNoticeDesc.style.color = '#9a3412';
            statusNoticeDesc.textContent = 'All promotional allocations for this partner voucher have been claimed by other tourists. Please check back later when more vouchers are released.';
        } else if (isUpcoming) {
            statusNotice.style.display = 'block';
            statusNotice.style.background = '#f0f9ff';
            statusNotice.style.border = '1px solid #bae6fd';
            statusNoticeTitle.style.color = '#0284c7';
            statusNoticeTitle.innerHTML = '<i class="fa-regular fa-clock"></i> Upcoming Promotion';
            statusNoticeDesc.style.color = '#0369a1';
            statusNoticeDesc.textContent = `This partner deal begins on ${item.valid_from_formatted || 'its start date'}. Save up your points to claim it when it unlocks!`;
        } else {
            statusNotice.style.display = 'none';
        }
    }

    currentModalItem = item;

    if (isAlreadyClaimed) {
        const claimCode = item.code || 'ELYU-PROMO';
        const activeQrToken = item.qr_token || claimCode;
        const isRedeemedOnWeb = isVoucherRedeemed(item) || item.isUsed || item.status === 'used';
        const isCancelledItem = item.isCancelled || item.status === 'cancelled';
        const isExpiredItem = item.isExpired || item.status === 'expired';
        const claimedActions = document.getElementById('modal-claimed-actions');
        const vTimerBox = document.getElementById('modal-voucher-timer-box');
        const qrTimerBox = document.getElementById('modal-qr-timer-box');

        if (claimedBox) {
            claimedBox.style.display = 'block';
            window._currentDiscountClaimCode = claimCode;
            window._currentActiveQrToken = activeQrToken;
            window._isDiscountCodeUnmasked = false;
            const codeEl = document.getElementById('modal-qr-mask-code');
            const iconEl = document.getElementById('modal-qr-mask-icon');
            const labelEl = document.getElementById('modal-qr-mask-label');
            const maskBtn = document.getElementById('modal-qr-mask-btn');
            if (codeEl) {
                codeEl.textContent = '••••••••••••';
                codeEl.style.letterSpacing = '2px';
                codeEl.style.color = '#1e3a8a';
            }
            if (iconEl) iconEl.className = 'fa-solid fa-eye';
            if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
            if (maskBtn) {
                maskBtn.style.background = '#eff6ff';
                maskBtn.style.borderColor = '#bfdbfe';
            }

            const qrImg = document.getElementById('modal-qr-img');
            const stampOverlay = document.getElementById('modal-qr-stamp-overlay');
            const stampBadge = document.getElementById('modal-qr-stamp-badge');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(activeQrToken)}`;
                qrImg.style.display = 'block';
                if (isRedeemedOnWeb || isCancelledItem || isExpiredItem) {
                    qrImg.style.filter = 'grayscale(1)';
                    qrImg.style.opacity = '0.3';
                } else {
                    qrImg.style.filter = 'none';
                    qrImg.style.opacity = '1';
                }
            }
            if (stampOverlay) {
                stampOverlay.style.display = isRedeemedOnWeb ? 'flex' : 'none';
            }
            if (stampBadge && isRedeemedOnWeb) {
                stampBadge.style.animation = 'none';
            }
            const qrNotice = document.getElementById('modal-qr-notice');
            if (qrNotice) {
                qrNotice.style.display = (isRedeemedOnWeb || isCancelledItem || isExpiredItem) ? 'none' : 'block';
            }
            if (isRedeemedOnWeb && statusBadge) {
                statusBadge.innerHTML = '<i class="fa-solid fa-check-double" style="color:#15803d; font-size:13px;"></i> Voucher Used & Verified';
                statusBadge.style.background = '#dcfce7';
                statusBadge.style.color = '#15803d';
                statusBadge.style.border = '1px solid #86efac';
            } else if (isCancelledItem && statusBadge) {
                statusBadge.innerHTML = '<i class="fa-solid fa-xmark" style="color:#dc2626; font-size:13px;"></i> Voucher Claim Cancelled';
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#dc2626';
                statusBadge.style.border = '1px solid #fca5a5';
            } else if (isExpiredItem && statusBadge) {
                statusBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#dc2626; font-size:13px;"></i> Voucher Expired';
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#dc2626';
                statusBadge.style.border = '1px solid #fca5a5';
            }
        }

        if (redeemBtn) redeemBtn.style.display = 'none';

        if (isRedeemedOnWeb || isCancelledItem || isExpiredItem) {
            stopModalTimers();
            if (vTimerBox) vTimerBox.style.display = 'none';
            if (qrTimerBox) qrTimerBox.style.display = 'none';
            if (claimedActions) claimedActions.style.display = 'none';
            if (footerBanner) footerBanner.style.display = 'none';
        } else {
            // Active Claimed State: Show 20-min QR & Voucher Expiry Dual Timers + Use & Cancel buttons
            if (vTimerBox) vTimerBox.style.display = 'flex';
            if (qrTimerBox) qrTimerBox.style.display = 'flex';
            if (footerBanner) footerBanner.style.display = 'flex';
            if (claimedActions) claimedActions.style.display = 'flex';

            const useBtn = document.getElementById('modal-btn-use');
            if (useBtn) {
                useBtn.disabled = false;
                useBtn.style.opacity = '1';
                useBtn.style.cursor = 'pointer';
            }
            const cancelBtn = document.getElementById('modal-btn-cancel');
            if (cancelBtn) {
                cancelBtn.disabled = false;
                cancelBtn.style.opacity = '1';
                cancelBtn.style.cursor = 'pointer';
            }

            startModalTimers(item);
            startLiveRedemptionSync(claimCode);
        }
    } else {
        stopModalTimers();
        if (claimedBox) claimedBox.style.display = 'none';
        const claimedActions = document.getElementById('modal-claimed-actions');
        if (claimedActions) claimedActions.style.display = 'none';
        if (footerBanner) footerBanner.style.display = 'flex';
        if (redeemBtn) {
            redeemBtn.style.display = 'flex';
            if (isExpired) {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                redeemBtn.style.background = '#ffffff';
                redeemBtn.style.color = '#dc2626';
                redeemBtn.innerHTML = '<i class="fa-solid fa-lock" style="color: #dc2626 !important;"></i> <span id="modal-redeem-btn-label" style="color:#dc2626; font-weight:800;">Voucher Expired (Details)</span>';
            } else if (isOutOfStock) {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                redeemBtn.style.background = '#ffffff';
                redeemBtn.style.color = '#ea580c';
                redeemBtn.innerHTML = '<i class="fa-solid fa-ban" style="color: #ea580c !important;"></i> <span id="modal-redeem-btn-label" style="color:#ea580c; font-weight:800;">Fully Claimed (Details)</span>';
            } else if (isUpcoming) {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                redeemBtn.style.background = '#ffffff';
                redeemBtn.style.color = '#0284c7';
                redeemBtn.innerHTML = `<i class="fa-regular fa-clock" style="color: #0284c7 !important;"></i> <span id="modal-redeem-btn-label" style="color:#0284c7; font-weight:800;">Starts on ${item.valid_from_formatted || 'Soon'}</span>`;
            } else {
                redeemBtn.disabled = false;
                redeemBtn.style.opacity = '1';
                redeemBtn.style.cursor = 'pointer';
                redeemBtn.style.background = '#ffffff';
                redeemBtn.style.color = '#1e3a8a';
                const cost = item.pointsCost || item.required_points || 100;
                redeemBtn.innerHTML = `<i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label" style="color:#1e3a8a; font-weight:900;">Claim for ${cost} Points</span>`;
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
    stopModalTimers();
    currentModalItem = null;
    window._isDiscountCodeUnmasked = false;
    const btn = document.getElementById('modal-redeem-btn');
    if (btn) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.innerHTML = '<i class="fa-solid fa-gift" style="color: #1e3a8a !important;"></i> <span id="modal-redeem-btn-label">Claim Voucher</span>';
    }
    const claimedActions = document.getElementById('modal-claimed-actions');
    if (claimedActions) claimedActions.style.display = 'none';
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

let modalTimerInterval = null;

function formatCountdown(ms) {
    if (ms <= 0) return '00:00';
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    const pad = (n) => String(n).padStart(2, '0');
    if (hours > 0) {
        return `${hours}h ${pad(minutes)}m ${pad(seconds)}s`;
    }
    return `${pad(minutes)}m ${pad(seconds)}s`;
}

function startModalTimers(item) {
    stopModalTimers();
    if (!item) return;

    const vExpRaw = item.voucher_expires_at || item.expires || item.expires_at;
    const qrExpRaw = item.qr_expires_at;
    const code = item.code || item.voucher_code;

    // Voucher expiration timestamp
    let voucherExpTime = vExpRaw ? new Date(vExpRaw).getTime() : (new Date(item.claimed_at || item.createdAt || Date.now()).getTime() + 24 * 3600 * 1000);
    // QR code validity timestamp (20 minutes)
    let qrExpTime = qrExpRaw ? new Date(qrExpRaw).getTime() : (Date.now() + 20 * 60 * 1000);

    const updateTick = () => {
        const now = Date.now();
        const vDiff = voucherExpTime - now;
        const qrDiff = qrExpTime - now;

        const vCountdownEl = document.getElementById('modal-voucher-countdown');
        const qrCountdownEl = document.getElementById('modal-qr-countdown');

        if (vCountdownEl) {
            vCountdownEl.textContent = formatCountdown(vDiff);
        }
        if (qrCountdownEl) {
            qrCountdownEl.textContent = formatCountdown(qrDiff);
        }

        // 1. Voucher Expired when countdown reaches 0
        if (vDiff <= 0) {
            stopModalTimers();
            item.status = 'expired';
            item.redemptionStatus = 'expired';
            item.isExpired = true;
            item.isClaimed = false;

            const useBtn = document.getElementById('modal-btn-use');
            if (useBtn) {
                useBtn.disabled = true;
                useBtn.style.opacity = '0.5';
                useBtn.style.cursor = 'not-allowed';
            }
            const claimedActions = document.getElementById('modal-claimed-actions');
            if (claimedActions) claimedActions.style.display = 'none';

            const statusNotice = document.getElementById('modal-status-notice');
            const statusNoticeTitle = document.getElementById('modal-status-notice-title');
            const statusNoticeDesc = document.getElementById('modal-status-notice-desc');
            if (statusNotice && statusNoticeTitle && statusNoticeDesc) {
                statusNotice.style.display = 'block';
                statusNotice.style.background = '#fef2f2';
                statusNotice.style.border = '1px solid #fecaca';
                statusNoticeTitle.style.color = '#dc2626';
                statusNoticeTitle.innerHTML = '<i class="fa-solid fa-lock"></i> Voucher Expired';
                statusNoticeDesc.style.color = '#991b1b';
                statusNoticeDesc.textContent = 'This voucher reached its countdown expiration time and is no longer valid.';
            }

            if (typeof showToast === 'function') {
                showToast("⚠️ Voucher countdown reached zero. Voucher is now Expired.", "warning", 5000);
            }
            renderDiscounts();
            return;
        }

        // 2. QR Code Expired (20-min validity period up) -> Automatic Refresh!
        if (qrDiff <= 0) {
            stopModalTimers();
            window.refreshModalQrCode(code);
        }
    };

    updateTick();
    modalTimerInterval = setInterval(updateTick, 1000);
}

function stopModalTimers() {
    if (modalTimerInterval) {
        clearInterval(modalTimerInterval);
        modalTimerInterval = null;
    }
}

window.refreshModalQrCode = async function(code) {
    if (!code) return;
    try {
        const baseUrl = getDiscountBaseUrl();
        const token = getDiscountAuthToken();
        const res = await fetch(`${baseUrl}/api/tourist/vouchers/${encodeURIComponent(code)}/refresh-qr`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
            window._currentActiveQrToken = data.qr_token;
            if (currentModalItem) {
                currentModalItem.qr_token = data.qr_token;
                currentModalItem.qr_expires_at = data.qr_expires_at;
            }
            const qrImg = document.getElementById('modal-qr-img');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(data.qr_token || code)}`;
            }
            startModalTimers(currentModalItem);
            if (typeof showToast === 'function') {
                showToast("QR code automatically refreshed (20-min validity)", "info", 2500);
            }
        } else if (data.voucher_status === 'expired') {
            if (currentModalItem) {
                currentModalItem.status = 'expired';
                currentModalItem.isExpired = true;
            }
            stopModalTimers();
            renderDiscounts();
        }
    } catch(e) {
        console.warn("Failed to auto-refresh QR code:", e);
    }
};

window.handleVoucherUse = async function() {
    let item = currentModalItem;
    if (!item && currentVoucherId) {
        item = vouchersData.find(v => v.id === currentVoucherId) || getUserRedemptionItems().find(v => v.id === currentVoucherId);
    }
    if (!item) return;

    const code = item.code || item.voucher_code;
    const qrToken = item.qr_token || window._currentActiveQrToken || code;

    if (item.status === 'expired' || isVoucherExpired(item)) {
        if (typeof showToast === 'function') showToast("This voucher has expired and cannot be used.", "error");
        return;
    }

    const useBtn = document.getElementById('modal-btn-use');
    if (useBtn) {
        useBtn.disabled = true;
        useBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Redeeming...';
    }

    try {
        const baseUrl = getDiscountBaseUrl();
        const token = getDiscountAuthToken();
        const res = await fetch(`${baseUrl}/api/tourist/vouchers/${encodeURIComponent(code)}/use`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ qr_token: qrToken })
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
            if (typeof showToast === 'function') {
                showToast("🎉 Voucher successfully redeemed and marked as Used!", "success");
            }
            if (window.confetti) {
                window.confetti({ particleCount: 80, spread: 70, origin: { y: 0.6 } });
            }

            item.status = 'used';
            item.redemptionStatus = 'used';
            item.isUsed = true;
            item.isClaimed = false;
            item.redeemed_at = data.redeemed_at || new Date().toISOString();

            stopModalTimers();

            const stampOverlay = document.getElementById('modal-qr-stamp-overlay');
            const stampBadge = document.getElementById('modal-qr-stamp-badge');
            const qrImg = document.getElementById('modal-qr-img');
            const claimedActions = document.getElementById('modal-claimed-actions');
            const footerBanner = document.getElementById('modal-footer-banner');

            if (stampOverlay) stampOverlay.style.display = 'flex';
            if (stampBadge) {
                stampBadge.style.animation = 'none';
                void stampBadge.offsetWidth;
                stampBadge.style.animation = 'qrStampSlam 0.45s cubic-bezier(0.22, 1, 0.36, 1) forwards';
            }
            if (qrImg) {
                qrImg.style.filter = 'grayscale(1)';
                qrImg.style.opacity = '0.3';
            }
            if (claimedActions) claimedActions.style.display = 'none';
            if (footerBanner) footerBanner.style.display = 'none';

            const statusNotice = document.getElementById('modal-status-notice');
            const statusNoticeTitle = document.getElementById('modal-status-notice-title');
            const statusNoticeDesc = document.getElementById('modal-status-notice-desc');
            if (statusNotice && statusNoticeTitle && statusNoticeDesc) {
                statusNotice.style.display = 'block';
                statusNotice.style.background = '#fef2f2';
                statusNotice.style.border = '1px solid #fecaca';
                statusNoticeTitle.style.color = '#dc2626';
                statusNoticeTitle.innerHTML = '<i class="fa-solid fa-check-double"></i> Voucher Used & Verified';
                statusNoticeDesc.style.color = '#991b1b';
                statusNoticeDesc.textContent = 'This voucher has been successfully used and redeemed.';
            }

            renderDiscounts();
            fetchUserPointsAndRedemptions();
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to use voucher.", "error");
            if (useBtn) {
                useBtn.disabled = false;
                useBtn.innerHTML = '<i class="fa-solid fa-check"></i> Use';
            }
        }
    } catch(e) {
        if (typeof showToast === 'function') showToast("Network error. Please try again.", "error");
        if (useBtn) {
            useBtn.disabled = false;
            useBtn.innerHTML = '<i class="fa-solid fa-check"></i> Use';
        }
    }
};

window.handleVoucherCancel = async function() {
    let item = currentModalItem;
    if (!item && currentVoucherId) {
        item = vouchersData.find(v => v.id === currentVoucherId) || getUserRedemptionItems().find(v => v.id === currentVoucherId);
    }
    if (!item) return;

    const code = item.code || item.voucher_code;

    if (!confirm("Are you sure you want to cancel this voucher claim? Your points will be refunded to your account.")) {
        return;
    }

    const cancelBtn = document.getElementById('modal-btn-cancel');
    if (cancelBtn) {
        cancelBtn.disabled = true;
        cancelBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Cancelling...';
    }

    try {
        const baseUrl = getDiscountBaseUrl();
        const token = getDiscountAuthToken();
        const res = await fetch(`${baseUrl}/api/tourist/vouchers/${encodeURIComponent(code)}/cancel`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
            if (typeof showToast === 'function') {
                showToast(data.message || "Voucher claim cancelled. Points refunded!", "info");
            }

            item.status = 'cancelled';
            item.redemptionStatus = 'cancelled';
            item.isCancelled = true;
            item.isClaimed = false;
            item.cancelled_at = new Date().toISOString();

            if (data.new_balance !== undefined) {
                userPointsBalance = data.new_balance;
                const ptsBadge = document.getElementById('discount-user-pts');
                if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

                try {
                    ['auth_user', 'auth_user_backup', 'intan_elyu_user'].forEach(key => {
                        const raw = localStorage.getItem(key);
                        if (raw) {
                            const u = JSON.parse(raw);
                            if (u && typeof u === 'object') {
                                u.points = userPointsBalance;
                                localStorage.setItem(key, JSON.stringify(u));
                            }
                        }
                    });
                    const token = getDiscountAuthToken();
                    if (token) {
                        const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
                        const cachedPtsRaw = localStorage.getItem(ptsCacheKey);
                        if (cachedPtsRaw) {
                            const parsed = JSON.parse(cachedPtsRaw);
                            if (parsed.data) parsed.data.points = userPointsBalance;
                            else parsed.points = userPointsBalance;
                            localStorage.setItem(ptsCacheKey, JSON.stringify(parsed));
                        }
                    }
                } catch(e) {}
            }

            // Remove cancelled voucher from local claimed set
            try {
                const cleanId = String(item.voucher_id || item.dbId || item.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
                const curCode = (item.code || item.voucher_code || '').toUpperCase().trim();
                let claimedArr = getClaimedVouchers().filter(k => {
                    const sk = String(k).replace(/^db_/, '').replace(/^rdm_/, '');
                    return sk !== cleanId && sk !== curCode;
                });
                setClaimedVouchers(claimedArr);
            } catch(e) {}

            stopModalTimers();

            const claimedActions = document.getElementById('modal-claimed-actions');
            const footerBanner = document.getElementById('modal-footer-banner');
            const qrImg = document.getElementById('modal-qr-img');
            if (claimedActions) claimedActions.style.display = 'none';
            if (footerBanner) footerBanner.style.display = 'none';
            if (qrImg) {
                qrImg.style.filter = 'grayscale(1)';
                qrImg.style.opacity = '0.3';
            }

            const statusNotice = document.getElementById('modal-status-notice');
            const statusNoticeTitle = document.getElementById('modal-status-notice-title');
            const statusNoticeDesc = document.getElementById('modal-status-notice-desc');
            if (statusNotice && statusNoticeTitle && statusNoticeDesc) {
                statusNotice.style.display = 'block';
                statusNotice.style.background = '#f8fafc';
                statusNotice.style.border = '1px solid #cbd5e1';
                statusNoticeTitle.style.color = '#475569';
                statusNoticeTitle.innerHTML = '<i class="fa-solid fa-xmark"></i> Voucher Claim Cancelled';
                statusNoticeDesc.style.color = '#64748b';
                statusNoticeDesc.textContent = 'This voucher claim was cancelled and points were refunded to your account.';
            }

            renderDiscounts();
            fetchUserPointsAndRedemptions();
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to cancel voucher claim.", "error");
            if (cancelBtn) {
                cancelBtn.disabled = false;
                cancelBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Cancel';
            }
        }
    } catch(e) {
        if (typeof showToast === 'function') showToast("Network error. Please try again.", "error");
        if (cancelBtn) {
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = '<i class="fa-solid fa-xmark"></i> Cancel';
        }
    }
};

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
            codeEl.style.letterSpacing = '2px';
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
            showToast("You have already claimed or redeemed this voucher. Check your Active Vouchers in Profile or Claimed tab to use your pass.", "info", 5000);
        }
        return;
    }

    const expiryInfo = getExpiryInfo(item.expires, item.is_expired, item.is_upcoming, item.valid_from, item.valid_from_formatted, item.is_no_expiration, item.expiration_type);
    if (isVoucherExpired(item) || expiryInfo.isExpired) {
        if (typeof showToast === 'function') {
            showToast(`This voucher reached its expiration date (${expiryInfo.text || 'validity period'}) and can no longer be redeemed.`, "warning", 4500);
        }
        return;
    }

    if (item.is_upcoming || expiryInfo.isUpcoming) {
        if (typeof showToast === 'function') {
            showToast(`This voucher promotion begins on ${item.valid_from_formatted || 'its start date'}. Save your points to claim it then!`, "info", 4500);
        }
        return;
    }

    if (isVoucherFullyClaimed(item)) {
        if (typeof showToast === 'function') {
            showToast("This voucher is currently out of stock (all promotional allocations claimed). Check back later!", "warning", 4500);
        }
        return;
    }

    const token = getDiscountAuthToken();
    if (!token || isDiscountGuest()) {
        if (typeof showToast === 'function') showToast("Login now to get your vouchers! Sign in to your tourist account to claim rewards.", "info", 4500);
        if (typeof closeVoucherModal === 'function') closeVoucherModal();
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

    if (!navigator.onLine || window.isAppOnline === false) {
        if (typeof showToast === 'function') {
            showToast("Voucher redemption requires an active internet connection to generate your official pass.");
        }
        return;
    }

    const btn = document.getElementById('modal-redeem-btn');
    if (btn) {
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Redeeming...';
        btn.disabled = true;
    }

    try {
        const baseUrl = getDiscountBaseUrl();
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
            const cleanId = String(item.dbId || item.id || '').replace(/^db_/, '').replace(/^rdm_/, '');
            const claimCode = (data.data && data.data.voucher_code) ? data.data.voucher_code : (data.claim_code || item.code || ('ELYU-' + cleanId + '-' + Math.random().toString(36).substring(2, 6).toUpperCase()));
            item.code = claimCode;
            item.redemptionStatus = 'claimed';
            item.status = 'claimed';

            let claimed = getClaimedVouchers();
            [item.id, 'db_' + item.id, cleanId, 'db_' + cleanId, claimCode, claimCode.split('-')[0], String(item.title).toLowerCase().trim()].forEach(k => {
                if (k && !claimed.includes(k)) claimed.push(k);
            });
            setClaimedVouchers(claimed);

            const newRedemptionId = (data.data && data.data.id) ? data.data.id : Date.now();
            const activeQrToken = data.qr_token || claimCode;
            const newVoucherItem = {
                id: newRedemptionId,
                voucher_id: cleanId,
                dbId: cleanId,
                type: item.title,
                title: item.title,
                voucher_name: item.title,
                points_cost: cost,
                pointsCost: cost,
                voucher_code: claimCode,
                code: claimCode,
                qr_token: activeQrToken,
                status: 'claimed',
                redemptionStatus: 'claimed',
                isClaimed: true,
                isUsed: false,
                isCancelled: false,
                isExpired: false,
                redeemed_at: null,
                created_at: new Date().toISOString(),
                claimed_at: new Date().toISOString(),
                voucher_expires_at: data.voucher_expires_at || null,
                qr_expires_at: data.qr_expires_at || null,
                partner_establishment: item.partner,
                partner: item.partner,
                merchant: item.partner,
                category: item.category,
                image: item.image || null,
                expires: data.voucher_expires_at || item.expires || null,
                expires_at: data.voucher_expires_at || item.expires || null,
                expires_formatted: item.expires_formatted || 'Claimed & Usable'
            };

            currentModalItem = newVoucherItem;
            window._currentActiveQrToken = activeQrToken;

            // Immediately register into window._touristRedemptions
            if (!Array.isArray(window._touristRedemptions)) window._touristRedemptions = [];
            window._touristRedemptions.unshift(newVoucherItem);

            // Update user's points
            const expectedPts = Math.max(0, userPointsBalance - cost);
            const newPoints = data.new_balance !== undefined ? Math.min(Number(data.new_balance), expectedPts) : expectedPts;
            userPointsBalance = newPoints;
            const ptsBadge = document.getElementById('discount-user-pts');
            if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

            try {
                ['auth_user', 'auth_user_backup', 'intan_elyu_user'].forEach(key => {
                    const raw = localStorage.getItem(key);
                    if (raw) {
                        const storedUser = JSON.parse(raw);
                        if (storedUser && typeof storedUser === 'object') {
                            storedUser.points = newPoints;
                            localStorage.setItem(key, JSON.stringify(storedUser));
                        }
                    }
                });
            } catch(e) {}

            // Update points_balance_ and profile_data_ caches for 0ms Profile hydration
            try {
                const profKey = 'profile_data_' + token.substring(0, 10);
                const profRaw = localStorage.getItem(profKey);
                if (profRaw) {
                    const profObj = JSON.parse(profRaw);
                    if (profObj && typeof profObj === 'object') {
                        if (profObj.user && typeof profObj.user === 'object') profObj.user.points = newPoints;
                        if (profObj.data && profObj.data.user && typeof profObj.data.user === 'object') profObj.data.user.points = newPoints;
                        localStorage.setItem(profKey, JSON.stringify(profObj));
                    }
                }

                const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
                let cachedPtsObj = JSON.parse(localStorage.getItem(ptsCacheKey) || '{}');
                if (!cachedPtsObj || typeof cachedPtsObj !== 'object') cachedPtsObj = {};
                const targetData = (cachedPtsObj.data && typeof cachedPtsObj.data === 'object') ? cachedPtsObj.data : cachedPtsObj;
                cachedPtsObj.points = newPoints;
                targetData.points = newPoints;
                if (!Array.isArray(targetData.vouchers)) targetData.vouchers = [];
                targetData.vouchers.unshift(newVoucherItem);
                if (cachedPtsObj !== targetData && Array.isArray(cachedPtsObj.vouchers)) {
                    cachedPtsObj.vouchers.unshift(newVoucherItem);
                }
                localStorage.setItem(ptsCacheKey, JSON.stringify(cachedPtsObj));
            } catch(e) {}

            window.dashboardNeedsRefresh = true;
            window.profileNeedsRefresh = false;

            // Broadcast global event to sync profile and any open views
            window.dispatchEvent(new CustomEvent('voucherClaimed', {
                detail: {
                    voucherId: cleanId,
                    dbId: cleanId,
                    code: claimCode,
                    claimCode: claimCode,
                    qr_token: activeQrToken,
                    title: item.title,
                    pointsCost: cost,
                    newBalance: newPoints,
                    voucher: newVoucherItem
                }
            }));

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
                    codeEl.style.letterSpacing = '2px';
                    codeEl.style.color = '#1e3a8a';
                }
                if (iconEl) iconEl.className = 'fa-solid fa-eye';
                if (labelEl) labelEl.textContent = 'Voucher Code (Tap to reveal)';
                if (maskBtn) {
                    maskBtn.style.background = '#eff6ff';
                    maskBtn.style.borderColor = '#bfdbfe';
                }

                const qrImg = document.getElementById('modal-qr-img');
                const stampOverlay = document.getElementById('modal-qr-stamp-overlay');
                if (qrImg) {
                    qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(activeQrToken)}`;
                    qrImg.style.display = 'block';
                    qrImg.style.filter = 'none';
                    qrImg.style.opacity = '1';
                }
                if (stampOverlay) {
                    stampOverlay.style.display = 'none';
                }

                // Show dual timers
                const vTimerBox = document.getElementById('modal-voucher-timer-box');
                const qrTimerBox = document.getElementById('modal-qr-timer-box');
                if (vTimerBox) vTimerBox.style.display = 'flex';
                if (qrTimerBox) qrTimerBox.style.display = 'flex';
            }

            // Show footer banner with Use and Cancel buttons
            if (btn) {
                btn.style.display = 'none';
            }
            const claimedActions = document.getElementById('modal-claimed-actions');
            if (claimedActions) claimedActions.style.display = 'flex';
            const footerBanner = document.getElementById('modal-footer-banner');
            if (footerBanner) footerBanner.style.display = 'flex';

            // Start countdown timers and background sync
            startModalTimers(newVoucherItem);
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

async function fetchLiveDatabaseVouchers(forceRefresh = false) {
    if (voucherFetchInFlight) return;
    voucherFetchInFlight = true;

    try {
        // Show cached cards immediately on initial load, then replace them with the live catalog.
        if (!forceRefresh) {
            try {
                const raw = localStorage.getItem(VOUCHERS_CACHE_KEY);
                if (raw) {
                    const parsed = (typeof window.safeJsonParse === 'function') ? window.safeJsonParse(raw, null) : JSON.parse(raw);
                    const list = Array.isArray(parsed) ? parsed : (parsed && Array.isArray(parsed.data) ? parsed.data : null);
                    if (list) processVouchersData(list);
                }
            } catch(e) {}
        }

        const baseUrl = getDiscountBaseUrl();
        const res = await fetch(baseUrl + '/api/vouchers?fresh=' + Date.now(), {
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache',
                'ngrok-skip-browser-warning': 'true'
            }
        });
        if (!res.ok) throw new Error('Voucher catalog request failed: ' + res.status);

        const data = await res.json();
        if (data.status === 'success' && Array.isArray(data.data)) {
            try {
                localStorage.setItem(VOUCHERS_CACHE_KEY, JSON.stringify({ data: data.data, timestamp: Date.now() }));
            } catch(e) {}
            processVouchersData(data.data);
        }
    } catch(e) {
        console.warn('Could not load live database vouchers:', e);
    } finally {
        voucherFetchInFlight = false;
    }
}

function isDiscountViewActive() {
    const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
    return current === 'discount' && Boolean(document.getElementById('discounts-grid'));
}

function stopVoucherCatalogSync() {
    if (window._discountVoucherCatalogInterval) {
        clearInterval(window._discountVoucherCatalogInterval);
        window._discountVoucherCatalogInterval = null;
    }
}

function startVoucherCatalogSync() {
    stopVoucherCatalogSync();
    if (!isDiscountViewActive()) return;

    window.discountNeedsRefresh = false;
    fetchLiveDatabaseVouchers(true);
    window._discountVoucherCatalogInterval = setInterval(() => {
        if (!isDiscountViewActive()) {
            stopVoucherCatalogSync();
            return;
        }
        if (document.visibilityState === 'visible' && navigator.onLine !== false) {
            fetchLiveDatabaseVouchers(true);
        }
    }, VOUCHERS_SYNC_INTERVAL_MS);
}

function processVouchersData(rawList) {
    const nextSignature = JSON.stringify(rawList || []);
    const gridEl = document.getElementById('discounts-grid');
    if (nextSignature === lastVouchersCatalogSignature && vouchersData.length > 0 && gridEl && gridEl.children.length > 0) {
        return;
    }
    lastVouchersCatalogSignature = nextSignature;

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
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => (r.redemptionStatus === 'claimed' || r.status === 'claimed') && !['redeemed', 'used', 'completed'].includes((r.redemptionStatus || r.status || '').toLowerCase())) : [];
    } else if (activeStatus === 'Redeemed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => ['redeemed', 'used', 'completed'].includes((r.redemptionStatus || r.status || '').toLowerCase())) : [];
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
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => (r.redemptionStatus === 'claimed' || r.status === 'claimed') && !['redeemed', 'used', 'completed'].includes((r.redemptionStatus || r.status || '').toLowerCase())) : [];
    } else if (activeStatus === 'Redeemed') {
        baseList = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems().filter(r => ['redeemed', 'used', 'completed'].includes((r.redemptionStatus || r.status || '').toLowerCase())) : [];
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
    const redemptions = (typeof getUserRedemptionItems === 'function') ? getUserRedemptionItems() : [];
    const countAll = vouchersData.filter(v => !isVoucherClaimed(v) && !isVoucherRedeemed(v) && !isVoucherExpired(v) && !isVoucherUpcoming(v)).length;
    const countClaimed = redemptions.filter(r => r.isClaimed).length;
    const countUsed = redemptions.filter(r => r.isUsed).length;
    const countCancelled = redemptions.filter(r => r.isCancelled).length;
    const countExpired = redemptions.filter(r => r.isExpired).length + vouchersData.filter(v => isVoucherExpired(v)).length;
    const countUpcoming = vouchersData.filter(v => isVoucherUpcoming(v) && !isVoucherExpired(v)).length;

    const statusList = [
        {
            value: 'All',
            label: 'Available Vouchers',
            desc: 'Available reward deals to claim',
            count: countAll
        },
        {
            value: 'Claimed',
            label: 'Claimed Vouchers',
            desc: 'Active with 20-min QR & countdown',
            count: countClaimed
        },
        {
            value: 'Used',
            label: 'Used Vouchers',
            desc: 'Successfully redeemed vouchers',
            count: countUsed
        },
        {
            value: 'Cancelled',
            label: 'Cancelled Vouchers',
            desc: 'Cancelled claims with refunded points',
            count: countCancelled
        },
        {
            value: 'Expired',
            label: 'Expired Vouchers',
            desc: 'Reached expiration countdown',
            count: countExpired
        },
        {
            value: 'Upcoming',
            label: 'Upcoming Vouchers',
            desc: 'Scheduled promotions opening soon',
            count: countUpcoming
        }
    ];

    if (floatingList) {
        floatingList.innerHTML = statusList.map(s => {
            const isSelected = activeStatus.toLowerCase() === s.value.toLowerCase() || (activeStatus === 'Redeemed' && s.value === 'Used');
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

    const currentItem = statusList.find(s => s.value.toLowerCase() === activeStatus.toLowerCase() || (activeStatus === 'Redeemed' && s.value === 'Used')) || statusList[0];
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
window.fetchLiveDatabaseVouchers = fetchLiveDatabaseVouchers;
window.fetchUserPointsAndRedemptions = fetchUserPointsAndRedemptions;
window.startVoucherCatalogSync = startVoucherCatalogSync;

// Synchronously populate default dropdown items right away
populateStatusDropdown();
populateCategoryDropdown();
populateMunicipalityDropdown();

fetchLiveDatabaseVouchers();
fetchUserPointsAndRedemptions();
startVoucherCatalogSync();

// Offline Indicator Status Management
function updateDiscountOfflineStatus() {
    const badge = document.getElementById('discount-offline-badge');
    const isOff = (typeof navigator.onLine === 'boolean') ? !navigator.onLine : (window.isAppOnline === false);
    if (badge) badge.style.setProperty('display', isOff ? 'block' : 'none', 'important');
    if (!isOff) {
        try {
            localStorage.removeItem('intan_elyu_offline_mode');
            localStorage.removeItem('intan_elyu_offline_guest');
        } catch(e) {}
    }
}
window.addEventListener('online', () => {
    updateDiscountOfflineStatus();
    if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
});
window.addEventListener('offline', updateDiscountOfflineStatus);
window.addEventListener('appOnline', () => {
    updateDiscountOfflineStatus();
    if (typeof window.enforceAllOfflineBadgesHidden === 'function') window.enforceAllOfflineBadgesHidden();
});
window.addEventListener('appOffline', updateDiscountOfflineStatus);
window.addEventListener('app-connection-changed', updateDiscountOfflineStatus);
setTimeout(updateDiscountOfflineStatus, 150);

if (window._discountOnlineCatalogHandler) window.removeEventListener('online', window._discountOnlineCatalogHandler);
window._discountOnlineCatalogHandler = function () {
    const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
    if (current !== 'discount') {
        window.discountNeedsRefresh = true;
        return;
    }
    startVoucherCatalogSync();
    fetchUserPointsAndRedemptions();
};
window.addEventListener('online', window._discountOnlineCatalogHandler);

if (window._discountAppOnlineCatalogHandler) window.removeEventListener('appOnline', window._discountAppOnlineCatalogHandler);
window._discountAppOnlineCatalogHandler = function () {
    const current = (document.body ? document.body.getAttribute('data-view') : '') || (window.currentView || '');
    if (current !== 'discount') {
        window.discountNeedsRefresh = true;
        return;
    }
    startVoucherCatalogSync();
    fetchUserPointsAndRedemptions();
};
window.addEventListener('appOnline', window._discountAppOnlineCatalogHandler);

if (window._discountVisibilityCatalogHandler) document.removeEventListener('visibilitychange', window._discountVisibilityCatalogHandler);
window._discountVisibilityCatalogHandler = function () {
    if (document.visibilityState === 'visible' && isDiscountViewActive()) startVoucherCatalogSync();
};
document.addEventListener('visibilitychange', window._discountVisibilityCatalogHandler);

// Real-time synchronization when a voucher is claimed in Profile or anywhere in the app
window.addEventListener('voucherClaimed', function (e) {
    const detail = e.detail || {};
    if (detail.newBalance !== undefined) {
        userPointsBalance = detail.newBalance;
    } else if (detail.pointsCost) {
        userPointsBalance = Math.max(0, userPointsBalance - detail.pointsCost);
    }
    const ptsBadge = document.getElementById('discount-user-pts');
    if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;

    const vid = detail.voucherId ? String(detail.voucherId).replace(/^db_/, '') : null;
    const vCode = detail.claimCode || (detail.voucher && (detail.voucher.voucher_code || detail.voucher.code));

    // Update claimed vouchers list
    let claimed = getClaimedVouchers();
    if (vid) {
        if (!claimed.includes(vid)) claimed.push(vid);
        if (!claimed.includes('db_' + vid)) claimed.push('db_' + vid);
    }
    if (vCode && !claimed.includes(vCode)) claimed.push(vCode);
    if (detail.voucher && detail.voucher.title) {
        const t = String(detail.voucher.title).toLowerCase().trim();
        if (!claimed.includes(t)) claimed.push(t);
    }
    setClaimedVouchers(claimed);

    // Register into window._touristRedemptions if voucher details provided
    if (detail.voucher) {
        if (!Array.isArray(window._touristRedemptions)) window._touristRedemptions = [];
        const exists = window._touristRedemptions.some(r => {
            const rCode = (r.voucher_code || r.code || '').toUpperCase().trim();
            const dCode = (vCode || '').toUpperCase().trim();
            return rCode && dCode && rCode === dCode;
        });
        if (!exists) {
            window._touristRedemptions.unshift({
                id: detail.voucher.id || Date.now(),
                voucher_id: vid || detail.voucher.voucher_id || '',
                type: detail.voucher.title || detail.voucher.type,
                points_cost: detail.pointsCost || detail.voucher.points_cost || 0,
                voucher_code: vCode || '',
                status: 'claimed',
                redeemed_at: null,
                created_at: new Date().toISOString(),
                partner_establishment: detail.voucher.partner || detail.voucher.partner_establishment || '',
                category: detail.voucher.category || ''
            });
        }
    }

    // Refresh UI immediately
    syncClaimedVouchersWithData();
    updateClaimedBadge();
    populateStatusDropdown();
    renderDiscounts();
});

// Real-time synchronization when navigating to the Discounts & Vouchers view
if (window._discountViewLoadedHandler) window.removeEventListener('viewLoaded', window._discountViewLoadedHandler);
window._discountViewLoadedHandler = function(e) {
    if (e && e.detail && e.detail.view === 'discount') {
        lastVouchersCatalogSignature = '';
        const token = getDiscountAuthToken();
        if (token) {
            try {
                const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if (u.points !== undefined || u.xp !== undefined) {
                    userPointsBalance = (u.points !== undefined) ? u.points : (u.xp ?? 0);
                    const ptsBadge = document.getElementById('discount-user-pts');
                    if (ptsBadge) ptsBadge.textContent = `${userPointsBalance.toLocaleString()} Points`;
                }
                const ptsCacheKey = 'points_balance_' + token.substring(0, 10);
                const cachedRaw = localStorage.getItem(ptsCacheKey);
                if (cachedRaw) {
                    const cachedData = JSON.parse(cachedRaw);
                    const dataObj = cachedData.data || cachedData;
                    if (Array.isArray(dataObj.vouchers)) {
                        window._touristRedemptions = dataObj.vouchers;
                    }
                }
            } catch(e) {}
        }
        syncClaimedVouchersWithData();
        updateClaimedBadge();
        populateStatusDropdown();
        renderDiscounts();
        startVoucherCatalogSync();
        fetchUserPointsAndRedemptions();
    } else if (e && e.detail && e.detail.view) {
        stopVoucherCatalogSync();
    }
};
window.addEventListener('viewLoaded', window._discountViewLoadedHandler);
})();
</script>
