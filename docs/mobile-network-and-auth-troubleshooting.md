# Mobile Networking, USB Tethering & Authentication Assessment

## Overview
This document assesses the architectural issues, network topology mismatches, and authentication quirks encountered when running the **Intan-Elyu Mobile Application** across different environments:
1. **Local PC Development** (`localhost:3000` / `localhost:8000`)
2. **Mobile Device via USB Tethering / Wi-Fi LAN** (`192.168.42.xxx` / `192.168.x.x`)
3. **Android Native APK** via Capacitor runtime
4. **Cloud Staging / Production** (`https://app.intan-elyu.online` / `https://api.intan-elyu.online`)

Related subsystems:
- [[mobile-pwa-architecture]] — Capacitor runtime, web assets, and platform detection.
- [[api-and-database]] — Laravel RESTful API endpoints, tokens, and CORS configuration.
- [[offline-sync-engine]] — Background queues and ambient offline mode.

---

## 1. Bug Assessment: Google OAuth 400 "Malformed Request"

### Symptoms
When tapping **"Sign in with Google"** on a mobile device connected via USB Tethering or visiting via local IP (`http://192.168.42.xxx:3000`), Google immediately rejects the request with:
> **400. That’s an error.**  
> The server cannot process the request because it is malformed. It should not be retried. That’s all we know.

### Root Cause
1. **RFC 6749 & RFC 8252 OAuth Specifications:**
   Google OAuth 2.0 strictly **forbids IP address literals** in the `redirect_uri` parameter (except IPv4 loopback literal `127.0.0.1`).
2. When testing over USB Tethering, `window.location.origin` is `http://192.168.42.xxx:3000`. The frontend generated:
   `redirect_uri=http://192.168.42.xxx:3000/index.php`
3. Google’s authorization server (`accounts.google.com/o/oauth2/v2/auth`) flags this as a syntax violation and returns HTTP 400 before displaying any account prompt.
4. **WebView Security Policy:** In native Android WebViews, Google disallows full-page OAuth redirection (`disallowed_useragent`).

### Resolution Applied
- Added proactive host validation in `auth.php`: when on private IPs (`192.168.*`, `10.*`, `172.*`), the app alerts the user that Google OAuth requires a registered top-level domain (`https://app.intan-elyu.online`) and guides them to use **Email/Password** or **Offline Mode**.
- For native Capacitor APK runtime, external browser intents (`_system` / Capacitor Browser plugin) are invoked to prevent embedded WebView blocking.

---

## 2. Bug Assessment: USB Tethering Port Mismatch & CORS Blocking

### Symptoms
Opening the mobile web version on a tethered phone resulted in:
- Failed API requests (login, profile, map spots, vouchers).
- Empty or failing leaderboard standings.
- Console errors: `Failed to fetch` or `CORS policy: No 'Access-Control-Allow-Origin' header is present`.

### Root Causes
1. **PHP Built-in Server Binding (`localhost:3000`):**
   In `frontend/Mobile/package.json`, the dev script was `php -S localhost:3000 router.php`. Loopback binding (`localhost`) ignores inbound traffic from the phone on `192.168.42.xxx`.
2. **Hardcoded Host Checks in `main.js` & `index.php`:**
   Both files only inspected `window.location.hostname === 'localhost'`. When accessed via an IP address like `192.168.42.xxx:3000`:
   - `index.php` defaulted `backendUrl` to the cloud API (`https://api.intan-elyu.online`).
   - `main.js` defaulted `getBackendUrl()` to port `3000` (the frontend router, not the port `8000` Laravel backend).
3. **CORS Restrictions (`backend/config/cors.php`):**
   `allowed_origins` explicitly listed localhost and production domains, but had an empty `allowed_origins_patterns`. Inbound requests with `Origin: http://192.168.42.xxx:3000` were rejected by Laravel's `HandleCors` middleware.
4. **Router Reverse Proxy (`router.php`):**
   `router.php` only set `$backendUrl = 'http://127.0.0.1:8000'` when `HTTP_HOST` contained `'localhost'`. All tethered traffic was proxied out to `https://api.intan-elyu.online`.

### Resolution Applied
- **[package.json](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/frontend/Mobile/package.json):** Updated script to `php -S 0.0.0.0:3000 router.php` to accept connections on all interfaces.
- **[cors.php](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/backend/config/cors.php):** Added regex patterns to `allowed_origins_patterns` supporting `192.168.*.*`, `10.*.*.*`, and `172.*.*.*`.
- **[index.php](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/frontend/Mobile/src/index.php) & [main.js](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/frontend/Mobile/src/assets/js/main.js):** Added `isLocalDevHost()` to dynamically map port `3000` on any local/tethered IP to port `8000`.
- **[router.php](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/frontend/Mobile/src/router.php):** Added `isLocalOrPrivateHost()` to route API requests to `http://127.0.0.1:8000` for all private subnets.

---

## 3. Bug Assessment: Leaderboard Data Inconsistency (APK vs Web)

### Symptoms
The leaderboard displayed different explorer rankings and scores between the Web browser and the native APK.

### Root Causes
1. **Separate Client State / LocalStorage:**
   `http://localhost:3000` (Web) and `https://app.intan-elyu.online` (APK) have isolated browser storage. The user was logged in on Web but unauthenticated on the APK.
2. **Missing Auth Token in Tier 4 Fallback:**
   In `frontend/Mobile/src/views/leaderboard.php`, Tier 4 fallback queried `https://api.intan-elyu.online/api/public/leaderboard?limit=10` without an `Authorization: Bearer` header. Even if logged in, the user's personal rank (`myRank`) was never returned.
3. **Empty Array Handling in Cache Engine:**
   `applyLeaderboardData()` only updated `rawLeadersList` if `list.length > 0`. If a backend returned an empty array (`[]`), the frontend failed to overwrite stale cached leaders.

### Resolution Applied
- **[leaderboard.php](file:///c:/xampp/htdocs/Intan-Elyu-Tourism-Management-System/frontend/Mobile/src/views/leaderboard.php):**
  - Updated `fetchLeaderboardWithFallbacks` with candidate endpoint iteration: direct local backend, relative router proxy (`/api/tourist/leaderboard`), and canonical production endpoints.
  - Forwarded the `Authorization: Bearer <token>` across all tiers to guarantee `myRank` resolution.
  - Corrected `applyLeaderboardData()` to update `rawLeadersList` whenever `list` is a valid array (including `[]`).

---

## 4. Environment Verification Matrix

| Test Mode | Hostname | Backend Target | Supported Auth | Leaderboard Source |
| :--- | :--- | :--- | :--- | :--- |
| **PC Web** | `localhost:3000` | `http://localhost:8000` | Email / Pass, Google GIS | Local MySQL / Live CTE |
| **USB Tethering Web** | `192.168.42.xxx:3000` | `http://192.168.42.xxx:8000` | Email / Pass, Offline Mode | Local Backend via Proxy |
| **Native APK** | `https://app.intan-elyu.online` | `https://api.intan-elyu.online` | Email / Pass, Google Auth | Cloud API / Named Tunnel |
| **Offline Mode** | Any | None (Local Storage Cache) | Saved Profile / Guest | Cached Standings |

---

## Related Documents
- [[index]] — Return to Architecture Hub
- [[mobile-pwa-architecture]] — Capacitor WebView & Asset Interceptor Details
- [[api-and-database]] — Backend Routing & Relational Schema
