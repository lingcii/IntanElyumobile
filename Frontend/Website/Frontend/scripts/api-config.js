(function() {
    /**
     * api-config.js
     * Central configuration for all API calls.
     *
     * All JS scripts should import/use API_CONFIG instead of
     * hardcoding the backend URL so that switching backends
     * (dev → production) only requires changing this file.
     *
     * IMPORTANT: Never hardcode a host (e.g. 127.0.0.1 or localhost) anywhere
     * else in the codebase — always go through window.API_CONFIG.* below.
     * Mixing hosts breaks the session cookie because browsers treat
     * 127.0.0.1 and localhost as different origins, even on the same machine.
     */

    // ── Top-level guard ──────────────────────────────────────────────────────────
    // This exits immediately if the script was already loaded to prevent duplicate logic.
    if (window.__API_CONFIG_LOADED__) {
        console.warn('[api-config] Script already loaded — skipping duplicate execution.');
        return;
    }
    window.__API_CONFIG_LOADED__ = true;

    // Always derive the host from the CURRENT page location.
    // This guarantees API calls always match whatever host/port the
    // browser actually used to load the page, so the session cookie
    // (scoped to that same host) is always sent.
    const apiHost = window.location.hostname || '127.0.0.1';
    const apiPort = '8000';
    const baseUrl = `${window.location.protocol}//${apiHost}:${apiPort}`;

    window.API_CONFIG = {
        BASE_URL: baseUrl,
        activeRequests: {},

        // Role-scoped base paths
        PITCO:     `${baseUrl}/api/pitco`,
        LUPTO:     `${baseUrl}/api/lupto`,
        MUNICIPAL: `${baseUrl}/api/municipal`,
        AUTH:      `${baseUrl}/api/auth`,
        PROFILE:   `${baseUrl}/api/profile`,

        /**
         * Returns the XSRF token from the cookie set by Laravel.
         * Required for POST/PUT/DELETE requests even when using session-based auth.
         */
        getCsrfToken() {
            const match = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
            if (match) return decodeURIComponent(match.trim().split('=').slice(1).join('='));
            // Fallback: meta tag
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        async fetch(url, options = {}) {
            // Guard against accidental hardcoded hosts slipping through
            // from other parts of the codebase — warn loudly in dev.
            if (/^https?:\/\/(127\.0\.0\.1|localhost)/i.test(url) && !url.startsWith(baseUrl)) {
                console.warn(
                    `[api-config] URL "${url}" uses a different host than the current page (${baseUrl}). ` +
                    `This will break the session cookie. Use window.API_CONFIG.* instead of a hardcoded host.`
                );
            }

            const method = (options.method || 'GET').toUpperCase();
            let controller;

            if (method === 'GET') {
                try {
                    const urlObj = new URL(url, window.location.origin);
                    const pathKey = urlObj.pathname;

                    if (this.activeRequests[pathKey]) {
                        this.activeRequests[pathKey].abort();
                    }

                    controller = new AbortController();
                    this.activeRequests[pathKey] = controller;
                    options.signal = controller.signal;
                } catch (e) {
                    // If URL parsing fails, don't abort
                }
            }

            const defaults = {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    ...(options.headers || {}),
                },
            };
            // Content-Type is only needed when sending a request body (e.g. POST, PUT, PATCH)
            if (method !== 'GET' && method !== 'HEAD' && !defaults.headers['Content-Type']) {
                defaults.headers['Content-Type'] = 'application/json';
            }
            const mergedOptions = { ...defaults, ...options, headers: defaults.headers };

            let response;
            try {
                response = await fetch(url, mergedOptions);
            } catch (networkError) {
                if (networkError.name === 'AbortError') {
                    return new Promise(() => {});
                }
                throw new Error('Network error: cannot reach the server. Is Laravel running on port 8000?');
            } finally {
                if (method === 'GET' && controller) {
                    try {
                        const urlObj = new URL(url, window.location.origin);
                        const pathKey = urlObj.pathname;
                        if (this.activeRequests[pathKey] === controller) {
                            delete this.activeRequests[pathKey];
                        }
                    } catch (e) {}
                }
            }

            const text = await response.text();
            if (!text.trim()) {
                throw new Error(`Empty response (HTTP ${response.status})`);
            }

            let data;
            try {
                data = JSON.parse(text);
            } catch {
                throw new Error(`Non-JSON response (HTTP ${response.status}): ${text.slice(0, 200)}`);
            }

            if (!response.ok) {
                if (response.status === 401) {
                    console.warn('Session expired or unauthorized. Redirecting to login...');
                    let loginRedirect = 'login.php';
                    const path = window.location.pathname;
                    if (path.includes('/views/PICTO/') || path.includes('/views/LUPTO/') || path.includes('/views/MUNICIPAL/')) {
                        loginRedirect = '../../login.php';
                    }
                    window.location.href = loginRedirect;
                    return new Promise(() => {}); // Halt further Javascript processing
                }
                throw new Error(data.error || data.message || `HTTP ${response.status}`);
            }

            return data;
        },

        /** Convenience: GET request */
        get(url, params = {}) {
            const qs = Object.keys(params).length
                ? '?' + new URLSearchParams(params).toString()
                : '';
            return this.fetch(url + qs, { method: 'GET' });
        },

        /** Convenience: POST request */
        post(url, body = {}) {
            return this.fetch(url, { method: 'POST', body: JSON.stringify(body) });
        },

        /** Convenience: PUT request */
        put(url, body = {}) {
            return this.fetch(url, { method: 'PUT', body: JSON.stringify(body) });
        },

        /** Convenience: DELETE request */
        delete(url) {
            return this.fetch(url, { method: 'DELETE' });
        },
    };
})();