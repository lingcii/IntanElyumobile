<!-- Global Review Destination Modal Component -->
<div id="testimony-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(10,25,60,0.75); z-index:2147483647 !important; align-items:center; justify-content:center; padding:16px; backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); opacity:0; transition:opacity 0.3s ease;">
    <div class="testimony-card-anim" style="background:#ffffff; border:none !important; outline:none !important; border-radius:24px; overflow:hidden; width:100%; max-width:385px; box-shadow:0 25px 50px rgba(10,25,60,0.5); text-align:left; box-sizing:border-box; transform:scale(0.88) translateY(16px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease; display:flex; flex-direction:column;">
        
        <!-- Header (Royal Blue) -->
        <div style="background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important; padding:18px 20px; display:flex; justify-content:space-between; align-items:flex-start; color:#ffffff; flex-shrink:0;">
            <div>
                <h3 id="testimony-modal-title" style="margin:0 0 2px; color:#ffffff; font-size:17px; font-weight:800; letter-spacing:-0.3px;">Review Destination</h3>
                <p id="testimony-modal-subtitle" style="margin:0; font-size:11px; color:rgba(255,255,255,0.9); line-height:1.35;">Share your site feedback and recommendations.</p>
            </div>
            <button type="button" onclick="window.closeWriteTestimonyModal(false, this)" style="background:rgba(255,255,255,0.2); border:none !important; outline:none !important; color:#ffffff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; flex-shrink:0; margin-left:8px; font-size:13px; transition:background 0.2s; touch-action:manipulation; pointer-events:auto;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="testimony-form" onsubmit="window.submitTestimony(event)" style="display:flex; flex-direction:column; margin:0; flex:1; min-height:0;">
            <input type="hidden" id="testimony-spot-id">

            <!-- Middle (White) -->
            <div style="background:#ffffff !important; padding:16px 20px 10px 20px; overflow-y:auto; max-height:calc(78vh - 130px); scrollbar-width:none; -ms-overflow-style:none; flex:1;">
                
                <!-- Rewards Incentive Callout Banner -->
                <div id="testimony-reward-banner" style="background:#eff6ff !important; border:1px solid #bfdbfe !important; outline:none !important; border-radius:12px; padding:10px 12px; margin-bottom:12px; display:flex; align-items:center; gap:10px; transition:all 0.3s ease;">
                    <div id="testimony-reward-icon" style="width:32px; height:32px; border-radius:8px; background:#dbeafe; display:flex; align-items:center; justify-content:center; color:#2563eb; font-size:14px; flex-shrink:0;">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <span id="testimony-reward-title" style="display:block; font-size:12px; font-weight:800; color:#1e3a8a; line-height:1.25;">Review & Earn Rewards</span>
                        <span id="testimony-reward-desc" style="font-size:11px; color:#475569; line-height:1.35; display:block; margin-top:1px;">Submit review to claim rewards</span>
                    </div>
                    <div id="testimony-reward-badges" style="display:none !important;"></div>
                </div>

                <!-- Star Rating selection -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <label style="font-size:11px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.4px;">Your Rating (1 to 5 Stars):</label>
                    <div style="display:flex; gap:6px; font-size:20px; color:#f59e0b;">
                        <i class="fa-regular fa-star star-btn" data-star="1" style="cursor:pointer; opacity:0.35;" onclick="window.setStarRating(1)"></i>
                        <i class="fa-regular fa-star star-btn" data-star="2" style="cursor:pointer; opacity:0.35;" onclick="window.setStarRating(2)"></i>
                        <i class="fa-regular fa-star star-btn" data-star="3" style="cursor:pointer; opacity:0.35;" onclick="window.setStarRating(3)"></i>
                        <i class="fa-regular fa-star star-btn" data-star="4" style="cursor:pointer; opacity:0.35;" onclick="window.setStarRating(4)"></i>
                        <i class="fa-regular fa-star star-btn" data-star="5" style="cursor:pointer; opacity:0.35;" onclick="window.setStarRating(5)"></i>
                    </div>
                    <input type="hidden" id="testimony-rating" value="0">
                </div>

                <!-- Cleanliness, Safety parameters -->
                <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:10px;">
                    <!-- Cleanliness -->
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <label style="font-size:10.5px; font-weight:700; color:#475569; text-transform:uppercase;">Cleanliness:</label>
                            <span id="cleanliness-selected-label" style="font-size:10.5px; font-weight:800; color:#10b981;">Clean</span>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="option-pill clean-pill active" data-val="clean" onclick="window.selectCleanliness('clean')" style="flex:1; padding:7px 4px; border-radius:8px; border:none !important; outline:none !important; background:#10b981; color:#ffffff; font-size:11px; font-weight:800; cursor:pointer; transition:all 0.2s ease;">
                                Clean
                            </button>
                            <button type="button" class="option-pill clean-pill" data-val="moderate" onclick="window.selectCleanliness('moderate')" style="flex:1; padding:7px 4px; border-radius:8px; border:1px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#475569; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                                Moderate
                            </button>
                            <button type="button" class="option-pill clean-pill" data-val="dirty" onclick="window.selectCleanliness('dirty')" style="flex:1; padding:7px 4px; border-radius:8px; border:1px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#475569; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                                Dirty
                            </button>
                        </div>
                        <input type="hidden" id="testimony-cleanliness" value="clean">
                    </div>

                    <!-- Safety -->
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                            <label style="font-size:10.5px; font-weight:700; color:#475569; text-transform:uppercase;">Safety Level:</label>
                            <span id="safety-selected-label" style="font-size:10.5px; font-weight:800; color:#10b981;">Safe</span>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="option-pill safety-pill active" data-val="safe" onclick="window.selectSafety('safe')" style="flex:1; padding:7px 4px; border-radius:8px; border:none !important; outline:none !important; background:#10b981; color:#ffffff; font-size:11px; font-weight:800; cursor:pointer; transition:all 0.2s ease;">
                                Safe
                            </button>
                            <button type="button" class="option-pill safety-pill" data-val="moderate" onclick="window.selectSafety('moderate')" style="flex:1; padding:7px 4px; border-radius:8px; border:1px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#475569; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                                Moderate
                            </button>
                            <button type="button" class="option-pill safety-pill" data-val="unsafe" onclick="window.selectSafety('unsafe')" style="flex:1; padding:7px 4px; border-radius:8px; border:1px solid #e2e8f0 !important; outline:none !important; background:#f8fafc; color:#475569; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                                Unsafe
                            </button>
                        </div>
                        <input type="hidden" id="testimony-safety" value="safe">
                    </div>
                </div>

                <!-- Feedback description -->
                <div style="margin-bottom:8px;">
                    <label style="font-size:10.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.4px; display:block; margin-bottom:4px;">Your Feedback:</label>
                    <textarea id="testimony-comment" placeholder="Describe your experience during this site visit..." style="width:100%; height:52px; background:#f8fafc; border:1px solid #cbd5e1 !important; outline:none !important; border-radius:10px; padding:8px 10px; color:#0f172a !important; -webkit-text-fill-color:#0f172a !important; font-size:12px; font-weight:500; font-family:inherit; resize:none; box-sizing:border-box; line-height:1.4; overflow:hidden !important; scrollbar-width:none; -ms-overflow-style:none;" required></textarea>
                </div>

                <!-- Policy Recommendation -->
                <div style="margin-bottom:6px;">
                    <label style="font-size:10.5px; font-weight:800; color:#334155; text-transform:uppercase; letter-spacing:0.4px; display:block; margin-bottom:4px;">Policy Recommendations (Optional):</label>
                    <textarea id="testimony-policy" placeholder="Suggestions for safety, cleanliness, or crowd control..." style="width:100%; height:52px; background:#f8fafc; border:1px solid #cbd5e1 !important; outline:none !important; border-radius:10px; padding:8px 10px; color:#0f172a !important; -webkit-text-fill-color:#0f172a !important; font-size:12px; font-weight:500; font-family:inherit; resize:none; box-sizing:border-box; line-height:1.4; overflow:hidden !important; scrollbar-width:none; -ms-overflow-style:none;"></textarea>
                </div>
            </div>

            <!-- Footer (The Buttons) -->
            <div style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:14px 20px 16px 20px; display:flex; gap:10px; flex-shrink:0;">
                <button type="submit" id="testimony-submit-btn" style="flex:1; padding:12px 14px; font-size:13.5px; background:linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); border:none !important; outline:none !important; color:#fff; border-radius:12px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(37,99,235,0.25); display:flex; align-items:center; justify-content:center; gap:6px;">
                    <i class="fa-solid fa-paper-plane" style="font-size:11px;"></i> <span id="testimony-submit-text">Submit Review</span>
                </button>
                <button type="button" style="padding:12px 18px; border-radius:12px; border:1px solid #e2e8f0 !important; outline:none !important; background:#ffffff; color:#475569; font-size:13px; font-weight:700; cursor:pointer; touch-action:manipulation; pointer-events:auto;" onclick="window.closeWriteTestimonyModal(false, this)">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
