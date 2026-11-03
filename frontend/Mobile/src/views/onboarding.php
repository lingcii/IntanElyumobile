<?php
$pageTitle = 'Complete Profile';
?>
<div class="onboarding-screen-wrapper" style="min-height: 100vh; background: #f8fafc; display: flex; flex-direction: column; justify-content: space-between; padding: 24px 20px max(24px, env(safe-area-inset-bottom, 24px)) 20px; box-sizing: border-box; max-width: 480px; margin: 0 auto; position: relative;">

    <!-- Top Progress Header -->
    <div style="margin-top: 10px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-size: 11px; font-weight: 800; color: #2563eb; text-transform: uppercase; letter-spacing: 1px;" id="onboard-step-pill">Step 1 of 3</span>
            <button type="button" onclick="window.finishOnboarding(true)" style="background: none; border: none; font-size: 13px; font-weight: 700; color: #64748b; cursor: pointer; padding: 4px 8px;">Skip All</button>
        </div>
        <div style="height: 6px; width: 100%; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
            <div id="onboard-progress-bar" style="height: 100%; width: 33.33%; background: linear-gradient(90deg, #1e3a8a, #2563eb); border-radius: 999px; transition: width 0.3s ease;"></div>
        </div>
    </div>

    <!-- Main Content Step Panels -->
    <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-start;">
        
        <!-- Header text (Dynamic) -->
        <div style="margin-bottom: 24px;">
            <h2 id="onboard-title" style="font-size: 22px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; line-height: 1.3;">Tell us a bit about you</h2>
            <p id="onboard-desc" style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5; font-weight: 500;">Help us customize your Elyu adventure and spot recommendations.</p>
        </div>

        <!-- Step 1: Age & Travel Persona -->
        <div id="onboard-step-1" class="onboard-step-panel" style="display: block;">
            <div style="margin-bottom: 22px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px;">How old are you?</label>
                <div style="position: relative;">
                    <input type="tel" id="onboard-age" inputmode="numeric" maxlength="2" placeholder="e.g. 24" 
                        oninput="this.value = this.value.replace(/\D/g, '').slice(0, 2);"
                        style="width: 100%; padding: 14px 16px; border-radius: 14px; border: 1.5px solid #cbd5e1; background: #ffffff; font-size: 15px; font-weight: 700; color: #0f172a; outline: none; box-sizing: border-box; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 10px;">What is your traveler style?</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;" id="onboard-persona-grid">
                    <div class="onboard-card-select" onclick="window.selectOnboardPersona(this, 'Solo Explorer')">
                        <div style="font-size: 24px; margin-bottom: 4px;">🎒</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Solo Explorer</div>
                        <div style="font-size: 11px; color: #64748b;">Free & flexible</div>
                    </div>
                    <div class="onboard-card-select" onclick="window.selectOnboardPersona(this, 'Couple / Duo')">
                        <div style="font-size: 24px; margin-bottom: 4px;">💑</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Couple / Duo</div>
                        <div style="font-size: 11px; color: #64748b;">Romantic & scenic</div>
                    </div>
                    <div class="onboard-card-select" onclick="window.selectOnboardPersona(this, 'Barkada & Friends')">
                        <div style="font-size: 24px; margin-bottom: 4px;">🏄‍♂️</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Barkada / Group</div>
                        <div style="font-size: 11px; color: #64748b;">Fun & nightlife</div>
                    </div>
                    <div class="onboard-card-select" onclick="window.selectOnboardPersona(this, 'Family Vacation')">
                        <div style="font-size: 24px; margin-bottom: 4px;">👨‍👩‍👧‍👦</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Family Vacation</div>
                        <div style="font-size: 11px; color: #64748b;">Relaxed & kids-safe</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Favorite Spot Categories -->
        <div id="onboard-step-2" class="onboard-step-panel" style="display: none;">
            <p style="font-size: 12.5px; color: #64748b; margin: 0 0 14px 0; font-weight: 600;">Select categories you love visiting the most:</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;" id="onboard-cat-grid">
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Beach')">
                    <div style="font-size: 24px; margin-bottom: 4px;">🏖️</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Beaches & Surfing</div>
                    <div style="font-size: 11px; color: #64748b;">Urbiztondo, Waves</div>
                </div>
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Nature')">
                    <div style="font-size: 24px; margin-bottom: 4px;">🌲</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Nature & Falls</div>
                    <div style="font-size: 11px; color: #64748b;">Tangadan, Rivers</div>
                </div>
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Heritage')">
                    <div style="font-size: 24px; margin-bottom: 4px;">🏛️</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Culture & Heritage</div>
                    <div style="font-size: 11px; color: #64748b;">Churches, Baluarte</div>
                </div>
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Food & Dining')">
                    <div style="font-size: 24px; margin-bottom: 4px;">🍲</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Food & Dining</div>
                    <div style="font-size: 11px; color: #64748b;">Cafes, Seafood</div>
                </div>
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Mountains')">
                    <div style="font-size: 24px; margin-bottom: 4px;">⛰️</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Mountains & Hiking</div>
                    <div style="font-size: 11px; color: #64748b;">Peaks, Trails</div>
                </div>
                <div class="onboard-card-select" onclick="window.toggleOnboardMulti(this, 'Sunset & Nightlife')">
                    <div style="font-size: 24px; margin-bottom: 4px;">🌅</div>
                    <div style="font-weight: 700; font-size: 13px; color: #0f172a;">Sunset & Nightlife</div>
                    <div style="font-size: 11px; color: #64748b;">Golden hour, Bars</div>
                </div>
            </div>
        </div>

        <!-- Step 3: Interests & Hobbies -->
        <div id="onboard-step-3" class="onboard-step-panel" style="display: none;">
            <p style="font-size: 12.5px; color: #64748b; margin: 0 0 14px 0; font-weight: 600;">Pick your favorite travel hobbies & activities:</p>
            <div style="display: flex; flex-wrap: wrap; gap: 9px;" id="onboard-hobbies-list">
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Surfing')">🏄‍♂️ Surfing</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Photography')">📸 Photography</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Cafe Hopping')">☕ Cafe Hopping</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Trekking')">🥾 Trekking & Hiking</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Sunset Watching')">🌇 Sunset Watching</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Camping')">⛺ Camping</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Food Crawl')">🍜 Food Crawl</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Eco-Tours')">🌱 Eco-Tours</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Local Souvenirs')">🛍️ Local Crafts</div>
                <div class="onboard-chip-pill" onclick="window.toggleOnboardChip(this, 'Swimming')">🏊 Swimming</div>
            </div>
        </div>

    </div>

    <!-- Bottom Controls & Pagination Dots -->
    <div style="margin-top: 30px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
        <!-- Prev Button -->
        <button type="button" id="onboard-btn-prev" onclick="window.onboardPrevStep()" 
            style="visibility: hidden; background: #ffffff; border: 1.5px solid #cbd5e1; color: #0f172a; font-weight: 700; font-size: 13.5px; padding: 12px 18px; border-radius: 14px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
            <i class="fa-solid fa-arrow-left"></i> <span>Back</span>
        </button>

        <!-- Dots in the Middle -->
        <div style="display: flex; align-items: center; gap: 7px;" id="onboard-dots-container">
            <span class="onboard-dot active" data-step="1"></span>
            <span class="onboard-dot" data-step="2"></span>
            <span class="onboard-dot" data-step="3"></span>
        </div>

        <!-- Next / Finish Button -->
        <button type="button" id="onboard-btn-next" onclick="window.onboardNextStep()"
            style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none; color: #ffffff; font-weight: 800; font-size: 14px; padding: 13px 22px; border-radius: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(37,99,235,0.3); transition: transform 0.1s ease;">
            <span id="onboard-next-label">Next</span> <i class="fa-solid fa-arrow-right" id="onboard-next-icon"></i>
        </button>
    </div>

