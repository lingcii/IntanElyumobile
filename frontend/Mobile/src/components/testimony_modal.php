<!-- Global Review Destination Modal Component -->
<style>
#testimony-modal.active { opacity: 1 !important; }
#testimony-modal.active .testimony-card-anim { transform: scale(1) translateY(0) !important; opacity: 1 !important; }
#testimony-comment::placeholder,
#testimony-policy::placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
    opacity: 1 !important;
}
#testimony-comment::-webkit-input-placeholder,
#testimony-policy::-webkit-input-placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
}
#testimony-comment:focus,
#testimony-policy:focus {
    background: rgba(255, 255, 255, 0.22) !important;
    border: 1.5px solid #38bdf8 !important;
    outline: none !important;
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.35) !important;
}
</style>

<div id="testimony-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.65); z-index:2147483647; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); opacity:0; transition:opacity 0.3s ease;">
    <div class="testimony-card-anim" style="background:linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border:none !important; outline:none !important; border-radius:24px; padding:24px; width:100%; max-width:380px; max-height:85vh; overflow-y:auto; box-shadow:0 25px 50px rgba(10,25,60,0.45); text-align:left; box-sizing:border-box; transform:scale(0.88) translateY(16px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
            <div>
                <h3 id="testimony-modal-title" style="margin:0 0 4px; color:#ffffff; font-size:19px; font-weight:800; letter-spacing:-0.3px;">Review Destination</h3>
                <p id="testimony-modal-subtitle" style="margin:0; font-size:12px; color:rgba(255,255,255,0.85); line-height:1.4;">Share your site testimony and policy recommendations to help local tourism.</p>
            </div>
            <button type="button" onclick="window.closeWriteTestimonyModal()" style="background:rgba(255,255,255,0.15); border:none; color:#ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0;">
                <i class="fa-solid fa-xmark" style="font-size:14px;"></i>
            </button>
        </div>

        <!-- Rewards Incentive Callout Banner -->
        <div id="testimony-reward-banner" style="background:linear-gradient(135deg, rgba(56, 189, 248, 0.2) 0%, rgba(37, 99, 235, 0.25) 100%); border:1px solid rgba(56, 189, 248, 0.35); border-radius:16px; padding:10px 14px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; gap:10px; transition:all 0.3s ease;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div id="testimony-reward-icon" style="width:34px; height:34px; border-radius:10px; background:rgba(255,255,255,0.2); display:flex; align-items:center; justify-content:center; color:#fbbf24; font-size:16px; flex-shrink:0;">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <span id="testimony-reward-title" style="display:block; font-size:12px; font-weight:800; color:#ffffff;">Review & Earn Rewards</span>
                    <span id="testimony-reward-desc" style="font-size:11px; color:rgba(255,255,255,0.9); line-height:1.2;">Submit review to claim tourist points</span>
                </div>
            </div>
            <div id="testimony-reward-badges" style="display:flex; gap:5px; flex-shrink:0;">
                <span style="background:rgba(56,189,248,0.3); color:#67e8f9; font-size:11px; font-weight:800; padding:4px 8px; border-radius:8px; white-space:nowrap;">+25 XP</span>
                <span style="background:rgba(251,191,36,0.3); color:#fbbf24; font-size:11px; font-weight:800; padding:4px 8px; border-radius:8px; white-space:nowrap;">+25 PTS</span>
            </div>
        </div>

        <form id="testimony-form" onsubmit="window.submitTestimony(event)">
            <input type="hidden" id="testimony-spot-id">

            <!-- Star Rating selection -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11.5px; font-weight:800; color:#ffffff; text-transform:uppercase; letter-spacing:0.4px; display:block; margin-bottom:6px;">Your Rating (1 to 5 Stars):</label>
                <div style="display:flex; gap:8px; font-size:24px; color:#f59e0b;">
                    <i class="fa-solid fa-star star-btn" data-star="1" style="cursor:pointer;" onclick="window.setStarRating(1)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="2" style="cursor:pointer;" onclick="window.setStarRating(2)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="3" style="cursor:pointer;" onclick="window.setStarRating(3)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="4" style="cursor:pointer;" onclick="window.setStarRating(4)"></i>
                    <i class="fa-solid fa-star star-btn" data-star="5" style="cursor:pointer;" onclick="window.setStarRating(5)"></i>
                </div>
                <input type="hidden" id="testimony-rating" value="5">
            </div>

            <!-- Cleanliness, Safety parameters -->
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">

                <!-- Cleanliness -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.9); text-transform:uppercase;">Cleanliness:</label>
                        <span id="cleanliness-selected-label" style="font-size:11px; font-weight:800; color:#10b981;">Clean</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill clean-pill active" data-val="clean" onclick="window.selectCleanliness('clean')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(16,185,129,0.22); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            Clean
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="moderate" onclick="window.selectCleanliness('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Moderate
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="dirty" onclick="window.selectCleanliness('dirty')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Dirty
                        </button>
                    </div>
                    <input type="hidden" id="testimony-cleanliness" value="clean">
                </div>

                <!-- Safety -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.9); text-transform:uppercase;">Safety Level:</label>
                        <span id="safety-selected-label" style="font-size:11px; font-weight:800; color:#10b981;">Safe</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill safety-pill active" data-val="safe" onclick="window.selectSafety('safe')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(16,185,129,0.22); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            Safe
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="moderate" onclick="window.selectSafety('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Moderate
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="unsafe" onclick="window.selectSafety('unsafe')" style="flex:1; padding:8px 4px; border-radius:10px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Unsafe
                        </button>
                    </div>
                    <input type="hidden" id="testimony-safety" value="safe">
                </div>
            </div>

            <!-- Testimony description -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11.5px; font-weight:800; color:#ffffff; text-transform:uppercase; letter-spacing:0.4px; display:block; margin-bottom:6px;">Your Testimony:</label>
                <textarea id="testimony-comment" placeholder="Describe your experience during this site visit..." style="width:100%; height:75px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25) !important; outline:none !important; border-radius:14px; padding:12px; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-size:13px; font-weight:500; font-family:inherit; resize:none; box-sizing:border-box; line-height:1.45;" required></textarea>
            </div>

            <!-- Policy Recommendation -->
            <div style="margin-bottom:20px;">
                <label style="font-size:11.5px; font-weight:800; color:#ffffff; text-transform:uppercase; letter-spacing:0.4px; display:block; margin-bottom:6px;">Policy Recommendations (Optional):</label>
                <textarea id="testimony-policy" placeholder="Any suggestions or recommendations for safety, cleanliness, or crowd control policies?..." style="width:100%; height:75px; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25) !important; outline:none !important; border-radius:14px; padding:12px; color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; font-size:13px; font-weight:500; font-family:inherit; resize:none; box-sizing:border-box; line-height:1.45;"></textarea>
            </div>

            <button type="submit" id="testimony-submit-btn" class="btn-primary" style="width:100%; padding:14px; font-size:14px; margin-bottom:10px; background:linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); border:none !important; outline:none !important; color:#fff; border-radius:14px; font-weight:800; cursor:pointer; box-shadow:none;">
                <i class="fa-solid fa-paper-plane" style="margin-right:6px;"></i> <span id="testimony-submit-text">Submit Review (+25 XP)</span>
            </button>
        </form>
        <button style="width:100%; padding:12px; border-radius:14px; border:none !important; outline:none !important; background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.85); font-size:13px; font-weight:700; cursor:pointer;" onclick="window.closeWriteTestimonyModal()">Cancel</button>
    </div>