// Global Set of Spot IDs already reviewed by the current user
window.userReviewedSpotIds = window.userReviewedSpotIds || new Set();
window.userReviewedItemIds = window.userReviewedItemIds || new Set();
window.userReviewedSpotData = window.userReviewedSpotData || {};

// Classification to Reward Points & Theme configuration
window.getRewardPointsForClassification = function(status) {
    const c = String(status || '').toUpperCase().trim();
    if (c === 'EMERGE' || c === 'EMERGING') {
        return { label: 'Emerging', points: 45, color: '#ef4444', bg: 'rgba(239, 68, 68, 0.22)', border: 'rgba(239, 68, 68, 0.35)', icon: 'fa-sparkles' };
    }
    if (c === 'POTENTIAL') {
        return { label: 'Potential', points: 35, color: '#10b981', bg: 'rgba(16, 185, 129, 0.22)', border: 'rgba(16, 185, 129, 0.35)', icon: 'fa-compass' };
    }
    // Default: EXISTING (Blue)
    return { label: 'Existing', points: 25, color: '#0284c7', bg: 'rgba(2, 132, 199, 0.22)', border: 'rgba(2, 132, 199, 0.35)', icon: 'fa-circle-check' };
};

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
            if (json && Array.isArray(json.reviewed_item_ids)) {
                json.reviewed_item_ids.forEach(id => window.userReviewedItemIds.add(Number(id)));
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
    document.querySelectorAll('[data-spot-id]:not(.saved-trip-checkin-btn):not(.trip-map-checkin-btn)').forEach(btn => {
        if (btn.classList.contains('saved-trip-checkin-btn') || btn.classList.contains('trip-map-checkin-btn') || btn.classList.contains('saved-trip-proof-btn')) return;
        const spotId = Number(btn.getAttribute('data-spot-id'));
        const itemId = Number(btn.getAttribute('data-itinerary-item-id'));
        const isReviewed = itemId
            ? window.userReviewedItemIds.has(itemId)
            : (spotId && window.userReviewedSpotIds.has(spotId));
        if (isReviewed) {
            btn.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px; margin-right:4px;"></i> Reviewed';
            btn.style.background = '#e2e8f0';
            btn.style.color = '#64748b';
            btn.style.boxShadow = 'none';
        } else if (btn.getAttribute('data-spot-classification') && !btn.innerHTML.includes('fa-check')) {
            btn.innerHTML = '<i class="fa-solid fa-pen" style="font-size:10px; margin-right:4px;"></i> Review';
        }
    });
};

