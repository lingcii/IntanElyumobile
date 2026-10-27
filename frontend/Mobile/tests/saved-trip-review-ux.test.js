const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.join(__dirname, '..');
const testimony = fs.readFileSync(path.join(root, 'src', 'components', 'testimony_modal.php'), 'utf8');
const trips = fs.readFileSync(path.join(root, 'src', 'views', 'saved_trips.php'), 'utf8');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const itinerary = fs.readFileSync(path.join(root, 'src', 'views', 'itinerary.php'), 'utf8');
const tripMap = fs.readFileSync(path.join(root, 'src', 'views', 'trip_map.php'), 'utf8');

assert.ok(itinerary.indexOf('id="itinerary-count"') < itinerary.indexOf("navigateTo('saved_trips')"),
    'the places count must appear to the left of Saved Trips');
assert.match(trips, /\.saved-trips-tab\.active\s*\{[^}]*background:#1e3a8a\s*!important/,
    'the selected Saved Trips or Trip History tab must retain its blue state');
assert.match(trips, /historyTab\.classList\.toggle\('active', window\._savedTripsTab === 'history'\)/,
    'the blue state must move to Trip History when selected');

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
assert.match(trips, /Saved Trips[\s\S]*Trip History/,
    'saved trips must provide active and completed trip tabs');
assert.match(trips, /showingHistory \? historyTrips : activeTrips/,
    'trip history must render completed trips separately');
assert.match(trips, /showTripCompletionReviewModal\(reviewItems, true\)/,
    'Complete must open the required-review workflow first');
assert.match(trips, /Review every destination before completing[\s\S]*Complete Trip/,
    'completion must remain gated until every destination is reviewed');
assert.match(trips, /Trip Successfully Completed![\s\S]*View Trip History/,
    'successful completion must show a confirmation modal linking to history');
assert.match(trips, /cachedPosition[\s\S]*timestamp[\s\S]*accuracy/,
    'saved-trip check-in must reuse a recent accurate GPS fix');
assert.match(trips, /timeout: 7000, maximumAge: 30000/,
    'saved-trip check-in must allow a recent GPS fix and use a shorter timeout');
assert.match(tripMap, /lockCheckedInButton[\s\S]*Checked In[\s\S]*removeAttribute\('onclick'\)/,
    'successful trip-map check-in must become inactive immediately');
assert.match(main, /transientNavClasses[\s\S]*classList\.remove\(\.\.\.transientNavClasses\)/,
    'navigation must clear stale modal and keyboard visibility state between screens');
assert.match(main, /isPhotoUploadOnly: Boolean\(data\.isPhotoUploadOnly\)/,
    'the offline queue must retain proof-only upload intent');
assert.match(main, /window\.notifyAppOnline = function[\s\S]*window\.syncAllDataWhenOnline\(\)/,
    'online state must launch the background sync orchestrator');
assert.match(main, /window\.addEventListener\('online',[\s\S]*window\.notifyAppOnline\(\)/,
    'connectivity restoration must trigger online-state handling');

console.log('saved trip and review UX tests passed');
