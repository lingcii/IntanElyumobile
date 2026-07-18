<!-- Profile View -->
<?php
$pageTitle = 'My Profile';
$activeTab = 'profile';
?>


<?php include __DIR__ . '/../components/header.php'; ?>

<div class="profile-container has-header has-bottom-nav animate-slide-up">
    
    <div class="profile-header stagger-1">
        <div class="profile-avatar-container">
            <img src="https://ui-avatars.com/api/?name=User&background=007AFF&color=fff&rounded=true&bold=true&size=128" alt="Profile" class="profile-avatar" id="profile-img">
        </div>
        <h2 class="profile-name" id="profile-name">Loading...</h2>
        <p class="profile-email" id="profile-email">loading@example.com</p>
        <button onclick="navigateTo('edit_profile')" style="margin-top: 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; font-weight:700; height: 32px; padding: 0 16px; border-radius:20px; font-size:12px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
            <i class="fa-solid fa-pen-to-square"></i> Edit Profile
        </button>
    </div>
    
    <div class="stats-container stagger-2">
        <div class="stat-card">
            <div class="stat-value" id="stat-xp">0</div>
            <div class="stat-label">Total XP</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-places">0</div>
            <div class="stat-label">Places Visited</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-rank">—</div>
            <div class="stat-label">Rank</div>
        </div>
    </div>
    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Trip History</h3>
    <div id="trip-history-list" class="stagger-3" style="margin-bottom: 24px;">
        <div style="text-align:center; padding:20px; color:rgba(148, 163, 184, 0.8); font-size:14px; background:rgba(37, 99, 235, 0.15); border:1px solid rgba(56, 189, 248, 0.2); backdrop-filter:blur(24px); border-radius:16px;">Loading history...</div>
    </div>
    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">🎁 Points & Rewards</h3>
    
    <div style="background:rgba(30, 41, 59, 0.4); border:1px solid rgba(255,255,255,0.06); border-radius:20px; padding:20px; margin-bottom:24px; box-shadow:0 10px 25px rgba(0,0,0,0.3); backdrop-filter:blur(10px);" class="stagger-3">
        <!-- Display balance -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:16px;">
            <div style="text-align: left;">
                <h4 style="margin:0 0 4px 0; font-size:12px; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:0.5px;">Claimable Points</h4>
                <div style="display:flex; align-items:baseline; gap:6px;">
                    <span id="profile-points-val" style="font-size:32px; font-weight:800; color:#38bdf8; letter-spacing:-1px;">--</span>
                    <span style="font-size:14px; font-weight:700; color:rgba(255,255,255,0.5);">PTS</span>
                </div>
            </div>
            <button onclick="navigateTo('puzzles')" style="background:rgba(56,189,248,0.12); border:1px solid rgba(56,189,248,0.3); color:#38bdf8; padding:8px 16px; border-radius:12px; font-weight:700; font-size:12px; cursor:pointer;">
                <i class="fa-solid fa-gamepad"></i> Play & Earn
            </button>
        </div>

        <!-- Catalog list -->
        <h5 style="margin:0 0 12px; font-size:13px; font-weight:700; color:#fff; text-align: left;">Redeem Rewards</h5>
        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
            <!-- Pasalubong center voucher -->
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:12px 14px; border-radius:14px;">
                <div style="text-align: left;">
                    <strong style="display:block; font-size:13px; color:#fff;">₱50 Pasalubong Discount</strong>
                    <span style="font-size:11px; color:rgba(255,255,255,0.5);">Claimable at local Pasalubong Center</span>
                </div>
                <button onclick="redeemReward('pasalubong_discount', 100)" style="background:#38bdf8; color:#000; border:none; padding:8px 12px; border-radius:10px; font-size:11px; font-weight:800; cursor:pointer;">
                    100 PTS
                </button>
            </div>

            <!-- Environmental fee waiver -->
            <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05); padding:12px 14px; border-radius:14px;">
                <div style="text-align: left;">
                    <strong style="display:block; font-size:13px; color:#fff;">Waived Environmental Fee</strong>
                    <span style="font-size:11px; color:rgba(255,255,255,0.5);">Waive standard municipality entry fee</span>
                </div>
                <button onclick="redeemReward('environmental_fee', 150)" style="background:#38bdf8; color:#000; border:none; padding:8px 12px; border-radius:10px; font-size:11px; font-weight:800; cursor:pointer;">
                    150 PTS
                </button>
            </div>
        </div>

        <!-- Active Claimed Vouchers -->
        <h5 style="margin:0 0 12px; font-size:13px; font-weight:700; color:#fff; text-align: left;">Active Vouchers</h5>
        <div id="vouchers-list" style="display:flex; flex-direction:column; gap:8px;">
            <div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;">No redeemed vouchers yet.</div>
        </div>
    </div>
    
    <h3 class="stagger-3" style="font-size: 18px; margin-bottom: 12px; margin-left: 8px;">Account</h3>
    
    <div class="settings-group stagger-3">
        <a href="#" class="settings-item" onclick="navigateTo('help'); return false;">
            <div class="settings-icon" style="background: #34C759;"><i class="fa-solid fa-circle-question"></i></div>
            <div class="settings-text">Help & Support</div>
            <i class="fa-solid fa-chevron-right settings-arrow"></i>
        </a>
        <a href="#" class="settings-item" onclick="handleLogout(event)">
            <div class="settings-icon" style="background: #FF3B30;"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
            <div class="settings-text" style="color: #FF3B30;">Log Out</div>
        </a>
    </div>

    
</div>

<!-- Include Bottom Navigation Component -->


