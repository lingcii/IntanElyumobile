<?php
$pageTitle = 'Help & Support';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="help-container has-header animate-slide-up">

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
                Browse tourist spots on the interactive map, tap any attraction to view details, then tap "Add to
                Itinerary." Head over to the Itinerary tab to adjust your schedule and finalize your personalized travel
                route.
            </div>
        </div>
    </div>

    <!-- FAQ Item 2 -->
    <div class="faq-item" onclick="toggleFaq(this)">
        <div class="faq-question">
            <i class="fa-solid fa-circle-question"></i>
            <span>How are Points earned?</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="faq-answer-wrapper">
            <div class="faq-answer">
                Earn Points by checking in at destinations and solving Game Zone puzzles.
                Accumulating Points boosts your position on the Leaderboard and allows you to claim rewards.
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
                Yes — navigate to Saved Trips from your profile or side drawer, locate the trip card, and tap the delete
                trash button to remove it anytime.
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
                Ensure you have GPS / location services enabled and an active internet connection. Try refreshing the
                view or re-opening the app. If the issue persists, reach out directly to customer service below.
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
                    <span class="dot"
                        style="box-shadow: none !important; animation: none !important; transform: none !important;"></span>
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
            <button type="button" id="btn-email-support" onclick="handleEmailSupport(event)" class="btn-email-support"
                title="Send email to support@intan-elyu.online">
                <i class="fa-solid fa-paper-plane"></i> Email Support
            </button>
            <button id="btn-copy-support" onclick="copySupportEmail(this)" class="btn-copy-support"
                title="Copy email address">
                <i class="fa-regular fa-copy"></i> <span>Copy</span>
            </button>
        </div>
    </div>

    <!-- In-App Support & Bug Report Form -->
    <div class="support-card" style="margin-top: 16px;">
        <div class="support-header">
            <div class="support-icon-wrap" style="background: rgba(56, 189, 248, 0.18);">
                <i class="fa-solid fa-bug" style="color: #38bdf8;"></i>
            </div>
            <div>
                <h3 class="support-title">Submit a Support Ticket</h3>
                <span class="support-status" style="color: rgba(255,255,255,0.8);">Report a bug or send feedback directly in-app</span>
            </div>
        </div>

        <form id="support-ticket-form" onsubmit="window.submitSupportTicketForm(event)" style="display: flex; flex-direction: column; gap: 10px; margin-top: 12px;">
            <div>
                <label for="support-category" style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:4px;">Category</label>
                <select id="support-category" style="width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.96); color:#0f172a; font-size:13px; font-weight:600;">
                    <option value="bug">Bug / Technical Issue</option>
                    <option value="account">Account &amp; Login</option>
                    <option value="trip">Map &amp; Itinerary</option>
                    <option value="voucher">Vouchers &amp; Points</option>
                    <option value="feedback">Suggestion / Feedback</option>
                    <option value="other">Other Inquiry</option>
                </select>
            </div>
            <div>
                <label for="support-subject" style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:4px;">Subject</label>
                <input id="support-subject" type="text" maxlength="120" required placeholder="Brief summary of your issue"
                    style="width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.96); color:#0f172a; font-size:13px; box-sizing:border-box;">
            </div>
            <div>
                <label for="support-email" style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:4px;">Reply Email (optional)</label>
                <input id="support-email" type="email" maxlength="150" placeholder="you@example.com"
                    style="width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.96); color:#0f172a; font-size:13px; box-sizing:border-box;">
            </div>
            <div>
                <label for="support-message" style="display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,0.85); margin-bottom:4px;">Details</label>
                <textarea id="support-message" rows="4" minlength="10" maxlength="2000" required placeholder="Describe what happened or how we can help..."
                    style="width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,0.22); background:rgba(255,255,255,0.96); color:#0f172a; font-size:13px; resize:vertical; box-sizing:border-box;"></textarea>
            </div>
            <button type="submit" id="btn-submit-support-ticket" class="btn-email-support" style="width:100%; margin-top:2px;">
                <i class="fa-solid fa-paper-plane"></i> Submit Ticket
            </button>
        </form>
    </div>

</div>

