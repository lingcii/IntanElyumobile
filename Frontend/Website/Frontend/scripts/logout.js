/**
 * logout.js
 * Handles the logout flow: calls the Laravel API to invalidate the session,
 * then redirects to login.php to destroy the PHP session as well.
 */
(function () {
    'use strict';

    /**
     * Determine the path to login.php relative to the current page.
     * Pages inside views/LUPTO/, views/PICTO/, views/MUNICIPAL/ are two levels deep.
     */
    function getLoginUrl() {
        const path = window.location.pathname;
        if (
            path.includes('/views/PICTO/') ||
            path.includes('/views/LUPTO/') ||
            path.includes('/views/MUNICIPAL/')
        ) {
            return '../../logout.php';
        }
        return 'logout.php';
    }

    /**
     * Perform logout: call the API endpoint to invalidate the Laravel session,
     * then redirect to logout.php to destroy the PHP session.
     */
    async function doLogout() {
        try {
            const base = window.API_CONFIG ? window.API_CONFIG.BASE_URL : '';
            if (base) {
                await fetch(`${base}/api/auth/logout`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' },
                });
            }
        } catch (_) {
            // Network error is non-fatal — still redirect to clear PHP session
        } finally {
            window.location.href = getLoginUrl();
        }
    }

    /**
     * Attach the logout handler to every element that links to logout.php.
     * This intercepts sidebar/header logout links so the API call fires first.
     */
    function bindLogoutLinks() {
        document.querySelectorAll('a[href*="logout.php"]').forEach(function (link) {
            // Avoid binding twice
            if (link.dataset.logoutBound) return;
            link.dataset.logoutBound = 'true';

            link.addEventListener('click', function (e) {
                e.preventDefault();
                doLogout();
            });
        });
    }

    // Bind immediately for links already in the DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindLogoutLinks);
    } else {
        bindLogoutLinks();
    }

    // Re-bind after SPA tab loads inject new DOM nodes
    document.addEventListener('tabshow', bindLogoutLinks, true);

    // Expose globally so it can be called directly if needed
    window.doLogout = doLogout;
})();
