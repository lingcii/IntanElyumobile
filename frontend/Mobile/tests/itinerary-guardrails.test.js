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
assert.doesNotMatch(itinerary, /hasMissingVehicle[\s\S]*Please choose vehicle type\./,
    'Save Draft must allow the explicit no-vehicle default');
assert.match(itinerary, /if \(window\._isSubmittingItinerary\) return;[\s\S]*window\._isSubmittingItinerary = true;[\s\S]*window\._isSubmittingItinerary = false;/,
    'Save Trip must ignore repeated submissions until the active request finishes');
assert.match(itinerary, /client_request_id: clientRequestId/,
    'new trips must carry a stable idempotency key into online and offline retries');
assert.match(itineraryController, /client_request_id[\s\S]*idempotencyCacheKey[\s\S]*Cache::put\(\$idempotencyCacheKey, \$itinerary->id/,
    'the backend must resolve retried Save Trip requests to the original itinerary');
assert.match(itemController, /MAX_DISTANCE_METERS = 70[\s\S]*hasIncompletePreviousStop[\s\S]*lockedStopResponse/,
    'the backend must enforce the 70-meter radius and sequential stop order');
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

console.log('Itinerary guardrail checks passed.');
