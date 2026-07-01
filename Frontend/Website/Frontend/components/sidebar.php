<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// ------------------------------
// ROLE-BASED SIDEBAR CONFIGURATION
// ------------------------------
$sidebarConfig = [
    // LUPTO Role
    'lupto' => [
        'brand' => 'LUPTO',
        'brand_sub' => 'San Fernando City, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Manage Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Transportation Fare'],
            ['href' => 'leaderboard.php', 'icon' => 'fa-trophy', 'label' => 'Leaderboard'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics'],
            ['href' => 'activity-logs.php',   'icon' => 'fa-history',         'label' => 'Activity Logs'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    // PICTO Role
    'picto' => [
        'brand' => 'PICTO',
        'brand_sub' => 'San Fernando City, La Union',
        'items' => [
            ['href' => 'dashboard.php',            'icon' => 'fa-gauge-high',             'label' => 'Dashboard'],
            ['href' => 'tourist-spots.php',        'icon' => 'fa-location-dot',           'label' => 'Manage Tourist Spots'],
            ['href' => 'fare-data.php',            'icon' => 'fa-money-bill-trend-up',    'label' => 'Transportation Fare'],
            ['href' => 'user-management.php',      'icon' => 'fa-user',                   'label' => 'User Management'],
            ['href' => 'analytics.php',            'icon' => 'fa-chart-simple',           'label' => 'Analytics'],
            ['href' => 'activity-logs.php',        'icon' => 'fa-history',                'label' => 'Activity Logs'],
            ['href' => 'archive-management.php',   'icon' => 'fa-box-archive',            'label' => 'Archive Management'],
            ['href' => 'settings.php',             'icon' => 'fa-cog',                    'label' => 'System Settings'],
        ]
    ],
    // Municipal/LGU Roles (fallback for all municipal roles)
    'municipal' => [
        'brand' => 'MTO',
        'brand_sub' => 'San Fernando City, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    // Specific municipal roles with custom brand names
    'san_juan_mto' => [
        'brand' => 'San Juan MTO',
        'brand_sub' => 'San Juan, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'san_fernando_mto' => [
        'brand' => 'San Fernando MTO',
        'brand_sub' => 'San Fernando City, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'bauang_mto' => [
        'brand' => 'Bauang MTO',
        'brand_sub' => 'Bauang, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'agoo_mto' => [
        'brand' => 'Agoo MTO',
        'brand_sub' => 'Agoo, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'luna_mto' => [
        'brand' => 'Luna MTO',
        'brand_sub' => 'Luna, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'san_gabriel_mto' => [
        'brand' => 'San Gabriel MTO',
        'brand_sub' => 'San Gabriel, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'balaoan_mto' => [
        'brand' => 'Balaoan MTO',
        'brand_sub' => 'Balaoan, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'aringay_mto' => [
        'brand' => 'Aringay MTO',
        'brand_sub' => 'Aringay, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'rosario_mto' => [
        'brand' => 'Rosario MTO',
        'brand_sub' => 'Rosario, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'bacnotan_mto' => [
        'brand' => 'Bacnotan MTO',
        'brand_sub' => 'Bacnotan, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'naguilian_mto' => [
        'brand' => 'Naguilian MTO',
        'brand_sub' => 'Naguilian, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'tubao_mto' => [
        'brand' => 'Tubao MTO',
        'brand_sub' => 'Tubao, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'pugo_mto' => [
        'brand' => 'Pugo MTO',
        'brand_sub' => 'Pugo, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'caba_mto' => [
        'brand' => 'Caba MTO',
        'brand_sub' => 'Caba, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'santo_tomas_mto' => [
        'brand' => 'Santo Tomas MTO',
        'brand_sub' => 'Santo Tomas, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'bangar_mto' => [
        'brand' => 'Bangar MTO',
        'brand_sub' => 'Bangar, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'burgos_mto' => [
        'brand' => 'Burgos MTO',
        'brand_sub' => 'Burgos, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'bagulin_mto' => [
        'brand' => 'Bagulin MTO',
        'brand_sub' => 'Bagulin, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'santol_mto' => [
        'brand' => 'Santol MTO',
        'brand_sub' => 'Santol, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ],
    'sudipen_mto' => [
        'brand' => 'Sudipen MTO',
        'brand_sub' => 'Sudipen, La Union',
        'items' => [
            ['href' => 'dashboard.php',       'icon' => 'fa-gauge-high',      'label' => 'Dashboard Overview'],
            ['href' => 'tourist-spots.php',   'icon' => 'fa-location-dot',    'label' => 'Tourist Spots'],
            ['href' => 'fare-data.php',       'icon' => 'fa-money-bill-trend-up', 'label' => 'Fare Management'],
            ['href' => 'analytics.php',       'icon' => 'fa-chart-simple',    'label' => 'Analytics and Statistics'],
            ['href' => 'report-generator.php','icon' => 'fa-file-pen',        'label' => 'Reports Management'],
            ['href' => 'settings.php',        'icon' => 'fa-cog',             'label' => 'System Settings'],
        ]
    ]
];

// Get current user's role from session
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Determine sidebar config (with fallback)
if ($userRole && isset($sidebarConfig[$userRole])) {
    $config = $sidebarConfig[$userRole];
} elseif (in_array($currentDir, ['LUPTO', 'MUNICIPAL', 'PICTO'])) {
    // Fallback based on directory for backward compatibility
    $dirToRole = [
        'LUPTO' => 'lupto',
        'MUNICIPAL' => 'municipal',
        'PICTO' => 'picto'
    ];
    $config = $sidebarConfig[$dirToRole[$currentDir]] ?? $sidebarConfig['lupto'];
} else {
    // Default fallback
    $config = $sidebarConfig['lupto'];
}

$navItems = $config['items'];
$brandName = $config['brand'];
$brandSub = $config['brand_sub'];
?>

<aside class="sidebar" id="sidebar">
    <!-- Logo / Brand -->
    <div class="sidebar-brand">
        <div class="brand-logo">
            <img src="<?= $basePath ?>images/san-fernando-seal.png" alt="San Fernando City Seal">
        </div>
        <div class="brand-info">
            <span class="brand-name"><?= htmlspecialchars($brandName) ?></span>
            <span class="brand-city"><?= htmlspecialchars($brandSub) ?></span>
        </div>
    </div>

    <!-- Primary Navigation -->
    <nav class="sidebar-nav" role="navigation" aria-label="Main navigation">
        <?php foreach ($navItems as $item):
            $active = ($currentPage === $item['href']) ? ' active' : '';
        ?>
        <a href="<?= htmlspecialchars($item['href']) ?>" class="nav-item<?= $active ?>" title="<?= htmlspecialchars($item['label']) ?>">
            <span class="nav-icon"><i class="fas <?= $item['icon'] ?>"></i></span>
            <span class="nav-label"><?= htmlspecialchars($item['label']) ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

</aside>

<script>
(function() {
    function initSidebarPrefetch() {
        const prefetched = new Set();
        
        function prefetchLink(url) {
            if (!url || url.startsWith('#') || url.includes('logout.php') || prefetched.has(url)) return;
            prefetched.add(url);
            
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        }
        
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-item');
        
        // Prefetch all links in the sidebar 500ms after load to make them instant
        setTimeout(() => {
            navLinks.forEach(item => {
                const href = item.getAttribute('href');
                if (href) prefetchLink(href);
            });
        }, 500);
        
        // Dynamic event listeners for hover/touch just in case
        navLinks.forEach(item => {
            const href = item.getAttribute('href');
            if (!href) return;
            
            item.addEventListener('mouseenter', () => prefetchLink(href));
            item.addEventListener('touchstart', () => prefetchLink(href), { passive: true });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarPrefetch);
    } else {
        initSidebarPrefetch();
    }
})();
</script>