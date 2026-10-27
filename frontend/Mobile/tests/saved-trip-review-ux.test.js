const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const testimony = fs.readFileSync(path.join(root, 'src', 'components', 'testimony_modal.php'), 'utf8');
const trips = fs.readFileSync(path.join(root, 'src', 'views', 'saved_trips.php'), 'utf8');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');

assert.match(testimony, /id="testimony-rating" value="0"/,
    'a new review must start with no rating selected');
assert.match(testimony, /setStarRating\(0\)/,
    'opening a new review must leave all stars unselected');
assert.match(testimony, /rating < 1 \|\| rating > 5[\s\S]*Please choose a rating from 1 to 5 stars\.[\s\S]*return;/,
    'review submission must stop until a valid star rating is chosen');

const renderStart = trips.indexOf('function renderSavedTrips(itineraries)');
const renderEnd = trips.indexOf('window.toggleTripDetails', renderStart);
const renderSource = trips.slice(renderStart, renderEnd);
assert.doesNotMatch(renderSource, /> View Details/,
    'saved-trip cards must not render a View Details button');
assert.doesNotMatch(renderSource, /> Sync Now/,
    'saved-trip cards must not render a manual Sync Now button');
assert.match(renderSource, /timeline-collapsible expanded/,
    'trip destinations must stay visible after removing View Details');
assert.match(renderSource, /button\[onclick\*="executeLocationCheckIn"\][\s\S]*button\[onclick\*="openProofUploadModal"\]/,
    'check-in and proof controls must be removed from the saved-trip summary card');

assert.match(main, /isPhotoUploadOnly: Boolean\(data\.isPhotoUploadOnly\)/,
    'the offline queue must retain proof-only upload intent');
assert.match(main, /window\.notifyAppOnline = function[\s\S]*window\.syncAllDataWhenOnline\(\)/,
    'online state must launch the background sync orchestrator');
assert.match(main, /window\.addEventListener\('online',[\s\S]*window\.notifyAppOnline\(\)/,
    'connectivity restoration must trigger online-state handling');

console.log('saved trip and review UX tests passed');