</div>

<script>
// Global Set of Spot IDs already reviewed by the current user
window.userReviewedSpotIds = window.userReviewedSpotIds || new Set();
window.userReviewedSpotData = window.userReviewedSpotData || {};

// Fetch spot IDs reviewed by user to keep UI synchronized across Trip Map, Saved Trips, and Trip History
window.fetchUserReviewedSpots = async function() {
    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    if (!token) return;

    const _backendBase = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');
    try {
        const res = await fetch(_backendBase + '/api/tourist/feedback/user-reviewed-spots', {
            headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });
        if (res.ok) {
            const json = await res.json();
            if (json && json.data && Array.isArray(json.data)) {
                json.data.forEach(id => window.userReviewedSpotIds.add(Number(id)));
            }
            if (json && json.reviews) {
                window.userReviewedSpotData = Object.assign({}, window.userReviewedSpotData, json.reviews);
            }
            if (typeof window.syncReviewedButtons === 'function') {
                window.syncReviewedButtons();
            }
        }
    } catch (e) {
        console.warn('Could not fetch user reviewed spots:', e);
    }
};

// Sync all buttons on current screen with reviewed state
window.syncReviewedButtons = function() {
    if (!window.userReviewedSpotIds) return;
    document.querySelectorAll('[data-spot-id]').forEach(btn => {
        const spotId = Number(btn.getAttribute('data-spot-id'));
        if (spotId && window.userReviewedSpotIds.has(spotId)) {
            btn.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed';
            btn.style.background = 'rgba(255, 255, 255, 0.18)';
            btn.style.boxShadow = 'none';
        }
    });
};

