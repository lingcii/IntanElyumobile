<!-- Global Review Destination Modal Component -->
<style>
#testimony-modal.active { opacity: 1 !important; }
#testimony-modal.active .testimony-card-anim { transform: scale(1) translateY(0) !important; opacity: 1 !important; }
</style>

<div id="testimony-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.78); z-index:2147483647; align-items:center; justify-content:center; padding:20px; backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); opacity:0; transition:opacity 0.3s ease;">
    <div class="testimony-card-anim" style="background:linear-gradient(135deg, #1e293b, #0f172a); border:1px solid rgba(255,255,255,0.12); border-radius:24px; padding:24px; width:100%; max-width:380px; max-height:85vh; overflow-y:auto; box-shadow:0 25px 50px rgba(0,0,0,0.4); text-align:left; box-sizing:border-box; transform:scale(0.88) translateY(16px); opacity:0; transition:transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;">
        <h3 style="margin:0 0 4px; color:#fff; font-size:18px; font-weight:800;">Review Destination</h3>
        <p style="font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:16px;">Help the tourism office and fellow travellers by sharing your verified site testimony and policy recommendations.</p>

        <form id="testimony-form" onsubmit="window.submitTestimony(event)">
            <input type="hidden" id="testimony-spot-id">

            <!-- Star Rating selection -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your Rating (1 to 5 Stars):</label>
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
                        <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Cleanliness:</label>
                        <span id="cleanliness-selected-label" style="font-size:11px; font-weight:700; color:#10b981;">Clean</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill clean-pill active" data-val="clean" onclick="window.selectCleanliness('clean')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid #10b981; background:rgba(16,185,129,0.18); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            Clean
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="moderate" onclick="window.selectCleanliness('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Moderate
                        </button>
                        <button type="button" class="option-pill clean-pill" data-val="dirty" onclick="window.selectCleanliness('dirty')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Dirty
                        </button>
                    </div>
                    <input type="hidden" id="testimony-cleanliness" value="clean">
                </div>

                <!-- Safety -->
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label style="font-size:10px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase;">Safety Level:</label>
                        <span id="safety-selected-label" style="font-size:11px; font-weight:700; color:#10b981;">Safe</span>
                    </div>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="option-pill safety-pill active" data-val="safe" onclick="window.selectSafety('safe')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid #10b981; background:rgba(16,185,129,0.18); color:#10b981; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 0 10px rgba(16,185,129,0.2);">
                            Safe
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="moderate" onclick="window.selectSafety('moderate')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Moderate
                        </button>
                        <button type="button" class="option-pill safety-pill" data-val="unsafe" onclick="window.selectSafety('unsafe')" style="flex:1; padding:8px 4px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04); color:rgba(255,255,255,0.7); font-size:11px; font-weight:700; cursor:pointer; transition:all 0.2s ease;">
                            Unsafe
                        </button>
                    </div>
                    <input type="hidden" id="testimony-safety" value="safe">
                </div>
            </div>

            <!-- Testimony description -->
            <div style="margin-bottom:14px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Your Testimony:</label>
                <textarea id="testimony-comment" placeholder="Describe your experience during this site visit..." style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;" required></textarea>
            </div>

            <!-- Policy Recommendation -->
            <div style="margin-bottom:20px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,0.7); text-transform:uppercase; display:block; margin-bottom:6px;">Policy Recommendations (Optional):</label>
                <textarea id="testimony-policy" placeholder="Any suggestions or recommendations for safety, cleanliness, or crowd control policies?..." style="width:100%; height:60px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:10px; color:#fff; font-size:12px; font-family:inherit; resize:none; box-sizing:border-box;"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:14px; font-size:14px; margin-bottom:10px; background:linear-gradient(135deg, #38bdf8, #2563eb); border:none; color:#fff; border-radius:12px; font-weight:800; cursor:pointer;">
                Submit Feedback
            </button>
        </form>
        <button style="width:100%; padding:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.1); background:transparent; color:rgba(255,255,255,0.5); font-size:13px; font-weight:600; cursor:pointer;" onclick="window.closeWriteTestimonyModal()">Cancel</button>
    </div>
</div>

<script>
window.openWriteTestimonyModal = function(spotId) {
    const targetSpotId = spotId || window.currentSelectedSpotId;
    if (targetSpotId) {
        window.currentSelectedSpotId = targetSpotId;
        const spotInput = document.getElementById('testimony-spot-id');
        if (spotInput) spotInput.value = targetSpotId;
    }
    
    if (typeof window.setStarRating === 'function') window.setStarRating(5);
    if (typeof window.selectCleanliness === 'function') window.selectCleanliness('clean');
    if (typeof window.selectSafety === 'function') window.selectSafety('safe');
    
    const commentInput = document.getElementById('testimony-comment');
    if (commentInput) commentInput.value = '';
    const policyInput = document.getElementById('testimony-policy');
    if (policyInput) policyInput.value = '';

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
    const _backendBase = window.backendUrl || 'https://api.intan-elyu.online';

    const submitBtn = document.querySelector('#write-testimony-modal form button[type="submit"]') || document.querySelector('#write-testimony-modal button.submit-btn');
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
            if (typeof showToast === 'function') showToast("Thank you for your feedback! 🗣️");
            window.closeWriteTestimonyModal();

            if (window._lastReviewedBtn) {
                window._lastReviewedBtn.disabled = true;
                window._lastReviewedBtn.style.background = 'rgba(16, 185, 129, 0.18)';
                window._lastReviewedBtn.style.border = '1px solid rgba(16, 185, 129, 0.4)';
                window._lastReviewedBtn.style.color = '#10b981';
                window._lastReviewedBtn.style.boxShadow = 'none';
                window._lastReviewedBtn.innerHTML = '<i class="fa-solid fa-check" style="font-size:10px;"></i> Reviewed';
                window._lastReviewedBtn = null;
            }

            const completionCloseBtn = document.getElementById('btn-trip-completion-close');
            if (completionCloseBtn) {
                completionCloseBtn.textContent = 'Go Back to Saved Trips';
                completionCloseBtn.style.background = 'linear-gradient(135deg, #10b981, #059669)';
                completionCloseBtn.style.color = '#ffffff';
                completionCloseBtn.style.border = '1px solid rgba(255,255,255,0.2)';
                completionCloseBtn.style.boxShadow = '0 4px 14px rgba(16,185,129,0.3)';
            }

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
</script>
