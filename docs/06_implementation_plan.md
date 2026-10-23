# Intan-Elyu Tourism Management System
## Implementation Plan & Deployment Roadmap

| **Document Version** | 2.4.0 |
| **System** | Intan-Elyu Smart Tourism Platform |
| **Execution Strategy** | Phased Agile Sprints with Offline-First Quality Gates |
| **Target Platforms** | Native Android Release (Capacitor), Mobile Web (PWA), Cloudflare Infrastructure |

---

## 1. Implementation Overview & Objectives

The primary objective of this implementation plan is to transition the **Intan-Elyu Tourism Management System** from active staging development to a battle-tested, high-performance production ecosystem for the Province of La Union.

Key technical milestones:
1. Deliver a responsive mobile app with native Android capabilities (Camera, Geolocation, Network Watcher).
2. Guarantee 100% offline usability in cellular dead-zones via the `OfflineCheckinManager`.
3. Provide high-speed media delivery via Cloudflare R2 object storage with local streaming proxies.
4. Establish seamless municipal administration tools for spot audits, photo check-in verification, and foot-traffic analytics.

---

## 2. Phased Rollout Roadmap

```
PHASE 1: Core Architecture & Backend API  ───► [Completed & Verified]
PHASE 2: Mobile UI/UX & Native Android     ───► [Completed & Verified]
PHASE 3: Offline-First Engine & Sync      ───► [Completed & Verified]
PHASE 4: Gamification & Rewards Ecosystem ───► [Completed & Verified]
PHASE 5: QA Testing, Linting & Hardening  ───► [Current Stage]
PHASE 6: Cloudflare Edge & Production App ───► [Active Rollout]
```

---

### Phase 1: Core Foundation & Backend API Gateway (Sprints 1–3)
* **Deliverables**:
  * Implement normalized relational database schema (67 migrations) covering all 20 municipalities of La Union.
  * Construct rate-limited authentication (`auth.throttle:5,60`) with email OTP verification and optional/enforced Two-Factor Authentication (2FA).
  * Configure dual-tier storage driver integrating Flysystem AWS S3 SDK with Cloudflare R2 bucket (`apks/`, `tourist_spots/`, `avatars/`, `proof_images/`).
  * Implement binary streaming fallback endpoint (`/api/serve?file=...`) to prevent ISP DNS filtering on `.r2.dev` domains.
* **Acceptance Criteria**:
  * 100% passing automated migration suite.
  * API response latency < 250ms for public endpoints.

### Phase 2: Mobile UI/UX & Capacitor Android Build (Sprints 4–6)
* **Deliverables**:
  * Implement mobile design system tokens: Deep Royal Blue (`#1e3a8a`), Surf Cyan (`#38bdf8`), Emerald Green (`#10b981`), and pure white content surfaces.
  * Construct standardized 3-section modal architecture (Royal Blue Header, White Middle Body, Action Footer with Cancel left / Submit right).
  * Configure floating bottom navigation bar with automatic backdrop blur suppression (`modal-blurred` class) when sheets or dialogs are active.
  * Integrate Capacitor Native Plugins: `@capacitor/camera`, `@capacitor/geolocation`, `@capacitor/network`, `@capacitor/browser`.
* **Acceptance Criteria**:
  * Zero UI overflow or navigation clipping on viewports between 360px and 430px.
  * Native camera capture launches cleanly on Android 8.0 through Android 15.

### Phase 3: Offline-First Engine & Field Resilience (Sprints 7–9)
* **Deliverables**:
  * Develop client-side canvas compression (`compressImageFile`) reducing 8MB raw photos to <350KB before transmission.
  * Implement `OfflineCheckinManager` with persistent FIFO queue in `localStorage` and `IndexedDB`.
  * Implement optimistic local state mutations (instant visited checkmarks and celebratory confetti feedback).
  * Build automatic reconnection listener (`window.addEventListener('online')`) with exponential backoff retry.
* **Acceptance Criteria**:
  * Seamless queueing and synchronization of up to 5 multi-photo check-ins during complete airplane-mode simulation.

