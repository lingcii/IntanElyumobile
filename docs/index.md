# Intan-Elyu Architecture Hub

Welcome to the **Intan-Elyu Tourism Management System** architectural knowledge graph.

This documentation uses Obsidian & Foam `[[wikilinks]]` so you can visualize relationships in Antigravity IDE using **Foam: Show Graph** or **Graphify**.

---

## System Subsystems

- [[offline-sync-engine]] — Background sync orchestration, local queues, and network reconnection handlers.
- [[itinerary-system]] — Interactive itinerary builder, travel mode selection, offline trip editing, and geodesic route mapping.
- [[gamezone-and-rewards]] — Mini-game logic, daily reward caps, anti-exploit protections, and point redemption.
- [[mobile-pwa-architecture]] — Capacitor Android runtime, service workers, asset caching, and client-side storage.
- [[api-and-database]] — Laravel RESTful API endpoints, token authentication, and MySQL relational schemas.
- [[mobile-erd-crows-foot]] — Complete Crow's Foot Notation ERD for the mobile tourist client and offline sync.
- [[mobile-network-and-auth-troubleshooting]] — Assessment of USB tethering, Google OAuth 400 errors, CORS, and leaderboard fallbacks.

---

> [!TIP]
> **View this as an interactive graph in your IDE:**  
> Press `Ctrl+Shift+P` $\rightarrow$ type `Foam: Show Graph` to see all nodes connected!
