const assert = require('assert');
const fs = require('fs');
const path = require('path');

const views = path.join(__dirname, '..', 'src', 'views');
const itinerary = fs.readFileSync(path.join(views, 'itinerary.php'), 'utf8');
const map = fs.readFileSync(path.join(views, 'map.php'), 'utf8');
const tripMap = fs.readFileSync(path.join(views, 'trip_map.php'), 'utf8');
const itineraryController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'ItineraryController.php'), 'utf8');
const itemController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'ItineraryItemController.php'), 'utf8');
const feedbackController = fs.readFileSync(path.join(__dirname, '..', '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'FeedbackController.php'), 'utf8');

assert.match(itinerary, /id="trip-transport" value="no_vehicle"/,
    'a new draft must default to no vehicle');
assert.doesNotMatch(itinerary, /data-mode="uve"|val:\s*'uve'/,
    'UV Express must not appear in itinerary vehicle selectors');
assert.doesNotMatch(map, /data-vehicle="uve"/,
    'UV Express must not appear in the Explore Map vehicle selector');
assert.match(tripMap, /key:\s*'uve'[\s\S]*?hiddenFromModal:\s*true/,
    'legacy UV Express trips must remain readable without exposing UVE in the trip-map selector');
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
assert.match(itinerary, /offlineBasePane[\s\S]*stroke: false[\s\S]*fillOpacity: 0/,
    'the bundled itinerary geography must not tint or outline municipality boundaries');
assert.match(itinerary, /getRememberedGpsLocation[\s\S]*window\.myLat[\s\S]*window\.myLng/,
    'the itinerary must draw its offline route immediately from recent GPS while refreshing location');
assert.match(itinerary, /Draw immediately so offline mode never waits for OSRM[\s\S]*L\.polyline\(latlngs/,
    'the itinerary preview must draw a local route before online routing resolves');
assert.match(itinerary, /!navigator\.onLine \|\| window\.isAppOnline === false\) \? Promise\.resolve\(null\)/,
    'offline itinerary rendering must not wait for an OSRM request');
assert.match(map, /window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipalities"[\s\S]*"data": window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipality-border"/,
    'Explore Map must inline bundled geography so its markers can initialize offline');
assert.match(map, /"id": "offline-municipality-border"[\s\S]*"line-width": isOfflineMap \? 1 : 0[\s\S]*"line-opacity": isOfflineMap \? 0\.75 : 0/,
    'Explore Map must show bundled municipality boundaries only while offline');
assert.match(map, /"id": "offline-municipality-fill"[\s\S]*"fill-opacity": isOfflineMap \? 0\.22 : 0/,
    'Explore Map must show a lightweight bundled basemap only while offline');
assert.match(map, /const data = await mapDataPromise;[\s\S]*window\.renderMarkers\(window\.allMapLocations\)[\s\S]*if \(isOfflineMap\) \{[\s\S]*return;/,
    'Explore Map must render cached tourist markers before skipping online municipality zones');

assert.match(itinerary, /currentFeeCompanions >= totalCompanionsLimit[\s\S]*Limit reached: Maximum of/,
    'fee category companions must not exceed total companions (Male + Female)');
assert.match(itinerary, /id="companion-fee-limit-info"/,
    'itinerary must display companion fee category limit indicator');

const swContent = fs.readFileSync(path.join(__dirname, '..', 'src', 'sw.js'), 'utf8');
assert.match(swContent, /!event\.request\.url\.startsWith\('http:\/\/'\) && !event\.request\.url\.startsWith\('https:\/\/'\)/,
    'ServiceWorker must only intercept http and https request schemes, ignoring chrome-extension and blob');

assert.match(itinerary, /const mapContainer = document\.getElementById\('itinerary-map'\);[\s\S]*if \(!mapContainer\) return;/,
    'initDraftMap must safely return if itinerary-map container is not mounted');

assert.match(itinerary, /const timeline = document\.getElementById\('itinerary-timeline'\);[\s\S]*if \(!timeline\) return;/,
    'renderItinerary must safely return if itinerary-timeline container is not mounted');

const mainJsContent = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
assert.match(mainJsContent, /window\.useCache = async function/,
    'main.js must define window.useCache');
assert.match(mainJsContent, /registration\.update\(\)\.catch/,
    'ServiceWorker registration.update must catch rejection gracefully');
assert.match(mainJsContent, /'save-trip-modal', 'confirm-modal'/,
    'main.js teardown must cleanup ported itinerary modals on SPA navigation');

assert.match(itinerary, /missingLegVehicle[\s\S]*Vehicle is required in all legs/,
    'openSaveModal must validate that every accessible leg has a vehicle selected');
assert.match(itinerary, /window\.closeSaveModal = function \(btn\)[\s\S]*document\.querySelectorAll\('#save-trip-modal'\)\.forEach[\s\S]*itin-confirm-open/,
    'closeSaveModal must close all save-trip-modal elements and clear backdrop flags');

assert.match(itinerary, /Trip Summary[\s\S]*save-p2p-transit-summary/,
    'Save modal must show Trip Summary instead of transport only');
assert.match(itinerary, /openTransitSummaryOpensheet[\s\S]*Travelers & Companions[\s\S]*compMales[\s\S]*compFemales[\s\S]*background:#ffffff !important/,
    'Entire Trip Summary opensheet must include male/female companions and render destination cards on white');
assert.doesNotMatch(itinerary, /id="transit-summary-badge"/,
    'Trip summary header must not display the pax and destination badge');
assert.doesNotMatch(itinerary, /fa-map-location-dot[\s\S]*Trip Summary/,
    'Trip summary header must not display the map location icon');
assert.match(itinerary, /id="save-p2p-transit-label"[^>]*>\s*View Summary\s*<\/div>/,
    'Save modal summary card must display clean View Summary text without vehicle/traveler clutter');

assert.match(itinerary, /Ready to save this trip\? You can still edit your plan anytime later\./,
    'submitItinerary must show rephrased confirmation modal before saving');

assert.match(itinerary, /class="next-stop-distance-chip"[\s\S]*font-size:10px !important;[\s\S]*white-space:nowrap !important;/,
    'itinerary.php must render next stop eta pill with 10px font size and nowrap to prevent awkward word wrapping');

assert.match(itinerary, /window\.hasSaveModalInputs = function \(\)/,
    'itinerary must define hasSaveModalInputs to inspect save modal inputs');
assert.match(itinerary, /window\.confirmCancelSaveModal = async function \(btn\)[\s\S]*!window\.hasSaveModalInputs\(\)[\s\S]*window\.closeSaveModal\(btn\)/,
    'confirmCancelSaveModal must close immediately if no inputs exist without confirmation prompt');
assert.match(itinerary, /spotEnvSenior[\s\S]*spotEnvPwd/,
    'itinerary must apply senior citizen and pwd rates to environmental fees matching site fees breakdown');

assert.match(itinerary, /Trip Cost Breakdown[\s\S]*feesSummaryHtml[\s\S]*Entrance Fee[\s\S]*Environmental Fee/,
    'Trip Cost Breakdown card must attach the light-themed Entrance & Environmental Fee summary');
const costCardStart = itinerary.indexOf('const overallCostCardHtml');
const costCardEnd = itinerary.indexOf('if (listEl)', costCardStart);
const costCard = itinerary.slice(costCardStart, costCardEnd);
assert.ok(costCard.indexOf('${feesSummaryHtml}') < costCard.indexOf('Transit Total'),
    'Entrance and environmental fee details must appear above the transit/site summary boxes');
assert.doesNotMatch(itinerary, /cardsHtml \+=[\s\S]*background:linear-gradient\(135deg, #1e3a8a 0%, #172554 100%\)/,
    'Destination stop cards must not contain misplaced dark blue fee cards');

console.log('Itinerary guardrail checks passed.');
