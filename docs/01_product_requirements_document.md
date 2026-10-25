# Intan-Elyu Tourism Management System
## Product Requirements Document (PRD)

| **Document Version** | 2.4.0 |
| **System Name** | Intan-Elyu Smart Tourism Platform |
| **Target Geography** | Province of La Union ("Elyu"), Philippines |
| **Last Updated** | October 2026 |
| **Target Platforms** | Android Native (Capacitor), Mobile Web (PWA), Desktop Admin Portal |

---

## 1. Executive Summary & Vision

The **Intan-Elyu Tourism Management System** is a unified digital smart-tourism ecosystem engineered to elevate travel, heritage preservation, local commerce, and municipal governance across the **20 municipalities of La Union, Philippines**. 

La Union is world-renowned for its premier surf breaks in San Juan, eco-tourism waterfalls, historical heritage sites, agricultural farms, and vibrant culinary scene. However, tourists often face inconsistent mobile network coverage in coastal and mountainous zones, fragmented travel information, and lack of verified local incentives.

**Intan-Elyu** solves these challenges through:
1. **Offline-First Tourist Mobile App**: A responsive mobile experience powered by Capacitor Android and progressive local caching, allowing seamless exploration, itinerary building, and destination check-ins even without an active cellular connection.
2. **Dynamic Gamification & Loyalty Loop**: Tourists earn Experience Points (XP) through verified visits, photo check-in submissions, destination reviews, and interactive mini-games, redeeming them for real-world merchant discount passes and vouchers.
3. **Data-Driven Governance & Tourism Administration**: Real-time analytics dashboards for the La Union Provincial Tourism Office (LUPTO), Public Information and Tourism Council (PITCO), Provincial Information and Creative Tourism Office (PICTO), and local municipal tourism officers.
4. **Partner Merchant Integration**: Connecting local businesses (surf schools, restaurants, cafes, hostels) directly with visitors via digital QR claim vouchers.

---

## 2. Target Audiences & User Personas

| User Persona | Role | Primary Goals | Key Pain Points |
| :--- | :--- | :--- | :--- |
| **The Solo Backpacker / Surfer** | Tourist | Discover live wave conditions, budget travel, spot check-ins, earn vouchers. | Intermittent beach cellular connectivity, unverified commute fare rates. |
| **The Group / Family Planner** | Tourist | Multi-day itineraries, track companions (total pax, males, females), budget estimation. | Complicated coordination, unexpected entrance fees, fragmented spot information. |
| **The Walk-in Visitor** | Guest Tourist | Fast spot search, map browsing, fare lookups without mandatory signup friction. | Forced account creation barriers before previewing local attractions. |
| **Municipal Tourism Staff** | Admin / Auditor | Verify photo check-ins, validate tourist counts, track spot foot-traffic. | Manual logbooks, unverified visitor counts, delayed reporting. |
| **Partner Merchant** | Business Partner | Accept tourist discount QR codes, verify voucher authenticity, increase foot traffic. | Counterfeiting concerns, complex redemption verification workflows. |

---

## 3. Product Features & Functional Requirements

### 3.1 Authentication & Account Management
* **Guest Exploration Mode**: Instant access to maps, public spot directories, fares, and voucher previews without entering credentials.
* **Tourist Registration & Verification**:
  * Email + Password with One-Time Password (OTP) email validation.
  * Google OAuth 2.0 integration for fast single-tap signup/login.
  * Demographic tracking (Age, Gender) for provincial tourism statistical compliance.
* **Two-Factor Authentication (2FA)**:
  * Optional or enforced secondary security verification via email OTP code.
  * Resend cooldowns, token expiration safeguards, and device session security.
* **Data Privacy & Terms Agreement**:
  * Explicit consent modal adhering to the **Philippine Data Privacy Act of 2012 (RA 10173)** before registration or profile completion.

### 3.2 Interactive Map & Geolocation Discovery
* **Real-time Map Interface**:
  * Built on Leaflet.js with high-performance tile rendering.
  * Categorized filter pills: *All, Attractions, Dining, Nature, Heritage, Nightlife, Accommodations, Surfing*.
  * Search bar with instant autocomplete across all 20 municipalities.
* **Destination Spot Details**:
  * Spot photography delivered via high-speed Cloudflare R2 CDN.
  * Entrance fee breakdown (Regular Adults, Children, PWD, Senior Citizens, Environmental Fees).
  * Operating hours, municipality assignment, contact channels, and amenity badges (parking, restrooms, private vehicle access).
* **Smart Routing & Distance Computation**:
  * Geodesic distance calculation and OSRM turn-by-turn driving / walking routes.
  * Dynamic travel time estimates based on real-time GPS coordinates.