<script>
    // Smooth FAQ Accordion Toggle with Elastic Click Feedback
    window.toggleFaq = function (el) {
        if (!el) return;
        el.classList.toggle('active');
    };

    // Pre-fill support email from logged-in tourist profile
    try {
        const u = JSON.parse(localStorage.getItem('auth_user') || '{}');
        const emailInput = document.getElementById('support-email');
        if (emailInput && u && u.email && !emailInput.value) {
            emailInput.value = u.email;
        }
    } catch (e) {}

    window.submitSupportTicketForm = async function (e) {
        if (e) e.preventDefault();
        const btn = document.getElementById('btn-submit-support-ticket');
        const category = document.getElementById('support-category')?.value || 'bug';
        const subject = (document.getElementById('support-subject')?.value || '').trim();
        const email = (document.getElementById('support-email')?.value || '').trim();
        const message = (document.getElementById('support-message')?.value || '').trim();

        if (subject.length < 3 || message.length < 10) {
            if (typeof showToast === 'function') showToast('Please enter a subject and at least 10 characters of details.', 'warning');
            return;
        }
        if (!navigator.onLine || window.isAppOnline === false) {
            if (typeof showToast === 'function') showToast('Internet connection required to submit a support ticket.', 'warning');
            return;
        }

        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting...';
        }

        try {
            const backendUrl = (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
            const token = localStorage.getItem('intan_elyu_token') || '';
            const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
            if (token && !token.startsWith('guest_') && !token.startsWith('offline_')) {
                headers['Authorization'] = 'Bearer ' + token;
            }
            const res = await fetch(backendUrl + '/api/support/ticket', {
                method: 'POST',
                headers,
                body: JSON.stringify({ category, subject, message, email: email || null })
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                if (typeof showToast === 'function') showToast(data.message || 'Support ticket submitted!', 'success');
                const form = document.getElementById('support-ticket-form');
                if (form) form.reset();
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'Could not submit ticket. Please try again.', 'error');
            }
        } catch (err) {
            if (typeof showToast === 'function') showToast('Network error submitting ticket. Try emailing support instead.', 'error');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
        }
    };

    // Safe email client dispatch for Android WebView & browsers (prevents net::ERR_UNKNOWN_URL_SCHEME)
    window.handleEmailSupport = function (e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }

        const email = 'support@intan-elyu.online';
        const mailtoUrl = 'mailto:' + email + '?subject=' + encodeURIComponent('Customer Support Inquiry - Intan Elyu');

        // 1. Always copy the email address to clipboard as a reliable guarantee
        copyTextToClipboard(email, function () {
            if (typeof showToast === 'function') {
                showToast('Opening mail client... (support@intan-elyu.online copied!) 📋');
            }
        });

        // Animate the Email Support button briefly (guard against double-tap overwriting origHTML)
        const emailBtn = document.getElementById('btn-email-support');
        if (emailBtn && !emailBtn.dataset.animating) {
            emailBtn.dataset.animating = '1';
            const origHTML = emailBtn.innerHTML;
            emailBtn.innerHTML = '<i class="fa-solid fa-check" style="color:#ffffff;"></i> Opening...';
            setTimeout(() => {
                emailBtn.innerHTML = origHTML;
                delete emailBtn.dataset.animating;
            }, 2200);
        }

        // 2. If running inside native Capacitor wrapper, use App.openUrl for mailto: (Browser.open rejects mailto: schemes)
        if (window.Capacitor && window.Capacitor.Plugins) {
            if (window.Capacitor.Plugins.App && typeof window.Capacitor.Plugins.App.openUrl === 'function') {
                window.Capacitor.Plugins.App.openUrl({ url: mailtoUrl }).catch(function () {
                    try { window.location.href = mailtoUrl; } catch (e) {}
                });
                return;
            }
        }

        // 3. Trigger mailto navigation safely
        try {
            window.location.href = mailtoUrl;
        } catch (err) {
            console.warn('Could not launch mail client:', err);
        }
    };

    // Copy email address to clipboard with animated button feedback
    window.copySupportEmail = function (btn) {
        const email = 'support@intan-elyu.online';

        function showSuccessUI() {
            if (typeof showToast === 'function') {
                showToast('Copied support@intan-elyu.online to clipboard! 📋');
            }
            if (btn && !btn.dataset.animating) {
                btn.dataset.animating = '1';
                const originalHTML = btn.innerHTML;
                btn.classList.add('copied');
                btn.innerHTML = '<i class="fa-solid fa-check" style="color:#00f2fe;"></i> <span>Copied!</span>';
                setTimeout(() => {
                    btn.classList.remove('copied');
                    btn.innerHTML = originalHTML;
                    delete btn.dataset.animating;
                }, 2000);
            }
        }

        copyTextToClipboard(email, showSuccessUI);
    };

    function copyTextToClipboard(text, onSuccess) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(() => { if (typeof onSuccess === 'function') onSuccess(); })
                .catch(() => fallbackCopy(text, onSuccess));
        } else {
            fallbackCopy(text, onSuccess);
        }
    }

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