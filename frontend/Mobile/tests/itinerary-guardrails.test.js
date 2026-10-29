const assert = require('assert');
const fs = require('fs');
const path = require('path');

const views = path.join(__dirname, '..', 'src', 'views');
const itinerary = fs.readFileSync(path.join(views, 'itinerary.php'), 'utf8');
const map = fs.readFileSync(path.join(views, 'map.php'), 'utf8');
const itineraryController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'ItineraryController.php'), 'utf8');
const itemController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'ItineraryItemController.php'), 'utf8');
const feedbackController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'FeedbackController.php'), 'utf8');

assert.match(itinerary, /id="trip-transport" value="no_vehicle"/,
    'a new draft must default to no vehicle');
assert.match(itinerary, /openSaveModal[\s\S]*Please choose a vehicle before saving your draft plan\.[\s\S]*Vehicle Required[\s\S]*return;/,
    'Save Draft must show a modal and stop when no vehicle is selected');
assert.match(itinerary, /if \(window\._isSubmittingItinerary\) return;[\s\S]*window\._isSubmittingItinerary = true;[\s\S]*window\._isSubmittingItinerary = false;/,
    'Save Trip must ignore repeated submissions until the active request finishes');
assert.match(itinerary, /client_request_id: clientRequestId/,
    'new trips must carry a stable idempotency key into online and offline retries');
assert.match(itineraryController, /client_request_id[\s\S]*idempotencyCacheKey[\s\S]*Cache::put\(\$idempotencyCacheKey, \$itinerary->id/,
    'the backend must resolve retried Save Trip requests to the original itinerary');
assert.match(itemController, /hasIncompletePreviousStop[\s\S]*whereNull\('visited_at'\)[\s\S]*orWhereNull\('proof_image'\)[\s\S]*orWhere\('proof_status', 'rejected'\)/,
    'the backend must unlock the next stop after check-in and proof submission, even while approval is pending');
assert.match(itemController, /MAX_DISTANCE_METERS = 70[\s\S]*hasIncompletePreviousStop[\s\S]*lockedStopResponse/,
    'the backend must still enforce the 70-meter radius and sequential stop order');
assert.match(itineraryController, /whereIn\('itinerary_item_id'[\s\S]*DB::transaction[\s\S]*increment\('visits', \$paxIncrement\)/,
    'completion must require per-stop reviews and count the full party exactly once');
assert.match(feedbackController, /itinerary_item_id[\s\S]*whereHas\('itinerary'[\s\S]*updateOrCreate/,
    'trip reviews must belong to an itinerary item owned by the tourist');
assert.match(itinerary, /confirmRemoveItineraryItem[\s\S]*Are you sure you want to delete this\?[\s\S]*confirmText: 'Yes'[\s\S]*cancelText: 'No'/,
    'Stop deletion must ask for Yes/No confirmation');
assert.match(map, /if \(opState\.isMaint\)[\s\S]*addItinBtn\.disabled = true[\s\S]*Under Maintenance/,
    'Maintenance destinations must disable Add to Trip');
assert.match(map, /if \(opState\.isMaint\) \{[\s\S]*under maintenance and cannot be added to a trip[\s\S]*return;/,
    'Add-to-trip handler must reject maintenance destinations');
assert.doesNotMatch(map, /id="sheet-open-badge"/,
    'the image overlay must not show an Open 24/7 badge');
assert.match(map, /quick-stat-label">Points[\s\S]*id="sheet-points"[\s\S]*rewardPoints[\s\S]*Points/,
    'the former Hours quick stat must show destination reward points');
assert.match(itinerary, /assets\/la_union_municipalities\.json[\s\S]*offlineBasePane/,
    'the itinerary preview must include bundled municipality geometry offline');
assert.match(itinerary, /Draw immediately so offline mode never waits for OSRM[\s\S]*L\.polyline\(latlngs/,
    'the itinerary preview must draw a local route before online routing resolves');
assert.match(itinerary, /!navigator\.onLine \|\| window\.isAppOnline === false\) \? Promise\.resolve\(null\)/,
    'offline itinerary rendering must not wait for an OSRM request');
assert.match(map, /window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipalities"[\s\S]*"data": window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipality-border"/,
    'Explore Map must inline bundled geography so its markers can initialize offline');

console.log('Itinerary guardrail checks passed.');
