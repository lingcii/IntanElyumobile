const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const mapSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'map.php'), 'utf8');
const mainSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
const sharedCss = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'css', 'style.css'), 'utf8');

assert.match(mapSource, /showToast\("GPS Active", "gps", 4000\)/,
    'the first real GPS lock must show the dedicated active-status notification');
assert.match(mainSource, /type === 'gps'[\s\S]*?fa-location-crosshairs[\s\S]*?Live location tracking[\s\S]*?toast-gps-live/,
    'the GPS notification must use clear location and live-status icons');
assert.match(sharedCss, /\.toast-card--gps[\s\S]*?\.toast-gps-icon[\s\S]*?\.toast-gps-dot[\s\S]*?gpsLivePulse/,
    'the GPS notification must have its own polished active-state styling');

assert.match(mapSource, /id="btn-map-directions"[\s\S]*?onclick="window\.openMapDirections\(\)"/,
    'the map must expose a directions button');
assert.match(mapSource, /id="btn-map-directions"[\s\S]*?width:44px; height:44px[\s\S]*?border-radius:12px; background:linear-gradient\(135deg, rgba\(30, 58, 138, 0\.9\)[\s\S]*?color:#ffffff !important/,
    'the directions control must match the dark-blue map-control stack');
assert.match(mapSource, /fa-diamond-turn-right" style="color:#ffffff/,
    'the directions icon must remain white on the dark-blue control');
const mapCss = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'css', 'views', 'map.css'), 'utf8');
assert.match(mapCss, /keyboard-open #btn-map-directions[\s\S]*?opacity:\s*0 !important/,
    'the directions control must hide instead of moving upward when map search opens the keyboard');
assert.match(mapSource, /id="directions-start-input"[\s\S]*?value="Your location"/);
assert.match(mapSource, /id="directions-destination-input"[\s\S]*?placeholder="Choose destination"/);
assert.match(mapSource, /data-vehicle="own_car"[\s\S]*?data-vehicle="tricycle"[\s\S]*?data-vehicle="car"[\s\S]*?data-vehicle="jeepney"[\s\S]*?directions-more-vehicles-btn/,
    'the route search must show Own Car, Tricycle, Car, Jeep, then More');
assert.match(mapSource, /id="directions-vehicle-menu"[\s\S]*?Utility Vehicle Express \(UVE\)[\s\S]*?PUB Regular[\s\S]*?PUB Aircon[\s\S]*?Modern Jeepney \(MPUJ\)[\s\S]*?Traditional Jeepney \(TPUJ\)/,
    'More must expose the complete private and public vehicle list');
assert.match(mapSource, /window\.toggleDirectionsVehicleMenu[\s\S]*?window\.selectDirectionsVehicle/,
    'the More dropdown and vehicle selection must be interactive');
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
assert.match(mapSource, /window\.showDirectionsDestinationDetails[\s\S]*?window\.openSheet\(destination, true\)/,
    'the selected tourist site must open the existing full place-details sheet');
assert.match(mapSource, /initDraggableSheet\('place-details-sheet',[\s\S]*?_returnToMapRouteAfterDetails[\s\S]*?map-route-summary'\)\?\.classList\.add\('open'\)/,
    'closing route-opened place details must restore the route summary');
assert.match(mapSource, /window\.openSheet = function \(locationData, returnToMapRoute = false\)[\s\S]*?_returnToMapRouteAfterDetails = returnToMapRoute === true/,
    'place details opened directly from the map must not inherit route-return state');
assert.match(mapSource, /window\.focusMapDirectionsRoute/,
    'the details sheet must allow the user to refocus the exact route');
assert.match(mapSource, /id="map-route-motorcycle-time"[\s\S]*?id="map-route-public-time"/,
    'motorcycle and public-transport tabs must expose travel-time values');
assert.match(mapSource, /const motorcycleMinutes[\s\S]*?const publicMinutes[\s\S]*?selectedMinutes[\s\S]*?primaryTime\.textContent = `\$\{selectedMinutes\} min`/,
    'the selected jeep or motorcycle mode must display an estimated duration');
assert.match(mapSource, /primaryDistance\.textContent = `\(\$\{distance\}\)`[\s\S]*?estimated travel time based on road distance/,
    'non-car routes must retain real route kilometers and label their time as estimated');
assert.match(mainSource, /id: 'map-directions-panel', fn: window\.closeMapDirectionsPanel/,
    'Android and browser back actions must close the directions screen first');

console.log('map directions tests passed');
