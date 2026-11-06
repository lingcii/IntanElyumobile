const assert = require('assert');

// 1. Test QuotaExceeded eviction logic
console.log('Testing QuotaExceeded safe storage handling...');

const mockStorage = {};
let throwQuotaOnce = true;

const localStorageMock = {
    getItem(key) { return mockStorage[key] || null; },
    setItem(key, val) {
        if (throwQuotaOnce && key.startsWith('dashboard_data_')) {
            throwQuotaOnce = false;
            const err = new Error('QuotaExceededError');
            err.name = 'QuotaExceededError';
            err.code = 22;
            throw err;
        }
        mockStorage[key] = val;
    },
    removeItem(key) { delete mockStorage[key]; },
    key(i) { return Object.keys(mockStorage)[i] || null; },
    get length() { return Object.keys(mockStorage).length; }
};

mockStorage['auth_user'] = JSON.stringify({ id: 1, points: 250, xp: 5000 });
mockStorage['dashboard_data_old'] = JSON.stringify({ old: true });
mockStorage['dashboard_rank_old'] = JSON.stringify({ rank: 1 });

function safeCacheSet(key, valueStr, storage) {
    try {
        storage.setItem(key, valueStr);
        return true;
    } catch (e) {
        const isQuota = e && (e.name === 'QuotaExceededError' || e.code === 22 || e.code === 1014 || e.number === -2147024882);
        if (!isQuota) return false;
        try {
            const preserveKeys = ['auth_user', 'auth_user_backup', 'intan_elyu_user', 'intan_elyu_token', 'Intan_Elyu_Token', 'api_token', 'user_role', 'offline_queue'];
            const keysToRemove = [];
            for (let i = 0; i < storage.length; i++) {
                const k = storage.key(i);
                if (!k || preserveKeys.includes(k)) continue;
                if (k.startsWith('dashboard_data_') || k.startsWith('dashboard_rank_') || k.startsWith('places_cache_') || k.includes('_cache') || k.startsWith('weather_') || k.startsWith('points_balance_')) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => {
                try { storage.removeItem(k); } catch (_) {}
            });
            storage.setItem(key, valueStr);
            return true;
        } catch (_) {
            return false;
        }
    }
}

const saved = safeCacheSet('dashboard_data_new', JSON.stringify({ new: true }), localStorageMock);
assert.strictEqual(saved, true, 'safeCacheSet must succeed even when quota was initially exceeded');
assert.strictEqual(mockStorage['dashboard_data_old'], undefined, 'Old dashboard cache should be evicted');
assert.notStrictEqual(mockStorage['auth_user'], undefined, 'auth_user must be preserved');
console.log('✓ QuotaExceeded eviction logic passed');

// 2. Test Word Scramble Slider Flow
console.log('Testing Word Scramble sliding progression...');

const questions = [
    { answer: 'SAN JUAN', scrambled: 'NAS NAUJ' },
    { answer: 'BALAOAN', scrambled: 'NAAALOB' },
    { answer: 'AGOO', scrambled: 'OOAG' },
    { answer: 'LUNA', scrambled: 'ANUL' }
];

let currentIndex = 0;
let submitted = false;

function normalize(s) {
    return (s || '').toString().trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
}

function handleNextOrSubmit(userAnswers) {
    const cur = questions[currentIndex];
    const ans = userAnswers[currentIndex];
    if (normalize(ans) !== normalize(cur.answer)) {
        return { status: 'blocked', reason: 'incorrect answer' };
    }
    if (currentIndex < questions.length - 1) {
        currentIndex++;
        return {
            status: 'next',
            index: currentIndex,
            buttonText: (currentIndex === questions.length - 1) ? 'Submit Answers' : 'Next Word'
        };
    } else {
        submitted = true;
        return { status: 'submitted' };
    }
}

// Word 1: Wrong answer
assert.strictEqual(handleNextOrSubmit(['WRONG']).status, 'blocked');
assert.strictEqual(currentIndex, 0);