<script>
    var backendUrl = window.backendUrl || 'https://intanelyumobile-production.up.railway.app';

    async function fetchProfileData() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        const cacheKey = 'profile_data_' + token.substring(0, 10);

        await window.useCache(
            cacheKey,
            async () => {
                const response = await fetch(backendUrl + '/api/tourist/profile', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });
                if (!response.ok) throw new Error("Failed to fetch profile");
                return await response.json();
            },
            (data) => {
                if (!data) return;
                const u = data.user || {};
                document.getElementById('stat-xp').textContent = u.xp || 0;
                document.getElementById('stat-places').textContent = data.places_visited || 0;
                if (data.my_rank) document.getElementById('stat-rank').textContent = '#' + data.my_rank;

                document.getElementById('profile-name').textContent = u.name || 'Explorer';
                document.getElementById('profile-email').textContent = u.email || '';

                // Avatar
                if (document.getElementById('profile-img')) {
                    document.getElementById('profile-img').src = u.avatar ||
                        `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'Tourist')}&background=007AFF&color=fff&rounded=true&bold=true&size=128`;
                }

                const historyList = document.getElementById('trip-history-list');
                if (!data.completed_trips || data.completed_trips.length === 0) {
                    historyList.innerHTML = '<div style="text-align:center; padding:20px; color:rgba(148, 163, 184, 0.8); font-size:14px; background:rgba(37, 99, 235, 0.15); border:1px solid rgba(56, 189, 248, 0.2); border-radius:16px; backdrop-filter:blur(24px);">No completed trips yet. Start exploring!</div>';
                    return;
                }

                let html = '';
                data.completed_trips.forEach(trip => {
                    const date = trip.trip_date ? new Date(trip.trip_date).toLocaleDateString() : 'No date';
                    html += `
                    <div style="background:rgba(37, 99, 235, 0.15); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); border:1px solid rgba(56, 189, 248, 0.2); border-radius:16px; padding:16px; margin-bottom:12px; box-shadow:0 4px 16px rgba(0,0,0,0.2);">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <strong style="color:#f8fafc; font-size:16px;">${trip.title}</strong>
                            <span style="color:#34C759; font-weight:700; font-size:14px;">Completed</span>
                        </div>
                        <div style="font-size:13px; color:rgba(148, 163, 184, 0.8); margin-bottom:12px;">
                            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> ${date}
                            <span style="margin:0 8px;">&bull;</span>
                            <i class="fa-solid fa-coins" style="margin-right:4px;"></i> ₱${trip.total_cost || 0}
                        </div>
                        <div style="font-size:12px; color:rgba(148, 163, 184, 0.6);">
                            ${trip.items ? trip.items.length : 0} Destinations Visited
                        </div>
                    </div>`;
                });
                historyList.innerHTML = html;
            },
            false,
            60000 // 1 minute TTL
        );
    }

    // Fetch Points & Vouchers
    async function fetchPointsAndVouchers() {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        try {
            const r = await fetch(backendUrl + '/api/tourist/points/balance', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            });
            const d = await r.json();
            if (d.status === 'success') {
                const ptsVal = document.getElementById('profile-points-val');
                if (ptsVal) ptsVal.textContent = d.points;
                
                // Render vouchers
                const list = document.getElementById('vouchers-list');
                if (list) {
                    if (d.vouchers && d.vouchers.length > 0) {
                        let html = '';
                        d.vouchers.forEach(v => {
                            const typeLabel = v.type === 'pasalubong_discount' ? '🎁 Pasalubong Discount' : '🌿 Env Fee Waived';
                            const badgeColor = v.status === 'active' ? '#34c759' : '#8e8e93';
                            html += `
                            <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); padding:12px; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                                <div style="text-align: left;">
                                    <span style="font-size:12px; font-weight:700; color:#fff; display:block;">${typeLabel}</span>
                                    <code style="font-size:13px; font-weight:800; color:#38bdf8; letter-spacing:0.5px; background:rgba(56,189,248,0.1); padding:2px 6px; border-radius:6px; margin-top:4px; display:inline-block;">${v.voucher_code}</code>
                                </div>
                                <span style="font-size:10px; font-weight:800; text-transform:uppercase; color:${badgeColor}; background:rgba(255,255,255,0.05); padding:4px 8px; border-radius:6px;">${v.status}</span>
                            </div>`;
                        });
                        list.innerHTML = html;
                    } else {
                        list.innerHTML = '<div style="font-size:12px; color:rgba(255,255,255,0.4); text-align:center; padding:10px;">No redeemed vouchers yet.</div>';
                    }
                }
            }
        } catch (e) {
            console.error("Points fetch error:", e);
        }
    }

    window.redeemReward = async function(type, cost) {
        const token = localStorage.getItem('intan_elyu_token');
        if (!token) return;

        if (!confirm(`Are you sure you want to redeem this reward for ${cost} Points?`)) return;

        try {
            const response = await fetch(backendUrl + '/api/tourist/points/redeem', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ type: type })
            });

            const data = await response.json();
            if (response.ok) {
                if (typeof showToast === 'function') showToast("Reward redeemed successfully! 🥳");
                fetchPointsAndVouchers();
            } else {
                if (typeof showToast === 'function') showToast(data.message || "Failed to redeem reward.");
            }
        } catch (error) {
            console.error("Redemption error:", error);
            if (typeof showToast === 'function') showToast("Network error.");
        }
    };

    // Export function to update points display globally
    window.updateProfilePointsDisplay = function(points) {
        const ptsVal = document.getElementById('profile-points-val');
        if (ptsVal) ptsVal.textContent = points;
    };

    fetchProfileData();
    fetchPointsAndVouchers();
</script>

