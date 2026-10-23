# Intan-Elyu Tourism Management System
## Technical Requirements Document (TRD)

| **Document Version** | 2.4.0 |
| **System Architecture** | Hybrid Offline-First Mobile PWA + Native Android (Capacitor) + Laravel REST API |
| **Backend Runtime** | PHP 8.2+ / Laravel 11/12 Framework |
| **Storage Infrastructure** | Cloudflare R2 S3-Compatible Object Storage + Railway MySQL / XAMPP MariaDB |
| **Mobile Runtime** | Capacitor 8.4.0 / Android SDK 34 / Java 21 |

---

## 1. System Architecture Overview

The Intan-Elyu ecosystem employs a multi-tiered, decoupled client-server architecture engineered specifically for offline resilience in remote travel regions:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER (Mobile App)                       │
│  ┌───────────────────────┐  ┌──────────────────────────────────────┐  │
│  │ Capacitor Android App │  │ Progressive Web App (PWA) / Browser  │  │
│  │  - Camera Plugin      │  │  - Service Worker Cache              │  │
│  │  - Geolocation Plugin │  │  - IndexedDB / LocalStorage Engine   │  │
│  │  - Network Watcher    │  │  - Canvas Image Compression Pipeline │  │
│  └───────────┬───────────┘  └──────────────────┬───────────────────┘  │
│              └────────────────┬────────────────┘                      │
│                               ▼                                       │
│                 OfflineCheckinManager (FIFO Queue)                    │
└───────────────────────────────┬────────────────────────────────────────┘
                                │ HTTPS / REST (Online or Sync)
                                ▼
┌────────────────────────────────────────────────────────────────────────┐
│                    EDGE & DISTRIBUTION LAYER                           │
│  - Cloudflare Tunnel / CDN Edge Cache                                 │
│  - Automatic DNS SSL Termination                                       │
│  - Cloudflare R2 Object Storage Mirror (*.r2.dev)                      │
└───────────────────────────────┬────────────────────────────────────────┘
                                │
                                ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        BACKEND APPLICATION LAYER                       │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │ Laravel 11/12 RESTful API Gateway                                │  │
│  │  - Token Authentication & 2FA Guard                              │  │
│  │  - Rate Limiter (auth.throttle:5,60)                             │  │
│  │  - Route Cache & Map Aggregation Engine                          │  │
│  │  - Cloudflare R2 Storage Driver (S3 v3 SDK)                      │  │
│  │  - Image Compressor & Binary Fallback Streamer                   │  │
│  └────────────────────────────┬─────────────────────────────────────┘  │
└───────────────────────────────┼────────────────────────────────────────┘
                                │
                                ▼
┌────────────────────────────────────────────────────────────────────────┐
│                          DATA PERSISTENCE LAYER                        │
│  - MySQL 8.0 / MariaDB 10.11 (InnoDB Engine, utf8mb4)                 │
│  - Cloudflare R2 Bucket: apks/, tourist_spots/, avatars/, proof_images│
└────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Frontend & Mobile Client Specifications

### 2.1 Technology Stack
* **Language & Runtime**: Modern ES6+ JavaScript, Semantic HTML5, Vanilla CSS3 (Glassmorphism & Fluid Design Tokens).
* **Native Hybrid Bridge**: Capacitor CLI 8.4.0, `@capacitor/android` 8.4.0, `@capacitor/camera` 8.2.1, `@capacitor/geolocation` 8.2.0, `@capacitor/network` 8.0.1, `@capacitor/browser` 8.0.5.
* **Mapping Engine**: Leaflet.js 1.9.4 with OpenStreetMap raster tiles, custom SVG markers, and OSRM (Open Source Routing Machine) routing polyline interpolation.
* **Icons & Typography**: FontAwesome 6.5.1 Pro/Free webfonts, Inter & Outfit typography families loaded locally to ensure offline font availability.

### 2.2 Client-Side Image Processing
To prevent mobile cellular data exhaustion and maximize server throughput, client devices perform hardware-accelerated Canvas compression before image transmission:
```javascript
window.compressImageFile = async function (fileOrBlob, maxDimension = 1280, quality = 0.8) {
    // Resize down to 1280px max bound preserving aspect ratio
    // Convert canvas buffer to image/jpeg at 80% compression
    // Reduces 8MB camera photos to ~180-350KB with zero perceptual loss
};
```

