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
        <h2 class="help-main-title">Frequently Asked Questions</h2>
        <p class="help-main-subtitle">Find quick answers to common questions.</p>
    </div>

    <!-- FAQ Item 1 -->
    <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-question">
            <i class="fa-solid fa-circle-question"></i>
            <span>How do I plan a trip?</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-answer-wrapper">
            <div class="faq-answer">
                Browse tourist spots on the interactive map, tap any attraction to view details, then tap "Add to Itinerary." Head over to the Itinerary tab to adjust your schedule and finalize your personalized travel route.
            </div>
        </div>
    </div>

    <!-- FAQ Item 2 -->
    <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-question">
            <i class="fa-solid fa-circle-question"></i>
            <span>How is XP calculated?</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-answer-wrapper">
            <div class="faq-answer">
                Earn XP by checking in at destinations, completing itinerary trips, and solving daily challenges. Collecting XP increases your rank level and unlocks exclusive badges and discounts.
            </div>
        </div>
    </div>

    <!-- FAQ Item 3 -->
    <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-question">
            <i class="fa-solid fa-circle-question"></i>
            <span>Can I remove a saved trip?</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-answer-wrapper">
            <div class="faq-answer">
                Yes — navigate to Saved Trips from your profile or side drawer, locate the trip card, and tap the delete trash button to remove it anytime.
            </div>
        </div>
    </div>

    <!-- FAQ Item 4 -->
    <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-question">
            <i class="fa-solid fa-circle-question"></i>
            <span>The map isn't loading — what should I do?</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-answer-wrapper">
            <div class="faq-answer">
                Ensure you have GPS / location services enabled and an active internet connection. Try refreshing the view or re-opening the app. If the issue persists, reach out directly to customer service below.
            </div>
        </div>
    </div>

    <!-- Customer Service Card -->
    <div class="support-card">
        <div class="support-header">
            <div class="support-icon-wrap">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div>
                <h3 class="support-title">Customer Service</h3>
                <span class="support-status">
                    <span class="dot"></span>
                    Available 24/7 • Fast Response
                </span>
            </div>
        </div>

        <p class="support-desc">
            Have questions, account inquiries, or need trip assistance? Reach out to our dedicated support team anytime.
        </p>

        <!-- Interactive Email Display Box -->
        <div class="support-email-box" onclick="handleEmailSupport(event)" title="Tap to compose an email">
            <div class="support-email-left">
                <i class="fa-solid fa-envelope" style="color: #00f2fe; font-size: 17px;"></i>
                <span class="support-email-text">support@intan-elyu.online</span>
            </div>
            <i class="fa-solid fa-arrow-up-right-from-square support-email-link-icon"></i>
        </div>

        <!-- Action Buttons -->
        <div class="support-actions">
            <a href="mailto:support@intan-elyu.online?subject=Customer%20Support%20Inquiry%20-%20Intan%20Elyu" 
               onclick="handleEmailSupport(event)" 
               class="btn-email-support" 
               title="Send email to support@intan-elyu.online">
                <i class="fa-solid fa-paper-plane"></i> Email Support
            </a>
            <button id="btn-copy-support" onclick="copySupportEmail(this)" class="btn-copy-support" title="Copy email address">
                <i class="fa-regular fa-copy"></i> <span>Copy</span>
            </button>
        </div>
    </div>

</div>

<script>
// Smooth FAQ Accordion Toggle with Elastic Click Feedback
window.toggleFaq = function(el) {
    if (!el) return;
    const isAlreadyActive = el.classList.contains('active');
    
    // Optional: close other accordion items smoothly if desired, or toggle current
    el.classList.toggle('active');
};

// Open default mail client for support@intan-elyu.online
window.handleEmailSupport = function(e) {
    if (e && typeof e.stopPropagation === 'function') e.stopPropagation();
    const mailtoUrl = 'mailto:support@intan-elyu.online?subject=Customer%20Support%20Inquiry%20-%20Intan%20Elyu';
    
    try {
        window.location.href = mailtoUrl;
    } catch(err) {
        window.open(mailtoUrl, '_system');
    }
};

// Copy email address to clipboard with animated button feedback
window.copySupportEmail = function(btn) {
    const email = 'support@intan-elyu.online';
    
    function showSuccessUI() {
        if (typeof showToast === 'function') {
            showToast('Copied support@intan-elyu.online to clipboard! 📋');
        }
        if (btn) {
            const originalHTML = btn.innerHTML;
            btn.classList.add('copied');
            btn.innerHTML = '<i class="fa-solid fa-check" style="color:#00f2fe;"></i> <span>Copied!</span>';
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = originalHTML;
            }, 2000);
        }
    }

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(email).then(showSuccessUI).catch(() => fallbackCopy(email, showSuccessUI));
    } else {
        fallbackCopy(email, showSuccessUI);
    }
};

function fallbackCopy(text, onSuccess) {
    try {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (typeof onSuccess === 'function') onSuccess();
    } catch (e) {
        if (typeof showToast === 'function') showToast(text);
    }
}
</script>