window.openWriteTestimonyModal = function(spotId, btnEl) {
    if (btnEl) {
        window._lastReviewedBtn = btnEl;
    }
    const targetSpotId = spotId || window.currentSelectedSpotId;
    if (targetSpotId) {
        window.currentSelectedSpotId = targetSpotId;
        const spotInput = document.getElementById('testimony-spot-id');
        if (spotInput) spotInput.value = targetSpotId;
    }

    const isAlreadyReviewed = targetSpotId && window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(targetSpotId));
    const titleEl = document.getElementById('testimony-modal-title');
    const subEl = document.getElementById('testimony-modal-subtitle');
    const bannerEl = document.getElementById('testimony-reward-banner');
    const iconEl = document.getElementById('testimony-reward-icon');
    const bannerTitleEl = document.getElementById('testimony-reward-title');
    const bannerDescEl = document.getElementById('testimony-reward-desc');
    const bannerBadgesEl = document.getElementById('testimony-reward-badges');
    const submitTextEl = document.getElementById('testimony-submit-text');
    const commentInput = document.getElementById('testimony-comment');
    const policyInput = document.getElementById('testimony-policy');

    if (isAlreadyReviewed) {
        // Repeated Review state: NO ADDITIONAL REWARDS
        if (titleEl) titleEl.textContent = 'Update Destination Review';
        if (subEl) subEl.textContent = 'Modify your site testimony and policy recommendations for this destination.';
        if (bannerEl) {
            bannerEl.style.background = 'linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.2) 100%)';
            bannerEl.style.borderColor = 'rgba(16, 185, 129, 0.35)';
        }
        if (iconEl) {
            iconEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10b981;"></i>';
        }
        if (bannerTitleEl) bannerTitleEl.textContent = 'Review Already Claimed';
        if (bannerDescEl) bannerDescEl.textContent = 'Rewards are one-time per spot. Updating will not grant additional XP.';
        if (bannerBadgesEl) {
            bannerBadgesEl.innerHTML = '<span style="background:rgba(16,185,129,0.25); color:#34d399; font-size:11px; font-weight:800; padding:4px 8px; border-radius:8px; white-space:nowrap;">Claimed ✓</span>';
        }
        if (submitTextEl) submitTextEl.textContent = 'Update Review';

        // Pre-fill existing data if available
        const prevData = window.userReviewedSpotData && window.userReviewedSpotData[targetSpotId];
        if (prevData) {
            if (typeof window.setStarRating === 'function') window.setStarRating(prevData.rating || 5);
            if (typeof window.selectCleanliness === 'function') window.selectCleanliness(prevData.cleanliness_level || 'clean');
            if (typeof window.selectSafety === 'function') window.selectSafety(prevData.safety_level || 'safe');
            if (commentInput) commentInput.value = prevData.testimony || '';
            if (policyInput) policyInput.value = prevData.policy_recommendation || '';
        }
    } else {
        // First Review state: EARN REWARDS (+25 XP, +25 PTS)
        if (titleEl) titleEl.textContent = 'Review Destination';
        if (subEl) subEl.textContent = 'Share your site testimony and policy recommendations to help local tourism.';
        if (bannerEl) {
            bannerEl.style.background = 'linear-gradient(135deg, rgba(56, 189, 248, 0.2) 0%, rgba(37, 99, 235, 0.25) 100%)';
            bannerEl.style.borderColor = 'rgba(56, 189, 248, 0.35)';
        }
        if (iconEl) {
            iconEl.innerHTML = '<i class="fa-solid fa-gift" style="color:#fbbf24;"></i>';
        }
        if (bannerTitleEl) bannerTitleEl.textContent = 'Review & Earn Rewards';
        if (bannerDescEl) bannerDescEl.textContent = 'Submit review to claim tourist points';
        if (bannerBadgesEl) {
            bannerBadgesEl.innerHTML = '<span style="background:rgba(56,189,248,0.3); color:#67e8f9; font-size:11px; font-weight:800; padding:4px 8px; border-radius:8px; white-space:nowrap;">+25 XP</span><span style="background:rgba(251,191,36,0.3); color:#fbbf24; font-size:11px; font-weight:800; padding:4px 8px; border-radius:8px; white-space:nowrap;">+25 PTS</span>';
        }
        if (submitTextEl) submitTextEl.textContent = 'Submit Review (+25 XP)';

        if (typeof window.setStarRating === 'function') window.setStarRating(5);
        if (typeof window.selectCleanliness === 'function') window.selectCleanliness('clean');
        if (typeof window.selectSafety === 'function') window.selectSafety('safe');
        if (commentInput) commentInput.value = '';
        if (policyInput) policyInput.value = '';
    }

    const modal = document.getElementById('testimony-modal');
    if (modal) {
        modal.style.display = 'flex';
        void modal.offsetHeight;
        modal.classList.add('active');
    }
};

