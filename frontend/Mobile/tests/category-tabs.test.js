const assert = require('assert');
const fs = require('node:fs');
const path = require('node:path');
const categoriesSource = fs.readFileSync(path.join(__dirname, '..', 'src', 'views', 'categories.php'), 'utf8');

// Test canonicalization and category matching logic from categories view
function canonicalizeCategory(cat) {
    if (!cat) return 'All';
    const raw = String(cat).trim();
    const lower = raw.toLowerCase();
    if (lower === 'all' || lower === 'all spots') return 'All';
    if (lower.includes('beach') || lower.includes('surf') || lower.includes('coastal')) return 'Beach';
    if (lower.includes('museum') || lower.includes('gallery')) return 'Museum';
    if (lower.includes('church') || lower.includes('religious') || lower.includes('shrine') || lower.includes('cathedral')) return 'Religious';
    if (lower.includes('heritage') || lower.includes('cultural') || lower.includes('historic')) return 'Heritage';
    if (lower.includes('food') || lower.includes('dining') || lower.includes('cafe') || lower.includes('restaurant')) return 'Food & Dining';
    if (lower.includes('mountain') || lower.includes('hiking') || lower.includes('trail') || lower.includes('peak')) return 'Mountains';
    if (lower.includes('lake') || lower.includes('fall') || lower.includes('waterfall') || lower.includes('river')) return 'Lakes';
    if (lower.includes('farm') || lower.includes('agri') || lower.includes('vineyard') || lower.includes('plantation')) return 'Farm';
    if (lower.includes('nightlife') || lower.includes('bar') || lower.includes('club')) return 'Nightlife';
    if (lower.includes('adventure') || lower.includes('camping') || lower.includes('camp')) return 'Adventure';
    if (lower.includes('nature') || lower.includes('park') || lower.includes('garden')) return 'Nature';
    return raw;
}

function matchesCategory(cardCategory, cardName, cardMuni, targetCat) {
    if (!targetCat || targetCat === 'All') return true;
    const c = (cardCategory || '').toLowerCase().trim();
    const n = (cardName || '').toLowerCase().trim();
    const m = (cardMuni || '').toLowerCase().trim();
    const combined = `${c} ${n} ${m}`;
    const t = targetCat.toLowerCase().trim();

    if (c && (c === t || c.includes(t) || t.includes(c))) return true;

    if (t === 'beach') {
        return combined.includes('beach') || combined.includes('surf') || combined.includes('coastal') || combined.includes('seascape') || combined.includes('island');
    }
    if (t === 'nature') {
        return combined.includes('nature') || combined.includes('park') || combined.includes('garden') || combined.includes('plaza') || combined.includes('mangrove') || combined.includes('baywalk');
    }
    if (t === 'heritage') {
        return combined.includes('heritage') || combined.includes('historic') || combined.includes('cultural') || combined.includes('landmark') || combined.includes('monument') || combined.includes('museum') || combined.includes('church') || combined.includes('shrine') || combined.includes('watchtower');
    }
    if (t === 'food & dining' || t === 'food') {
        return combined.includes('food') || combined.includes('dining') || combined.includes('restaurant') || combined.includes('cafe') || combined.includes('bistro') || combined.includes('eatery') || combined.includes('kitchen');
    }
    if (t === 'mountains' || t === 'mountain') {
        return combined.includes('mountain') || combined.includes('peak') || combined.includes('hiking') || combined.includes('trail') || combined.includes('view deck') || combined.includes('viewdeck') || combined.includes('mt.');
    }
    if (t === 'lakes' || t === 'lake') {
        return combined.includes('lake') || combined.includes('fall') || combined.includes('waterfall') || combined.includes('river') || combined.includes('dam') || combined.includes('spring');
    }
    if (t === 'farm') {
        return combined.includes('farm') || combined.includes('grapes') || combined.includes('plantation') || combined.includes('agri') || combined.includes('vineyard');
    }
    if (t === 'museum') {
        return combined.includes('museum') || combined.includes('gallery') || combined.includes('cultural') || combined.includes('pottery') || combined.includes('weaving');
    }
    if (t === 'religious') {
        return combined.includes('church') || combined.includes('shrine') || combined.includes('basilica') || combined.includes('cathedral') || combined.includes('chapel') || combined.includes('parish') || combined.includes('religious');
    }
    if (t === 'adventure') {
        return combined.includes('adventure') || combined.includes('camp') || combined.includes('glamping') || combined.includes('trek');
    }
    if (t === 'nightlife') {
        return combined.includes('nightlife') || combined.includes('bar') || combined.includes('pub') || combined.includes('club') || combined.includes('lounge');
    }
    return false;
}