### 2.3 Offline Engine Architecture (`OfflineCheckinManager`)
1. **Network Interceptor**: Wraps API calls to detect `navigator.onLine === false` or network fetch timeout errors (`AbortController` 20-second threshold).
2. **Persistent Queueing**: Stores check-in metadata, geolocation latitude/longitude, and base64/blob image proof in `localStorage` under `intan_elyu_offline_queue`.
3. **Optimistic Local Mutation**: Immediately alters UI status badges to "Queued for Sync" and adds visit marks to cached trips so the user receives instant tactile feedback.
4. **Resumption Dispatcher**: Upon detection of online connectivity (`window.addEventListener('online')` or app lifecycle `resume`), triggers sequential FIFO background POST requests to `/api/tourist/itineraries/items/{id}/proof`.

---

## 3. Backend Architecture & API Specifications

### 3.1 Technology Stack & Core Packages
* **Framework**: Laravel 11/12 on PHP 8.2+ with Composer dependency management.
* **Web Server**: Nginx / Apache 2.4 on XAMPP (Local Dev) & Linux Containers on Railway.
* **Cloud Storage**: Flysystem S3 Driver (`league/flysystem-aws-s3-v3`) connected to Cloudflare R2 endpoints.
* **Authentication**: Token-based authentication using custom 64-character SHA-256 tokens stored in the `users.api_token` column with Bearer header parsing.

### 3.2 Dual-Tier Storage & Streaming Gateway
To bypass Internet Service Provider (ISP) DNS filtering and SSL certificate conflicts on raw `*.r2.dev` public buckets in the Philippines:
1. **Direct R2 S3 Upload**: Images are compressed on the server using Intervention Image / GD and written to Cloudflare R2 via S3 API keys.
2. **Backend Proxy Streamer (`/api/serve?file=...`)**:
   * Evaluates local `/storage/` directories first.
   * If not local, opens a non-blocking streaming pointer via `Storage::disk('r2')->readStream($path)`.
   * Outputs raw binary bytes with `Content-Type: image/jpeg` and `Cache-Control: public, max-age=604800, immutable`.

### 3.3 Rate Limiting & Security Defenses
* **Brute-Force Shield**: `auth.throttle:5,60` applied to `/api/auth/login`, `/api/auth/register`, and `/api/auth/verify-otp` (maximum 5 attempts per 60 seconds per IP).
* **Two-Factor Authentication**: Generates 6-digit cryptographic OTPs with 10-minute validity saved as hashed secrets in `users.two_factor_code` and `users.two_factor_expires_at`.
* **Universal CORS Preflight**: Dynamic header resolution for `Access-Control-Allow-Origin` allowing native `capacitor://localhost` and web clients.

---

## 4. Key API Endpoints Specification

| Route | Method | Access | Description |
| :--- | :--- | :--- | :--- |
| `/api/auth/register` | `POST` | Public (Throttled) | Creates new user; sends email verification OTP. |
| `/api/auth/login` | `POST` | Public (Throttled) | Validates credentials; returns token or 2FA challenge. |
| `/api/auth/2fa/verify` | `POST` | Public | Validates 2FA code; returns authenticated bearer token. |
| `/api/public/map` | `GET` | Public | Cached GeoJSON list of tourist spots with coordinates. |
| `/api/public/weather` | `GET` | Public | Live 5-day weather & surf condition forecast. |
| `/api/tourist/itineraries` | `GET`, `POST`| Tourist Bearer | Retrieves and creates multi-day itineraries. |
| `/api/tourist/itineraries/items/{id}/checkin` | `POST` | Tourist Bearer | Submits instant GPS location check-in. |
| `/api/tourist/itineraries/items/{id}/proof` | `POST` | Tourist Bearer | Multi-image proof upload (+50 points). |
| `/api/tourist/leaderboard` | `GET` | Tourist / Public | Returns ranked tourists by points & activities. |
| `/api/vouchers` | `GET` | Public / Tourist | Lists available merchant discount vouchers. |
| `/api/admin/proof-checkins/{id}/approve` | `POST` | Admin / Staff | Approves proof check-in and credits points. |

---

## 5. Android Build & Deployment Pipeline

* **Keystore Specification**:
  * Keystore: `intan-elyu-key.jks` located in `frontend/Mobile/android/app/`.
  * Key Alias: `intanelyu`
* **Compilation Command**:
  ```powershell
  cd frontend/Mobile/android
  .\gradlew.bat assembleRelease
  ```
* **Post-Build Sync Hook**:
  Gradle automatically mirrors fresh release APKs to `frontend/Mobile/public/downloads/intan-elyu.apk` via `afterEvaluate` build hooks.
* **Cloudflare R2 Direct Upload CLI**:
  ```powershell
  php artisan apk:upload-r2
  ```
  Streams the binary APK directly to Cloudflare R2 bucket (`apks/intan-elyu.apk`), enabling live worldwide downloads without taxing the primary web server.
