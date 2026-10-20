# GameZone & Rewards

The **GameZone** provides gamified educational challenges (puzzles, word scrambles, memory match, and trivia) that reward tourists with XP and redeemable points.

Connected Subsystems: [[offline-sync-engine]], [[api-and-database]]

---

## Reward Rules & Daily Caps

| Game Type | Daily XP / Points | Backend Action Log Keyword |
| :--- | :--- | :--- |
| **Sliding Block Puzzle** | 100 Points | `%Puzzle%` |
| **Memory Match** | 75 Points | `memory match` |
| **Word Scramble** | 75 Points | `word scramble` |
| **La Union Trivia** | 50 Points | `%Trivia%` |

---

## Anti-Exploit Security

1. **Client De-duplication:**  
   `OfflineGameClaimsManager` ignores repeated plays within the same session while offline. Only the first completion is queued.
2. **Server Enforcement:**  
   `PointsController.php` validates that no `activity_logs` entry exists for the user on the current calendar date (`toDateString()`). Subsequent attempts return HTTP `429 Too Many Requests`.

---

> [!IMPORTANT]
> Tourists can continue playing in practice mode as often as they wish; points are awarded once per calendar day.
