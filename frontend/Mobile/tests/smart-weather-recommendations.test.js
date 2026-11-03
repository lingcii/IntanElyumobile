const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const dashboardPath = path.join(__dirname, '../src/views/dashboard.php');
const dashboardSrc = fs.readFileSync(dashboardPath, 'utf8');

// 1. Structural assertions on dashboard.php
assert(!dashboardSrc.includes('id="weather-muni-select"'), 'Municipality dropdown removed in favor of real-time GPS location');
assert(dashboardSrc.includes('id="weather-gps-badge"'), 'Real-time Live GPS badge exists on Current Weather Card');
assert(dashboardSrc.includes('id="weather-best-time"'), 'Best Time to Visit element exists on Current Weather Card');
assert(dashboardSrc.includes('id="weather-alert-banner"'), 'Weather Alerts banner exists on Dashboard');
assert(dashboardSrc.includes('id="smart-weather-rec-section"'), 'Smart Weather Recommendations section exists on Dashboard');
assert(dashboardSrc.includes('id="weather-rec-container"'), 'Recommended for Today\'s Weather horizontal container exists');
assert(dashboardSrc.includes('id="weather-day-picks-modal"'), '5-Day Forecast interactive day pop-up modal exists');
assert(dashboardSrc.includes('window.openForecastDayPicksModal'), '5-Day Forecast day click handler exists');

// 2. Behavioral assertions on weather reconciliation, classification & spot recommendation engine
const startMarker = 'window.LA_UNION_MUNI_COORDS = {';
const endMarker = 'window.quickAddWeatherSpotToItinerary = function';
const startIdx = dashboardSrc.indexOf(startMarker);
const endIdx = dashboardSrc.indexOf(endMarker);
assert(startIdx !== -1 && endIdx > startIdx, 'Smart weather functions found in dashboard.php');

const extractedCode = dashboardSrc.slice(startIdx, endIdx);
const sandbox = {
    window: {
        currentWeatherLocation: { lat: 16.6711, lng: 120.3385, name: 'San Juan, La Union', isCurrentLoc: true },
        resolveMunicipalityName: (s) => s.municipality || 'La Union'
    },
    localStorage: { getItem: () => null, setItem: () => {} }
};
vm.createContext(sandbox);
vm.runInContext(extractedCode, sandbox);

const sampleSpots = [
    { id: 1, name: 'Urbiztondo Beach', category: 'Beach', municipality: 'San Juan', status: 'approved', is_maintenance: false, rating: 4.8, visits: 500 },
    { id: 2, name: 'Pindangan Ruins', category: 'Historical,Landmark', municipality: 'San Fernando', status: 'approved', is_maintenance: false, rating: 4.6, visits: 300 },
    { id: 3, name: 'Museo de Iloko', category: 'Museum,Cultural Heritage', municipality: 'Agoo', status: 'approved', is_maintenance: false, rating: 4.7, visits: 250 },
    { id: 4, name: 'Tangadan Falls', category: 'Waterfalls,Nature', municipality: 'San Gabriel', status: 'approved', is_maintenance: false, rating: 4.9, visits: 600 },
    { id: 5, name: 'Gapuz Grape Farm', category: 'Farm,Garden', municipality: 'Bauang', status: 'approved', is_maintenance: false, rating: 4.5, visits: 400 },
    { id: 6, name: 'Coastal Shrine Beach', category: 'Beach,Historical', municipality: 'Luna', status: 'approved', is_maintenance: false, rating: 4.9, visits: 700 },
    { id: 7, name: 'Pending Beach Resort', category: 'Beach', municipality: 'San Juan', status: 'pending', is_maintenance: false, rating: 5.0, visits: 999 },
    { id: 8, name: 'Closed Park', category: 'Park', municipality: 'San Juan', status: 'approved', is_maintenance: true, rating: 5.0, visits: 999 }
];

// Nearest La Union municipality resolution for GPS
assert.strictEqual(sandbox.window.resolveNearestLaUnionMuni(16.6072, 120.4422), 'Bagulin, La Union');
assert.strictEqual(sandbox.window.resolveNearestLaUnionMuni(16.6711, 120.3385), 'San Juan, La Union');

