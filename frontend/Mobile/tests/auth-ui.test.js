const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const auth = fs.readFileSync(path.join(root, 'src', 'views', 'auth.php'), 'utf8');
const sourceCss = fs.readFileSync(path.join(root, 'src', 'assets', 'css', 'views', 'auth.css'), 'utf8');
const publicCss = fs.readFileSync(path.join(root, 'public', 'assets', 'css', 'views', 'auth.css'), 'utf8');

assert.match(auth, /id="auth-subtitle"[\s\S]*Explore places\. Create memories\./,
    'the scenic auth hero must include the reference subtitle');
assert.match(auth, /id="btn-login"[^>]*data-label="Log In"/);
assert.match(auth, /id="btn-register"[^>]*data-label="Create Account"/);
assert.match(auth, /id="form-login" onsubmit="handleLogin\(event\)"/,
    'the login redesign must retain the existing submit handler');
assert.match(auth, /id="form-register" onsubmit="handleRegisterSubmit\(event\)"/,
    'the register redesign must retain the existing submit handler');
assert.match(sourceCss, /\.auth-top::before,[\s\S]*clip-path: polygon/,
    'the hero must include code-rendered mountain layers');
assert.match(sourceCss, /@keyframes authWaveForward[\s\S]*@keyframes authWaveReverse/,
    'the layered waves must animate independently');
assert.match(sourceCss, /#btn-login\.btn-circle-submit,[\s\S]*width: 100% !important/,
    'login and registration actions must use full-width reference buttons');
assert.equal(publicCss, sourceCss,
    'web source and packaged mobile auth styles must stay synchronized');

console.log('auth UI tests passed');
