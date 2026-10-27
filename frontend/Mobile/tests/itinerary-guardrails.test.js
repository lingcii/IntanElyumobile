const assert = require('assert');
const fs = require('fs');
const path = require('path');

const views = path.join(__dirname, '..', 'src', 'views');
const itinerary = fs.readFileSync(path.join(views, 'itinerary.php'), 'utf8');
const map = fs.readFileSync(path.join(views, 'map.php'), 'utf8');

assert.match(itinerary, /hasMissingVehicle[\s\S]*Please choose vehicle type\./,
    'Save Draft must require a vehicle selection');
assert.match(itinerary, /confirmRemoveItineraryItem[\s\S]*Are you sure you want to delete this\?[\s\S]*confirmText: 'Yes'[\s\S]*cancelText: 'No'/,
    'Stop deletion must ask for Yes/No confirmation');
assert.match(map, /if \(opState\.isMaint\)[\s\S]*addItinBtn\.disabled = true[\s\S]*Under Maintenance/,
    'Maintenance destinations must disable Add to Trip');
assert.match(map, /if \(opState\.isMaint\) \{[\s\S]*under maintenance and cannot be added to a trip[\s\S]*return;/,
    'Add-to-trip handler must reject maintenance destinations');

console.log('Itinerary guardrail checks passed.');
