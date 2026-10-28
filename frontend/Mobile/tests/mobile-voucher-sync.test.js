const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'discount.php'), 'utf8');
const profileSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'profile.php'), 'utf8');
const pointsControllerSource = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'PointsController.php'), 'utf8');
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
assert.match(profileSource, /onclick="navigateTo\('discount'\)"[\s\S]*?View All Deals/,
    'View All Deals must open the complete Discounts & Vouchers page');
assert.match(profileSource, /window\.renderFullDealsList = function \(deals\)[\s\S]*?deals\.forEach\(/,
    'View All Deals must render every voucher passed to it without a three-item limit');
assert.doesNotMatch(profileSource, /const topVouchers = availableVouchers\.slice\(0, 3\)/,
    'profile rewards must show every latest available voucher');
assert.match(profileSource, />My Vouchers<\/h5>/,
    'profile claimed voucher section must be labelled My Vouchers');
assert.match(source, /localRedemption\.status = 'redeemed';[\s\S]*?fetchUserPointsAndRedemptions\(\);/,
    'a live merchant redemption must immediately update and refresh the mobile voucher list');
assert.match(source, /public\/redemptions\/\$\{encodeURIComponent\(code\)\}\/status`, \{\s*cache: 'no-store'/,
    'discount QR status polling must never reuse a stale claimed response');
assert.match(profileSource, /public\/redemptions\/\$\{encodeURIComponent\(code\)\}\/status`, \{\s*cache: 'no-store'/,
    'profile QR status polling must never reuse a stale claimed response');
assert.doesNotMatch(profileSource, /Array\.isArray\(vouchersPayload\.data\) && vouchersPayload\.data\.length > 0/,
    'an empty live catalog must be allowed to replace stale cached deals');
assert.match(source, /return imageUrl\(v\.image\.trim\(\)\)/,
    'discount voucher cards must use the shared image URL resolver');
assert.match(source, /function getDiscountBaseUrl\(\)/,
    'discount view must resolve backend URL consistently via window.getBackendUrl');
assert.match(source, /function getDiscountAuthToken\(\)/,
    'discount view must check all auth token storage keys');
assert.match(source, /nextSignature === lastVouchersCatalogSignature/,
    'discount catalog polling must skip full DOM rebuilds when catalog payload is unchanged');
assert.match(profileSource, /fetch\(backendUrl \+ '\/api\/vouchers\?fresh=' \+ Date\.now\(\)/,
    'profile voucher catalog fetch must bust proxy/CDN cache');
assert.match(profileSource, /JSON\.stringify\(\{ data: vouchersPayload\.data, timestamp: Date\.now\(\) \}\)/,
    'profile and discount views must share the same cached voucher shape');

const voucherSortStart = profileSource.indexOf('function sortVouchersNewestFirst');
const voucherSortEnd = profileSource.indexOf('window.renderPointsAndVouchersData', voucherSortStart);
assert.ok(voucherSortStart >= 0 && voucherSortEnd > voucherSortStart,
    'profile must provide deterministic newest-first voucher sorting');
const voucherSortContext = { Date, Number };
vm.createContext(voucherSortContext);
vm.runInContext(profileSource.slice(voucherSortStart, voucherSortEnd), voucherSortContext);
assert.deepEqual(
    Array.from(voucherSortContext.sortVouchersNewestFirst([
        { id: 1, claimed_at: '2026-10-20T09:00:00+08:00' },
        { id: 2, claimed_at: '2026-10-28T09:00:00+08:00' }
    ], 'claimed'), voucher => voucher.id),
    [2, 1],
    'My Vouchers preview must show the latest claimed voucher first'
);
assert.deepEqual(
    Array.from(voucherSortContext.sortVouchersNewestFirst([
        { id: 1, redeemed_at: '2026-10-28T08:00:00+08:00' },
        { id: 2, redeemed_at: '2026-10-28T10:00:00+08:00' }
    ], 'redeemed'), voucher => voucher.id),
    [2, 1],
    'voucher history must show the latest redeemed voucher first'
);
assert.match(profileSource, /const displayVouchers = sortedActiveVouchers\.slice\(0, 2\)/,
    'profile My Vouchers preview must contain the two latest claimed vouchers');
assert.match(profileSource, /window\.openFullVouchersModal[\s\S]*?fetchPointsAndVouchers\(true\)/,
    'opening My Vouchers must force a live refresh instead of accepting cached data');
assert.match(profileSource, /Default: 'all' -> shows BOTH active vouchers and history vouchers[\s\S]*?activeVouchers\.map\(renderActiveCard\)[\s\S]*?historyVouchers\.map\(renderHistoryCard\)/,
    'the All tab must include every claimed and redeemed voucher');
assert.match(pointsControllerSource, /\['redeemed', 'used', 'completed'\][\s\S]*?redeemed_at[\s\S]*?claimed_at/,
    'backend voucher ordering must use the appropriate redeemed or claimed timestamp');

console.log('mobile voucher sync tests passed');
