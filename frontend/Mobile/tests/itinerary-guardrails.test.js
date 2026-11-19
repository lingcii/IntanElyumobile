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
assert.match(itemController, /hasIncompletePreviousStop[\s\S]*whereNull\('visited_at'\)[\s\S]*where\('is_visited',\s*false\)/,
    'the backend must unlock the next stop once check-in is recorded without requiring optional proof photos');
assert.match(itemController, /MAX_DISTANCE_METERS = 70[\s\S]*hasIncompletePreviousStop[\s\S]*lockedStopResponse/,
    'the backend must still enforce the 70-meter radius and sequential stop order');
assert.match(itineraryController, /DB::transaction[\s\S]*where\('status',\s*'!=',\s*'completed'\)[\s\S]*update\(\['status' => 'completed'\]\)/,
    'trip completion must transition to completed atomically once');
assert.match(itemController, /recordFirstVisitPax[\s\S]*DB::transaction[\s\S]*increment\('visits',\s*max\(1,\s*\$totalPax\)\)/,
    'check-in must count the full party visits atomically once on first visit');
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
assert.match(itinerary, /offlineBasePane[\s\S]*stroke: false[\s\S]*fillOpacity: isOfflineDraftMap \? 0\.18 : 0/,
    'the offline itinerary must show a borderless land silhouette when network tiles are unavailable');
assert.match(itinerary, /getRememberedGpsLocation[\s\S]*window\.myLat[\s\S]*window\.myLng/,
    'the itinerary must draw its offline route immediately from recent GPS while refreshing location');
assert.match(itinerary, /Draw immediately so offline mode never waits for OSRM[\s\S]*L\.polyline\(latlngs/,
    'the itinerary preview must draw a local route before online routing resolves');
assert.match(itinerary, /!navigator\.onLine \|\| window\.isAppOnline === false\) \? Promise\.resolve\(null\)/,
    'offline itinerary rendering must not wait for an OSRM request');
assert.match(map, /window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipalities"[\s\S]*"data": window\.OFFLINE_MUNICIPALITIES_GEOJSON[\s\S]*"offline-municipality-border"/,
    'Explore Map must inline bundled geography so its markers can initialize offline');
assert.match(map, /"id": "offline-municipality-border"[\s\S]*"line-width": 0[\s\S]*"line-opacity": 0/,
    'Explore Map must keep bundled municipality borders hidden offline');
assert.match(map, /"id": "offline-municipality-fill"[\s\S]*"fill-opacity": 0/,
    'Explore Map must keep bundled municipality polygons visually hidden');
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
assert.match(mainJsContent, /'save-trip-modal', 'confirm-modal', 'focus-base-modal', 'custom-calendar-dropdown'/,
    'main.js teardown must cleanup ported itinerary modals and calendar dropdown on SPA navigation');

assert.match(itinerary, /missingLegVehicle[\s\S]*Vehicle is required in all legs/,
    'openSaveModal must validate that every accessible leg has a vehicle selected');
assert.match(itinerary, /window\.closeSaveModal = function \(btn\)[\s\S]*document\.querySelectorAll\('#save-trip-modal'\)\.forEach[\s\S]*itin-confirm-open/,
    'closeSaveModal must close all save-trip-modal elements and clear backdrop flags');

assert.match(itinerary, /id="itin-step-1"[\s\S]*id="itin-step-2"[\s\S]*id="itin-step-3"/,
    'itinerary must render all three wizard steps');
assert.match(itinerary, /window\.openTransitSummaryOpensheet = function \(\)/,
    'itinerary must expose openTransitSummaryOpensheet');
assert.doesNotMatch(itinerary, /id="transit-summary-badge"/,
    'Trip summary header must not display the pax and destination badge');
assert.doesNotMatch(itinerary, /fa-map-location-dot[\s\S]*Trip Summary/,
    'Trip summary header must not display the map location icon');

assert.match(itinerary, /Ready to save this trip\? You can still edit your plan anytime later\./,
    'submitItinerary must show rephrased confirmation modal before saving');
assert.doesNotMatch(itinerary, /const todayIso = new Date\(\)\.toISOString\(\)\.split\('T'\)\[0\]/,
    'submitItinerary must compare trip dates using local calendar date instead of UTC');
assert.match(itinerary, /sessionStorage\.getItem\('editing_trip_budget'\)[\s\S]*budgetInput\.value = savedBudget/,
    'editing a saved trip must restore its saved budget into #trip-budget');
assert.match(itineraryController, /whereNotIn\('tourist_spot_id', \$request->destinations\)[\s\S]*whereNull\('visited_at'\)[\s\S]*delete\(\)/,
    'updating an itinerary must preserve already-visited stops and their check-in proof');
assert.doesNotMatch(itineraryController, /Cache::flush\(\)/,
    'completing an itinerary must use targeted Cache::forget instead of wiping global Cache::flush()');
assert.match(itinerary, /err\.isClientValidation = true;[\s\S]*if \(error && error\.isClientValidation\)/,
    '4xx validation errors must not be queued into OfflineTripManager as connection failures');
assert.match(itinerary, /const companionAgesPayload = tripType === 'group' \?/,
    'solo trips must never submit stale companion_ages');
assert.doesNotMatch(itinerary, /else if \(isNoVeh \|\| finalTransport === 'own_car'\)/,
    'own_car must not be overwritten to No Vehicle Selected');
assert.match(itinerary, /osrmSeq !== window\._itineraryOsrmSeq/,
    'async OSRM route callbacks must ignore stale responses');

assert.match(itinerary, /class="next-stop-distance-chip"[\s\S]*font-size:10px !important;[\s\S]*white-space:nowrap !important;/,
    'itinerary.php must render next stop eta pill with 10px font size and nowrap to prevent awkward word wrapping');

assert.match(itinerary, /spotEnvSenior[\s\S]*spotEnvPwd/,
    'itinerary must apply senior citizen and pwd rates to environmental fees matching site fees breakdown');

const costCardStart = itinerary.indexOf('const overallCostCardHtml');
const costCardEnd = itinerary.indexOf('const totalTripEstimatedCost', costCardStart);
const costCard = itinerary.slice(costCardStart, costCardEnd);
assert.match(costCard, /Transit Total[\s\S]*Entrance Fees[\s\S]*Environmental Fees[\s\S]*Estimated Total Cost/,
    'Trip cost card must show Transit Total, Entrance Fees, Environmental Fees, and Estimated Total Cost');
assert.doesNotMatch(itinerary, /cardsHtml \+=[\s\S]*background:linear-gradient\(135deg, #1e3a8a 0%, #172554 100%\)/,
    'Destination stop cards must not contain misplaced dark blue fee cards');

assert.match(itinerary, /window\.getFareFromMatrix\('tricycle',\s*d,\s*cleanA\s*\|\|\s*cleanB\)/,
    'calculateSingleLegCost must pass cleanA || cleanB for tricycle so starting-point legs resolve the destination municipality tariff');
assert.match(itinerary, /15\s*\+\s*Math\.max\(0,\s*Math\.ceil\(d\)\s*-\s*1\)\s*\*\s*3/,
    'tricycle fallback tariff must follow the LUPTO ₱15 base + ₱3/km stage ceiling breakdown (e.g. 2.9 km -> ₱21.00)');
assert.match(itinerary, /getLegTransportInfo[\s\S]*window\._cachedNextStopOSRM\s*&&\s*window\._cachedNextStopOSRM\.spotKey\s*===\s*placeKey/,
    'getLegTransportInfo must synchronize leg 0 distance with OSRM road distance when available');

console.log('Itinerary guardrail checks passed.');
