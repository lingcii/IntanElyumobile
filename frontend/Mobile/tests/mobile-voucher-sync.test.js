const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'discount.php'), 'utf8');
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

console.log('mobile voucher sync tests passed');
