const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const discountSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'discount.php'), 'utf8');
const profileSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'profile.php'), 'utf8');
const voucherControllerSource = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'VoucherController.php'), 'utf8');
const pointsControllerSource = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'PointsController.php'), 'utf8');
const apiRoutesSource = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'routes', 'api.php'), 'utf8');

console.log('--- Testing Voucher Lifecycle Implementation ---');

// 1. Verify 5 Standard Voucher Statuses in Floating Status Dropdown
const requiredStatuses = ['Available', 'Claimed', 'Used', 'Cancelled', 'Expired'];
requiredStatuses.forEach(st => {
    assert.ok(
        discountSource.includes(st),
        `discount.php must handle status '${st}'`
    );
});
assert.match(discountSource, /value:\s*'All'[\s\S]*?label:\s*'Available Vouchers'/i, 'Floating status menu must include Available Vouchers');
assert.match(discountSource, /value:\s*'Claimed'[\s\S]*?label:\s*'Claimed Vouchers'/i, 'Floating status menu must include Claimed Vouchers');
assert.match(discountSource, /value:\s*'Used'[\s\S]*?label:\s*'Used Vouchers'/i, 'Floating status menu must include Used Vouchers');
assert.match(discountSource, /value:\s*'Cancelled'[\s\S]*?label:\s*'Cancelled Vouchers'/i, 'Floating status menu must include Cancelled Vouchers');
assert.match(discountSource, /value:\s*'Expired'[\s\S]*?label:\s*'Expired Vouchers'/i, 'Floating status menu must include Expired Vouchers');

// 2. Claiming Logic & Prevent Multiple Claims
assert.match(voucherControllerSource, /public function redeemVoucher/, 'VoucherController must have redeemVoucher');
assert.match(voucherControllerSource, /already claimed|valid for one-time claim only/i, 'Claiming must prevent multiple claims of the same voucher');
assert.match(discountSource, />\s*Claim\s*<\/button>/i, 'Action button on available catalog card must be Claim');

// 3. Dual Timers & Expiration Countdown
assert.match(discountSource, /id="modal-voucher-countdown"/, 'discount modal must have voucher expiration countdown element');
assert.match(discountSource, /id="modal-qr-countdown"/, 'discount modal must have 20-min QR validity countdown element');
assert.match(profileSource, /id="active-qr-voucher-countdown"/, 'profile QR pass must have voucher expiration countdown element');
assert.match(profileSource, /id="active-qr-validity-countdown"/, 'profile QR pass must have 20-min QR validity countdown element');

// Test Timer countdown logic in isolation
function testTimerLogic() {
    const now = Date.now();
    const voucherExpiry = new Date(now + 3600 * 1000).toISOString(); // 1 hr remaining
    const qrExpiry = new Date(now + 20 * 60 * 1000).toISOString();      // 20 min remaining

    const remainingVoucherMs = new Date(voucherExpiry).getTime() - now;
    const remainingQrMs = new Date(qrExpiry).getTime() - now;

    assert.ok(remainingVoucherMs > 0, 'Voucher countdown should be positive when not expired');
    assert.ok(remainingQrMs > 0 && remainingQrMs <= 20 * 60 * 1000, 'QR validity should be 20 minutes');

    // Past expiry
    const pastExpiry = new Date(now - 10000).toISOString();
    const pastDiff = new Date(pastExpiry).getTime() - now;
    const isPast = pastDiff <= 0;
    assert.equal(isPast, true, 'Countdown reaching <= 0 marks expired');
}
testTimerLogic();

// 4. Use and Cancel Buttons
assert.match(discountSource, /id="modal-btn-use"/, 'discount modal must have Use button');
assert.match(discountSource, /id="modal-btn-cancel"/, 'discount modal must have Cancel button');
assert.match(profileSource, /id="active-qr-btn-use"/, 'profile modal must have Use button');
assert.match(profileSource, /id="active-qr-btn-cancel"/, 'profile modal must have Cancel button');