window.openWriteTestimonyModal = function(spotId, btnEl, itineraryItemId = null) {
    if (btnEl) {
        window._lastReviewedBtn = btnEl;
    }
    const targetSpotId = spotId || window.currentSelectedSpotId;
    window.currentReviewItineraryItemId = itineraryItemId ? Number(itineraryItemId) : null;
    if (targetSpotId) {
        window.currentSelectedSpotId = targetSpotId;
        const spotInput = document.getElementById('testimony-spot-id');
        if (spotInput) spotInput.value = targetSpotId;
    }

    const isAlreadyReviewed = window.currentReviewItineraryItemId
        ? window.userReviewedItemIds.has(window.currentReviewItineraryItemId)
        : (targetSpotId && window.userReviewedSpotIds && window.userReviewedSpotIds.has(Number(targetSpotId)));
    const isCompletionReview = window._isCompletionReviewFlow === true;
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

    if (typeof window.setStarRating === 'function') window.setStarRating(0);

    // Detect classification status
    let spotClassification = '';
    if (btnEl && btnEl.getAttribute('data-spot-classification')) {
        spotClassification = btnEl.getAttribute('data-spot-classification');
    } else if (window.currentSelectedLocationData && window.currentSelectedLocationData.classification_status) {
        spotClassification = window.currentSelectedLocationData.classification_status;
    } else if (window.allDestinations && Array.isArray(window.allDestinations)) {
        const found = window.allDestinations.find(s => s.id == targetSpotId);
        if (found && found.classification_status) spotClassification = found.classification_status;
    } else if (window.allMapLocations && Array.isArray(window.allMapLocations)) {
        const found = window.allMapLocations.find(s => s.id == targetSpotId);
        if (found && found.classification_status) spotClassification = found.classification_status;
    }

    const classMeta = (window.getRewardPointsForClassification)
        ? window.getRewardPointsForClassification(spotClassification)
        : { label: 'Existing', points: 25, color: '#10b981', bg: 'rgba(16, 185, 129, 0.22)', border: 'rgba(16, 185, 129, 0.35)', icon: 'fa-circle-check' };

    if (isAlreadyReviewed) {
        // Repeated Review state: NO ADDITIONAL REWARDS
        if (titleEl) titleEl.textContent = 'Update Destination Review';
        if (subEl) subEl.textContent = 'Modify your site feedback and policy recommendations for this destination.';
        if (bannerEl) {
            bannerEl.style.background = '#f8fafc';
            bannerEl.style.border = '1px solid #e2e8f0';
            bannerEl.style.outline = 'none';
        }
        if (iconEl) {
            iconEl.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#10b981;"></i>';
        }
        if (bannerTitleEl) bannerTitleEl.innerHTML = '<span style="color:#10b981; font-weight:800;">Review Already Claimed</span>';
        if (bannerDescEl) bannerDescEl.textContent = 'Rewards are one-time per spot. Updating will not grant additional Points.';
        if (bannerBadgesEl) {
            bannerBadgesEl.innerHTML = '';
            bannerBadgesEl.style.display = 'none';
        }
        if (submitTextEl) submitTextEl.textContent = 'Update Review';

        // Pre-fill existing data if available
        const prevData = window.userReviewedSpotData && window.userReviewedSpotData[targetSpotId];
        if (prevData) {
            if (typeof window.setStarRating === 'function') window.setStarRating(prevData.rating || 0);
            if (typeof window.selectCleanliness === 'function') window.selectCleanliness(prevData.cleanliness_level || 'clean');
            if (typeof window.selectSafety === 'function') window.selectSafety(prevData.safety_level || 'safe');
            if (commentInput) commentInput.value = prevData.testimony || '';
            if (policyInput) policyInput.value = prevData.policy_recommendation || '';
        }
    } else {
        // First Review state: EARN REWARDS based on classification (Existing 25, Emerging 45, Potential 35)
        if (titleEl) titleEl.textContent = 'Review Destination';
        if (subEl) subEl.textContent = `Share your site feedback for this ${classMeta.label.toLowerCase()} destination to help local tourism.`;
        if (bannerEl) {
            bannerEl.style.background = '#eff6ff';
            bannerEl.style.border = '1px solid #bfdbfe';
            bannerEl.style.outline = 'none';
        }
        if (iconEl) {
            iconEl.innerHTML = `<i class="fa-solid ${classMeta.icon}" style="color:${classMeta.color};"></i>`;
        }
        if (bannerTitleEl) bannerTitleEl.innerHTML = `<span style="color:${classMeta.color}; font-weight:800;">${classMeta.label} Site</span> • Earn Rewards`;
        if (bannerDescEl) bannerDescEl.textContent = `Submit review to claim +${classMeta.points} Points`;
        if (bannerBadgesEl) {
            bannerBadgesEl.innerHTML = '';
            bannerBadgesEl.style.display = 'none';
        }
        if (submitTextEl) submitTextEl.textContent = 'Submit Review';

        if (typeof window.setStarRating === 'function') window.setStarRating(0);
        if (typeof window.selectCleanliness === 'function') window.selectCleanliness('clean');
        if (typeof window.selectSafety === 'function') window.selectSafety('safe');
        if (commentInput) commentInput.value = '';
        if (policyInput) policyInput.value = '';
    }

    if (isCompletionReview) {
        if (titleEl) titleEl.textContent = 'Review Destination';
        if (subEl) subEl.textContent = 'Share your experience before completing this trip.';
        if (submitTextEl) submitTextEl.textContent = 'Save Review';
    }

    const modal = document.getElementById('testimony-modal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.zIndex = '2147483647';
        modal.style.display = 'flex';
        void modal.offsetHeight;
        modal.classList.add('active');
        const card = modal.querySelector('.testimony-card-anim');
        if (card) {
            card.style.transform = 'scale(1) translateY(0)';
            card.style.opacity = '1';
        }
    }
};

