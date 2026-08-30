<?php
$pageTitle = 'Help & Support';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<script>
(function() {
    var backBtn = document.querySelector('.header-icon .fa-arrow-left');
    if (backBtn) {
        backBtn.closest('.header-icon').onclick = function() { history.back(); };
    }
})();
</script>

<div class="help-container has-header animate-slide-up" style="margin-top: 20px;">

    <div class="help-section">
        <h2 style="font-size: 22px; margin-bottom: 8px;">Frequently Asked Questions</h2>
        <p style="color: rgba(148,163,184,0.8); font-size: 14px; margin-bottom: 20px;">Find quick answers to common questions.</p>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            <i class="fa-solid fa-circle-question"></i>
            <span>How do I plan a trip?</span>
            <i class="fa-solid fa-chevron-down" style="margin-left: auto;"></i>
        </div>
        <div class="faq-answer" style="display:none;">
            Browse tourist spots on the map, tap a spot, then tap "Add to Itinerary." Go to the Itinerary tab to review and save your trip.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            <i class="fa-solid fa-circle-question"></i>
            <span>How is XP calculated?</span>
            <i class="fa-solid fa-chevron-down" style="margin-left: auto;"></i>
        </div>
        <div class="faq-answer" style="display:none;">
            Earn XP by visiting places, completing trips, and engaging with the community. The more you explore, the higher your rank!
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            <i class="fa-solid fa-circle-question"></i>
            <span>Can I remove a saved trip?</span>
            <i class="fa-solid fa-chevron-down" style="margin-left: auto;"></i>
        </div>
        <div class="faq-answer" style="display:none;">
            Yes — go to Saved Trips, swipe left on a trip, and tap Delete to remove it from your list.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            <i class="fa-solid fa-circle-question"></i>
            <span>The map isn't loading — what should I do?</span>
            <i class="fa-solid fa-chevron-down" style="margin-left: auto;"></i>
        </div>
        <div class="faq-answer" style="display:none;">
            Make sure you have an active internet connection. Try refreshing the page or re-opening the app. If it persists, reach out to customer service.
        </div>
    </div>

    <!-- Customer Service Card -->
    <div style="margin-top: 32px; padding: 22px; background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%); border: none !important; outline: none !important; border-radius: 20px; box-shadow: 0 8px 24px rgba(10,25,60,0.25);">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
            <div style="width: 46px; height: 46px; border-radius: 14px; background: rgba(56,189,248,0.2); border: none; display: flex; align-items: center; justify-content: center; color: #00f2fe; font-size: 20px;">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff;">Customer Service</h3>
                <span style="font-size: 12px; color: #34c759; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 4px;"></i>Available 24/7 • Fast Response</span>
            </div>
        </div>

        <p style="margin: 0 0 16px 0; color: rgba(255,255,255,0.9); font-size: 13px; line-height: 1.5;">Have questions, account inquiries, or need trip assistance? Reach out to our dedicated support team.</p>

        <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: rgba(0,0,0,0.25); border: none; border-radius: 12px; margin-bottom: 16px;">
            <i class="fa-solid fa-envelope" style="color: #00f2fe; font-size: 16px;"></i>
            <span style="font-size: 14px; font-weight: 600; color: #ffffff; font-family: monospace;">support@intan-elyu.online</span>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="mailto:acekillersmile@gmail.com?subject=Customer%20Support%20Inquiry%20-%20Intan%20Elyu" style="flex: 1; text-align: center; background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%); color: white; border: none; outline: none; padding: 12px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 14px rgba(0,242,254,0.3);">
                <i class="fa-solid fa-paper-plane"></i> Email Support
            </a>
            <button onclick="copySupportEmail()" style="background: rgba(255,255,255,0.15); border: none; outline: none; color: #ffffff; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i class="fa-regular fa-copy"></i> Copy
            </button>
        </div>
    </div>

</div>


<script>
window.toggleFaq = function(el) {
    const answer = el.nextElementSibling;
    const icon = el.querySelector('.fa-chevron-down');
    if (answer.style.display === 'none' || !answer.style.display) {
        answer.style.display = 'block';
        if (icon) icon.style.transform = 'rotate(180deg)';
    } else {
        answer.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
};

window.copySupportEmail = function() {
    navigator.clipboard.writeText('support@intan-elyu.online').then(() => {
        if (typeof showToast === 'function') showToast('Copied support@intan-elyu.online to clipboard! 📋');
    });
};

window.contactSupport = function(e) {
    if (e) e.preventDefault();
    window.location.href = 'mailto:acekillersmile@gmail.com?subject=Customer%20Support%20Inquiry%20-%20Intan%20Elyu';
};
</script>
