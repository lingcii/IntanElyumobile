const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const publicMain = fs.readFileSync(path.join(root, 'public', 'assets', 'js', 'main.js'), 'utf8');
const style = fs.readFileSync(path.join(root, 'src', 'assets', 'css', 'style.css'), 'utf8');
const publicStyle = fs.readFileSync(path.join(root, 'public', 'assets', 'css', 'style.css'), 'utf8');
const mapView = fs.readFileSync(path.join(root, 'src', 'views', 'map.php'), 'utf8');
const tripMapView = fs.readFileSync(path.join(root, 'src', 'views', 'trip_map.php'), 'utf8');
const dashboardView = fs.readFileSync(path.join(root, 'src', 'views', 'dashboard.php'), 'utf8');
const leaderboardView = fs.readFileSync(path.join(root, 'src', 'views', 'leaderboard.php'), 'utf8');
const serviceWorker = fs.readFileSync(path.join(root, 'src', 'sw.js'), 'utf8');
const publicServiceWorker = fs.readFileSync(path.join(root, 'public', 'sw.js'), 'utf8');

assert.equal(publicMain, main, 'Capacitor public main.js must match the source copy');
assert.equal(publicStyle, style, 'Capacitor public style.css must match the source copy');
assert.equal(publicServiceWorker, serviceWorker, 'Capacitor public service worker must match the source copy');
assert.doesNotMatch(main, /setTimeout\(updateContent,\s*200\)/, 'navigation must not add an artificial delay');
assert.match(main, /if \(html && !isNetworkOffline\)[\s\S]*fetchViewPartial\(targetView, extraParams\)/,
    'cached screens must render while their template revalidates in the background');
assert.match(main, /window\._viewFetches\.has\(fetchUrl\)/, 'identical view requests must be deduplicated');
assert.match(main, /elyu_view_cache_v6_/, 'SPA templates must use the current cache generation');
assert.match(main, /k\.startsWith\('elyu_view_cache_v5_'\)/, 'stale executable view templates must be invalidated');
assert.match(main, /window\.isExpectedAbortError/, 'expected request cancellation must be recognized');
assert.match(main, /if \(window\.isExpectedAbortError\(err\)\) return;/,
    'expected request cancellation must not be logged as a fetch failure');
assert.match(main, /window\.clearExpiredAuthSession/, 'expired identity state must have a targeted cleanup path');
assert.match(main, /window\._cacheFetchInflight\.get\(cacheKey\)/, 'identical data requests must be deduplicated');
assert.match(main, /k\.startsWith\('elyu_view_cache_'\)/, 'logout must clear persisted view templates');
assert.match(main, /window\.refreshCurrentView = async function/);
assert.match(main, /#tourist-map/, 'pull-to-refresh must ignore map gestures');
assert.match(main, /window\._pullRefreshPromise/, 'pull-to-refresh must prevent concurrent refreshes');
assert.match(main, /viewsToPrewarm = \['dashboard', 'map', 'itinerary', 'leaderboard', 'profile'\]/,
    'idle prewarming must be limited to primary navigation screens');
assert.match(main, /intan_elyu_map_view_state/);
assert.match(mapView, /initialCenter[\s\S]*initialZoom[\s\S]*new maplibregl\.Map/,
    'the map must restore its previous center and zoom');
assert.match(mapView, /const municipalityMap = window\.mapInstance;[\s\S]*window\.mapInstance !== municipalityMap/,
    'late municipality callbacks must not mutate a removed or replaced map');
assert.match(tripMapView, /function isTripMapActive\(map\)[\s\S]*window\._tripMapInstance = tripMap/,
    'trip routing callbacks must be tied to the currently mounted map');
assert.match(serviceWorker, /isViewRequest && url\.searchParams\.get\('ajax'\) === '1'[\s\S]*if \(cached\)[\s\S]*event\.waitUntil\(network\)/,
    'view templates must use stale-while-revalidate without applying it to live APIs');
assert.doesNotMatch(serviceWorker, /'\.\/index\.php\?view=(?:discount|trending|puzzles|settings)&ajax=1'/,
    'service-worker install must not preload every secondary module');
assert.match(dashboardView, /if \(window\._dashboardInitPromise\) return window\._dashboardInitPromise/,
    'Home must coalesce overlapping initialization requests');
assert.match(dashboardView, /!forceRefresh && window\._dashboardLastInitCompletedAt[\s\S]*< 1500/,
    'Home lifecycle events must not immediately repeat a completed refresh');
assert.match(dashboardView, /_dashboardLastReconnectRefreshAt[\s\S]*< 2000[\s\S]*addEventListener\('online', refreshDashboardAfterReconnect\)[\s\S]*addEventListener\('appOnline', refreshDashboardAfterReconnect\)/,
    'Home must coalesce duplicate browser and app reconnect events');
assert.match(leaderboardView, /if \(window\._leaderboardFetchPromise\) return window\._leaderboardFetchPromise/,
    'Leaderboard must coalesce overlapping refresh requests');
assert.doesNotMatch(leaderboardView, /let _isFetchingLeaderboard/,
    'dynamically reloaded Leaderboard scripts must not redeclare lexical globals');
assert.doesNotMatch(leaderboardView, /const refreshLeaderboardOnce/,
    'Leaderboard refresh implementation must be safe to redefine after SPA navigation');
assert.match(dashboardView, /const isOfflineGuest = localStorage\.getItem/,
    'Home must define offline guest state before using it');
assert.match(leaderboardView, /if \(!window\._leaderboardLifecycleListenersBound\)/,
    'Leaderboard lifecycle listeners must only be registered once');
assert.match(leaderboardView, /_leaderboardLastViewRenderAt[\s\S]*< 250/,
    'duplicate document and window viewLoaded events must render Leaderboard once');

console.log('mobile performance tests passed');
