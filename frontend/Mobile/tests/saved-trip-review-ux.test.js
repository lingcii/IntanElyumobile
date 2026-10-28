const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.join(__dirname, '..');
const testimony = fs.readFileSync(path.join(root, 'src', 'components', 'testimony_modal.php'), 'utf8');
const trips = fs.readFileSync(path.join(root, 'src', 'views', 'saved_trips.php'), 'utf8');
const main = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const itinerary = fs.readFileSync(path.join(root, 'src', 'views', 'itinerary.php'), 'utf8');
const tripMap = fs.readFileSync(path.join(root, 'src', 'views', 'trip_map.php'), 'utf8');
const puzzles = fs.readFileSync(path.join(root, 'src', 'views', 'puzzles.php'), 'utf8');

for (const [name, source] of [['saved_trips.php', trips], ['trip_map.php', tripMap], ['testimony_modal.php', testimony], ['puzzles.php', puzzles]]) {
    const scripts = [...source.matchAll(/<script[^>]*>([\s\S]*?)<\/script>/gi)];
    scripts.forEach((match, index) => {
        assert.doesNotThrow(() => new vm.Script(match[1], { filename: `${name}#${index + 1}` }),
            `${name} embedded script ${index + 1} must parse`);
    });
}

assert.ok(itinerary.indexOf('id="itinerary-count"') < itinerary.indexOf("navigateTo('saved_trips')"),
    'the places count must appear to the left of Saved Trips');
assert.match(trips, /\.saved-trips-tab\.active\s*\{[^}]*background:#1e3a8a\s*!important/,
    'the selected Saved Trips or Trip History tab must retain its blue state');
assert.match(trips, /historyTab\.classList\.toggle\('active', window\._savedTripsTab === 'history'\)/,
    'the blue state must move to Trip History when selected');
assert.match(trips, /if \(window\._savedTripsTab !== 'history'\) window\._savedTripsTab = 'saved'/,
    're-rendering Saved Trips must not reset an active Trip History tab');
assert.match(trips, /saved-trips-tab[^}]*pointer-events:auto !important/,
    'Saved Trips and Trip History tabs must remain tappable above trip cards');

assert.match(testimony, /id="testimony-rating" value="0"/,
    'a new review must start with no rating selected');
assert.match(testimony, /setStarRating\(0\)/,
    'opening a new review must leave all stars unselected');
assert.match(testimony, /rating < 1 \|\| rating > 5[\s\S]*Please choose a rating from 1 to 5 stars\.[\s\S]*return;/,
    'review submission must stop until a valid star rating is chosen');
assert.match(testimony, /userReviewedItemIds[\s\S]*reviewed_item_ids[\s\S]*itinerary_item_id: window\.currentReviewItineraryItemId/,
    'reviews must be tracked and submitted for the exact itinerary item');

const renderStart = trips.indexOf('function renderSavedTrips(itineraries)');
const renderEnd = trips.indexOf('window.toggleTripDetails', renderStart);
const renderSource = trips.slice(renderStart, renderEnd);
assert.doesNotMatch(renderSource, /> View Details/,
    'saved-trip cards must not render a View Details button');
assert.doesNotMatch(renderSource, /> Sync Now/,
    'saved-trip cards must not render a manual Sync Now button');
assert.match(renderSource, /timeline-collapsible expanded/,
    'trip destinations must stay visible after removing View Details');
assert.match(renderSource, /isStopUnlocked[\s\S]*data-stop-unlocked[\s\S]*Complete the previous destination to unlock/,
    'later destinations must remain visibly locked until the preceding stop is complete');
assert.doesNotMatch(renderSource, /\bisQueued\b/,
    'renderSavedTrips must use isCheckinQueued instead of an undeclared isQueued identifier so trip cards close properly');
assert.match(renderSource, /class="saved-trip-checkin-btn[\s\S]*data-dest-lat=[\s\S]*disabled[\s\S]*executeLocationCheckIn/,
    'each destination card must include a lower-right check-in button that starts disabled');
assert.match(renderSource, /executeLocationCheckIn"\]:not\(\.saved-trip-checkin-btn\)/,
    'legacy card controls may be removed without deleting the GPS-aware check-in button');
assert.match(renderSource, /isLocVerified && \(!hasProof \|\| isRejected\)[\s\S]*class="saved-trip-proof-btn"[\s\S]*Proof of Validation[\s\S]*openProofUploadModal/,
    'a verified check-in must reveal the proof-of-validation upload beside it');