// Word 1: Correct answer
let res = handleNextOrSubmit(['San Juan']);
assert.strictEqual(res.status, 'next');
assert.strictEqual(currentIndex, 1);
assert.strictEqual(res.buttonText, 'Next Word');

// Word 2: Correct answer
res = handleNextOrSubmit(['San Juan', 'balaoan']);
assert.strictEqual(res.status, 'next');
assert.strictEqual(currentIndex, 2);
assert.strictEqual(res.buttonText, 'Next Word');

// Word 3: Correct answer -> Moves to word 4 (last question)
res = handleNextOrSubmit(['San Juan', 'balaoan', 'agoo']);
assert.strictEqual(res.status, 'next');
assert.strictEqual(currentIndex, 3);
assert.strictEqual(res.buttonText, 'Submit Answers');

// Word 4: Submit answers
res = handleNextOrSubmit(['San Juan', 'balaoan', 'agoo', 'luna']);
assert.strictEqual(res.status, 'submitted');
assert.strictEqual(submitted, true);
console.log('✓ Word Scramble slider progression passed');

// 3. Test points extraction prioritizing spendable points over xp
console.log('Testing points balance calculation...');
const mockApiResponse = { status: 'success', points: 350, xp: 100000 };
const userPoints = Number(mockApiResponse.points !== undefined ? mockApiResponse.points : (mockApiResponse.xp ?? 0));
assert.strictEqual(userPoints, 350, 'Spendable points (350) must not be clobbered by XP (100,000)');
console.log('✓ Spendable points resolution passed');

// 4. Test Slide Puzzle description sanitize to 50 Points
console.log('Testing Slide Puzzle points sanitization...');
const legacyDesc100 = 'Rearrange the tiles to reveal the image of Agoo Arc in Agoo! Solve to earn <strong style="color: #38bdf8;">+100 Points</strong>.';
const legacyDesc30 = 'Rearrange the tiles to reveal Immuki Island in Balaoan! Solve to earn <strong style="color: #38bdf8;">+30 Points</strong>.';
const sanitized100 = legacyDesc100.replace(/\+(?:100|30)\s*Points/gi, '+50 Points');
const sanitized30 = legacyDesc30.replace(/\+(?:100|30)\s*Points/gi, '+50 Points');
assert.ok(sanitized100.includes('+50 Points'), 'Should sanitize +100 Points to +50 Points');
assert.ok(!sanitized100.includes('+100 Points'), 'Should not contain +100 Points');
assert.ok(sanitized30.includes('+50 Points'), 'Should sanitize +30 Points to +50 Points');
console.log('✓ Slide puzzle points sanitization passed');

// 5. Test Daily Game Reset Countdown calculation
console.log('Testing Daily Game Reset Countdown calculation...');
function calcDailyReset(nowMs, nextMidnightMs) {
    const diffMs = Math.max(0, nextMidnightMs - nowMs);
    const hours = Math.floor(diffMs / (1000 * 60 * 60));
    const mins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    const secs = Math.floor((diffMs % (1000 * 60)) / 1000);
    return `${hours}h ${String(mins).padStart(2, '0')}m ${String(secs).padStart(2, '0')}s`;
}
const fixedNow = new Date('2026-11-06T19:00:00').getTime();
const fixedMidnight = new Date('2026-11-07T00:00:00').getTime();
const countdownStr = calcDailyReset(fixedNow, fixedMidnight);
assert.strictEqual(countdownStr, '5h 00m 00s', 'Should calculate 5 hours remaining until midnight');
console.log('✓ Daily game reset countdown calculation passed');

// 6. Test Practice Mode completion return logic
console.log('Testing Practice Mode completion return...');
const practiceModes = { memory_match: true };
let gameDone = true; // completed today
function isDoneToday(type) {
    if (practiceModes[type]) return false;
    return gameDone;
}
assert.strictEqual(isDoneToday('memory_match'), false, 'During practice mode, isDone must be false');
// On submit / claim:
delete practiceModes['memory_match'];
assert.strictEqual(isDoneToday('memory_match'), true, 'After practice mode completion, isDone must return to true');
console.log('✓ Practice mode completion return passed');

console.log('All GameZone & Word Scramble tests passed!');

