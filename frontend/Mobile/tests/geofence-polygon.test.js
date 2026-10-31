const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const mainJsPath = path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js');
const mainJs = fs.readFileSync(mainJsPath, 'utf8');

// Ensure the helpers are exposed on window
assert.match(mainJs, /window\.spotHasPolygonBoundary\s*=/, 'main.js must define window.spotHasPolygonBoundary');
assert.match(mainJs, /window\.isPointInSpotPolygon\s*=/, 'main.js must define window.isPointInSpotPolygon');

const window = {};

const hasBoundaryMatch = mainJs.match(/window\.spotHasPolygonBoundary\s*=\s*(function\s*\(geoJson\)[\s\S]*?\n\s*return false;\s*\n\s*\};)/);
assert.ok(hasBoundaryMatch, 'window.spotHasPolygonBoundary function definition found');
eval('window.spotHasPolygonBoundary = ' + hasBoundaryMatch[1]);
const spotHasPolygonBoundary = window.spotHasPolygonBoundary;

const fnMatch = mainJs.match(/window\.isPointInSpotPolygon\s*=\s*(function\s*\(lat,\s*lng,\s*geoJson\)[\s\S]*?\n\s*return false;\s*\n\s*\};)/);
assert.ok(fnMatch, 'window.isPointInSpotPolygon function definition found');
eval('window.isPointInSpotPolygon = ' + fnMatch[1]);
const isPointInSpotPolygon = window.isPointInSpotPolygon;

// Test Polygon GeoJSON (sample San Juan Pagoda Hill spot polygon)
const samplePolygon = {
    type: 'Polygon',
    coordinates: [
        [
            [120.341255, 16.670614],
            [120.341148, 16.669945],
            [120.342403, 16.669786],
            [120.342527, 16.670423],
            [120.341255, 16.670614]
        ]
    ]
};

// 1. Polygon boundary presence detection
assert.equal(spotHasPolygonBoundary(samplePolygon), true, 'Valid polygon object has boundary');
assert.equal(spotHasPolygonBoundary(JSON.stringify(samplePolygon)), true, 'Valid polygon JSON string has boundary');
assert.equal(spotHasPolygonBoundary(null), false, 'Null has no boundary');
assert.equal(spotHasPolygonBoundary(undefined), false, 'Undefined has no boundary');
assert.equal(spotHasPolygonBoundary(''), false, 'Empty string has no boundary');
assert.equal(spotHasPolygonBoundary({ type: 'Polygon', coordinates: [] }), false, 'Empty coordinates has no boundary');

// 2. Point inside polygon
const insideLat = 16.670200;
const insideLng = 120.341800;
assert.equal(isPointInSpotPolygon(insideLat, insideLng, samplePolygon), true, 'Point inside boundary must return true');

// 3. Point outside polygon
const outsideLat = 16.675000;
const outsideLng = 120.345000;
assert.equal(isPointInSpotPolygon(outsideLat, outsideLng, samplePolygon), false, 'Point outside boundary must return false');

// 4. Edge cases
assert.equal(isPointInSpotPolygon(insideLat, insideLng, null), false, 'Null boundary must return false');
assert.equal(isPointInSpotPolygon(null, null, samplePolygon), false, 'Null coordinates must return false');
assert.equal(isPointInSpotPolygon(NaN, NaN, samplePolygon), false, 'NaN coordinates must return false');

// 5. Dual check logic verification:
// When polygon is present, it activates; otherwise 70m radius is used.
function canCheckIn(userLat, userLng, spotLat, spotLng, boundaryGeoJson) {
    if (spotHasPolygonBoundary(boundaryGeoJson)) {
        return isPointInSpotPolygon(userLat, userLng, boundaryGeoJson);
    }
    // Fallback 70m radius check
    const R = 6371e3;
    const phi1 = userLat * Math.PI / 180;
    const phi2 = spotLat * Math.PI / 180;
    const dPhi = (spotLat - userLat) * Math.PI / 180;
    const dLam = (spotLng - userLng) * Math.PI / 180;
    const a = Math.sin(dPhi / 2) * Math.sin(dPhi / 2) + Math.cos(phi1) * Math.cos(phi2) * Math.sin(dLam / 2) * Math.sin(dLam / 2);
    const dist = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return dist <= 70;
}

const spotCenterLat = 16.670172;
const spotCenterLng = 120.341551;

// Case A: Spot HAS polygon -> polygon activates
assert.equal(canCheckIn(insideLat, insideLng, spotCenterLat, spotCenterLng, samplePolygon), true, 'Inside polygon must allow check-in');
assert.equal(canCheckIn(outsideLat, outsideLng, spotCenterLat, spotCenterLng, samplePolygon), false, 'Outside polygon must reject check-in even if admin set it');

// Case B: Spot DOES NOT have polygon -> 70m radius fallback used
const within70mLat = 16.670200; // ~10m from center
const within70mLng = 16.670172 ? 120.341600 : 120.341600;
const beyond70mLat = 16.675000; // ~500m away
const beyond70mLng = 120.345000;

assert.equal(canCheckIn(within70mLat, within70mLng, spotCenterLat, spotCenterLng, null), true, 'No polygon + within 70m must allow check-in');
assert.equal(canCheckIn(beyond70mLat, beyond70mLng, spotCenterLat, spotCenterLng, null), false, 'No polygon + beyond 70m must reject check-in');

console.log('geofence polygon tests passed');