window.closeWriteTestimonyModal = function() {
    const modal = document.getElementById('testimony-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            if (!modal.classList.contains('active')) {
                modal.style.display = 'none';
            }
        }, 320);
    }
};

window.setStarRating = function(rating) {
    const input = document.getElementById('testimony-rating');
    if (input) input.value = rating;
    document.querySelectorAll('#testimony-modal .star-btn').forEach((btn, index) => {
        const starNum = parseInt(btn.dataset.star);
        btn.classList.remove('pop-anim');
        void btn.offsetWidth;

        if (starNum <= rating) {
            btn.style.opacity = '1';
            btn.className = 'fa-solid fa-star star-btn';
            setTimeout(() => {
                btn.classList.add('pop-anim');
            }, index * 40);
        } else {
            btn.style.opacity = '0.35';
            btn.className = 'fa-regular fa-star star-btn';
            btn.style.filter = 'none';
        }
    });
};

window.selectCleanliness = function(val) {
    const input = document.getElementById('testimony-cleanliness');
    if (input) input.value = val;

    const labelMap = { clean: 'Clean', moderate: 'Moderate', dirty: 'Dirty' };
    const colorMap = { clean: '#10b981', moderate: '#f59e0b', dirty: '#f43f5e' };
    const label = document.getElementById('cleanliness-selected-label');
    if (label) {
        label.textContent = labelMap[val] || val;
        label.style.color = colorMap[val] || '#fff';
    }

    document.querySelectorAll('#testimony-modal .clean-pill').forEach(btn => {
        if (btn.dataset.val === val) {
            btn.classList.add('active');
            btn.style.border = '1px solid ' + (colorMap[val] || '#10b981');
            btn.style.background = (colorMap[val] || '#10b981') + '2e';
            btn.style.color = colorMap[val] || '#10b981';
        } else {
            btn.classList.remove('active');
            btn.style.border = '1px solid rgba(255,255,255,0.1)';
            btn.style.background = 'rgba(255,255,255,0.04)';
            btn.style.color = 'rgba(255,255,255,0.7)';
        }
    });
};

window.selectSafety = function(val) {
    const input = document.getElementById('testimony-safety');
    if (input) input.value = val;

    const labelMap = { safe: 'Safe', moderate: 'Moderate', unsafe: 'Unsafe' };
    const colorMap = { safe: '#10b981', moderate: '#f59e0b', unsafe: '#f43f5e' };
    const label = document.getElementById('safety-selected-label');
    if (label) {
        label.textContent = labelMap[val] || val;
        label.style.color = colorMap[val] || '#fff';
    }

    document.querySelectorAll('#testimony-modal .safety-pill').forEach(btn => {
        if (btn.dataset.val === val) {
            btn.classList.add('active');
            btn.style.border = '1px solid ' + (colorMap[val] || '#10b981');
            btn.style.background = (colorMap[val] || '#10b981') + '2e';
            btn.style.color = colorMap[val] || '#10b981';
        } else {
            btn.classList.remove('active');
            btn.style.border = '1px solid rgba(255,255,255,0.1)';
            btn.style.background = 'rgba(255,255,255,0.04)';
            btn.style.color = 'rgba(255,255,255,0.7)';
        }
    });
};

