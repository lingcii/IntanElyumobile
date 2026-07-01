<?php
$pageTitle = 'Help Center';
?>

<?php include __DIR__ . '/../components/header.php'; ?>



<div class="help-container has-header has-bottom-nav animate-slide-up">
    
    <div class="faq-item stagger-1">
        <div class="faq-question"><i class="fa-solid fa-circle-question"></i> How do I generate an itinerary?</div>
        <div class="faq-answer">Simply go to the Map tab, select the tourist spots you want to visit, and click "Generate Route". The app will automatically calculate the best path.</div>
    </div>
    
    <div class="faq-item stagger-2">
        <div class="faq-question"><i class="fa-solid fa-location-dot"></i> Why is my location inaccurate?</div>
        <div class="faq-answer">Ensure that you have granted Intan Elyu GPS permissions in your phone's settings and that you have a clear view of the sky.</div>
    </div>
    
    <div class="faq-item stagger-3">
        <div class="faq-question"><i class="fa-solid fa-trophy"></i> How do I rank up on the Leaderboard?</div>
        <div class="faq-answer">Visit more verified tourist spots in La Union! Each spot you check into awards you points that increase your rank.</div>
    </div>
    
    <div class="contact-support stagger-4" onclick="showToast('Opening Support Email...')">
        <i class="fa-solid fa-envelope" style="font-size: 24px; margin-bottom: 10px;"></i>
        <h3 style="margin: 0 0 5px 0;">Need more help?</h3>
        <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 14px;">Contact our Support Team</p>
    </div>
</div>
