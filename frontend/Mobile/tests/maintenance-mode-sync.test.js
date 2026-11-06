const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const src = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
const publicCopy = fs.readFileSync(path.join(__dirname, '..', 'public', 'assets', 'js', 'main.js'), 'utf8');
const apiRoutes = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'routes', 'api.php'), 'utf8');

assert.equal(publicCopy, src, 'Capacitor public main.js must match the source copy');
assert.match(src, /MAINTENANCE_CHECK_INTERVAL_MS\s*=\s*2000/);
assert.match(
    src,
    /response\.status\s*===\s*503[\s\S]*setMobileMaintenanceMode\(true\)/,
    'HTTP 503 must enable the mobile maintenance screen'
);
assert.match(
    src,
    /public\/maintenance-status\?_t=[\s\S]*response\.json\(\)[\s\S]*status\?\.active === true/,
    'the app must follow the admin portal mobile maintenance target'
);
assert.match(src, /overlay\.id = 'mobile-maintenance-overlay'/);
assert.match(src, /Under Maintenance/);
assert.match(src, /Maintenance in progress[\s\S]*Your saved trips and account are safe/,
    'maintenance UI must clearly communicate status and data safety');
assert.match(src, /mm-retry\.is-checking[\s\S]*Checking\.\.\./,
    'Check Again must show responsive loading feedback');
assert.match(src, /prefers-reduced-motion:reduce/,
    'maintenance animations must respect reduced-motion accessibility');
assert.match(src, /mobile-maintenance-message[\s\S]*status\?\.message/,
    'the maintenance screen must support a custom admin message');
assert.match(apiRoutes, /maintenance-status[\s\S]*maintenance_mode[\s\S]*in_array\('mobile', \$targets, true\)/,
    'the public maintenance endpoint must read the shared mobile target');

console.log('maintenance mode sync tests passed');
