const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const src = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
const publicCopy = fs.readFileSync(path.join(__dirname, '..', 'public', 'assets', 'js', 'main.js'), 'utf8');

assert.equal(publicCopy, src, 'Capacitor public main.js must match the source copy');
assert.match(src, /MAINTENANCE_CHECK_INTERVAL_MS\s*=\s*2000/);
assert.match(
    src,
    /response\.status\s*===\s*503[\s\S]*setMobileMaintenanceMode\(true\)/,
    'HTTP 503 must enable the mobile maintenance screen'
);
assert.match(
    src,
    /response\.ok[\s\S]*setMobileMaintenanceMode\(false\)/,
    'a successful probe must disable the maintenance screen'
);
assert.match(src, /overlay\.id = 'mobile-maintenance-overlay'/);
assert.match(src, /Under Maintenance/);

console.log('maintenance mode sync tests passed');
