<?php
$pageTitle = 'Help & Support';
$backRoute = 'profile';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

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
            Make sure you have an active internet connection. Try refreshing the page or re-opening the app. If it persists, contact support.
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

window.contactSupport = function(e) {
    e.preventDefault();
    if (typeof showToast === 'function') {
        showToast('Contact support at support@intan-elyu.com');
    } else {
        alert('Contact support at support@intan-elyu.com');
    }
};
</script>
