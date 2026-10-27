const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'discount.php'), 'utf8');
const profileSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'profile.php'), 'utf8');
const mainSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
const start = source.indexOf('function isVoucherUpcoming');
const end = source.indexOf('function isVoucherFullyClaimed', start);
assert.ok(start >= 0 && end > start, 'upcoming voucher classifier must exist');

const context = { Date };
vm.createContext(context);
vm.runInContext(source.slice(start, end), context);

assert.equal(context.isVoucherUpcoming({ status: 'upcoming' }), true);
assert.equal(context.isVoucherUpcoming({ valid_from: '2999-01-01T00:00:00+08:00' }), true);
assert.equal(context.isVoucherUpcoming({ status: 'active', valid_from: '2020-01-01T00:00:00+08:00' }), false);

const claimedStart = source.indexOf('function isVoucherClaimed');
const claimedEnd = source.indexOf('function getUserRedemptionItems', claimedStart);
assert.ok(claimedStart >= 0 && claimedEnd > claimedStart, 'claimed voucher classifier must exist');
const claimedContext = {
    window: { _touristRedemptions: [] },
    getClaimedVouchers: () => []
};
vm.createContext(claimedContext);
vm.runInContext(source.slice(claimedStart, claimedEnd), claimedContext);
assert.equal(claimedContext.isVoucherClaimed({ id: 'db_1', dbId: 1, status: 'active' }), false,
    'an active catalog voucher must remain available');
assert.equal(claimedContext.isVoucherClaimed({ id: 'rdm_1', redemptionId: 1, status: 'active' }), true,
    'an active redemption must remain claimed');
assert.match(source, /window\.fetchLiveDatabaseVouchers\s*=\s*fetchLiveDatabaseVouchers/);
assert.match(source, /cache:\s*'no-store'/);
assert.match(source, /VOUCHERS_SYNC_INTERVAL_MS\s*=\s*1500/);
assert.match(source, /startVoucherCatalogSync\(\);\s*fetchUserPointsAndRedemptions\(\);/);
assert.ok(mainSource.includes("if (/\\.r2\\.dev$/i.test(parsed.hostname))"),
    'R2 images must be routed through the backend proxy on mobile networks');
assert.match(mainSource, /proxyBase \+ '\/api\/image\/' \+ r2Path/);
assert.match(mainSource, /\? 'https:\/\/api\.intan-elyu\.online'/,
    'local development must use the working hosted proxy for public R2 voucher images');
const imageResolverStart = mainSource.indexOf('window.getFullImageUrl = function');
const imageResolverEnd = mainSource.indexOf('// Extract initial view name', imageResolverStart);
const imageContext = {
    URL,
    window: { getBackendUrl: () => 'http://127.0.0.1:8000', placeholderImage: '' }
};
vm.createContext(imageContext);
vm.runInContext(mainSource.slice(imageResolverStart, imageResolverEnd), imageContext);
assert.equal(
    imageContext.window.getFullImageUrl('https://public-bucket.r2.dev/logo/BAUANG.png'),
    'https://api.intan-elyu.online/api/image/logo/BAUANG.png'
);
assert.match(profileSource, /window\.getFullImageUrl\(v\.image \|\| 'logo\/LUPTO\.png'\)/,
    'profile reward cards must use the shared image URL resolver');
assert.match(profileSource, /window\.openFullDealsModal[\s\S]*?modal\.style\.display = 'flex';\s*fetchProfileVoucherCatalog\(\);/,
    'View All Deals must refresh the complete live voucher catalog when opened');
assert.match(profileSource, /window\.renderFullDealsList = function \(deals\)[\s\S]*?deals\.forEach\(/,
    'View All Deals must render every voucher passed to it without a three-item limit');
assert.doesNotMatch(profileSource, /Array\.isArray\(vouchersPayload\.data\) && vouchersPayload\.data\.length > 0/,
    'an empty live catalog must be allowed to replace stale cached deals');
assert.match(source, /return imageUrl\(v\.image\.trim\(\)\)/,
    'discount voucher cards must use the shared image URL resolver');

console.log('mobile voucher sync tests passed');