// Sunny in San Juan -> Urbiztondo Beach prioritized first, excludes pending & maintenance
const sunnyRes = sandbox.window.getWeatherRecommendedSpots({ weather_code: 0, condition: 'Clear Sky', location: 'San Juan, La Union', temperature: 31 }, sampleSpots);
assert.strictEqual(sunnyRes.isSevere, false);
assert.strictEqual(sunnyRes.meta.group, 'sunny');
assert.strictEqual(sunnyRes.spots[0].spot.name, 'Urbiztondo Beach');
assert(!sunnyRes.spots.some(s => s.spot.status === 'pending' || s.spot.is_maintenance), 'Excludes unapproved and maintenance spots');

// Real-time reconciliation: coarse "Clear Night" (code 0) when today's forecast is "Thunderstorm · 100% rain"
// must NOT classify as sunny or recommend beaches/viewpoints!
const coarseNightStorm = sandbox.window.getWeatherRecommendedSpots({
    weather_code: 0,
    condition: 'Clear Night',
    weather_group: 'sunny',
    short_description: 'Bright sunny weather — perfect for beaches, coastal viewpoints, parks, and outdoor adventures.',
    location: 'Bagulin, La Union',
    temperature: 25,
    forecast: [{ day: 'Tue', condition: 'Thunderstorm', weather_code: 95, rain_prob: 100, temp_max: 31, temp_min: 23 }]
}, sampleSpots);
assert.strictEqual(coarseNightStorm.meta.group, 'severe');
assert(!coarseNightStorm.meta.shortDesc.toLowerCase().includes('sunny'), 'Reconciled storm weather never shows sunny description');
assert(!coarseNightStorm.spots.some(s => s.spot.name.includes('Beach') || s.spot.name.includes('Falls')), 'Storm weather excludes beaches and waterfalls');
assert(coarseNightStorm.spots.some(s => s.spot.name === 'Museo de Iloko' || s.spot.name === 'Pindangan Ruins'), 'Storm weather recommends safe indoor/cultural sites');

// Rainy -> Recommends Museum/Historical and strictly excludes Beach/Waterfalls (even multi-category "Beach,Historical")
const rainyRes = sandbox.window.getWeatherRecommendedSpots({ weather_code: 61, condition: 'Moderate Rain', location: 'San Juan, La Union', temperature: 26 }, sampleSpots);
assert.strictEqual(rainyRes.meta.group, 'rainy');
assert(!rainyRes.spots.some(s => s.spot.name.includes('Beach') || s.spot.name.includes('Falls')), 'Rainy mode excludes beaches and waterfalls including multi-category Beach,Historical');
assert(rainyRes.spots.some(s => s.spot.name === 'Museo de Iloko' || s.spot.name === 'Pindangan Ruins'), 'Rainy mode recommends museum and heritage sites');

// Cloudy (e.g. tapping "Wed · Overcast" in 5-Day Forecast) -> Recommends Heritage & Farm/Garden + related categories
const cloudyRes = sandbox.window.getWeatherRecommendedSpots({ _isForecastDayPick: true, weather_code: 3, condition: 'Overcast', rain_prob: 84, location: 'Bauang, La Union', temperature: 27 }, sampleSpots);
assert.strictEqual(cloudyRes.meta.group, 'cloudy');
assert.strictEqual(cloudyRes.spots[0].spot.name, 'Gapuz Grape Farm', 'Cloudy mode prioritizes matching farm/garden in selected municipality');
assert(Array.isArray(cloudyRes.meta.categories) && cloudyRes.meta.categories.length === 3, 'Forecast day pick provides related categories for the modal');

// Partly Cloudy -> Recommends Nature/Waterfalls
const partlyRes = sandbox.window.getWeatherRecommendedSpots({ weather_code: 2, condition: 'Partly Cloudy', location: 'San Gabriel, La Union', temperature: 28 }, sampleSpots);
assert.strictEqual(partlyRes.meta.group, 'partly_cloudy');
assert.strictEqual(partlyRes.spots[0].spot.name, 'Tangadan Falls');

// Severe Weather -> Generates danger alert and recommends only safe indoor/cultural shelters (never beaches/waterfalls)
const severeRes = sandbox.window.getWeatherRecommendedSpots({ weather_code: 95, condition: 'Thunderstorm', location: 'San Juan, La Union', wind_speed: 45 }, sampleSpots);
assert.strictEqual(severeRes.isSevere, true);
assert(severeRes.meta.alerts.some(a => a.level === 'danger'), 'Severe weather produces danger safety alert');
assert(severeRes.spots.length > 0 && !severeRes.spots.some(s => s.spot.name.includes('Beach') || s.spot.name.includes('Falls')), 'Severe weather only suggests safe indoor/cultural shelters');

console.log('All smart weather recommendation assertions passed!');