### Phase 4: Gamification, Rewards & Partner Merchants (Sprints 10–12)
* **Deliverables**:
  * Build interactive GameZone hub: *Slide Puzzle*, *Word Scramble*, *Memory Card Flip*, and *Elyu Trivia*.
  * Implement server-side anti-cheat guardrails and daily point accrual caps.
  * Deploy Voucher Marketplace with dynamic, self-expiring digital QR claim passes.
  * Establish merchant QR scanner portal and redemption verification audit logging.
* **Acceptance Criteria**:
  * Verified points allocation upon destination check-in approval (+50 XP).
  * One-time QR claim pass invalidation upon merchant scan.

### Phase 5: Verification, Quality Assurance & Security Hardening (Sprints 13–14)
* **Deliverables**:
  * Continuous PHP syntax validation across all views (`php -l`).
  * Security audit verifying bcrypt hashing, CORS preflight guarantees, and SQL parameterization.
  * Gradle Android release build optimization (`.\gradlew.bat assembleRelease`) with signing key `intan-elyu-key.jks`.
  * Cloudflare R2 direct APK streaming CLI integration (`php artisan apk:upload-r2`).
* **Acceptance Criteria**:
  * Zero PHP linting errors across all backend and frontend files.
  * Successful release APK assembly with verified download accessibility.

### Phase 6: Production Launch & Cloudflare Distribution (Sprint 15)
* **Deliverables**:
  * Deploy Cloudflare Zero Trust Tunnel for high-availability backend API routing.
  * Publish compiled release APK to Cloudflare R2 CDN mirror.
  * Distribute progressive web application with service worker asset caching.
  * Train municipal tourism officers (LUPTO, PITCO, PICTO) on audit workflows.
* **Acceptance Criteria**:
  * Public web visitors can download fresh APKs directly from high-speed mirror without web server CPU spikes.

---

## 3. Work Breakdown Structure (WBS) & Responsibility Matrix

| Task Item | Module | Primary Owner | Status | Output Artifact |
| :--- | :--- | :--- | :--- | :--- |
| **WBS 1.1** | Database Migrations & Seeders | Backend Team | Completed | `database/migrations/*` |
| **WBS 1.2** | 2FA Lifecycle & Auth Safeguards | Security / Backend | Completed | `LoginController.php` |
| **WBS 2.1** | 3-Section Proof Modal Redesign | Frontend Team | Completed | `saved_trips.php`, `trip_map.php` |
| **WBS 2.2** | Weather Forecast Sheet Refactor | Frontend Team | Completed | `dashboard.php`, `modals.css` |
| **WBS 3.1** | Offline Queue Engine | Mobile Team | Completed | `OfflineCheckinManager.js` |
| **WBS 4.1** | GameZone Puzzles & Rewards | Mobile Team | Completed | `puzzles.php`, `PuzzleController` |
| **WBS 5.1** | Release Gradle Compilation | DevOps / Android | Completed | `app-release.apk` |
| **WBS 5.2** | Cloudflare R2 Mirror Upload | DevOps / Cloud | Completed | `UploadApkToR2.php` |

---

## 4. Operational Maintenance & Runbook

### 4.1 Rebuilding & Deploying Mobile Android APK
Whenever mobile view templates, stylesheets, or client scripts are updated:
```powershell
# 1. Sync updated web assets to Android package
cd frontend/Mobile
npx cap copy android

# 2. Compile signed Android release APK
cd android
.\gradlew.bat assembleRelease

# 3. Stream compiled APK directly to Cloudflare R2 mirror
cd ../../../backend
php artisan apk:upload-r2
```

### 4.2 Cloudflare R2 Image Verification & Repair
To verify that all tourist spot photographs in the database exist on Cloudflare R2:
```http
POST /api/admin/spots/scan-r2?repair=true
Authorization: Bearer <ADMIN_TOKEN>
```
Scans all destination records, maps missing links, uploads repaired assets, and invalidates public map caches.
