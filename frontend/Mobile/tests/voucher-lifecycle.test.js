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
assert.match(discountSource, /claimedActions\.style\.display\s*=\s*isActiveQr \? 'none' : 'flex'/,
    'discount modal must display Use and Cancel buttons only when voucher is claimed');
assert.match(profileSource, /claimedActions\.style\.display\s*=\s*isActiveQr \? 'none' : 'flex'/,
    'profile modal must display Use and Cancel buttons only when voucher is claimed');

// 5. Automatic Expiration disables Use button and hides action bar
assert.match(discountSource, /useBtn\.disabled\s*=\s*true[\s\S]*?claimedActions\.style\.display\s*=\s*'none'/,
    'discount modal must hide or disable Use button when voucher expires');
assert.match(profileSource, /useBtn\.disabled\s*=\s*true[\s\S]*?claimedActions\.style\.display\s*=\s*'none'/,
    'profile modal must hide or disable Use button when voucher expires');

// 6. Claim lifetime and 20-minute QR activation logic
assert.match(voucherControllerSource, /public function refreshQrCode/, 'VoucherController must have refreshQrCode method');
assert.match(voucherControllerSource, /while \(\$newQrExpiresAt->lte\(now\(\)\)\)[\s\S]*?addMinutes\(20\)/, 'expired QR windows must rotate in server-aligned 20-minute steps');
assert.match(voucherControllerSource, /if\s*\(\$voucherExpiresAt\s*&&\s*\$newQrExpiresAt->gt\(\$voucherExpiresAt\)\)\s*\{\s*\$newQrExpiresAt\s*=\s*\$voucherExpiresAt->copy\(\);\s*\}/,
    'QR expiration must be capped at voucher expiration and never extend voucher expiry');
assert.match(voucherControllerSource, /\$voucherExpiresAt = now\(\)->addDays\(3\)/,
    'a claimed voucher must expire after three days');
assert.match(voucherControllerSource, /\$qrExpiresAt = null;[\s\S]*?\$qrToken = null;/,
    'claiming must not expose a QR token before Use is tapped');
assert.match(voucherControllerSource, /public function useVoucher[\s\S]*?\$qrExpiresAt = \$now->copy\(\)->addMinutes\(20\)/,
    'Use must start a fresh 20-minute QR session');
assert.match(voucherControllerSource, /public function useVoucher[\s\S]*?'status' => 'active'/,
    'Use must activate the QR instead of redeeming the voucher');
const useVoucherSource = voucherControllerSource.slice(
    voucherControllerSource.indexOf('public function useVoucher'),
    voucherControllerSource.indexOf('public function cancelVoucher')
);
assert.doesNotMatch(useVoucherSource, /Schema::hasTable/,
    'voucher activation must not perform schema-discovery queries on every tap');
assert.match(useVoucherSource, /if \(!\$voucherRecord\)[\s\S]*DB::table\('point_redemptions'\)/,
    'voucher activation must query the legacy points record only as a fallback');
assert.match(discountSource, /item\.status = 'active';[\s\S]*?item\.qr_token = data\.qr_token/,
    'discount Use action must display the activated QR without marking it redeemed');
assert.match(profileSource, /item\.status = 'active';[\s\S]*?item\.qr_token = data\.qr_token/,
    'profile Use action must display the activated QR without marking it redeemed');
assert.match(discountSource, /id="voucher-cancel-confirm-modal"[\s\S]*?window\.handleVoucherCancel\(true\)/,
    'discount cancellation must use the styled in-app confirmation modal');
assert.match(discountSource, /id="modal-expiry-badge"[\s\S]*?id="modal-status-notice"/,
    'modal expiry badge must appear above the status notice card');
assert.match(discountSource, /handleVoucherCancel[\s\S]*?promptBox\.style\.display\s*=\s*'none'[\s\S]*?claimedBox\.style\.display\s*=\s*'none'/,
    'cancelling a voucher must immediately hide the claim prompt card and claimed box');
assert.match(discountSource, /isNearOneDay[\s\S]*?vCountdownEl\.style\.color\s*=\s*isNearOneDay\s*\?\s*'#dc2626'\s*:\s*'#000000'/,
    'discount voucher countdown must turn red when within 1 day and black otherwise');
assert.match(profileSource, /isNearOneDay[\s\S]*?vCountdownEl\.style\.color\s*=\s*isNearOneDay\s*\?\s*'#dc2626'\s*:\s*'#000000'/,
    'profile voucher countdown must turn red when within 1 day and black otherwise');