window.closeWriteTestimonyModal = function(preserveCompletion = false, trigger = null) {
    const wasCompletionReview = window._isCompletionReviewFlow === true;
    const modals = Array.from(document.querySelectorAll('#testimony-modal'));
    const modal = (trigger && trigger.closest ? trigger.closest('#testimony-modal') : null) ||
        modals.find(candidate => candidate.classList.contains('active')) ||
        modals.find(candidate => candidate.style.display !== 'none') ||
        modals[0];
    if (modal) {
        modal.classList.remove('active');
        const card = modal.querySelector('.testimony-card-anim');
        if (card) {
            card.style.transform = 'scale(0.88) translateY(16px)';
            card.style.opacity = '0';
        }
        setTimeout(() => {
            if (!modal.classList.contains('active')) {
                modal.style.display = 'none';
            }
        }, 320);
    }
    const compModal = document.getElementById('trip-completion-review-modal');
    if (compModal) {
        compModal.style.zIndex = '99998';
        compModal.style.opacity = '1';
    }
    if (wasCompletionReview && !preserveCompletion && !compModal) {
        window._completionReviewRequired = false;
        window._completionReviewRequiredIds = [];
        window._pendingCompleteTripId = null;
    }
    window._isCompletionReviewFlow = false;
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
    const colorMap = { clean: '#10b981', moderate: '#f59e0b', dirty: '#ef4444' };
    const label = document.getElementById('cleanliness-selected-label');
    if (label) {
        label.textContent = labelMap[val] || val;
        label.style.color = colorMap[val] || '#fff';
    }

    document.querySelectorAll('#testimony-modal .clean-pill').forEach(btn => {
        if (btn.dataset.val === val) {
            btn.classList.add('active');
            btn.style.border = 'none !important';
            btn.style.outline = 'none !important';
            btn.style.background = colorMap[val] || '#10b981';
            btn.style.color = '#ffffff';
            btn.style.fontWeight = '800';
            btn.style.boxShadow = 'none';
        } else {
            btn.classList.remove('active');
            btn.style.border = '1px solid #e2e8f0 !important';
            btn.style.outline = 'none !important';
            btn.style.background = '#f8fafc';
            btn.style.color = '#475569';
            btn.style.fontWeight = '700';
            btn.style.boxShadow = 'none';
        }
    });
};