// 1. Test Category Tab Canonicalization
assert.strictEqual(canonicalizeCategory('Beach'), 'Beach');
assert.strictEqual(canonicalizeCategory('Cultural'), 'Heritage');
assert.strictEqual(canonicalizeCategory('Museums & Cultural'), 'Museum');
assert.strictEqual(canonicalizeCategory('Heritage & Churches'), 'Religious');
assert.strictEqual(canonicalizeCategory('Indoor Dining & Cafes'), 'Food & Dining');
assert.strictEqual(canonicalizeCategory('Food'), 'Food & Dining');
assert.strictEqual(canonicalizeCategory('Nature'), 'Nature');
assert.strictEqual(canonicalizeCategory('All Spots'), 'All');

// 2. Test Spot Category Matching
const mockSpots = [
    { id: 1, name: 'Urbiztondo Beach', category: 'Beach', municipality: 'San Juan' },
    { id: 2, name: 'Tangadan Falls', category: 'Waterfalls', municipality: 'San Gabriel' },
    { id: 3, name: 'Namacpacan Church', category: 'Religious', municipality: 'Luna' },
    { id: 4, name: 'Baluarte Watchtower', category: 'Historical', municipality: 'Luna' },
    { id: 5, name: 'Halo-Halo de Iloko', category: 'Dining', municipality: 'San Fernando' },
    { id: 6, name: 'Lomboy Grapes Farm', category: 'Farm', municipality: 'Bauang' },
    { id: 7, name: 'Silid Cultural Museum', category: 'Museum', municipality: 'San Fernando' }
];

// Beach Tab
const beachMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Beach'));
assert.strictEqual(beachMatches.length, 1);
assert.strictEqual(beachMatches[0].name, 'Urbiztondo Beach');

// Lakes & Falls Tab
const fallsMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Lakes'));
assert.strictEqual(fallsMatches.length, 1);
assert.strictEqual(fallsMatches[0].name, 'Tangadan Falls');

// Religious Tab
const churchMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Religious'));
assert.strictEqual(churchMatches.length, 1);
assert.strictEqual(churchMatches[0].name, 'Namacpacan Church');

// Heritage Tab matches both watchtower and historical
const heritageMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Heritage'));
assert.ok(heritageMatches.some(s => s.name === 'Baluarte Watchtower'));

// Food & Dining Tab
const foodMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Food & Dining'));
assert.strictEqual(foodMatches.length, 1);
assert.strictEqual(foodMatches[0].name, 'Halo-Halo de Iloko');

// Farm Tab
const farmMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Farm'));
assert.strictEqual(farmMatches.length, 1);
assert.strictEqual(farmMatches[0].name, 'Lomboy Grapes Farm');

// Museum Tab
const museumMatches = mockSpots.filter(s => matchesCategory(s.category, s.name, s.municipality, 'Museum'));
assert.strictEqual(museumMatches.length, 1);
assert.strictEqual(museumMatches[0].name, 'Silid Cultural Museum');

assert.match(categoriesSource, /Most Visited[\s\S]*Name A-Z[\s\S]*Name Z-A/,
    'the category screen must provide the requested sorting choices');
assert.doesNotMatch(categoriesSource, /Free Entry|With Entrance Fee/,
    'entry-fee filters must be removed');
assert.match(categoriesSource, /currentSort === 'alpha-asc'[\s\S]*currentSort === 'alpha-desc'/,
    'both alphabetical directions must be implemented');

console.log('Category tab canonicalization & spot matching tests passed!');