### 3.3 Custom Itinerary Planner & Smart Travel Modes
* **Multi-Day Trip Organization**:
  * Custom trip titles, date ranges, and companion tracking (Total Pax, Companion Males, Companion Females).
  * Spot reordering with persistent index ordering.
* **Budget & Transportation Cost Estimator**:
  * Real-time calculation using official provincial Tricycle and Jeepney Fare Guides.
  * Travel mode toggle: *Commute (Public Utility Vehicles)* vs *Private Vehicle*.
* **Destination Check-in & Photo Verification**:
  * **Independent Location Check-in**: Fast GPS-radius validation marking spots as visited.
  * **Photo Proof Upload Modal**:
    * Clean 3-section layout: Royal Blue gradient header, pure white scrollable middle, light bottom action bar with Cancel (left) and Submit (right).
    * Multi-photo submission (up to 5 photos per destination) with client-side image compression.
    * Transparent review status (*Pending Review*, *Approved*, *Rejected* with explanation).

### 3.4 Offline-First Engine (`OfflineCheckinManager`)
* **Zero-Latency Local Operation**:
  * Full caching of saved trips, spot directories, and map metadata in LocalStorage and IndexedDB.
  * Offline check-ins and photo proofs are immediately queued locally with optimistic UI completion.
* **Automatic Background Synchronization**:
  * Listens for `window.addEventListener('online')` and app resume events.
  * Sequentially uploads queued binary proofs and check-in payloads to the backend API without user intervention.
  * Visual offline mode badge notifying users of queued sync operations.

### 3.5 GameZone, Gamification & Loyalty Rewards
* **Interactive Mini-Games**:
  * *Slide Puzzle*: Image tiles of iconic La Union landmarks.
  * *Word Scramble*: Elyu culture, Ilokano phrases, and heritage trivia.
  * *Memory Card Flip*: Spot recognition and eco-tourism themes.
  * *Elyu Trivia Quiz*: Municipal history and environmental etiquette.
* **Anti-Exploit Protections**:
  * Daily game reward caps preventing bot abuse.
  * Rate-limited point accruals and server-side verification.
* **Voucher Marketplace & Claim Passes**:
  * Browse partner merchant vouchers (e.g., 10% off surfing lessons, free beverage, hotel discounts).
  * Redeem points for digital claim passes with dynamic expiration timers and secure anti-screenshot QR codes.

### 3.6 Weather & Coastal Ocean Conditions
* **5-Day Weather Forecast**:
  * Accurate hourly temperature, humidity, precipitation chance, and weather status icons.
* **Surf & Marine Wave Conditions**:
  * Tailored for San Juan surf spots (Urbiztondo, Mona Liza Point, Jesus Point).
  * Wave height, swell direction, wind speed, and tide warning advisories.

### 3.7 Notifications & Travel Alerts
* **Administrative Broadcasts**: Local government disaster alerts, surf safety advisories, and municipal festival notices.
* **Activity Notifications**: Instant alerts when check-in proofs are approved, points are credited, or new vouchers become available.
* **Full Retention & Deletion**: Individual dismissal and "Clear All" with persistent backend sync.

---

## 4. Non-Functional Requirements (NFRs)

| Metric | Target | Rationale |
| :--- | :--- | :--- |
| **API Response Time** | < 250 ms (95th percentile) | Essential for responsive mobile browsing on 3G/4G connections. |
| **Offline Resilience** | 100% Core Read & Queue | App must remain completely functional during cellular dead-zones. |
| **Client Image Compression**| < 400 KB per photo | Photos compressed in-browser (1280px max, 80% quality) before upload to save mobile data. |
| **APK Binary Footprint** | Optimized release binary | Fast downloads and low disk footprint on budget Android handsets. |
| **Security Standards** | OWASP Top 10 Compliant | Bcrypt hashing, CSRF/CORS protections, Bearer auth, SQL injection parameterization. |
| **Regulatory Compliance** | RA 10173 (Data Privacy Act)| Mandatory consent checkboxes, privacy policy disclosures, right to data deletion. |

---

## 5. Success Metrics & Product KPIs

1. **Monthly Active Travelers (MAT)**: Growth in verified tourist accounts and guest explorer sessions.
2. **Offline Check-in Success Rate**: Percentage of offline queued check-ins successfully synced upon network recovery (>99.5% target).
3. **Merchant Foot-Traffic Conversion**: Number of QR claim codes verified and redeemed at partner merchants.
4. **Data Accuracy**: Provincial tourism staff coverage across all 20 municipalities in La Union.
