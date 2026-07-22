<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  * header.php — shared top-header component
//  * Reads live data from $conn (injected by including page).
// ROLE-BASED HEADER CONFIGURATION
$headerConfig = [
    // LUPTO Role
    'lupto' => [
        'title' => 'LUPTO',
        'subtitle' => 'LA UNION PROVINCIAL TOURISM OFFICE (LUPTO)'
    ],
    // PICTO Role
    'picto' => [
        'title' => 'PICTO',
        'subtitle' => 'PROVINCIAL INFORMATION AND COMMUNICATIONS TECHNOLOGY OFFICE (PICTO)'
    ],
    // Municipal/LGU Roles
    'municipal' => [
        'title' => 'MTO',
        'subtitle' => 'MUNICIPAL TOURISM OFFICE (MTO)'
    ],
    'san_juan_mto' => [
        'title' => 'San Juan MTO',
        'subtitle' => 'SAN JUAN MUNICIPAL TOURISM OFFICE'
    ],
    'san_fernando_mto' => [
        'title' => 'San Fernando MTO',
        'subtitle' => 'SAN FERNANDO MUNICIPAL TOURISM OFFICE'
    ],
    'bauang_mto' => [
        'title' => 'Bauang MTO',
        'subtitle' => 'BAUANG MUNICIPAL TOURISM OFFICE'
    ],
    'agoo_mto' => [
        'title' => 'Agoo MTO',
        'subtitle' => 'AGOO MUNICIPAL TOURISM OFFICE'
    ],
    'luna_mto' => [
        'title' => 'Luna MTO',
        'subtitle' => 'LUNA MUNICIPAL TOURISM OFFICE'
    ],
    'san_gabriel_mto' => [
        'title' => 'San Gabriel MTO',
        'subtitle' => 'SAN GABRIEL MUNICIPAL TOURISM OFFICE'
    ],
    'balaoan_mto' => [
        'title' => 'Balaoan MTO',
        'subtitle' => 'BALAOAN MUNICIPAL TOURISM OFFICE'
    ],
    'aringay_mto' => [
        'title' => 'Aringay MTO',
        'subtitle' => 'ARINGAY MUNICIPAL TOURISM OFFICE'
    ],
    'rosario_mto' => [
        'title' => 'Rosario MTO',
        'subtitle' => 'ROSARIO MUNICIPAL TOURISM OFFICE'
    ],
    'bacnotan_mto' => [
        'title' => 'Bacnotan MTO',
        'subtitle' => 'BACNOTAN MUNICIPAL TOURISM OFFICE'
    ],
    'naguilian_mto' => [
        'title' => 'Naguilian MTO',
        'subtitle' => 'NAGUILIAN MUNICIPAL TOURISM OFFICE'
    ],
    'tubao_mto' => [
        'title' => 'Tubao MTO',
        'subtitle' => 'TUBAO MUNICIPAL TOURISM OFFICE'
    ],
    'pugo_mto' => [
        'title' => 'Pugo MTO',
        'subtitle' => 'PUGO MUNICIPAL TOURISM OFFICE'
    ],
    'caba_mto' => [
        'title' => 'Caba MTO',
        'subtitle' => 'CABA MUNICIPAL TOURISM OFFICE'
    ],
    'santo_tomas_mto' => [
        'title' => 'Santo Tomas MTO',
        'subtitle' => 'SANTO TOMAS MUNICIPAL TOURISM OFFICE'
    ],
    'bangar_mto' => [
        'title' => 'Bangar MTO',
        'subtitle' => 'BANGAR MUNICIPAL TOURISM OFFICE'
    ],
    'burgos_mto' => [
        'title' => 'Burgos MTO',
        'subtitle' => 'BURGOS MUNICIPAL TOURISM OFFICE'
    ],
    'bagulin_mto' => [
        'title' => 'Bagulin MTO',
        'subtitle' => 'BAGULIN MUNICIPAL TOURISM OFFICE'
    ],
    'santol_mto' => [
        'title' => 'Santol MTO',
        'subtitle' => 'SANTOL MUNICIPAL TOURISM OFFICE'
    ],
    'sudipen_mto' => [
        'title' => 'Sudipen MTO',
        'subtitle' => 'SUDIPEN MUNICIPAL TOURISM OFFICE'
    ]
];