window.submitTestimony = async function(event) {
    if (event) event.preventDefault();
    if (window._isSubmittingTestimony) return;

    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    if (!token) {
        if (typeof showToast === 'function') showToast("Please log in to submit a review.");
        return;
    }
    const _backendBase = (window.backendUrl || 'https://api.intan-elyu.online').replace(/\/+$/, '');

    const submitBtn = document.querySelector('#testimony-modal button[type="submit"]') || document.querySelector('#write-testimony-modal form button[type="submit"]') || document.querySelector('#write-testimony-modal button.submit-btn');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    const spotId = document.getElementById('testimony-spot-id').value;
    const rating = document.getElementById('testimony-rating').value;
    const testimony = document.getElementById('testimony-comment').value;
    const policy = document.getElementById('testimony-policy').value;
    const cleanliness = document.getElementById('testimony-cleanliness').value;
    const safety = document.getElementById('testimony-safety').value;

    try {
        window._isSubmittingTestimony = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Submitting...';
        }

        const response = await fetch(_backendBase + '/api/tourist/feedback', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                tourist_spot_id: spotId,
                rating: rating,
                testimony: testimony,
                policy_recommendation: policy,
                cleanliness_level: cleanliness,
                safety_level: safety
            })
        });

        const data = await response.json();
        if (response.ok) {
            window.closeWriteTestimonyModal();

            const isRewardAwarded = data.reward_awarded === true;

            // Track spot as reviewed in local state
            if (spotId) {
                window.userReviewedSpotIds.add(Number(spotId));
                window.userReviewedSpotData[spotId] = {
                    rating: rating,
                    testimony: testimony,
                    policy_recommendation: policy,
                    cleanliness_level: cleanliness,
                    safety_level: safety
                };
            }

            if (isRewardAwarded) {
                if (window.confetti) {
                    window.confetti({ particleCount: 85, spread: 70, origin: { y: 0.6 } });
                }
                if (typeof showToast === 'function') {
                    showToast(data.message || "🎉 Review submitted! You earned +25 XP & +25 Points!");
                }
                // Invalidate cached user profile & dashboard so rewards counters immediately update
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (token) {
                    localStorage.removeItem('user_profile_' + token.substring(0, 10));
                    localStorage.removeItem('dashboard_data_' + token.substring(0, 10));
                }
                window.dispatchEvent(new CustomEvent('user-points-updated', { detail: { xp: 25, points: 25 } }));
                if (typeof window.fetchUserProfile === 'function') {
                    window.fetchUserProfile(true);
                }
            } else {
                // Repeated review: no points, no confetti
                if (typeof showToast === 'function') {
                    showToast(data.message || "Review updated! (Rewards already claimed for this destination)");
                }
            }

            // Sync all review buttons on the page
            if (typeof window.syncReviewedButtons === 'function') {
                window.syncReviewedButtons();
            }

            if (window._lastReviewedBtn) {
                window._lastReviewedBtn.style.background = 'rgba(255, 255, 255, 0.18)';
                window._lastReviewedBtn.style.border = 'none !important';
                window._lastReviewedBtn.style.color = '#ffffff';
                window._lastReviewedBtn.style.boxShadow = 'none';
                window._lastReviewedBtn.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed';
                window._lastReviewedBtn = null;
            }

            const completionCloseBtn = document.getElementById('btn-trip-completion-close');
            if (completionCloseBtn) {
                completionCloseBtn.textContent = 'Go Back to Saved Trips';
                completionCloseBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                completionCloseBtn.style.color = '#ffffff';
                completionCloseBtn.style.border = 'none !important';
                completionCloseBtn.style.boxShadow = '0 4px 14px rgba(16,185,129,0.3)';
            }

            // Dispatch global spot-reviewed event
            window.dispatchEvent(new CustomEvent('spot-reviewed', { 
                detail: { spotId: spotId, rewardAwarded: isRewardAwarded } 
            }));

            if (typeof fetchTestimonies === 'function') fetchTestimonies(spotId);
        } else {
            if (typeof showToast === 'function') showToast(data.message || "Failed to submit review.");
        }
    } catch (error) {
        console.error("Testimony submission error:", error);
        if (typeof showToast === 'function') showToast("Network error.");
    } finally {
        window._isSubmittingTestimony = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
};

// Automatically fetch user reviewed spots on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.fetchUserReviewedSpots === 'function') window.fetchUserReviewedSpots();
    });
} else {
    if (typeof window.fetchUserReviewedSpots === 'function') window.fetchUserReviewedSpots();
}
</script>
