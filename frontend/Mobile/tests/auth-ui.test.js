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
assert.match(sourceCss, /\.auth-top::before,[\s\S]*clip-path: polygon/,
    'the hero must include code-rendered mountain layers');
assert.match(sourceCss, /@keyframes authWaveForward[\s\S]*@keyframes authWaveReverse/,
    'the layered waves must animate independently');
assert.match(sourceCss, /#btn-login\.btn-circle-submit,[\s\S]*width: 100% !important/,
    'login and registration actions must use full-width reference buttons');
assert.match(sourceCss, /\.auth-keyboard-dismiss[\s\S]*body\.auth-keyboard-visible \.auth-keyboard-dismiss/,
    'the themed keyboard control must only become interactive while the keyboard is visible');
assert.match(sourceCss, /\.forms-wrapper:not\(\.show-register\):not\(\.show-forgot\):not\(\.show-otp\)[\s\S]*flex: 0 0 auto/,
    'the Login slider must wrap its content without changing Register, Forgot, or OTP sizing');
assert.equal(publicCss, sourceCss,
    'web source and packaged mobile auth styles must stay synchronized');
assert.doesNotMatch(leaderboard, /leaderboard-refresh-(?:btn|icon)/,
    'the leaderboard header must not render the removed circular refresh control');

console.log('auth UI tests passed');