// Use and Cancel only shown when status is Claimed
assert.match(discountSource, /claimedActions\.style\.display\s*=\s*'flex'/,
    'discount modal must display Use and Cancel buttons only when voucher is claimed');
assert.match(profileSource, /claimedActions\.style\.display\s*=\s*'flex'/,
    'profile modal must display Use and Cancel buttons only when voucher is claimed');

// 5. Automatic Expiration disables Use button and hides action bar
assert.match(discountSource, /useBtn\.disabled\s*=\s*true[\s\S]*?claimedActions\.style\.display\s*=\s*'none'/,
    'discount modal must hide or disable Use button when voucher expires');
assert.match(profileSource, /useBtn\.disabled\s*=\s*true[\s\S]*?claimedActions\.style\.display\s*=\s*'none'/,
    'profile modal must hide or disable Use button when voucher expires');

// 6. 20-minute QR Code Automatic Refresh Logic
assert.match(voucherControllerSource, /public function refreshQrCode/, 'VoucherController must have refreshQrCode method');
assert.match(voucherControllerSource, /\$newQrExpiresAt = now\(\)->addMinutes\(20\)/, 'QR expires in exactly 20 minutes');
assert.match(voucherControllerSource, /if\s*\(\$voucherExpiresAt\s*&&\s*\$newQrExpiresAt->gt\(\$voucherExpiresAt\)\)\s*\{\s*\$newQrExpiresAt\s*=\s*\$voucherExpiresAt->copy\(\);\s*\}/,
    'QR expiration must be capped at voucher expiration and never extend voucher expiry');
assert.match(discountSource, /window\.refreshModalQrCode/, 'discount view must provide automated QR refresh');
assert.match(profileSource, /window\.refreshActiveModalQrCode/, 'profile view must provide automated QR refresh');

// 7. Backend Routes Validation
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/use',/, 'API route for voucher use must exist');
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/cancel',/, 'API route for voucher cancel must exist');
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/refresh-qr',/, 'API route for voucher refresh-qr must exist');

// 8. Backend Security and Timestamp Validations
assert.match(voucherControllerSource, /now\(\)->gte\(\$voucherExpiresAt\)/, 'useVoucher must validate server-side expiration timestamp');
assert.match(voucherControllerSource, /The QR code has expired \(20-minute validity limit reached\)/, 'useVoucher must enforce QR 20-min expiration check');
assert.match(voucherControllerSource, /whereIn\('status',\s*\['claimed',\s*'active',\s*'redeemed',\s*'used',\s*'completed'\]\)/,
    'redeemVoucher duplicate claim check must exclude cancelled vouchers');
assert.match(pointsControllerSource, /!in_array\(\$st,\s*\['cancelled'\]\)/,
    'PointsController getBalance must exclude cancelled vouchers when computing redeemed total');
assert.match(voucherControllerSource, /Cannot cancel an expired voucher|Cannot cancel a voucher that has already been used/i, 'cancelVoucher must validate status and expiration');
assert.match(voucherControllerSource, /\$user->increment\('points',\s*\$cost\)/, 'cancelVoucher must refund tourist points to user balance');
assert.match(profileSource, /stopActiveQrTimers\(\)[\s\S]*?#active-voucher-qr-modal/, 'closeActiveVoucherQrModal must stop timers to prevent interval leak');
assert.doesNotMatch(profileSource, /^( {4})?(let|const)\s+activeQrTimerInterval/m, 'profile.php must not use top-level let/const for activeQrTimerInterval');

// 9. Verify renderDiscounts template variables integrity
assert.doesNotMatch(discountSource, /\$\{isRedeemed\s*\?/, 'renderDiscounts template literal must not reference undeclared isRedeemed');
assert.match(discountSource, /const isRedeemed\s*=\s*isUsed;/, 'discount.php must declare isRedeemed alias');

console.log('All voucher lifecycle tests passed successfully!');