</div>

<style>
.onboard-card-select {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    user-select: none;
}
.onboard-card-select:hover {
    border-color: #93c5fd;
    transform: translateY(-1px);
}
.onboard-card-select.selected {
    background: #eff6ff !important;
    border-color: #2563eb !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15) !important;
    transform: translateY(-2px);
}
.onboard-card-select.selected .onboard-card-title {
    color: #1e3a8a !important;
}

.onboard-chip-pill {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 999px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.onboard-chip-pill:hover {
    border-color: #93c5fd;
}
.onboard-chip-pill.selected {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
    box-shadow: 0 3px 10px rgba(37, 99, 235, 0.25);
}

.onboard-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: #cbd5e1;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.onboard-dot.active {
    width: 22px;
    background: #2563eb;
}
</style>

<script>
(function() {
    let currentStep = 1;
    const totalSteps = 3;

    window._onboardState = {
        age: '',
        persona: '',
        categories: [],
        hobbies: []
    };

    window.selectOnboardPersona = function(el, val) {
        document.querySelectorAll('#onboard-persona-grid .onboard-card-select').forEach(card => card.classList.remove('selected'));
        el.classList.add('selected');
        window._onboardState.persona = val;
    };

    window.toggleOnboardMulti = function(el, val) {
        el.classList.toggle('selected');
        const idx = window._onboardState.categories.indexOf(val);
        if (idx > -1) {
            window._onboardState.categories.splice(idx, 1);
        } else {
            window._onboardState.categories.push(val);
        }
    };

    window.toggleOnboardChip = function(el, val) {
        el.classList.toggle('selected');
        const idx = window._onboardState.hobbies.indexOf(val);
        if (idx > -1) {
            window._onboardState.hobbies.splice(idx, 1);
        } else {
            window._onboardState.hobbies.push(val);
        }
    };

    function updateStepUI() {
        // Update Step Pill & Progress Bar
        const pill = document.getElementById('onboard-step-pill');
        const bar = document.getElementById('onboard-progress-bar');
        if (pill) pill.textContent = `Step ${currentStep} of ${totalSteps}`;
        if (bar) bar.style.width = `${(currentStep / totalSteps) * 100}%`;

        // Update Title and Subtitle
        const titleEl = document.getElementById('onboard-title');
        const descEl = document.getElementById('onboard-desc');
        if (currentStep === 1) {
            titleEl.textContent = 'Tell us a bit about you';
            descEl.textContent = 'Help us customize your Elyu adventure and spot recommendations.';
        } else if (currentStep === 2) {
            titleEl.textContent = 'Which spots do you enjoy?';
            descEl.textContent = 'Pick categories to get personalized destination recommendations.';
        } else if (currentStep === 3) {
            titleEl.textContent = 'What are your hobbies?';
            descEl.textContent = 'We will highlight spots that match what you love doing most.';
        }

        // Show/hide Panels
        for (let i = 1; i <= totalSteps; i++) {
            const p = document.getElementById(`onboard-step-${i}`);
            if (p) p.style.display = (i === currentStep) ? 'block' : 'none';
        }

        // Prev Button
        const prevBtn = document.getElementById('onboard-btn-prev');
        if (prevBtn) {
            prevBtn.style.visibility = (currentStep > 1) ? 'visible' : 'hidden';
        }

        // Dots
        document.querySelectorAll('#onboard-dots-container .onboard-dot').forEach((dot, idx) => {
            if (idx + 1 === currentStep) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });

        // Next / Finish Button
        const nextLabel = document.getElementById('onboard-next-label');
        const nextIcon = document.getElementById('onboard-next-icon');
        if (currentStep === totalSteps) {
            if (nextLabel) nextLabel.textContent = 'Finish & Explore';
            if (nextIcon) nextIcon.className = 'fa-solid fa-sparkles';
        } else {
            if (nextLabel) nextLabel.textContent = 'Next';
            if (nextIcon) nextIcon.className = 'fa-solid fa-arrow-right';
        }
    }

    window.onboardPrevStep = function() {
        if (currentStep > 1) {
            currentStep--;
            updateStepUI();
        }
    };

    window.onboardNextStep = function() {
        if (currentStep === 1) {
            const ageInput = document.getElementById('onboard-age');
            if (ageInput && ageInput.value) {
                window._onboardState.age = ageInput.value.trim();
            }
        }

        if (currentStep < totalSteps) {
            currentStep++;
            updateStepUI();
        } else {
            // Finish
            window.finishOnboarding(false);
        }
    };

    window.finishOnboarding = async function(isSkipped) {
        const nextBtn = document.getElementById('onboard-btn-next');
        if (nextBtn) {
            nextBtn.disabled = true;
            nextBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Setting up...';
        }

        let user = {};
        try {
            user = JSON.parse(localStorage.getItem('auth_user') || '{}');
        } catch (e) { }

        // Combine selected categories and hobbies into travel_preferences
        let allPrefs = [];
        if (!isSkipped) {
            if (window._onboardState.categories && window._onboardState.categories.length) {
                allPrefs.push(...window._onboardState.categories);
            }
            if (window._onboardState.hobbies && window._onboardState.hobbies.length) {
                allPrefs.push(...window._onboardState.hobbies);
            }
            if (window._onboardState.persona) {
                allPrefs.push(window._onboardState.persona);
            }
        }

        const travelPrefsStr = allPrefs.join(', ');
        const ageVal = (!isSkipped && window._onboardState.age) ? parseInt(window._onboardState.age, 10) : (user.age || null);

        // Update local session
        user.travel_preferences = travelPrefsStr || user.travel_preferences || 'Beaches, Nature, Food & Dining';
        if (ageVal) user.age = ageVal;
        user.onboarding_completed = true;
        localStorage.setItem('auth_user', JSON.stringify(user));
        localStorage.setItem('intan_elyu_onboarding_done_' + (user.id || 'user'), 'true');

        // Persist to server
        const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('api_token');
        if (token && !token.startsWith('offline_') && !token.startsWith('guest_')) {
            try {
                const backend = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
                await fetch(backend + '/api/tourist/profile', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        age: ageVal,
                        travel_preferences: user.travel_preferences
                    })
                });
            } catch (err) {
                console.warn('Profile onboarding sync error:', err);
            }
        }

        if (typeof showToast === 'function') {
            showToast(isSkipped ? 'Welcome! Exploring all of La Union.' : 'Preferences saved! Curating your recommendations...', 'success');
        }

        // Navigate to dashboard
        if (typeof navigateTo === 'function') {
            navigateTo('dashboard');
        } else {
            window.location.href = '?view=dashboard';
        }
    };

    updateStepUI();
})();
</script>

