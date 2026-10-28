const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const splash = fs.readFileSync(path.join(root, 'src', 'views', 'splash.php'), 'utf8');
const sourceCss = fs.readFileSync(path.join(root, 'src', 'assets', 'css', 'views', 'splash.css'), 'utf8');
const publicCss = fs.readFileSync(path.join(root, 'public', 'assets', 'css', 'views', 'splash.css'), 'utf8');
const sourceSw = fs.readFileSync(path.join(root, 'src', 'sw.js'), 'utf8');

const slideMatches = splash.match(/data-onboarding-slide="\d"/g) || [];
assert.equal(slideMatches.length, 4, 'the startup animation must contain exactly four scenes');
assert.match(splash, /Create[\s\S]*Memorable Trips/);
assert.match(splash, /Discover[\s\S]*La Union/);
assert.match(splash, /id="onboarding-loader"[\s\S]*aria-valuemax="4"/,
    'the lower loading indicator must report all four scenes');
assert.match(splash, /showSlide\(index\)[\s\S]*navigateAfterSplash\('auth'\)/,
    'the animation must advance through the scenes before opening Login');
assert.match(splash, /realTourist[\s\S]*navigateAfterSplash\('dashboard'\)/,
    'signed-in tourists must still bypass Login');
assert.match(sourceCss, /\.onboarding-slide\.is-active[\s\S]*@keyframes loaderFill/,
    'the onboarding slides and lower loading bar must be animated');
assert.equal(publicCss, sourceCss, 'web source and packaged splash styles must stay synchronized');

for (const name of ['elyu-coast-morning.jpg', 'elyu-coast-island.jpg', 'elyu-coast-blue-hour.jpg']) {
    assert.ok(fs.existsSync(path.join(root, 'src', 'assets', 'img', 'onboarding', name)), `${name} must exist in source assets`);
    assert.ok(fs.existsSync(path.join(root, 'public', 'assets', 'img', 'onboarding', name)), `${name} must exist in packaged assets`);
    assert.match(sourceSw, new RegExp(name.replace('.', '\\.')), `${name} must be available offline`);
}

console.log('splash onboarding tests passed');
