<?php
$pageTitle = 'User Manual';
$backRoute = 'settings';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<style>
.user-manual-wrapper {
    width: 100%;
    height: calc(100vh - 60px);
    display: flex;
    flex-direction: column;
    background: #060b19;
    overflow: hidden;
}
.user-manual-subbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: rgba(15, 23, 42, 0.95);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    flex-shrink: 0;
}
.user-manual-subbar-title {
    font-size: 13px;
    font-weight: 700;
    color: #38bdf8;
    display: flex;
    align-items: center;
    gap: 8px;
}
.user-manual-iframe {
    flex: 1;
    width: 100%;
    height: 100%;
    border: none;
    background: #060b19;
}
</style>

<div class="user-manual-wrapper has-header animate-slide-up">
    <div class="user-manual-subbar">
        <div class="user-manual-subbar-title">
            <i class="fa-solid fa-book-open"></i> Mobile App User Manual
        </div>
        <a href="user_manual_mobile.html" target="_blank" style="background: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); color: #38bdf8; padding: 5px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-up-right-from-square"></i> Fullscreen
        </a>
    </div>
    <iframe src="user_manual_mobile.html" class="user-manual-iframe" title="Intan Elyu Official User Manual"></iframe>
</div>

<script>
(function() {
    var backBtn = document.querySelector('.header-icon .fa-arrow-left');
    if (backBtn) {
        backBtn.closest('.header-icon').onclick = function() {
            if (typeof navigateTo === 'function') {
                navigateTo('settings');
            } else {
                history.back();
            }
        };
    }
})();
</script>