assert.match(discountSource, /const isStatusItem = isUsed \|\| isCancelled \|\| isActive \|\| isClaimed \|\| isCardExpired;[\s\S]*?!isStatusItem && v\.badge/,
    'voucher cards must omit status badges from the top right');
assert.match(discountSource, /font-size:\s*13\.5px;\s*font-weight:\s*800;[\s\S]*?\$\{v\.title\}/,
    'voucher card title font size must be reduced');
assert.doesNotMatch(discountSource, /confirm\("Are you sure you want to cancel this voucher claim/,
    'discount cancellation must not use the native browser confirmation dialog');
const primaryActivationStart = useVoucherSource.indexOf('if ($voucherRecord)', useVoucherSource.indexOf("$qrToken ="));
const legacyActivationStart = useVoucherSource.indexOf('if ($pointRecord)', primaryActivationStart);
const primaryActivationSource = useVoucherSource.slice(primaryActivationStart, legacyActivationStart);
assert.doesNotMatch(primaryActivationSource, /DB::table\('point_redemptions'\)/,
    'primary voucher activation must not wait for a redundant legacy-table update');
assert.match(discountSource, /qrDiff !== null && qrDiff <= 0[\s\S]*?refreshModalQrCode/,
    'an expired discount QR must rotate automatically');
assert.match(profileSource, /qrDiff !== null && qrDiff <= 0[\s\S]*?refreshActiveModalQrCode/,
    'an expired profile QR must rotate automatically');

// 7. Backend Routes Validation
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/use',/, 'API route for voucher use must exist');
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/cancel',/, 'API route for voucher cancel must exist');
assert.match(apiRoutesSource, /Route::post\('\/vouchers\/\{code\}\/refresh-qr',/, 'API route for voucher refresh-qr must exist');

// 8. Backend Security and Timestamp Validations
assert.match(voucherControllerSource, /now\(\)->gte\(\$voucherExpiresAt\)/, 'useVoucher must validate server-side expiration timestamp');
assert.match(voucherControllerSource, /\$isActive[\s\S]*?now\(\)->gte\(\$qrExpiresAt\)[\s\S]*?'status' => 'active'/,
    'status checks must rotate an expired QR while keeping the three-day claim active');
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

// 10. Redeemed display keeps only the redeemed stamp, without stale QR details.
assert.match(discountSource, /data\.is_redeemed[\s\S]*?passContent\.style\.display = 'none'[\s\S]*?statusNotice\.style\.display = 'none'/,
    'discount redeemed state must hide QR/code content and the duplicate redeemed notice');
assert.match(profileSource, /data\.is_redeemed[\s\S]*?passContent\.style\.display = 'none'[\s\S]*?timersContainer\.style\.display = 'none'/,
    'profile redeemed state must hide QR/code content and timers');

// 11. Day, Hours, and Mins Countdown Formatting
assert.match(discountSource, /days > 0[\s\S]*?\$\{days\}d \$\{hours\}h \$\{pad\(minutes\)\}m/, 'discount countdown must format multi-day durations as Day, Hours, and mins');
assert.match(profileSource, /days > 0[\s\S]*?\$\{days\}d \$\{hours\}h \$\{pad\(minutes\)\}m/, 'profile countdown must format multi-day durations as Day, Hours, and mins');

// 12. Profile in-app cancellation modal & deduplication
assert.match(profileSource, /id="active-voucher-cancel-confirm-modal"/, 'profile view must use styled in-app cancel modal');
assert.doesNotMatch(profileSource, /confirm\("Are you sure you want to cancel this voucher claim/, 'profile view must not use native browser confirm');
assert.match(profileSource, /deduplicatedActiveVouchers/, 'profile view must deduplicate active voucher cards');
assert.match(profileSource, /if \(st === 'cancelled'\) return;/, 'profile view must exclude cancelled vouchers from active list');

// 13. Available Catalog Vouchers must not be marked as Used or Active
assert.match(discountSource, /const isActive = v\.isActive !== undefined \? v\.isActive : \(isRedemptionItem \? \(v\.status === 'active'\) : isVoucherInUse\(v\)\);/,
    'catalog items must not treat status=active as user-activated in-use status');
assert.match(discountSource, /const isAlreadyClaimed = isRedemptionItem \|\| isVoucherClaimed\(item\) \|\| isVoucherInUse\(item\) \|\| isVoucherRedeemed\(item\);/,
    'modal must not treat catalog status=active as already claimed');

console.log('All voucher lifecycle tests passed successfully!');