assert.match(renderSource, /openProofUploadModal"\]:not\(\.saved-trip-proof-btn\)/,
    'legacy proof controls may be removed without deleting the post-check-in proof action');
assert.match(trips, /SAVED_TRIP_CHECKIN_RADIUS_METERS = 70[\s\S]*gpsUpdated[\s\S]*updateSavedTripCheckinAvailability/,
    'live GPS updates must enable check-in only within the backend 70-meter radius');
const proximityStart = trips.indexOf('const SAVED_TRIP_CHECKIN_RADIUS_METERS = 70');
const proximityEnd = trips.indexOf('function renderSavedTrips(itineraries)', proximityStart);
const proximityButton = {
    dataset: { checked: 'false', destLat: '16.6159', destLng: '120.3209' },
    disabled: true,
    title: '',
    classList: { toggle() {} },
    setAttribute() {}
};
const proximityContext = {
    window: {},
    document: {
        querySelectorAll: () => [proximityButton],
        addEventListener() {}
    }
};
vm.runInNewContext(`${trips.slice(proximityStart, proximityEnd)};`, proximityContext);
proximityContext.window.updateSavedTripCheckinAvailability(16.6165, 120.3209, 20);
assert.equal(proximityButton.disabled, false, 'check-in must enable automatically when GPS is within 70 meters');
proximityContext.window.updateSavedTripCheckinAvailability(16.6200, 120.3209, 20);
assert.equal(proximityButton.disabled, true, 'check-in must remain disabled when GPS is outside 70 meters');
assert.match(trips, /Saved Trips[\s\S]*Trip History/,
    'saved trips must provide active and completed trip tabs');
assert.match(trips, /showingHistory \? historyTrips : activeTrips/,
    'trip history must render completed trips separately');
assert.match(trips, /function hasConfirmedProofSubmission\(item\)[\s\S]*proofStatus === 'pending' && hasServerProof/,
    'a pending status may hide Start Trip only when an actual proof was submitted');
const proofGuardStart = trips.indexOf('function hasConfirmedProofSubmission(item)');
const proofGuardEnd = trips.indexOf('const SAVED_TRIP_CHECKIN_RADIUS_METERS', proofGuardStart);
const proofGuardContext = {};
vm.runInNewContext(`${trips.slice(proofGuardStart, proofGuardEnd)}; this.hasConfirmedProofSubmission = hasConfirmedProofSubmission;`, proofGuardContext);
assert.equal(proofGuardContext.hasConfirmedProofSubmission({ proof_status: 'pending', proof_images: [] }), false,
    'a fresh draft with a stale pending label must retain Start Trip');
assert.equal(proofGuardContext.hasConfirmedProofSubmission({ proof_status: 'pending', proof_images: ['proof.jpg'] }), true,
    'a genuinely submitted pending proof may hide Start Trip');
assert.match(trips, /is_queued: Boolean\(it\.is_offline_queued \|\| \(previous && previous\.is_queued\)\)/,
    'queued proof state must survive cache merging without being mistaken for a confirmed submission');
assert.match(trips, /items\.length > 0 && items\.every\(hasConfirmedProofSubmission\)/,
    'Start Trip must hide only after every stop in that specific itinerary has submitted proof');
assert.match(trips, /tripHasCheckin = Array\.isArray\(trip\.items\)[\s\S]*shouldShowTripAction = isTripCompleted \|\| unvisitedCount === 0 \|\| \(!tripHasCheckin && !shouldHideStartTripForTrip\(trip\)\)/,
    'Start Trip must disappear after location check-in while remaining available for a new draft');
assert.doesNotMatch(trips, /targetIdStr\.includes\(itIdStr\)/,
    'local proof and check-in updates must use exact itinerary-item IDs');
assert.doesNotMatch(main, /targetIdStr\.includes\(itIdStr\)/,
    'shared proof and check-in updates must not match partial IDs from another trip');
const proofSubmitStart = trips.indexOf('window.submitProofPhotos = async function');
const proofSuccessStart = trips.indexOf('if (response.ok && data.success)', proofSubmitStart);
assert.ok(proofSubmitStart >= 0 && proofSuccessStart > proofSubmitStart,
    'proof submission workflow must be present');
assert.doesNotMatch(trips.slice(proofSubmitStart, proofSuccessStart), /applyPhotoProofUpdateLocally/,
    'failed or queued uploads must not be marked as backend-submitted optimistically');