// Get current user's role from session (with validation)
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Determine header text (with fallback for unrecognized roles)
if ($userRole && isset($headerConfig[$userRole])) {
    $headerText = $headerConfig[$userRole];
} else {
    // Fallback for unrecognized roles or no role
    $headerText = [
        'title' => 'Dashboard',
        'subtitle' => 'Tourism Monitoring System'
    ];
}

// Logged-in user name (from session)
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Notification type → icon/color map
$notifMeta = [
    'missing_data'         => ['icon' => 'fa-triangle-exclamation', 'color' => 'var(--danger)'],
    'delayed_program'      => ['icon' => 'fa-clock',                'color' => 'var(--danger)'],
    'low_investment'       => ['icon' => 'fa-chart-line',           'color' => 'var(--warning)'],
    'agricultural_decline' => ['icon' => 'fa-leaf',                 'color' => 'var(--warning)'],
];

// Human-readable time-ago helper
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff/60)  . ' min ago';
    if ($diff < 86400)  return floor($diff/3600) . ' hr ago';
    return floor($diff/86400) . ' day(s) ago';
}
?>
<header class="top-header" id="topHeader">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-brand">
            <h1 class="brand-title"><?= htmlspecialchars($headerText['subtitle']) ?></h1>
            <span class="brand-subtitle">Tourism Monitoring System</span>
        </div>
    </div>

    <div class="header-controls">

        <div class="date-control">
            <i class="fas fa-calendar-day date-icon"></i>
            <input type="date" class="ctrl-date" id="reportDate" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="notif-control" style="margin-right: 4px;">
            <button class="notif-btn" id="spaRefreshBtn" title="Refresh Active Tab" aria-label="Refresh">
                <i class="fas fa-sync-alt" id="spaRefreshIcon"></i>
            </button>
        </div>

        <div class="notif-control">
            <button class="notif-btn" id="notifBtn" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </button>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <span class="notif-count" id="notifCount">0 new</span>
                </div>
                <div id="notifItems">
                    <div class="notif-item">
                        <i class="fas fa-spinner fa-spin" style="color:var(--text-muted)"></i>
                        <div><p class="notif-text">Loading notifications…</p></div>
                    </div>
                </div>
                <div class="notif-footer">
                    <a href="#">View all notifications</a>
                </div>
            </div>
        </div>

        <div class="user-control" id="userControl">
            <div class="user-avatar-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <span class="user-label"><?= htmlspecialchars($userName) ?></span>
            <i class="fas fa-chevron-down user-caret" id="userCaret"></i>
            <div class="user-dropdown" id="userDropdown">
                <a href="settings.php" class="dd-item">
                    <i class="fas fa-user-pen"></i> My Profile
                </a>
                <a href="settings.php" class="dd-item">
                    <i class="fas fa-sliders"></i> Settings
                </a>
                <div class="dd-divider"></div>
                <a href="<?= $basePath ?>logout.php" class="dd-item dd-danger">
                    <i class="fas fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Logout Confirmation Modal Styles (inline for global availability) -->
<style>
#logoutConfirmModal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10002;
  overflow-y: auto;
  padding: 24px;
  backdrop-filter: blur(2px);
}
#logoutConfirmModal.active {
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.2s ease;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
#logoutConfirmModal .modal-content {
  background: white;
  border-radius: 16px;
  width: 90%;
  max-width: 420px;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
#logoutConfirmModal .btn {
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  border: 2px solid #E5E7EB;
  background: white;
  color: #4B5563;
}
#logoutConfirmModal .btn:hover {
  background: #F9FAFB;
}
#logoutConfirmModal .btn.btn-outline {
  border: 2px solid #E5E7EB;
}
#logoutConfirmModal .btn.btn-danger {
  border: none;
  color: white;
}
</style>

