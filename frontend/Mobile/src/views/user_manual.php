<?php
$pageTitle = 'User Manual';
$backRoute = 'settings';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<style>
.user-manual-page {
    padding-top: max(calc(env(safe-area-inset-top) + 75px), 115px);
    padding-left: 16px;
    padding-right: 16px;
    padding-bottom: 80px;
    color: #f8fafc;
    font-family: 'Inter', sans-serif;
    max-width: 800px;
    margin: 0 auto;
}

#bottom-navigation {
    display: none !important;
}

.manual-hero {
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 58, 138, 0.4) 100%);
    border: 1px solid rgba(56, 189, 248, 0.25);
    border-radius: 24px;
    padding: 24px 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

.manual-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(56, 189, 248, 0.15);
    border: 1px solid rgba(56, 189, 248, 0.3);
    color: #38bdf8;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 100px;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.manual-hero h1 {
    font-size: 22px;
    font-weight: 900;
    margin: 0 0 8px 0;
    color: #ffffff;
    line-height: 1.2;
}

.manual-hero h1 span {
    color: #38bdf8;
}

.manual-hero p {
    font-size: 13px;
    color: rgba(148, 163, 184, 0.9);
    line-height: 1.5;
    margin: 0 0 16px 0;
}

.manual-search-box {
    position: relative;
    margin-bottom: 16px;
}

.manual-search-box input {
    width: 100%;
    padding: 12px 16px 12px 42px;
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(56, 189, 248, 0.3);
    border-radius: 14px;
    color: #ffffff;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
    box-sizing: border-box;
}

.manual-search-box input:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 12px rgba(56, 189, 248, 0.25);
}

.manual-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #38bdf8;
    font-size: 15px;
}

.manual-nav-chips {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 8px;
    scrollbar-width: none;
}
.manual-nav-chips::-webkit-scrollbar { display: none; }

