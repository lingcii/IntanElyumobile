const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', 'src', 'assets', 'js', 'main.js'), 'utf8');
const start = source.indexOf('window.OfflineCheckinManager = {');
const end = source.indexOf('\n};', start);
assert.ok(start >= 0 && end > start, 'OfflineCheckinManager source must be present');

const values = new Map();
const context = {
    window: {
        dispatchEvent() {},
    },
    localStorage: {
        getItem(key) { return values.has(key) ? values.get(key) : null; },
        setItem(key, value) { values.set(key, String(value)); },
    },
    CustomEvent: class CustomEvent {
        constructor(type, init) { this.type = type; this.detail = init && init.detail; }
    },
    console,
    Date,
    Math,
    JSON,
    String,
    Boolean,
    Array,
    Blob,
};

vm.createContext(context);
vm.runInContext(source.slice(start, end + 3), context);
const manager = context.window.OfflineCheckinManager;

manager.saveQueue([
    { id: 'a', type: 'itinerary', itemId: 101, spotId: 9, itineraryId: 1 },
    { id: 'b', type: 'itinerary', itemId: 202, spotId: 9, itineraryId: 2 },
]);

assert.equal(manager.getQueuedItem(101, 9, 1).id, 'a');
assert.equal(manager.getQueuedItem(202, 9, 2).id, 'b');
assert.equal(manager.getQueuedItem(303, 9, 3), null, 'a new itinerary must not inherit a same-spot proof');
assert.equal(manager.getQueuedItem('offline_item', 9, 2).id, 'b', 'spot fallback must remain scoped to one itinerary');

manager.remapItineraryItems(1, {
    id: 11,
    items: [{ id: 111, tourist_spot_id: 9 }],
});
assert.equal(manager.getQueuedItem(111, 9, 11).id, 'a');
assert.equal(manager.getQueuedItem(202, 9, 2).id, 'b');

(async () => {
    await manager.queueOfflineCheckin({ type: 'itinerary', itemId: 303, spotId: 9, itineraryId: 3, isPhotoUploadOnly: true });
    assert.equal(manager.getQueue().length, 3, 'same destination in a different itinerary must create a separate queue entry');
    assert.equal(manager.getQueuedItem(303, 9, 3).isPhotoUploadOnly, true,
        'offline proof uploads must retain proof-only sync intent');
    console.log('offline proof isolation tests passed');
})().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