window.selectSafety = function(val) {
    const input = document.getElementById('testimony-safety');
    if (input) input.value = val;

    const labelMap = { safe: 'Safe', moderate: 'Moderate', unsafe: 'Unsafe' };
    const colorMap = { safe: '#10b981', moderate: '#f59e0b', unsafe: '#ef4444' };
    const label = document.getElementById('safety-selected-label');
    if (label) {
        label.textContent = labelMap[val] || val;
        label.style.color = colorMap[val] || '#fff';
    }

    document.querySelectorAll('#testimony-modal .safety-pill').forEach(btn => {
        if (btn.dataset.val === val) {
            btn.classList.add('active');
            btn.style.border = 'none !important';
            btn.style.outline = 'none !important';
            btn.style.background = colorMap[val] || '#10b981';
            btn.style.color = '#ffffff';
            btn.style.fontWeight = '800';
            btn.style.boxShadow = 'none';
        } else {
            btn.classList.remove('active');
            btn.style.border = '1px solid #e2e8f0 !important';
            btn.style.outline = 'none !important';
            btn.style.background = '#f8fafc';
            btn.style.color = '#475569';
            btn.style.fontWeight = '700';
            btn.style.boxShadow = 'none';
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
    const rating = Number(document.getElementById('testimony-rating').value || 0);
    const testimony = document.getElementById('testimony-comment').value;
    const policy = document.getElementById('testimony-policy').value;
    const cleanliness = document.getElementById('testimony-cleanliness').value;
    const safety = document.getElementById('testimony-safety').value;

    if (!Number.isInteger(rating) || rating < 1 || rating > 5) {
        if (typeof showToast === 'function') showToast('Please choose a rating from 1 to 5 stars.');
        const starRow = document.querySelector('#testimony-modal .star-btn')?.closest('div');
        if (starRow && typeof starRow.animate === 'function') {
            starRow.animate(
                [{ transform: 'translateX(0)' }, { transform: 'translateX(-6px)' }, { transform: 'translateX(6px)' }, { transform: 'translateX(0)' }],
                { duration: 260 }
            );
        }
        return;
    }

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
                itinerary_item_id: window.currentReviewItineraryItemId,
                rating: rating,
                testimony: testimony,
                policy_recommendation: policy,
                cleanliness_level: cleanliness,
                safety_level: safety
            })
        });

        const data = await response.json().catch(() => ({}));
        if (response.ok) {
            window.closeWriteTestimonyModal(true);

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
            if (window.currentReviewItineraryItemId) {
                window.userReviewedItemIds.add(Number(window.currentReviewItineraryItemId));
            }

            if (isRewardAwarded) {
                const earnedPts = (data && data.earned_points !== undefined) ? parseInt(data.earned_points, 10) : 50;
                let newTotal = null;
                try {
                    const rawUser = localStorage.getItem('auth_user');
                    if (rawUser) {
                        const parsedUser = JSON.parse(rawUser);
                        if (data && data.total_points !== undefined && data.total_points !== null) {
                            parsedUser.points = parseInt(data.total_points, 10);
                        } else {
                            parsedUser.points = (parseInt(parsedUser.points, 10) || 0) + earnedPts;
                        }
                        if (data && data.xp !== undefined && data.xp !== null) {
                            parsedUser.xp = parseInt(data.xp, 10);
                        } else if (data && data.earned_xp) {
                            parsedUser.xp = (parseInt(parsedUser.xp, 10) || 0) + parseInt(data.earned_xp, 10);
                        }
                        if (data && data.completed_activities !== undefined) {
                            parsedUser.completed_activities = parseInt(data.completed_activities, 10);
                        } else {
                            parsedUser.completed_activities = (parseInt(parsedUser.completed_activities, 10) || 0) + 1;
                        }
                        newTotal = parsedUser.points;
                        localStorage.setItem('auth_user', JSON.stringify(parsedUser));
                        localStorage.setItem('auth_user_backup', JSON.stringify(parsedUser));
                    }
                } catch (e) {
                    console.warn('Failed to update local auth_user points:', e);
                }

                // Update all user points displays and badges in DOM immediately
                document.querySelectorAll('#user-points-display, .header-points-badge, .user-points-badge, .points-count, [data-user-points], #user-points-val').forEach(el => {
                    if (newTotal !== null) {
                        el.textContent = Number(newTotal).toLocaleString();
                    }
                });

                if (window.confetti) {
                    window.confetti({ particleCount: 85, spread: 70, origin: { y: 0.6 } });
                }
                if (typeof showToast === 'function') {
                    showToast(data.message || `Review submitted! You earned +${earnedPts} Points!`);
                }
                // Invalidate cached user profile & dashboard so rewards counters immediately update
                const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
                if (token) {
                    localStorage.removeItem('user_profile_' + token.substring(0, 10));
                    localStorage.removeItem('dashboard_data_' + token.substring(0, 10));
                }
                window.dispatchEvent(new CustomEvent('user-points-updated', { detail: { points: earnedPts, total_points: newTotal } }));
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
                window._lastReviewedBtn.style.background = '#e2e8f0';
                window._lastReviewedBtn.style.border = 'none !important';
                window._lastReviewedBtn.style.color = '#64748b';
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
                detail: { spotId: spotId, itineraryItemId: window.currentReviewItineraryItemId, rewardAwarded: isRewardAwarded }
            }));

            if (typeof fetchTestimonies === 'function') fetchTestimonies(spotId);
        } else {
            if (typeof showToast === 'function') showToast(data.message || `Failed to submit review (${response.status}).`);
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
