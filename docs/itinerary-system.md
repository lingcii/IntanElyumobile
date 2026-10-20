# Itinerary System

The **Itinerary System** empowers tourists to design, customize, save, edit, and navigate multi-stop travel routes across La Union.

Connected Subsystems: [[offline-sync-engine]], [[api-and-database]], [[mobile-pwa-architecture]]

---

## Key Workflows

### 1. Itinerary Planning (`itinerary.php`)
- **Spot Selection:** Add and remove destinations from cached municipal catalogs.
- **Transport Mode Selection:** Own Car, Van, Jeepney, Tricycle, Motorcycle, Walking.
- **Route Optimization:** Supports Recommended (shortest) vs. Alternative routes.

### 2. Offline Trip Editing
- When editing a saved trip in airplane mode:
  - Updates offline-queued trips in-place without generating duplicates.
  - Stages updates for server-synced trips and marks `is_offline_update: true`.
  - Sends `PUT /api/tourist/itineraries/{id}` when reconnected.

### 3. Navigation & Route Drawing (`trip_map.php`)
- **Online Mode:** OSRM street network routing with turn-by-turn geometry.
- **Offline Fallback:** Geodesic fallback mode using local Haversine calculations and vehicle speed estimates.

---

> [!TIP]
> See [[offline-sync-engine]] for how edited trips are merged in `saved_trips.php`.
