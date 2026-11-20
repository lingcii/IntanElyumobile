<?php
$pageTitle = 'Notifications';
$backRoute = 'dashboard';
?>

<?php include __DIR__ . '/../components/header.php'; ?>

<div class="notifications-page settings-container has-header animate-slide-up">

    <!-- Header Summary & Bulk Actions -->
    <div style="width: 100%; box-sizing: border-box; background: linear-gradient(135deg, #1e3a8a 0%, #2b549c 100%); border-radius: 20px; padding: 14px 14px; margin-bottom: 12px; color: #ffffff; box-shadow: 0 6px 18px rgba(30, 58, 138, 0.25);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; min-width: 0;">
            <div style="min-width: 0; flex: 1;">
                <div style="font-size: 15.5px; font-weight: 800; letter-spacing: -0.2px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-bell" style="color: #00f2fe; flex-shrink: 0;"></i>
                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Notification Center</span>
                </div>
                <div id="notif-center-summary" style="font-size: 12px; color: rgba(255,255,255,0.85); margin-top: 2px;">Loading your notifications...</div>
            </div>
            <button type="button" onclick="window.loadNotificationsCenter(true)" title="Refresh"
                style="width: 34px; height: 34px; border-radius: 50%; border: none; background: rgba(255,255,255,0.18); color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fa-solid fa-rotate-right" style="font-size: 13px;"></i>
            </button>
        </div>

        <div style="display: flex; gap: 8px; width: 100%; box-sizing: border-box;">
            <button type="button" id="nc-mark-all-btn" onclick="window.notifCenterMarkAllRead()"
                style="flex: 1; min-width: 0; padding: 8px 10px; border-radius: 100px; border: none; background: #ffffff; color: #1e3a8a; font-size: 11.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                <i class="fa-solid fa-check-double"></i> Mark All Read
            </button>
            <button type="button" id="nc-clear-all-btn" onclick="window.notifCenterClearAll()"
                style="flex: 1; min-width: 0; padding: 8px 10px; border-radius: 100px; border: none; background: rgba(239, 68, 68, 0.92); color: #ffffff; font-size: 11.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                <i class="fa-solid fa-trash-can"></i> Clear All
            </button>
        </div>
    </div>

    <!-- Filter Chips (matching User Manual flat pills, no shadow) -->
    <div style="width: 100%; box-sizing: border-box; background: transparent; padding: 2px 0; margin-bottom: 12px;">
        <div class="hide-scrollbar nc-nav-chips" style="display: flex; align-items: center; gap: 8px; overflow-x: auto; width: 100%; box-sizing: border-box; -webkit-overflow-scrolling: touch;">
            <button type="button" class="nc-filter-chip active" data-filter="all" onclick="window.setNotifCenterFilter('all', this)">All</button>
            <button type="button" class="nc-filter-chip" data-filter="unread" onclick="window.setNotifCenterFilter('unread', this)">Unread</button>
            <button type="button" class="nc-filter-chip" data-filter="trip" onclick="window.setNotifCenterFilter('trip', this)">Trips</button>
            <button type="button" class="nc-filter-chip" data-filter="voucher" onclick="window.setNotifCenterFilter('voucher', this)">Vouchers &amp; Points</button>
            <button type="button" class="nc-filter-chip" data-filter="security" onclick="window.setNotifCenterFilter('security', this)">Security</button>
        </div>
    </div>

    <!-- Notifications List -->
    <div id="notif-center-list" style="display: flex; flex-direction: column; gap: 10px; width: 100%; box-sizing: border-box;"></div>

</div>

<style>
    .nc-nav-chips::-webkit-scrollbar { display: none; }
    .nc-filter-chip {
        flex-shrink: 0;
        padding: 7px 14px;
        border-radius: 999px !important;
        border: 1.5px solid #cbd5e1 !important;
        background: #f1f5f9 !important;
        color: #1e293b !important;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        box-shadow: none !important;
        outline: none !important;
    }
    .nc-filter-chip.active {
        background: #1e3a8a !important;
        border: 1.5px solid #1e3a8a !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    .nc-swipe-wrap {
        position: relative;
        width: 100%;
        border-radius: 14px;
        overflow: hidden;
        background: transparent;
        touch-action: pan-y;
    }
    .nc-swipe-bg {
        position: absolute;
        inset: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 20px;
        color: #ffffff;
        font-size: 12.5px;
        font-weight: 800;
        gap: 6px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.16s ease;
    }
    .nc-swipe-card {
        position: relative;
        z-index: 2;
        width: 100%;
        box-sizing: border-box;
        border-radius: 14px;
        padding: 13px 14px;
        display: flex;
        gap: 10px;
        align-items: flex-start;
        cursor: pointer;
        background: #ffffff !important;
        border: 1.5px solid #e2e8f0 !important;
        box-shadow: none !important;
        transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: transform;
        user-select: none;
    }