<!-- Logout Confirmation Modal -->
<div class="modal" id="logoutConfirmModal">
    <div class="modal-content" style="max-width: 420px; border-radius: 16px; overflow: hidden;">
        <div style="background: #FEE2E2; padding: 28px 28px 16px 28px; text-align: center;">
            <div style="width: 56px; height: 56px; background: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                <i class="fas fa-right-from-bracket" style="color: white; font-size: 22px;"></i>
            </div>
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: #991B1B;">Logout</h3>
        </div>
        <div style="padding: 20px 28px 28px 28px;">
            <p style="text-align: center; color: #4B5563; margin: 0 0 24px 0; font-size: 14px;">Are you sure you want to logout?</p>
            <div style="display: flex; gap: 12px;">
                <button class="btn btn-outline" id="cancelLogoutBtn" style="flex: 1; justify-content: center;">
                    <i class="fas fa-times" style="margin-right: 6px;"></i> No
                </button>
                <button class="btn btn-danger" id="confirmLogoutBtn" style="flex: 1; justify-content: center; background: #DC2626; border-color: #DC2626;">
                    <i class="fas fa-check" style="margin-right: 6px;"></i> Yes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Load notifications from Laravel API after page load
(function loadNotifications() {
    const role = '<?= htmlspecialchars($userRole ?? '') ?>';
    const prefixMap = { picto: 'pitco', lupto: 'lupto' };
    // Notifications are part of the dashboard response; use a quick /api/auth/check call
    // to confirm session, then fetch alerts from the dashboard endpoint.
    const prefix = prefixMap[role] || (role.endsWith('_mto') || role === 'municipal' ? 'municipal' : null);

    const notifItemsEl  = document.getElementById('notifItems');
    const notifBadgeEl  = document.getElementById('notifBadge');
    const notifCountEl  = document.getElementById('notifCount');

    if (!prefix || !notifItemsEl) return;

    const baseUrl = window.API_CONFIG ? window.API_CONFIG.BASE_URL : 'http://localhost:8000';
    fetch(`${baseUrl}/api/${prefix}/dashboard`, { credentials: 'include' })
        .then(r => r.ok ? r.json() : Promise.reject(r.status))
        .then(data => {
            const alerts = (data.alerts || []).slice(0, 5);
            const count  = alerts.length;

            if (notifBadgeEl) {
                notifBadgeEl.textContent = count;
                notifBadgeEl.style.display = count > 0 ? '' : 'none';
            }
            if (notifCountEl) notifCountEl.textContent = count + ' new';

            if (!notifItemsEl) return;
            if (count === 0) {
                notifItemsEl.innerHTML = `
                    <div class="notif-item">
                        <i class="fas fa-check-circle" style="color:var(--success)"></i>
                        <div><p class="notif-text">No unread notifications.</p>
                        <span class="notif-time">All clear</span></div>
                    </div>`;
                return;
            }

            function timeAgoJS(dateStr) {
                const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
                if (diff < 60)     return 'Just now';
                if (diff < 3600)   return Math.floor(diff / 60)   + ' min ago';
                if (diff < 86400)  return Math.floor(diff / 3600)  + ' hr ago';
                return Math.floor(diff / 86400) + ' day(s) ago';
            }

            notifItemsEl.innerHTML = alerts.map(a => `
                <div class="notif-item unread">
                    <i class="fas fa-bell" style="color:var(--warning)"></i>
                    <div>
                        <p class="notif-text">${(a.message || '').replace(/</g,'&lt;')}</p>
                        <span class="notif-time">${timeAgoJS(a.created_at)}</span>
                    </div>
                </div>`).join('');
        })
        .catch(() => {
            if (notifItemsEl) notifItemsEl.innerHTML = `
                <div class="notif-item">
                    <i class="fas fa-check-circle" style="color:var(--success)"></i>
                    <div><p class="notif-text">No unread notifications.</p></div>
                </div>`;
        });
})();
</script>