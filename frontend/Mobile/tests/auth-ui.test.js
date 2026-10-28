const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const auth = fs.readFileSync(path.join(root, 'src', 'views', 'auth.php'), 'utf8');
const sourceCss = fs.readFileSync(path.join(root, 'src', 'assets', 'css', 'views', 'auth.css'), 'utf8');
const publicCss = fs.readFileSync(path.join(root, 'public', 'assets', 'css', 'views', 'auth.css'), 'utf8');
const leaderboard = fs.readFileSync(path.join(root, 'src', 'views', 'leaderboard.php'), 'utf8');

assert.match(auth, /id="auth-subtitle"[\s\S]*Explore places\. Create memories\./,
    'the scenic auth hero must include the reference subtitle');
assert.match(auth, /id="btn-login"[^>]*data-label="Log In"/);
assert.match(auth, /id="btn-register"[^>]*data-label="Create Account"/);
assert.match(auth, /id="form-login" onsubmit="handleLogin\(event\)"/,
    'the login redesign must retain the existing submit handler');
assert.match(auth, /id="form-register" onsubmit="handleRegisterSubmit\(event\)"/,
    'the register redesign must retain the existing submit handler');
assert.match(auth, /id="auth-keyboard-dismiss"[\s\S]*fa-chevron-down/,
    'auth must provide a keyboard-only down-arrow dismissal control');
assert.match(auth, /id="login-email"[^>]*enterkeyhint="next"[\s\S]*id="login-password"[^>]*enterkeyhint="done"/,
    'login inputs must expose native Next and Done actions');
assert.match(auth, /id="reg-first-name"[^>]*data-auth-focus-order="1"[\s\S]*id="reg-password"[^>]*data-auth-focus-order="4"/,
    'registration inputs must follow a stable sequential focus order');
assert.match(auth, /function keepFocusedFieldVisible[\s\S]*function dismissKeyboard[\s\S]*keyboard\.hide/,
    'auth must keep focused fields visible and support native keyboard dismissal');
assert.match(auth, /if \(window\._isRegistering\) return;[\s\S]*window\._isRegistering = true;/,
    'registration must ignore duplicate submissions while one request is active');
const emailPatternLiteral = auth.match(/const completeEmailPattern = (\/\^[^\r\n]+\/);/);
assert.ok(emailPatternLiteral, 'registration must define a complete-email validation pattern');
const completeEmailPattern = Function(`"use strict"; return (${emailPatternLiteral[1]});`)();
assert.equal(completeEmailPattern.test('side24250@gmail.d'), false,
    'a one-letter, unfinished email suffix must not show the green check');
const emailValidatorBody = auth.match(/window\.isCompleteRegistrationEmail = function \(email\) \{([\s\S]*?)\n\s*\};/);
assert.ok(emailValidatorBody, 'registration must expose its shared email validator');
const isCompleteRegistrationEmail = Function('email', emailValidatorBody[1]);
assert.equal(isCompleteRegistrationEmail('side24250@gmail.co'), false,
    'a mistyped Gmail domain must not show the green check');
assert.equal(isCompleteRegistrationEmail('side24250@gmail.com'), true,
    'a complete Gmail address must show the green check');
assert.equal(isCompleteRegistrationEmail('name@example.co'), true,
    'legitimate non-Gmail .co domains must remain valid');
assert.match(auth, /id="form-otp" onsubmit="handleVerifyOtp\(event\)"/,
    'registration must expose a dedicated email-code panel');
assert.match(auth, /Enter Verification Code[\s\S]*secure 6-digit code/,
    'the OTP card must give concise, non-duplicated instructions');
const showOtpPanel = auth.match(/window\.showRegistrationOtpPanel = function[\s\S]*?window\.hideOtpPanel = function/);
assert.ok(showOtpPanel, 'registration must expose the OTP panel transition');
assert.doesNotMatch(showOtpPanel[0], /\.focus\(/,
    'opening the OTP panel must not auto-open the keyboard or shift the viewport');
assert.match(showOtpPanel[0], /classList\.remove\('keyboard-open', 'auth-keyboard-visible'\)[\s\S]*setTimeout\(resetOtpViewport, 320\)/,
    'opening the OTP panel must clear keyboard state and restore the top after keyboard dismissal');
assert.match(auth, /id="otp-success-modal"[\s\S]*id="otp-invalid-modal"/,
    'OTP verification must provide separate success and invalid-code dialogs');
assert.match(sourceCss, /\.auth-top::before,[\s\S]*clip-path: polygon/,
    'the hero must include code-rendered mountain layers');
assert.match(sourceCss, /@keyframes authWaveForward[\s\S]*@keyframes authWaveReverse/,
    'the layered waves must animate independently');
assert.match(sourceCss, /#btn-login\.btn-circle-submit,[\s\S]*width: 100% !important/,
    'login and registration actions must use full-width reference buttons');
assert.match(sourceCss, /#btn-otp\.btn-circle-submit::before[\s\S]*position: static !important;[\s\S]*animation: none !important;/,
    'the Verify Code label must stay centered inside its button');
assert.match(sourceCss, /\.auth-container\.mode-otp \.auth-bottom[\s\S]*padding-bottom: calc\(max\(env\(safe-area-inset-bottom/,
    'OTP mode must not inherit the register keyboard spacer');
assert.match(sourceCss, /\.auth-keyboard-dismiss[\s\S]*body\.auth-keyboard-visible \.auth-keyboard-dismiss/,
    'the themed keyboard control must only become interactive while the keyboard is visible');
assert.match(sourceCss, /\.forms-wrapper:not\(\.show-register\):not\(\.show-forgot\):not\(\.show-otp\)[\s\S]*flex: 0 0 auto/,
    'the Login slider must wrap its content without changing Register, Forgot, or OTP sizing');
assert.match(sourceCss, /\.auth-result-card--success[\s\S]*\.auth-result-card--invalid/,
    'email verification result dialogs must have distinct success and invalid states');
assert.equal(publicCss, sourceCss,
    'web source and packaged mobile auth styles must stay synchronized');
assert.doesNotMatch(leaderboard, /leaderboard-refresh-(?:btn|icon)/,
    'the leaderboard header must not render the removed circular refresh control');

console.log('auth UI tests passed');
