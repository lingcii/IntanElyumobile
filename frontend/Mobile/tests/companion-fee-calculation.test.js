const assert = require('assert');
const fs = require('fs');
const path = require('path');

function testCompanionFeeCalculation() {
    // 1. Verify exact math formula for traveler categories
    const isGroup = true;
    const compAdults = 1;
    const compKids = 2;
    const compSeniors = 1;
    const compPwds = 1;

    const numAdults = 1 + (isGroup ? compAdults : 0);
    const numKids = isGroup ? compKids : 0;
    const numSeniors = isGroup ? compSeniors : 0;
    const numPwds = isGroup ? compPwds : 0;

    assert.strictEqual(numAdults, 2, 'Primary traveler + 1 companion adult = 2 adults');
    assert.strictEqual(numKids, 2, '2 kids');
    assert.strictEqual(numSeniors, 1, '1 senior');
    assert.strictEqual(numPwds, 1, '1 PWD');

    // Spot fee setup
    const spot = {
        adult_fee: 100,
        entrance_fee: 100,
        kids_fee: 50,
        senior_citizen_fee: 80,
        pwd_fee: 80,
        environmental_fee: 50
    };

    const baseAdult = spot.adult_fee > 0 ? spot.adult_fee : (spot.entrance_fee > 0 ? spot.entrance_fee : 0);
    const kidsFee = spot.kids_fee !== undefined ? spot.kids_fee : baseAdult;
    const seniorFee = spot.senior_citizen_fee > 0 ? spot.senior_citizen_fee : (baseAdult * 0.8);
    const pwdFee = spot.pwd_fee > 0 ? spot.pwd_fee : (baseAdult * 0.8);
    const envFee = spot.environmental_fee || 0;
    const spotEnvSenior = (seniorFee > 0 && baseAdult > 0) ? Math.round(envFee * (seniorFee / baseAdult)) : Math.round(envFee * 0.8);
    const spotEnvPwd = (pwdFee > 0 && baseAdult > 0) ? Math.round(envFee * (pwdFee / baseAdult)) : Math.round(envFee * 0.8);

    const totAdultEntrance = numAdults * baseAdult;
    const totChildEntrance = numKids * kidsFee;
    const totSeniorEntrance = numSeniors * seniorFee;
    const totPwdEntrance = numPwds * pwdFee;

    const totAdultEnvi = numAdults * envFee;
    const totChildEnvi = numKids * envFee;
    const totSeniorEnvi = numSeniors * spotEnvSenior;
    const totPwdEnvi = numPwds * spotEnvPwd;

    const totalEntranceFees = totAdultEntrance + totChildEntrance + totSeniorEntrance + totPwdEntrance;
    const totalEnvironmentalFees = totAdultEnvi + totChildEnvi + totSeniorEnvi + totPwdEnvi;
    const totalSiteFees = totalEntranceFees + totalEnvironmentalFees;

    assert.strictEqual(totAdultEntrance, 200);
    assert.strictEqual(totChildEntrance, 100);
    assert.strictEqual(totSeniorEntrance, 80);
    assert.strictEqual(totPwdEntrance, 80);
    assert.strictEqual(totalEntranceFees, 460);

    assert.strictEqual(totAdultEnvi, 100);
    assert.strictEqual(totChildEnvi, 100);
    assert.strictEqual(totSeniorEnvi, 40);
    assert.strictEqual(totPwdEnvi, 40);
    assert.strictEqual(totalEnvironmentalFees, 280);

    assert.strictEqual(totalSiteFees, 740);

    // 2. Inspect frontend/Mobile/src/views/itinerary.php content for exact requirements
    const itineraryPhp = fs.readFileSync(path.join(__dirname, '../src/views/itinerary.php'), 'utf8');

    // General: row should NOT be present in Entrance Fee or Environmental Fee cards
    assert.ok(!itineraryPhp.includes('<span>General:</span><span style="font-weight:700; color:#0f172a;">₱${fmtFee(totGeneralEntrance)}</span>'),
        'General entrance fee row must be removed');
    assert.ok(!itineraryPhp.includes('<span>General:</span><span style="font-weight:700; color:#0f172a;">₱${fmtFee(totGeneralEnvi)}</span>'),
        'General environmental fee row must be removed');

    // Must calculate totals across counts
    assert.ok(itineraryPhp.includes('totAdultEntrance += (numAdults * baseAdult)'), 'Adult entrance must multiply count');
    assert.ok(itineraryPhp.includes('totChildEntrance += (numKids * kidsFee)'), 'Child entrance must multiply count');
    assert.ok(itineraryPhp.includes('totPwdEntrance += (numPwds * pwdFee)'), 'PWD entrance must multiply count');
    assert.ok(itineraryPhp.includes('totSeniorEntrance += (numSeniors * seniorFee)'), 'Senior entrance must multiply count');

    assert.ok(itineraryPhp.includes('totAdultEnvi += (numAdults * displayEnvi)'), 'Adult env must multiply count');
    assert.ok(itineraryPhp.includes('totChildEnvi += (numKids * displayEnvi)'), 'Child env must multiply count');
    assert.ok(itineraryPhp.includes('totPwdEnvi += (numPwds * spotEnvPwd)'), 'PWD env must multiply count');
    assert.ok(itineraryPhp.includes('totSeniorEnvi += (numSeniors * spotEnvSenior)'), 'Senior env must multiply count');

    console.log('Companion category fee calculation test passed successfully!');
}

testCompanionFeeCalculation();

