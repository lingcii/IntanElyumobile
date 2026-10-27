const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const publicMain = fs.readFileSync(path.join(root, 'public', 'assets', 'js', 'main.js'), 'utf8');
const style = fs.readFileSync(path.join(root, 'src', 'assets', 'css', 'style.css'), 'utf8');
const publicStyle = fs.readFileSync(path.join(root, 'public', 'assets', 'css', 'style.css'), 'utf8');
const mapView = fs.readFileSync(path.join(root, 'src', 'views', 'map.php'), 'utf8');
const serviceWorker = fs.readFileSync(path.join(root, 'src', 'sw.js'), 'utf8');
const publicServiceWorker = fs.readFileSync(path.join(root, 'public', 'sw.js'), 'utf8');

assert.equal(publicMain, main, 'Capacitor public main.js must match the source copy');
assert.equal(publicStyle, style, 'Capacitor public style.css must match the source copy');
assert.equal(publicServiceWorker, serviceWorker, 'Capacitor public service worker must match the source copy');
assert.doesNotMatch(main, /setTimeout\(updateContent,\s*200\)/, 'navigation must not add an artificial delay');
assert.match(main, /if \(html && !isNetworkOffline\)[\s\S]*fetchViewPartial\(targetView, extraParams\)/,
    'cached screens must render while their template revalidates in the background');
assert.match(main, /window\._viewFetches\.has\(fetchUrl\)/, 'identical view requests must be deduplicated');
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
assert.match(serviceWorker, /isViewRequest && url\.searchParams\.get\('ajax'\) === '1'[\s\S]*if \(cached\)[\s\S]*event\.waitUntil\(network\)/,
    'view templates must use stale-while-revalidate without applying it to live APIs');
assert.doesNotMatch(serviceWorker, /'\.\/index\.php\?view=(?:discount|trending|puzzles|settings)&ajax=1'/,
    'service-worker install must not preload every secondary module');

console.log('mobile performance tests passed');