.manual-chip {
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 100px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.manual-chip:hover, .manual-chip.active {
    background: rgba(56, 189, 248, 0.2);
    border-color: #38bdf8;
    color: #38bdf8;
}

.manual-section {
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.manual-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    padding-bottom: 12px;
}

.manual-section-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.manual-section-icon.blue { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
.manual-section-icon.purple { background: rgba(167, 139, 250, 0.15); color: #a78bfa; border: 1px solid rgba(167, 139, 250, 0.3); }
.manual-section-icon.green { background: rgba(52, 199, 89, 0.15); color: #34c759; border: 1px solid rgba(52, 199, 89, 0.3); }
.manual-section-icon.yellow { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); }
.manual-section-icon.red { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

.manual-section-title {
    font-size: 17px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 2px 0;
}

.manual-section-sub {
    font-size: 12px;
    color: rgba(148, 163, 184, 0.8);
    margin: 0;
}

.step-card {
    display: flex;
    gap: 12px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 14px;
    padding: 12px 14px;
    margin-bottom: 10px;
}

.step-num {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.18);
    color: #38bdf8;
    font-weight: 900;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.step-title {
    font-size: 13px;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 4px;
}

.step-desc {
    font-size: 12px;
    color: rgba(148, 163, 184, 0.85);
    line-height: 1.5;
}

.step-desc ul {
    margin: 6px 0 0 16px;
    padding: 0;
}

.step-desc li {
    margin-bottom: 4px;
}

.manual-info-box {
    background: rgba(56, 189, 248, 0.08);
    border: 1px solid rgba(56, 189, 248, 0.25);
    border-radius: 12px;
    padding: 12px;
    margin-top: 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 12px;
    color: #e2e8f0;
}

.manual-info-box i {
    color: #38bdf8;
    font-size: 16px;
    margin-top: 1px;
}

.manual-info-box.warning {
    background: rgba(245, 158, 11, 0.08);
    border-color: rgba(245, 158, 11, 0.3);
    color: #fef3c7;
}

.manual-info-box.warning i { color: #f59e0b; }

.grid-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.grid-card-item {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    padding: 12px;
    text-align: center;
}

.grid-card-icon {
    font-size: 22px;
    margin-bottom: 6px;
}

.grid-card-item h4 {
    font-size: 12px;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 4px 0;
}

.grid-card-item p {
    font-size: 10px;
    color: rgba(148, 163, 184, 0.8);
    margin: 0;
    line-height: 1.4;
}

.ref-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 12px;
}

.ref-table th {
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
    text-align: left;
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
}

.ref-table td {
    padding: 8px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    color: #cbd5e1;
}

.ref-table tr:last-child td {
    border-bottom: none;
}
</style>

<div class="user-manual-page has-header animate-slide-up">

    <!-- HERO -->
    <div class="manual-hero">
        <div class="manual-hero-badge"><i class="fa-solid fa-book-open"></i> Official System Guide</div>
        <h1>Intan Elyu <span>User Manual</span></h1>
        <p>Complete step-by-step guide for exploring La Union, Philippines &mdash; from trip planning to earning XP, completing AR check-ins, playing games, and redeeming local vouchers.</p>
        
        <!-- Search Box -->
        <div class="manual-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="manual-search" placeholder="Search guide (e.g. Map, XP, Check-in, Vouchers, 2FA)..." onkeyup="filterManual(this.value)">
        </div>

        <!-- Section Navigation Chips -->
        <div class="manual-nav-chips">
            <button type="button" onclick="selectManualChip('', this)" class="manual-chip active">Overview</button>
            <button type="button" onclick="selectManualChip('Splash Screen', this)" class="manual-chip">1. Splash</button>
            <button type="button" onclick="selectManualChip('Login & Registration', this)" class="manual-chip">2. Login</button>
            <button type="button" onclick="selectManualChip('Dashboard', this)" class="manual-chip">3. Dashboard</button>
            <button type="button" onclick="selectManualChip('Explore Map', this)" class="manual-chip">4. Map</button>
            <button type="button" onclick="selectManualChip('Itinerary Planner', this)" class="manual-chip">5. Itinerary</button>
            <button type="button" onclick="selectManualChip('AR Check-In', this)" class="manual-chip">6. AR Check-In</button>
            <button type="button" onclick="selectManualChip('Quests & Challenges', this)" class="manual-chip">7. Quests</button>
            <button type="button" onclick="selectManualChip('GameZone Mini-Games', this)" class="manual-chip">8. Games</button>
            <button type="button" onclick="selectManualChip('Leaderboard & Ranks', this)" class="manual-chip">9. Leaderboard</button>
            <button type="button" onclick="selectManualChip('Discounts & Vouchers', this)" class="manual-chip">10. Vouchers</button>
            <button type="button" onclick="selectManualChip('My Profile', this)" class="manual-chip">11. Profile</button>
            <button type="button" onclick="selectManualChip('Settings & Security', this)" class="manual-chip">12. Settings</button>
            <button type="button" onclick="selectManualChip('Quick Reference', this)" class="manual-chip">XP Guide</button>
        </div>
    </div>

    <!-- APP OVERVIEW -->
    <div class="manual-section" id="section-overview">
        <div class="manual-section-header">
            <div class="manual-section-icon blue"><i class="fa-solid fa-compass"></i></div>
            <div>
                <h2 class="manual-section-title">App Overview</h2>
                <p class="manual-section-sub">Gamified tourism companion for La Union, Philippines</p>
            </div>
        </div>
        <p style="font-size: 13px; color: rgba(148,163,184,0.9); line-height: 1.5; margin-bottom: 12px;">
            Intan Elyu provides smart interactive maps, AI-assisted itinerary planning, AR location verification, mini-games, and local merchant voucher rewards.
        </p>
        <div class="grid-cards">
            <div class="grid-card-item"><div class="grid-card-icon">🗺️</div><h4>Explore Map</h4><p>Discover spots, beaches, and heritage sites with GPS navigation.</p></div>
            <div class="grid-card-item"><div class="grid-card-icon">📅</div><h4>Itinerary Planner</h4><p>Build trips with live transport fares and route optimization.</p></div>
            <div class="grid-card-item"><div class="grid-card-icon">📸</div><h4>AR Check-In</h4><p>Scan your location & photo proof to earn +50 XP per spot.</p></div>
            <div class="grid-card-item"><div class="grid-card-icon">🎮</div><h4>GameZone</h4><p>Play Slide Puzzle, Memory Match, and Word Scramble for points.</p></div>
            <div class="grid-card-item"><div class="grid-card-icon">🏆</div><h4>Leaderboard</h4><p>Compete across La Union and claim top explorer title.</p></div>
            <div class="grid-card-item"><div class="grid-card-icon">🎟️</div><h4>Vouchers</h4><p>Redeem XP & Points for local dining, surf, and hotel discounts.</p></div>
        </div>
    </div>

    <!-- STEP 1: SPLASH -->
    <div class="manual-section" id="section-splash">
        <div class="manual-section-header">
            <div class="manual-section-icon blue"><i class="fa-solid fa-mobile-screen-button"></i></div>
            <div>
                <h2 class="manual-section-title">Step 1 &mdash; Splash Screen</h2>
                <p class="manual-section-sub">Welcome screen and automatic session detection</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Launch Application</div>
                <div class="step-desc">Open Intan Elyu on your mobile device. The animated logo screen appears.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Automatic Session Check</div>
                <div class="step-desc">The app checks for active user login:
                    <ul>
                        <li><strong>Logged In</strong> &rarr; Redirects to <strong>Dashboard</strong></li>
                        <li><strong>Not Logged In</strong> &rarr; Redirects to <strong>Login / Register</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: AUTH -->
    <div class="manual-section" id="section-auth">
        <div class="manual-section-header">
            <div class="manual-section-icon purple"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <h2 class="manual-section-title">Step 2 &mdash; Login &amp; Registration</h2>
                <p class="manual-section-sub">Account creation and security verification</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Register New Account</div>
                <div class="step-desc">Tap the <strong>Register</strong> tab, enter your First Name, Last Name, Email, and password, then accept the Terms.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Sign In</div>
                <div class="step-desc">Enter your email and password on the <strong>Login</strong> tab, then tap the arrow button to log in.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Google One-Tap Auth</div>
                <div class="step-desc">Tap <strong>Sign in with Google</strong> for instant authentication without password creation.</div>
            </div>
        </div>
    </div>

    <!-- STEP 3: DASHBOARD -->
    <div class="manual-section" id="section-dashboard">
        <div class="manual-section-header">
            <div class="manual-section-icon blue"><i class="fa-solid fa-gauge-high"></i></div>
            <div>
                <h2 class="manual-section-title">Step 3 &mdash; Dashboard</h2>
                <p class="manual-section-sub">Home screen, explorer profile, stats, and weather</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Explorer Profile Card</div>
                <div class="step-desc">Displays avatar, name, level badge, and XP progress bar toward your next level.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Live Weather &amp; 5-Day Forecast</div>
                <div class="step-desc">Check real-time temperature, wind, humidity, and forecast for La Union.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Quick Actions &amp; Trending</div>
                <div class="step-desc">Access Explore Map, Itinerary Planner, Trending Spots, and Quests directly.</div>
            </div>
        </div>
    </div>

    <!-- STEP 4: MAP -->
    <div class="manual-section" id="section-map">
        <div class="manual-section-header">
            <div class="manual-section-icon green"><i class="fa-solid fa-map-location-dot"></i></div>
            <div>
                <h2 class="manual-section-title">Step 4 &mdash; Explore Map</h2>
                <p class="manual-section-sub">Interactive 3D/2D map and destination discovery</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Browse Map Markers</div>
                <div class="step-desc">Tap any destination pin on the map to open the detail sheet with entrance fees, descriptions, and photos.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Filter by Category</div>
                <div class="step-desc">Filter spots by <strong>Beaches</strong>, <strong>Surfing</strong>, <strong>Food &amp; Dining</strong>, <strong>Heritage</strong>, or <strong>Waterfalls</strong>.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Add to Itinerary</div>
                <div class="step-desc">Tap <strong>Add to Itinerary</strong> on any spot to queue it in your draft trip planner.</div>
            </div>
        </div>
    </div>

    <!-- STEP 5: ITINERARY -->
    <div class="manual-section" id="section-itinerary">
        <div class="manual-section-header">
            <div class="manual-section-icon yellow"><i class="fa-solid fa-route"></i></div>
            <div>
                <h2 class="manual-section-title">Step 5 &mdash; Itinerary Planner</h2>
                <p class="manual-section-sub">AI route sequence, fare calculator, and budget planner</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Reorder Destinations</div>
                <div class="step-desc">Drag or shift destinations in your list to optimize your travel order.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Select Route &amp; Transport Mode</div>
                <div class="step-desc">Choose <strong>Recommended</strong>, <strong>Alternate</strong>, or <strong>Scenic Route</strong>, and pick your transport (Own Car, Taxi, Bus, Jeepney, Tricycle).</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Calculate Budget &amp; Save Trip</div>
                <div class="step-desc">Set your travel budget to view the automated budget pie chart. Tap <strong>Save Trip</strong> to store under <strong>My Saved Trips</strong>.</div>
            </div>
        </div>
    </div>

    <!-- STEP 6: AR CHECK-IN -->
    <div class="manual-section" id="section-checkin">
        <div class="manual-section-header">
            <div class="manual-section-icon red"><i class="fa-solid fa-camera"></i></div>
            <div>
                <h2 class="manual-section-title">Step 6 &mdash; AR Check-In</h2>
                <p class="manual-section-sub">Location verification and visit rewards</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Arrive at Destination</div>
                <div class="step-desc">Open AR Check-In when you are within proximity of the tourist spot.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Attach Photo Proof &amp; Verify GPS</div>
                <div class="step-desc">Snap or select a selfie photo at the destination, then tap <strong>Verify Location &amp; Submit</strong>.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Claim Reward</div>
                <div class="step-desc">Earn <strong>+50 XP</strong> and <strong>+50 Points</strong> automatically upon successful verification!</div>
            </div>
        </div>
    </div>

    <!-- STEP 7: QUESTS -->
    <div class="manual-section" id="section-quests">
        <div class="manual-section-header">
            <div class="manual-section-icon purple"><i class="fa-solid fa-scroll"></i></div>
            <div>
                <h2 class="manual-section-title">Step 7 &mdash; Quests &amp; Challenges</h2>
                <p class="manual-section-sub">Daily, weekly, and monthly explorer challenges</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">View Active Quests</div>
                <div class="step-desc">Check active challenges (e.g., *"Visit 3 San Juan Spots"*, *"Complete 1 Trip"*).</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Claim Reward Points</div>
                <div class="step-desc">When progress reaches 100%, tap <strong>Claim Quest Reward</strong> for bonus XP and Points.</div>
            </div>
        </div>
    </div>

    <!-- STEP 8: GAMEZONE -->
    <div class="manual-section" id="section-gamezone">
        <div class="manual-section-header">
            <div class="manual-section-icon blue"><i class="fa-solid fa-gamepad"></i></div>
            <div>
                <h2 class="manual-section-title">Step 8 &mdash; GameZone Mini-Games</h2>
                <p class="manual-section-sub">Interactive puzzles for extra reward points</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Slide Puzzle (+100 PTS)</div>
                <div class="step-desc">Rearrange image tiles of La Union landmarks into their complete picture.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Memory Match (+80 PTS)</div>
                <div class="step-desc">Flip cards and match matching pairs of tourist spots.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Word Scramble (+60 PTS)</div>
                <div class="step-desc">Unscramble letters to spell La Union municipalities and beaches.</div>
            </div>
        </div>
    </div>

    <!-- STEP 9: LEADERBOARD -->
    <div class="manual-section" id="section-leaderboard">
        <div class="manual-section-header">
            <div class="manual-section-icon yellow"><i class="fa-solid fa-trophy"></i></div>
            <div>
                <h2 class="manual-section-title">Step 9 &mdash; Leaderboard &amp; Ranks</h2>
                <p class="manual-section-sub">Community rankings and level progression</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Check Your Rank</div>
                <div class="step-desc">View your global rank among all tourists exploring La Union.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Unlock Explorer Titles</div>
                <div class="step-desc">Earn higher rank titles: *Novice Explorer* &rarr; *Beach Wanderer* &rarr; *Master Explorer of Elyu*.</div>
            </div>
        </div>
    </div>

    <!-- STEP 10: DISCOUNTS -->
    <div class="manual-section" id="section-vouchers">
        <div class="manual-section-header">
            <div class="manual-section-icon green"><i class="fa-solid fa-tags"></i></div>
            <div>
                <h2 class="manual-section-title">Step 10 &mdash; Discounts &amp; Vouchers</h2>
                <p class="manual-section-sub">Redeem points for dining, surf lessons, and hotels</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Redeem Points for Vouchers</div>
                <div class="step-desc">Use accumulated points to unlock discounts at partner restaurants, surf schools, and resorts.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Show QR Code at Merchant</div>
                <div class="step-desc">Open your redeemed voucher under <strong>My Vouchers</strong> and present the QR code to the merchant to apply your discount.</div>
            </div>
        </div>
    </div>

    <!-- STEP 11: PROFILE -->
    <div class="manual-section" id="section-profile">
        <div class="manual-section-header">
            <div class="manual-section-icon blue"><i class="fa-solid fa-user-circle"></i></div>
            <div>
                <h2 class="manual-section-title">Step 11 &mdash; My Profile</h2>
                <p class="manual-section-sub">Profile details, trip history, and preferences</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">Edit Details &amp; Avatar</div>
                <div class="step-desc">Update your profile picture, bio, and travel preference tags (Beach Lover, Foodie, Adventurer).</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">Review Trip History</div>
                <div class="step-desc">View all completed visits, check-in timestamps, and uploaded photos.</div>
            </div>
        </div>
    </div>

    <!-- STEP 12: SETTINGS -->
    <div class="manual-section" id="section-settings">
        <div class="manual-section-header">
            <div class="manual-section-icon red"><i class="fa-solid fa-sliders"></i></div>
            <div>
                <h2 class="manual-section-title">Step 12 &mdash; Settings &amp; Security</h2>
                <p class="manual-section-sub">Preferences, 2FA security, language, and account actions</p>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">1</div>
            <div class="step-content">
                <div class="step-title">App Preferences</div>
                <div class="step-desc">Toggle Push Notifications, Location Services, Offline Maps, and Cloud Sync.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">2</div>
            <div class="step-content">
                <div class="step-title">App Language &amp; 2FA</div>
                <div class="step-desc">Select preferred language (English, Tagalog, Ilocano) and enable Two-Factor Authentication.</div>
            </div>
        </div>
        <div class="step-card">
            <div class="step-num">3</div>
            <div class="step-content">
                <div class="step-title">Maintenance &amp; Sign Out</div>
                <div class="step-desc">Clear storage cache or sign out securely from your current device.</div>
            </div>
        </div>
    </div>

    <!-- QUICK REFERENCE -->
    <div class="manual-section" id="section-quickref">
        <div class="manual-section-header">
            <div class="manual-section-icon yellow"><i class="fa-solid fa-table-list"></i></div>
            <div>
                <h2 class="manual-section-title">Quick Reference &mdash; XP &amp; Points Matrix</h2>
                <p class="manual-section-sub">How to earn rewards across the system</p>
            </div>
        </div>
        <table class="ref-table">
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>XP</th>
                    <th>Points</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>📸 AR / Photo Check-In</td>
                    <td><strong style="color:#38bdf8;">+50 XP</strong></td>
                    <td><strong style="color:#38bdf8;">+50 PTS</strong></td>
                </tr>
                <tr>
                    <td>🧩 Solve Slide Puzzle</td>
                    <td>—</td>
                    <td><strong style="color:#34c759;">+100 PTS</strong></td>
                </tr>
                <tr>
                    <td>🃏 Complete Memory Match</td>
                    <td>—</td>
                    <td><strong style="color:#34c759;">+80 PTS</strong></td>
                </tr>
                <tr>
                    <td>🔤 Solve Word Scramble</td>
                    <td>—</td>
                    <td><strong style="color:#34c759;">+60 PTS</strong></td>
                </tr>
                <tr>
                    <td>📜 Daily Quest Completion</td>
                    <td><strong style="color:#38bdf8;">+100 XP</strong></td>
                    <td><strong style="color:#34c759;">+150 PTS</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
(function() {
    var backBtn = document.querySelector('.header-icon .fa-arrow-left');
    if (backBtn) {
        backBtn.closest('.header-icon').onclick = function() {
            if (typeof navigateTo === 'function') {
                navigateTo('settings');
            } else {
                history.back();
            }
        };
    }
})();

window.selectManualChip = function(keyword, btn) {
    if (btn) {
        document.querySelectorAll('.manual-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
    }
    const searchInput = document.getElementById('manual-search');
    if (searchInput) {
        searchInput.value = keyword;
    }
    window.filterManual(keyword);
};

window.filterManual = function(query) {
    const q = (query || '').toLowerCase().trim();
    const sections = document.querySelectorAll('.manual-section');
    sections.forEach(sec => {
        if (!q) {
            sec.style.display = 'block';
            return;
        }
        const text = sec.innerText.toLowerCase();
        if (text.includes(q)) {
            sec.style.display = 'block';
        } else {
            sec.style.display = 'none';
        }
    });
};
</script>
