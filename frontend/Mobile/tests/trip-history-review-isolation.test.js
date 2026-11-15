const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '..');
const testimonyPhp = fs.readFileSync(path.join(root, 'src', 'components', 'testimony_modal.php'), 'utf8');
const profilePhp = fs.readFileSync(path.join(root, 'src', 'views', 'profile.php'), 'utf8');
const mainJs = fs.readFileSync(path.join(root, 'src', 'assets', 'js', 'main.js'), 'utf8');
const feedbackController = fs.readFileSync(path.resolve(root, '..', '..', 'backend', 'app', 'Http', 'Controllers', 'Tourist', 'FeedbackController.php'), 'utf8');

const scriptMatch = testimonyPhp.match(/<script[^>]*>([\s\S]*?)<\/script>/i);
assert(scriptMatch, 'testimony_modal.php should contain a script block');

const elements = {};
const makeEl = (id) => ({
    id,
    value: '',
    textContent: '',
    innerHTML: '',
    style: {},
    classList: { add() {}, remove() {}, contains() { return false; } },
    querySelector() { return null; },
    getAttribute() { return ''; },
});
[
    'testimony-spot-id', 'testimony-modal-title', 'testimony-modal-subtitle',
    'testimony-submit-text', 'testimony-comment', 'testimony-policy',
    'testimony-rating', 'testimony-cleanliness', 'testimony-safety', 'testimony-modal'
].forEach(id => { elements[id] = makeEl(id); });

const context = {
    window: null,
    document: {
        body: { appendChild() {} },
        getElementById: (id) => elements[id] || null,
        querySelectorAll: () => [],
        addEventListener() {},
    },
    localStorage: { getItem: () => null },
    setTimeout,
    clearTimeout,
    console,
};
context.window = context;

vm.runInNewContext(scriptMatch[1], context, { filename: 'testimony_modal.php#1' });

context.userReviewedSpotIds = new Set([5]);
context.userReviewedItemIds = new Set([101, 202]);
context.userReviewedSpotData = {
    5: { rating: 4, testimony: 'Spot level review', policy_recommendation: 'More shade', cleanliness_level: 'clean', safety_level: 'safe' }
};
context.userReviewedItemData = {
    101: { itinerary_item_id: 101, tourist_spot_id: 5, rating: 5, testimony: 'First trip review', policy_recommendation: 'Good signs', cleanliness_level: 'clean', safety_level: 'safe' },
    202: { itinerary_item_id: 202, tourist_spot_id: 5, rating: 3, testimony: 'Second trip review', policy_recommendation: 'Fix stairs', cleanliness_level: 'moderate', safety_level: 'safe' }
};

// Opening Trip 1 stop should prefill Trip 1 review
context.openWriteTestimonyModal('5', null, '101');
assert.strictEqual(elements['testimony-comment'].value, 'First trip review');
assert.strictEqual(elements['testimony-policy'].value, 'Good signs');
assert.strictEqual(context.currentReviewItineraryItemId, 101);

// Opening Trip 2 stop for the SAME destination must prefill Trip 2 review, not Trip 1
context.openWriteTestimonyModal('5', null, '202');
assert.strictEqual(elements['testimony-comment'].value, 'Second trip review');
assert.strictEqual(elements['testimony-policy'].value, 'Fix stairs');
assert.strictEqual(context.currentReviewItineraryItemId, 202);

// Opening an unreviewed Trip 3 stop for the SAME destination must start empty, not reuse Trip 1/2/spot review
context.openWriteTestimonyModal('5', null, '303');
assert.strictEqual(elements['testimony-modal-title'].textContent, 'Review Destination');
assert.strictEqual(elements['testimony-comment'].value, '');
assert.strictEqual(elements['testimony-policy'].value, '');

// Source wiring assertions across Profile Trip History, completion modal, and backend
assert.match(profilePhp, /data-itinerary-item-id="\$\{itemId\}"[\s\S]*openWriteTestimonyModal\('\$\{spotId\}', this, \$\{itemId \? `'\$\{itemId\}'` : 'null'\}\)/,
    'profile.php Trip History detail modal must pass item.id to openWriteTestimonyModal');
assert.match(mainJs, /data-itinerary-item-id="\$\{itemId\}"[\s\S]*startReviewFromCompletion\('\$\{spotId\}', this, \$\{itemId \? `'\$\{itemId\}'` : 'null'\}\)/,
    'main.js completion review modal must pass item.id');
assert.match(feedbackController, /'item_reviews'\s*=>\s*\(object\)\s*\$itemReviewsMap/,
    'FeedbackController::userReviewedSpots must return item_reviews keyed by itinerary_item_id');
assert.match(feedbackController, /\['user_id' => \$userId, 'tourist_spot_id' => \$spotId, 'itinerary_item_id' => null\]/,
    'FeedbackController::store must not overwrite trip-item reviews when submitting a non-trip spot review');

console.log('trip history review isolation tests passed');