</style>

<script>
(function () {
    let allNotifs = [];
    let activeFilter = 'all';

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getAuthToken() {
        const t = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token') || localStorage.getItem('api_token') || '';
        if (!t || t === 'guest_token' || t === 'offline_guest_token' || t.startsWith('guest_') || t.startsWith('offline_')) return '';
        return t;
    }

    function getBaseUrl() {
        return (typeof window.getBackendUrl === 'function' ? window.getBackendUrl() : (window.backendUrl || 'https://api.intan-elyu.online')).replace(/\/+$/, '');
    }

    function formatRelative(n) {
        let secs = Number(n.elapsed_seconds);
        if (!Number.isFinite(secs) || secs <= 0) {
            if (n.created_at) {
                const parsed = new Date(n.created_at).getTime();
                if (Number.isFinite(parsed)) secs = Math.max(0, Math.floor((Date.now() - parsed) / 1000));
            }
        }
        secs = Math.max(0, Number.isFinite(secs) ? secs : 0);
        if (secs < 60) return 'Just now';
        if (secs < 3600) return Math.floor(secs / 60) + 'm ago';
        if (secs < 86400) return Math.floor(secs / 3600) + 'h ago';
        return Math.floor(secs / 86400) + 'd ago';
    }

    function matchesFilter(n, filter) {
        if (filter === 'all') return true;
        if (filter === 'unread') return !n.is_read;
        const hay = `${n.type || ''} ${n.title || ''} ${n.message || ''}`.toLowerCase();
        if (filter === 'trip') return /trip|itinerary|check-?in|route|stop/.test(hay);
        if (filter === 'voucher') return /voucher|point|reward|discount|redeem|puzzle|game/.test(hay);
        if (filter === 'security') return /security|password|2fa|login|account/.test(hay);
        return true;
    }

    function renderList() {
        const listEl = document.getElementById('notif-center-list');
        const summaryEl = document.getElementById('notif-center-summary');
        if (!listEl) return;

        const unreadCount = allNotifs.filter(n => !n.is_read).length;
        if (summaryEl) {
            summaryEl.textContent = allNotifs.length === 0
                ? 'You are all caught up!'
                : `${allNotifs.length} total • ${unreadCount} unread`;
        }
        if (typeof window.updateUnreadBadge === 'function') {
            window.updateUnreadBadge(unreadCount);
        }

        const filtered = allNotifs.filter(n => matchesFilter(n, activeFilter));
        if (filtered.length === 0) {
            const subText = activeFilter === 'all'
                ? "You're all caught up! Updates, trip reminders, and vouchers will appear here."
                : 'Nothing matches this filter right now.';
            listEl.innerHTML = `
                <div class="empty-state-card notif-empty-card" style="width: 100%; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 32px 18px; background: #1e3a8a !important; border: none !important; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25) !important; border-radius: 16px; margin: 4px 0;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #ffffff !important; border: none !important; outline: none !important; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);">
                        <i class="fa-solid fa-bell-slash" style="color: #1e3a8a !important; font-size: 22px;"></i>
                    </div>
                    <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 800; color: #ffffff; letter-spacing: -0.2px;">No Notifications Yet</h4>
                    <p style="margin: 0; font-size: 12px; color: rgba(255, 255, 255, 0.9); line-height: 1.45; max-width: 240px; font-weight: 500;">
                        ${subText}
                    </p>
                </div>
            `;
            return;
        }

        listEl.innerHTML = filtered.map(n => {
            const isUnread = !n.is_read;
            const isWelcome = n.type === 'welcome';
            const isTripReminder = n.type === 'itinerary_reminder' && !/completed/i.test(String(n.title || ''));
            const itemTime = (typeof window.getNotifTimestamp === 'function')
                ? window.getNotifTimestamp(n)
                : (n.created_at ? new Date(n.created_at).getTime() : Date.now());
            const timerStr = (typeof window.formatReverseTimer === 'function')
                ? window.formatReverseTimer(itemTime)
                : formatRelative(n);
            const formattedDate = new Date(itemTime).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            const rawTitle = (typeof window.cleanNotifTitle === 'function') ? window.cleanNotifTitle(n.title, isWelcome) : (n.title || 'Update');
            const spotName = n.spot_name || n.spot || (n.data && n.data.spot_name) || null;
            const rawMsg = (typeof window.cleanNotifMessage === 'function')
                ? window.cleanNotifMessage(n.message, spotName)
                : String(n.message || '').replace(/\s*(?:&|and)\s*XP\b/gi, '');

            return `
                <div class="nc-swipe-wrap" data-notif-id="${Number(n.id)}">
                    <div class="nc-swipe-bg"><i class="fa-solid fa-trash-can"></i> Delete</div>
                    <div class="nc-swipe-card" data-notif-id="${Number(n.id)}" style="display: block;">
                        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; min-width: 0;">
                            ${isUnread ? '<span class="unread-dot" style="width: 7px; height: 7px; border-radius: 50%; background: #0284c7; flex-shrink: 0; display: inline-block;"></span>' : ''}
                            ${isWelcome ? '<span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 2px 6px; border-radius: 4px; flex-shrink: 0;">Welcome</span>' : ''}
                            ${isTripReminder ? '<i class="fa-solid fa-bell" aria-hidden="true" style="color: #f59e0b; font-size: 13.5px; flex-shrink: 0; display: inline-block;"></i>' : ''}
                            <span style="font-size: 14px; color: #0f172a; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escHtml(rawTitle)}</span>
                        </div>
                        <p style="margin: 0 0 8px 0; font-size: 12.5px; color: ${isUnread ? '#334155' : '#64748b'}; line-height: 1.45; font-weight: ${isUnread ? '600' : '500'}; overflow-wrap: break-word;">${escHtml(rawMsg)}</p>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                            <span style="font-size: 10px; font-weight: 700; color: #0284c7; display: inline-flex; align-items: center; background: #e0f2fe; padding: 2px 8px; border-radius: 100px; border: 1px solid #bae6fd;">${escHtml(timerStr)}</span>
                            <span style="font-size: 10.5px; color: #94a3b8; font-weight: 600;">${escHtml(formattedDate)}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        bindSwipeHandlers();
    }

    function bindSwipeHandlers() {
        document.querySelectorAll('.nc-swipe-card').forEach(card => {
            let startX = 0;
            let startY = 0;
            let currentX = 0;
            let swiping = false;
            let didDrag = false;
            const notifId = Number(card.dataset.notifId);
            const swipeBg = card.parentElement ? card.parentElement.querySelector('.nc-swipe-bg') : null;

            const onStart = (x, y) => {
                startX = x;
                startY = y;
                currentX = 0;
                swiping = true;
                didDrag = false;
                card.style.transition = 'none';
            };

            const onMove = (x, y) => {
                if (!swiping) return;
                const dx = x - startX;
                const dy = y - startY;
                if (!didDrag && Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 8) {
                    swiping = false;
                    card.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1)';
                    card.style.transform = 'translateX(0px)';
                    if (swipeBg) swipeBg.style.opacity = '0';
                    return;
                }
                if (dx < -6) {
                    didDrag = true;
                    currentX = Math.max(dx, -card.offsetWidth);
                    card.style.transform = `translateX(${currentX}px)`;
                    if (swipeBg) swipeBg.style.opacity = '1';
                }
            };

            const onEnd = () => {
                if (!swiping) return;
                swiping = false;
                card.style.transition = 'transform 0.22s cubic-bezier(0.16, 1, 0.3, 1)';
                if (currentX <= -90) {
                    card.style.transform = 'translateX(-105%)';
                    setTimeout(() => window.notifCenterDelete(notifId), 190);
                } else {
                    card.style.transform = 'translateX(0px)';
                    if (swipeBg) swipeBg.style.opacity = '0';
                }
            };

            card.addEventListener('touchstart', e => {
                if (e.touches && e.touches[0]) onStart(e.touches[0].clientX, e.touches[0].clientY);
            }, { passive: true });
            card.addEventListener('touchmove', e => {
                if (e.touches && e.touches[0]) onMove(e.touches[0].clientX, e.touches[0].clientY);
            }, { passive: true });
            card.addEventListener('touchend', onEnd);
            card.addEventListener('touchcancel', onEnd);

            card.addEventListener('pointerdown', e => {
                if (e.pointerType === 'mouse') {
                    onStart(e.clientX, e.clientY);
                    card.setPointerCapture?.(e.pointerId);
                }
            });
            card.addEventListener('pointermove', e => {
                if (e.pointerType === 'mouse') onMove(e.clientX, e.clientY);
            });
            card.addEventListener('pointerup', e => {
                if (e.pointerType === 'mouse') onEnd();
            });
            card.addEventListener('click', () => {
                if (!didDrag) window.notifCenterMarkRead(notifId);
            });
        });
    }

    window.setNotifCenterFilter = function (filter, btn) {
        activeFilter = filter;
        document.querySelectorAll('.nc-filter-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');
        renderList();
    };

    window.loadNotificationsCenter = async function () {
        const token = getAuthToken();
        const listEl = document.getElementById('notif-center-list');
        const summaryEl = document.getElementById('notif-center-summary');
        if (!token) {
            if (summaryEl) summaryEl.textContent = 'Sign in to view your notifications.';
            if (listEl) {
                listEl.innerHTML = `<div style="text-align:center; padding:32px 18px; background:#ffffff; border-radius:16px; border:1.5px solid #e2e8f0; color:#475569; font-size:13px; font-weight:600;">Guest accounts do not receive cloud notifications. Sign in to stay updated!</div>`;
            }
            return;
        }
        try {
            const res = await fetch(getBaseUrl() + '/api/tourist/notifications', {
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                allNotifs = Array.isArray(data.notifications) ? data.notifications : [];
                renderList();
            }
        } catch (e) {
            if (summaryEl) summaryEl.textContent = 'Offline — unable to refresh notifications.';
        }
    };

    window.notifCenterMarkRead = async function (id) {
        const token = getAuthToken();
        const target = allNotifs.find(n => Number(n.id) === Number(id));
        if (target && !target.is_read) {
            target.is_read = true;
            renderList();
            if (token) {
                fetch(getBaseUrl() + '/api/tourist/notifications/' + id + '/read', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                }).catch(() => {});
            }
        }
    };

    window.notifCenterMarkAllRead = async function () {
        const token = getAuthToken();
        if (!token) return;
        allNotifs.forEach(n => { n.is_read = true; });
        renderList();
        try {
            await fetch(getBaseUrl() + '/api/tourist/notifications/read-all', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (typeof showToast === 'function') showToast('All notifications marked as read.', 'success');
        } catch (e) {}
    };

    window.notifCenterDelete = async function (id) {
        const token = getAuthToken();
        allNotifs = allNotifs.filter(n => Number(n.id) !== Number(id));
        renderList();
        if (token) {
            fetch(getBaseUrl() + '/api/tourist/notifications/' + id, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            }).catch(() => {});
        }
    };

    window.notifCenterClearAll = async function () {
        const token = getAuthToken();
        if (!token || allNotifs.length === 0) return;
        const backup = [...allNotifs];
        allNotifs = [];
        renderList();
        try {
            const res = await fetch(getBaseUrl() + '/api/tourist/notifications/clear-all', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            if (typeof window.fetchNotifications === 'function') window.fetchNotifications(true);
            if (typeof showToast === 'function') showToast('All notifications cleared.', 'info');
        } catch (e) {
            allNotifs = backup;
            renderList();
            if (typeof showToast === 'function') showToast('Failed to clear notifications. Please try again.', 'error');
        }
    };

    if (window._notifCenterSyncHandler) {
        window.removeEventListener('notificationsUpdated', window._notifCenterSyncHandler);
    }
    window._notifCenterSyncHandler = function (e) {
        const d = e && e.detail ? e.detail : {};
        if (d.action === 'clear-all') {
            allNotifs = [];
            renderList();
        } else if (d.action === 'delete' && d.id != null) {
            allNotifs = allNotifs.filter(n => String(n.id) !== String(d.id));
            renderList();
        } else if (d.action === 'read-all') {
            allNotifs.forEach(n => { n.is_read = true; });
            renderList();
        } else if (d.action === 'read' && d.id != null) {
            const target = allNotifs.find(n => String(n.id) === String(d.id));
            if (target) {
                target.is_read = true;
                renderList();
            }
        }
    };
    window.addEventListener('notificationsUpdated', window._notifCenterSyncHandler);

    window.loadNotificationsCenter();
})();
</script>
