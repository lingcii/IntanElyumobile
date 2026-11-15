const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const mobileRoot = path.join(__dirname, '..');
const repoRoot = path.join(mobileRoot, '..', '..');
const auth = fs.readFileSync(path.join(mobileRoot, 'src', 'views', 'auth.php'), 'utf8');
const controller = fs.readFileSync(path.join(repoRoot, 'backend', 'app', 'Http', 'Controllers', 'Auth', 'RegisterController.php'), 'utf8');
const loginController = fs.readFileSync(path.join(repoRoot, 'backend', 'app', 'Http', 'Controllers', 'Auth', 'LoginController.php'), 'utf8');
const main = fs.readFileSync(path.join(mobileRoot, 'src', 'assets', 'js', 'main.js'), 'utf8');
const routes = fs.readFileSync(path.join(repoRoot, 'backend', 'routes', 'api.php'), 'utf8');
const resendService = fs.readFileSync(path.join(repoRoot, 'backend', 'app', 'Services', 'ResendMailService.php'), 'utf8');
const registerMethod = controller.slice(controller.indexOf('public function register'), controller.indexOf('public function verifyOtp'));
const verifyMethod = controller.slice(controller.indexOf('public function verifyOtp'));

assert.match(registerMethod, /random_int\(100000, 999999\)/,
    'registration must create a six-digit email code');
assert.match(registerMethod, /Cache::put\(\$cacheKey[\s\S]*now\(\)->addMinutes\(10\)/,
    'pending registration details must expire after ten minutes');
assert.match(controller, /EmailVerificationOtpMail/,
    'Create Account must email the verification code');
assert.match(registerMethod, /Gmail addresses must end with @gmail\.com\./,
    'registration must reject mistyped Gmail domains at the API boundary');
assert.match(registerMethod, /'requires_otp'\s*=>\s*true/,
    'Create Account must tell the mobile UI to open its OTP panel');
assert.doesNotMatch(registerMethod, /User::create/,
    'the tourist account must not exist before email verification');
assert.match(controller, /ResendMailService::send/,
    'registration must prefer the fast HTTPS email transport when configured');
assert.match(registerMethod, /Cache::add\(\$cooldownKey[\s\S]*addSeconds\(45\)/,
    'registration OTP resend must enforce a 45-second cooldown');
assert.match(verifyMethod, /\$userData\s*=\s*\[[\s\S]*'gender'\s*=>\s*\$pending\['gender'\][\s\S]*User::create\(\$userData\)/,
    'verification must create the tourist with the pending registration data');
assert.match(verifyMethod, /app\(\)->terminating\([\s\S]*TouristWelcomeMail/,
    'welcome email delivery must not delay the verification response');

assert.match(routes, /register\/resend-otp[\s\S]*RegisterController::class,\s*'resendOtp'/,
    'the API must expose registration OTP resend');
assert.match(resendService, /connectTimeout\(3\)[\s\S]*timeout\(10\)/,
    'the HTTPS email transport must fail fast enough to use its fallback');
assert.doesNotMatch(resendService, /CURLOPT_SSL_VERIFYPEER[\s\S]*false/,
    'HTTPS certificate verification must not be disabled');

assert.match(auth, /showRegistrationOtpPanel\(data\.email \|\| email\)/,
    'successful registration must open the OTP panel');
assert.match(auth, /handleResendRegistrationOtp[\s\S]*register\/resend-otp/,
    'the OTP panel must allow a delayed code to be resent');
assert.match(auth, /showOtpInvalidModal\(errText\)/,
    'a rejected code must show the invalid-code dialog');
assert.match(auth, /showOtpSuccessModal[\s\S]*navigateTo\('onboarding'\)/,
    'a verified account must show success and continue to Complete Profile');
assert.match(loginController, /\$isNewUser = !\$user;[\s\S]*'travel_preferences'[\s\S]*'is_new_user'/,
    'Google authentication must identify new accounts and return profile-completion state');
assert.match(loginController, /'travel_preferences'\s*=>\s*\$user->travel_preferences,[\s\S]*'is_new_user'\s*=>\s*false/,
    'normal login must explicitly identify existing accounts');
assert.match(auth, /const shouldOnboard = [^;]*user\?\.is_new_user === true[^;]*;/,
    'existing email and Google users must bypass Complete Profile');
assert.match(main, /const postAuthView = data\.user\?\.is_new_user === true \? 'onboarding' : 'dashboard';/,
    'the direct Google callback must show Complete Profile only for a newly created account');

const loginGoogleMethod = loginController.slice(
    loginController.indexOf('public function googleLogin'),
    loginController.indexOf('public function sendResetLinkEmail')
);

assert.match(loginGoogleMethod, /'requires_otp'\s*=>\s*true/,
    'new Google Sign-Up accounts must require 6-digit OTP verification before creation');
assert.doesNotMatch(loginGoogleMethod, /User::create/,
    'Google Sign-Up must defer User::create until verifyOtp confirms the email code');
assert.match(verifyMethod, /'google_id'\s*=>\s*\$pending\['google_id'\]\s*\?\?\s*null/,
    'verifyOtp must persist google_id from pending Google registration');
assert.match(auth, /if\s*\(data\.requires_otp\s*===\s*true\)[\s\S]*showRegistrationOtpPanel\(targetEmail\)/,
    'Google Sign-Up response handler must open the OTP panel before password setup');
assert.match(main, /if\s*\(data\.requires_otp\s*===\s*true\s*&&\s*targetEmail\)[\s\S]*showRegistrationOtpPanel\(targetEmail\)/,
    'Google OAuth callback must open the OTP panel when OTP verification is required');

console.log('registration OTP flow tests passed');
