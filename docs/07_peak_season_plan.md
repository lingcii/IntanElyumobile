# Peak Season Strategy & Implementation Plan

## 1. Executive Summary & Problem Analysis

In **La Union ("Elyu")**, tourist volumes fluctuate based on distinct seasons and events:
1. **Surfing Season (Amihan Swell)**: October to March (Focus: San Juan, Bacnotan).
2. **Summer Vacation**: March to May (Highland streams, waterfalls like Tangadan, beach resorts).
3. **Major Festivals & Holidays**: Sillag Festival (Poro Point, April), Dinengdeng Festival (Agoo, May), Christmas/New Year week, Holy Week (Semana Santa).
4. **Weekend Influx**: High Friday-to-Sunday arrivals from Metro Manila and Baguio.

### Identified Discrepancies in Current Codebase
- **Client vs. Backend Mismatch**:
  - `frontend/Mobile/src/views/map.php` (line 4514) uses a hardcoded client-side check: `(currentMonth >= 9 || currentMonth <= 4)` with a **+20% surcharge**.
  - `backend/app/Services/CostEstimationService.php` (line 161) checks `[1, 3, 4, 5, 10, 11, 12] || isWeekend` with a **1.25x (25%) multiplier**.
- **No LGU Admin Flexibility**: Tourism officers cannot declare temporary festival surges (e.g., Sillag Festival, surf competitions) without editing source code.
- **Lack of Crowd Density Alerts**: Tourists arrive at crowded spots (Urbiztondo, Immuki) without prior warning or recommendations for alternative destinations.

---

## 2. Strategic Pillars

```
+--------------------------------------------------------------------------------+
|                           PEAK SEASON SYSTEM PILLARS                          |
+-------------------+--------------------+--------------------+------------------+
| 1. Dynamic Fares  | 2. Crowd Surge     | 3. Smart Itinerary | 4. LGU Admin     |
|    & Pricing      |    Monitoring      |    Time Buffers    |    Manager       |
|  Unified backend  |  Real-time crowd   |  Traffic delay     |  Calendar UI to  |
|  rates (+20%/25%) |  badges & alternate|  estimates (+25%)  |  set holidays &  |
|  with tariff note |  eco destinations  |  for travel dates  |  local festivals |
+-------------------+--------------------+--------------------+------------------+
```

---

## 3. Database Architecture

### Table: `tourism_seasons`

```sql
CREATE TABLE `tourism_seasons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'e.g., Surfing Season, Summer Peak, Sillag Festival',
  `type` ENUM('annual_season', 'event_festival', 'weekend_surge', 'custom') NOT NULL DEFAULT 'annual_season',
  `start_date` DATE NULL COMMENT 'Specific date for one-time or annual holiday',
  `end_date` DATE NULL COMMENT 'End date for fixed event',
  `start_month` TINYINT UNSIGNED NULL COMMENT '1-12 for recurring seasonal window',
  `end_month` TINYINT UNSIGNED NULL COMMENT '1-12 for recurring seasonal window',
  `applicable_days` JSON NULL COMMENT '["Friday", "Saturday", "Sunday"] or null for everyday',
  `municipality` VARCHAR(100) NULL COMMENT 'Null = Province-wide, or specific municipality',
  `fare_surcharge_percent` DECIMAL(5,2) NOT NULL DEFAULT 20.00 COMMENT '+20% fare surcharge',
  `cost_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.20 COMMENT '1.20x cost multiplier',
  `traffic_delay_multiplier` DECIMAL(3,2) NOT NULL DEFAULT 1.25 COMMENT '1.25x transit duration',
  `description` TEXT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Seed Data
| Name | Type | Period / Months | Scope | Surcharge | Multiplier |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Amihan Surfing Season** | `annual_season` | October to March | San Juan, Bacnotan | +20% | 1.20x |
| **Summer Beach & Falls** | `annual_season` | March 15 to May 31 | Province-wide | +20% | 1.20x |
| **Weekend Influx Surge** | `weekend_surge` | Fri 15:00 to Sun 23:59 | Province-wide | +15% | 1.15x |
| **Holy Week (Semana Santa)** | `event_festival` | Movable (March/April) | Province-wide | +25% | 1.25x |
| **Sillag Festival of Lights** | `event_festival` | April (Fixed) | San Fernando City | +20% | 1.20x |

---

## 4. API Endpoints

### 1. `GET /api/seasons/status`
Returns whether a specified date (or current date/time) is in peak season, active seasons, effective surcharge, and travel advisory notes.

### 2. `POST /api/tourist/itineraries/estimate-cost`
Updated to query `tourism_seasons` based on `trip_date`, returning both accurate seasonal cost estimates and highway traffic delay estimates.

### 3. `GET /api/spots/{id}/crowd-status`
Returns predicted crowd density based on spot category, time of day, day of week, and active peak season status (`low`, `moderate`, `high_surge`).

---

## 5. Mobile UI/UX Enhancements

1. **Map Transport Bottom Sheet (`map.php`)**:
   - Replaces hardcoded client banner with dynamic API data.
   - Shows badge: `☀️ Peak Season Active (Oct–May): +20% LGU Surcharge Included`.
   - Clear breakdown itemizing Base Fare and Peak Adjustment.
2. **Dashboard Travel Notice (`dashboard.php`)**:
   - Displays real-time seasonal advisory below weather forecast.
3. **Spot Details & Crowd Meter**:
   - Color-coded crowd badge (`Quiet`, `Moderate`, `Peak Surge`).
   - Alternative destination recommendation when prime spot is experiencing high density.
4. **Gamification Integration**:
   - "Off-Peak Explorer" bonus points (+50 PTS) for checking in at less-crowded eco-cultural spots.

---

## 6. Implementation Phases

- **Phase 1: Database & Backend Single Source of Truth**:
  Create migration, model, and `SeasonController` with unified status endpoint.
- **Phase 2: Client Harmonization**:
  Update `map.php` and `CostEstimationService.php` to use the unified API.
- **Phase 3: Itinerary Cost & Travel Time Buffering**:
  Add date-sensitive congestion buffers and transparent cost breakdowns in `itinerary.php`.
- **Phase 4: Crowd Level Indicators**:
  Implement crowd status pills on tourist spots with alternate destination suggestions.
- **Phase 5: Admin Festival / Peak Manager**:
  Provide a simple calendar UI in the Admin portal for setting local festivals and custom surge periods.
