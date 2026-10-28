const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const mobileRoot = path.join(__dirname, '..');
const repoRoot = path.join(mobileRoot, '..', '..');
const auth = fs.readFileSync(path.join(mobileRoot, 'src', 'views', 'auth.php'), 'utf8');
const controller = fs.readFileSync(path.join(repoRoot, 'backend', 'app', 'Http', 'Controllers', 'Auth', 'RegisterController.php'), 'utf8');
const registerMethod = controller.slice(controller.indexOf('public function register'), controller.indexOf('public function verifyOtp'));
const verifyMethod = controller.slice(controller.indexOf('public function verifyOtp'));

assert.match(registerMethod, /random_int\(100000, 999999\)/,
    'registration must create a six-digit email code');
assert.match(registerMethod, /Cache::put\(\$cacheKey[\s\S]*now\(\)->addMinutes\(10\)/,
    'pending registration details must expire after ten minutes');
assert.match(registerMethod, /EmailVerificationOtpMail/,
    'Create Account must email the verification code');
assert.match(registerMethod, /Gmail addresses must end with @gmail\.com\./,
    'registration must reject mistyped Gmail domains at the API boundary');
assert.match(registerMethod, /'requires_otp'\s*=>\s*true/,
    'Create Account must tell the mobile UI to open its OTP panel');
assert.doesNotMatch(registerMethod, /User::create/,
    'the tourist account must not exist before email verification');
assert.match(verifyMethod, /User::create\([\s\S]*'gender'\s*=>\s*\$pending\['gender'\]/,
    'verification must create the tourist with the pending registration data');

assert.match(auth, /showRegistrationOtpPanel\(data\.email \|\| email\)/,
    'successful registration must open the OTP panel');
assert.match(auth, /showOtpInvalidModal\(errText\)/,
    'a rejected code must show the invalid-code dialog');
assert.match(auth, /showOtpSuccessModal[\s\S]*navigateTo\('dashboard'\)/,
    'a verified account must show success and continue to Dashboard');

console.log('registration OTP flow tests passed');