assert.match(trips.slice(proofSuccessStart), /applyPhotoProofUpdateLocally\(itemId, permanentUrls, data\.proof_status \|\| 'pending'\)[\s\S]*fetchSavedTrips\(true\)/,
    'successful backend proof submission must update immediately and refresh authoritative state');
assert.match(trips, /const resolvedStatus = serverStatus \|\|/,
    'fresh backend proof status must take precedence over stale local state');
assert.match(trips, /if \(window\._savedTripsFetchPromise\) return window\._savedTripsFetchPromise/,
    'overlapping Saved Trips refresh triggers must share one in-flight request');
assert.doesNotMatch(trips, /}, 3500\)/,
    'pending proof approval must not trigger an aggressive 3.5-second refresh loop');
assert.doesNotMatch(trips, /window\._savedTripsInterval = setInterval/,
    'Saved Trips and Trip History must not poll in the background');
assert.doesNotMatch(trips, /addEventListener\('(?:visibilitychange|focus)'[\s\S]*fetchSavedTrips/,
    'focus and visibility events must not cause unnecessary Saved Trips requests');
assert.match(trips, /Single initial request[\s\S]*window\.fetchSavedTrips\(false\)\.finally/,
    'opening Saved Trips must issue one guarded initial request');
assert.match(trips, /saved-trips-loading-state[\s\S]*Loading Your Trips/,
    'Saved Trips must show a loading state while the initial request is pending');
assert.match(trips, /function renderSavedTripsLoadError\(\)[\s\S]*Could Not Load Trips[\s\S]*Try Again/,
    'Saved Trips must show a retryable error when no cached data is available');
assert.doesNotMatch(trips, /syncQueuedTrips\(\)[\s\S]*addEventListener\('online'/,
    'Saved Trips must defer reconnect queue processing to the single app-wide orchestrator');
assert.match(trips, /showTripCompletionReviewModal\(reviewItems, true\)/,
    'Complete must open the required-review workflow first');
assert.match(trips, /reviewItems\.length === 1[\s\S]*startReviewFromCompletion/,
    'a one-stop trip must open Review Destination directly from Complete');
assert.match(testimony, /isCompletionReview[\s\S]*Review Destination[\s\S]*Save Review/,
    'completion reviews must use Review Destination and Save Review labels');
assert.match(trips, /spot-reviewed[\s\S]*remaining\.length === 0[\s\S]*finishTripAfterReviews/,
    'saving the final required review must finish the trip automatically');
assert.match(trips, /Review every destination before completing[\s\S]*Complete Trip/,
    'completion must remain gated until every destination is reviewed');
assert.match(trips, /Trip Successfully Completed![\s\S]*View Trip History/,
    'successful completion must show a confirmation modal linking to history');
assert.match(trips, /markTripAsCompletedLocally\(id,[\s\S]*switchSavedTripsTab\('history'\)/,
    'completed trips must move into Trip History immediately');
assert.match(trips, /cachedPosition[\s\S]*timestamp[\s\S]*accuracy/,
    'saved-trip check-in must reuse a recent accurate GPS fix');
assert.match(trips, /timeout: 7000, maximumAge: 30000/,
    'saved-trip check-in must allow a recent GPS fix and use a shorter timeout');
assert.match(tripMap, /lockCheckedInButton[\s\S]*Checked In[\s\S]*removeAttribute\('onclick'\)/,
    'successful trip-map check-in must become inactive immediately');
assert.match(main, /transientNavClasses[\s\S]*classList\.remove\(\.\.\.transientNavClasses\)/,
    'navigation must clear stale modal and keyboard visibility state between screens');
assert.doesNotMatch(main, /noNavViews = \[[^\]]*'saved_trips'/,
    'Saved Trips must keep the bottom navigation visible');
assert.match(tripMap, /Loading trip route\.\.\.\", \"info\", 1400/,
    'the route-loading notice must clear quickly instead of lingering after navigation');
assert.match(main, /isPhotoUploadOnly: Boolean\(data\.isPhotoUploadOnly\)/,
    'the offline queue must retain proof-only upload intent');
assert.match(main, /window\.notifyAppOnline = function[\s\S]*window\.syncAllDataWhenOnline\(\)/,
    'online state must launch the background sync orchestrator');
assert.match(main, /window\.addEventListener\('online',[\s\S]*window\.notifyAppOnline\(\)/,
    'connectivity restoration must trigger online-state handling');

console.log('saved trip and review UX tests passed');
