const assert = require('assert');
const fs = require('fs');
const path = require('path');

const views = path.join(__dirname, '..', 'src', 'views');
const itinerary = fs.readFileSync(path.join(views, 'itinerary.php'), 'utf8');
const map = fs.readFileSync(path.join(views, 'map.php'), 'utf8');
const itineraryController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'ItineraryController.php'), 'utf8');

assert.match(itinerary, /hasMissingVehicle[\s\S]*Please choose vehicle type\./,
    'Save Draft must require a vehicle selection');
assert.match(itinerary, /if \(window\._isSubmittingItinerary\) return;[\s\S]*window\._isSubmittingItinerary = true;[\s\S]*window\._isSubmittingItinerary = false;/,
    'Save Trip must ignore repeated submissions until the active request finishes');
assert.match(itinerary, /client_request_id: clientRequestId/,
    'new trips must carry a stable idempotency key into online and offline retries');
assert.match(itineraryController, /client_request_id[\s\S]*idempotencyCacheKey[\s\S]*Cache::put\(\$idempotencyCacheKey, \$itinerary->id/,
    'the backend must resolve retried Save Trip requests to the original itinerary');
assert.match(itinerary, /confirmRemoveItineraryItem[\s\S]*Are you sure you want to delete this\?[\s\S]*confirmText: 'Yes'[\s\S]*cancelText: 'No'/,
    'Stop deletion must ask for Yes/No confirmation');
assert.match(map, /if \(opState\.isMaint\)[\s\S]*addItinBtn\.disabled = true[\s\S]*Under Maintenance/,
    'Maintenance destinations must disable Add to Trip');
assert.match(map, /if \(opState\.isMaint\) \{[\s\S]*under maintenance and cannot be added to a trip[\s\S]*return;/,
    'Add-to-trip handler must reject maintenance destinations');

console.log('Itinerary guardrail checks passed.');
