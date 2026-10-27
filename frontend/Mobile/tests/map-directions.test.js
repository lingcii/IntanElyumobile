const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const mapSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'map.php'), 'utf8');
const mainSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');

assert.match(mapSource, /id="btn-map-directions"[\s\S]*?onclick="window\.openMapDirections\(\)"/,
    'the map must expose a directions button');
assert.match(mapSource, /fa-diamond-turn-right" style="color:#1e3a8a/,
    'the directions icon must use the requested dark-blue color');
assert.match(mapSource, /id="directions-start-input"[\s\S]*?value="Your location"/);
assert.match(mapSource, /id="directions-destination-input"[\s\S]*?placeholder="Choose destination"/);
assert.match(mapSource, /data-mode="driving"[\s\S]*?data-mode="motorcycle"[\s\S]*?data-mode="public"/);
assert.match(mapSource, /intan_elyu_route_history_/,
    'recent destinations must be stored per user');
assert.match(mapSource, /getDirectionsHistory\(\)\.filter\([\s\S]*?history\.unshift\(normalized\)/,
    'recent destinations must be deduplicated and newest-first');
assert.match(mapSource, /historyCount > 0/,
    'the More from recent history control must be available whenever history exists');
assert.match(mapSource, /overview=full&geometries=geojson&alternatives=true/,
    'route previews must request real geometry and supported alternatives');
assert.match(mapSource, /map\.addSource\('map-directions-route'/);
assert.match(mapSource, /map\.fitBounds\(bounds/);
assert.match(mapSource, /setDirectionsBaseMarkersVisible\(false\)/,
    'route preview must hide unrelated tourist-site markers');
assert.match(mapSource, /setDirectionsBaseMarkersVisible\(true\)/,
    'closing a route must restore tourist-site markers');
assert.match(mapSource, /function getDirectionsPlaceImage[\s\S]*?window\.getDestImage/,
    'direction results must resolve the existing tourist-site image');
assert.match(mapSource, /class="directions-result-icon">\$\{visual\}/,
    'direction search results must render the resolved image');
assert.match(mapSource, /\.map-route-summary\.open\s*\{[\s\S]*?transform:\s*translateY\(0\)/,
    'the route details sheet must animate upward');
assert.match(mapSource, /body\.map-route-active #bottom-navigation[\s\S]*?display:\s*none\s*!important/,
    'the blurred bottom navigation must not overlap an active route');
assert.match(mapSource, /id="map-route-endpoints"[\s\S]*?id="map-route-start-name"[\s\S]*?id="map-route-destination-name"/,
    'the route screen must display its exact start and destination');
assert.match(mapSource, /id="map-route-primary-time"[\s\S]*?id="map-route-primary-distance"/,
    'the route detail sheet must display duration and distance');
assert.match(mapSource, /window\.showDirectionsDestinationDetails[\s\S]*?window\.openSheet\(destination\)/,
    'the selected tourist site must open the existing full place-details sheet');
assert.match(mapSource, /window\.focusMapDirectionsRoute/,
    'the details sheet must allow the user to refocus the exact route');
assert.match(mapSource, /mapDirectionsState\.mode === 'driving'[\s\S]*?exact travel time unavailable/,
    'unsupported transport modes must not display fabricated travel times');
assert.match(mainSource, /id: 'map-directions-panel', fn: window\.closeMapDirectionsPanel/,
    'Android and browser back actions must close the directions screen first');

console.log('map directions tests passed');
