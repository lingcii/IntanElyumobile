<?php
$currentNavView = $view ?? 'dashboard';
$navViewsList = ['dashboard', 'map', 'itinerary', 'leaderboard', 'profile'];
$curNavIdx = array_search($currentNavView, $navViewsList);
if ($curNavIdx === false) $curNavIdx = 0;
$initialIndicatorLeft = (10 + ($curNavIdx * 20)) . '%';
?>
<script>
window.onMagicNavItemClick = function(el) {
    try {
        var nav = document.getElementById('magic-nav');
        if (!nav) return;
        var items = nav.querySelectorAll('.magic-nav-item');
        items.forEach(function(item) {
            var svg = item.querySelector('svg');
            if (item === el) {
                item.classList.add('active');
                item.style.setProperty('color', '#ffffff', 'important');
                if (svg) {
                    svg.style.setProperty('stroke', '#ffffff', 'important');
                    svg.querySelectorAll('path, line, polygon, rect, circle').forEach(function(s) { s.style.setProperty('stroke', '#ffffff', 'important'); });
                }
            } else {
                item.classList.remove('active');
                item.style.setProperty('color', '#000000', 'important');
                if (svg) {
                    svg.style.setProperty('stroke', '#000000', 'important');
                    svg.querySelectorAll('path, line, polygon, rect, circle').forEach(function(s) { s.style.setProperty('stroke', '#000000', 'important'); });
                }
            }
        });
        var indicator = document.getElementById('magic-indicator');
        if (indicator && el.dataset.index !== undefined) {
            var idx = parseInt(el.dataset.index);
            indicator.style.left = (10 + (idx * 20)) + '%';
            indicator.style.boxShadow = 'none';
            indicator.style.filter = 'none';
            indicator.style.background = '#1e3a8a';
        }
    } catch(e) {}
};
</script>
<!-- Magic Bottom Navigation Bar -->
<div class="magic-nav" id="magic-nav">
    <div class="magic-nav-bg" style="background: #ffffff !important; background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12), 0 2px 8px rgba(15, 23, 42, 0.06) !important;">
        <svg class="magic-nav-svg" id="magic-nav-svg" viewBox="0 0 448 66" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            
            <!-- Bright white base for a light color aesthetic (we use rgba for dark mode tuning) -->
            <path id="magic-nav-path-base" d="" fill="#ffffff"  />
            <!-- Dynamic color tint -->
            <path id="magic-nav-path-tint" d="" fill="#ffffff" opacity="0" />
            <!-- Top gloss line -->
            <path id="magic-nav-path-edge" d="" fill="none" stroke="#e2e8f0" stroke-width="1.5" stroke-linejoin="round" />
        </svg>
    </div>  

    <div class="magic-nav-items">
        <a href="#" class="magic-nav-item <?= ($curNavIdx === 0) ? 'active' : '' ?>" data-index="0"  data-view="dashboard" data-color="#1e3a8a" onclick="window.onMagicNavItemClick(this); navigateTo('dashboard'); return false;">
            <span class="icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" />
                    <path d="M9 21V12h6v9" />
                </svg>
            </span>
        </a>
        <a href="#" class="magic-nav-item <?= ($curNavIdx === 1) ? 'active' : '' ?>" data-index="1"  data-view="map" data-color="#1e3a8a" onclick="window.onMagicNavItemClick(this); navigateTo('map'); return false;">
            <span class="icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                    <line x1="9" y1="3" x2="9" y2="18" />
                    <line x1="15" y1="6" x2="15" y2="21" />
                </svg>
            </span>
        </a>
        <a href="#" class="magic-nav-item <?= ($curNavIdx === 2) ? 'active' : '' ?>" data-index="2"  data-view="itinerary" data-color="#1e3a8a" onclick="window.onMagicNavItemClick(this); if(window.state && window.state.currentView === 'itinerary' && sessionStorage.getItem('editing_itinerary_id') && typeof window.cancelEditingSavedTrip === 'function'){ window.cancelEditingSavedTrip(); return false; } navigateTo('itinerary'); return false;">
            <span class="icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
            </span>
        </a>
        <a href="#" class="magic-nav-item <?= ($curNavIdx === 3) ? 'active' : '' ?>" data-index="3"  data-view="leaderboard" data-color="#1e3a8a" onclick="window.onMagicNavItemClick(this); navigateTo('leaderboard'); return false;">
            <span class="icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 21h8M12 17v4" />
                    <path d="M17 8c0 2.76-2.24 5-5 5S7 10.76 7 8V4h10v4z" />
                    <path d="M7 4H4v4a3 3 0 0 0 3 3" />
                    <path d="M17 4h3v4a3 3 0 0 1-3 3" />
                </svg>
            </span>
        </a>
        <a href="#" class="magic-nav-item <?= ($curNavIdx === 4) ? 'active' : '' ?>" data-index="4"  data-view="profile" data-color="#1e3a8a" onclick="window.onMagicNavItemClick(this); navigateTo('profile'); return false;">
            <span class="icon">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </span>
        </a>
    </div>

    <div class="magic-circle" id="magic-indicator" style="left: <?= $initialIndicatorLeft ?>; background: #1e3a8a !important; box-shadow: none !important; filter: none !important;">
        
    </div>
</div>
