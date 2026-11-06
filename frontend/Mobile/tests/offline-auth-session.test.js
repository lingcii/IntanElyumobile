const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.join(__dirname, '..');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const publicMain = fs.readFileSync(path.join(root, 'public', 'assets', 'js', 'main.js'), 'utf8');
const dashboard = fs.readFileSync(path.join(root, 'src', 'views', 'dashboard.php'), 'utf8');
const savedTrips = fs.readFileSync(path.join(root, 'src', 'views', 'saved_trips.php'), 'utf8');
const itinerary = fs.readFileSync(path.join(root, 'src', 'views', 'itinerary.php'), 'utf8');

const start = main.indexOf('window._lastNetworkDisconnectAt = 0;');
const end = main.indexOf('window.getPhilippineTimestampISO', start);
assert.ok(start >= 0 && end > start, 'offline auth-session guard must be defined early');

const storage = new Map();
const context = {
    Date,
    navigator: { onLine: false },
    localStorage: {
        getItem: key => storage.has(key) ? storage.get(key) : null,
        setItem: (key, value) => storage.set(key, String(value))
    },
    window: {}
};
vm.createContext(context);
vm.runInContext(main.slice(start, end), context);

assert.equal(context.window.shouldPreserveAuthSession(), true,
    'a real account session must be preserved when Android reports offline');
context.navigator.onLine = true;
context.window._lastNetworkDisconnectAt = 0;
assert.equal(context.window.shouldPreserveAuthSession(), false,
    'a confirmed online 401 may still expire an invalid session');
context.window._lastNetworkDisconnectAt = Date.now();
assert.equal(context.window.shouldPreserveAuthSession(), true,
    'the disconnect grace window must cover delayed WebView connectivity state');

assert.equal(publicMain, main, 'packaged and source main.js must stay synchronized');
assert.match(main, /response\.status === 401[\s\S]*?shouldPreserveAuthSession\(\)[\s\S]*?return getLocalTripsCombined\(\)/,
    'shared saved-trip fetch must use cached data instead of clearing auth while offline');
assert.match(dashboard, /res\.status === 401[\s\S]*?shouldPreserveAuthSession\(\)/,
    'dashboard must suppress automatic login redirects during a disconnect');
assert.match(savedTrips, /response\.status === 401[\s\S]*?shouldPreserveAuthSession\(\)[\s\S]*?return getLocalTripsCombined\(\)/,
    'saved trips must preserve the account and render cached trips while offline');
assert.match(itinerary, /response\.status === 401[\s\S]*?shouldPreserveAuthSession\(\)[\s\S]*?Network disconnected while saving itinerary/,
    'itinerary saves must enter the existing offline queue instead of logging out');

console.log('offline auth session tests passed');
