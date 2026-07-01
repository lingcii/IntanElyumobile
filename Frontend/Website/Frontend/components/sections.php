<?php

$frontendRootPath = strtolower(str_replace('\\', '/', dirname(__DIR__)));
$entryFileDir = strtolower(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])));

$basePath = '';
if (str_starts_with($entryFileDir, $frontendRootPath)) {
    $relativePath = substr($entryFileDir, strlen($frontendRootPath));
    $depth = substr_count($relativePath, '/');
    for ($i = 0; $i < $depth; $i++) {
        $basePath .= '../';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?></title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Component CSS -->
    <link rel="stylesheet" href="<?= $basePath ?>css/components/header.css">
    <link rel="stylesheet" href="<?= $basePath ?>css/components/sidebar.css">
    <link rel="stylesheet" href="<?= $basePath ?>css/components/sections.css">
    
    <!-- Role-specific Base CSS -->
    <?php
    $userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'lupto';
    // Determine the correct CSS directory
    if ($userRole === 'picto') {
        $cssDir = 'PICTO';
    } elseif (str_ends_with($userRole, '_mto') || $userRole === 'municipal') {
        $cssDir = 'MUNICIPAL';
    } else {
        $cssDir = 'LUPTO';
    }
    ?>
    <link rel="stylesheet" href="<?= $basePath ?>css/<?= $cssDir ?>/base.css">

    <!-- Extra head content from page -->
    <?= isset($extraHeadContent) ? $extraHeadContent : '' ?>

    <!-- api-config.js must be in <head> so it runs before any inline or module scripts in the body -->
    <script src="<?= $basePath ?>scripts/api-config.js"></script>
</head>

<body>
    <!-- APPLY SIDEBAR STATE IMMEDIATELY BEFORE ANYTHING ELSE RENDERS -->
    <script>
    (function() {
        var isMobile = window.innerWidth <= 768;
        if (!isMobile) {
            var saved = localStorage.getItem('cpdo_sidebar_collapsed') === 'true';
            // Add/remove collapsed class RIGHT NOW before DOM is fully rendered
            document.documentElement.classList.toggle('sidebar-collapsed-initial', saved);
        }
    })();
    </script>

    <div class="app-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Header -->
            <?php include __DIR__ . '/header.php'; ?>

            <!-- Page Body -->
            <div class="page-body">
                <?= isset($pageContent) ? $pageContent : '' ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= $basePath ?>scripts/components/header.js"></script>
    <script src="<?= $basePath ?>scripts/components/sidebar.js"></script>
    <script src="<?= $basePath ?>scripts/map-cache.js"></script>
    <script src="<?= $basePath ?>scripts/spa-router.js"></script>
</body>
</html>
