/**
 * Shared Utilities & Helpers for Intan-Elyu Tourism Management System
 * Includes status classification badges, category icons, colors, toast alerts, and HTML escaping.
 */

// Classification status styles
function getClassificationStyle(status) {
    status = (status || '').toUpperCase();
    if (status === 'EXIST' || status === 'EXISTING') return { bg: '#D1FAE5', color: '#047857', border: '#A7F3D0', label: 'EXISTING' };
    if (status === 'EMERGE' || status === 'EMERGING') return { bg: '#EDE9FE', color: '#6D28D9', border: '#DDD6FE', label: 'EMERGING' };
    return { bg: '#FEF3C7', color: '#B45309', border: '#FDE68A', label: 'POTENTIAL' };
}

function getClassificationBadgeHTML(status) {
    if (!status) return '';
    const s = getClassificationStyle(status);
    return `<span class="lupto-spot-badge" style="background:${s.bg};color:${s.color};border:1px solid ${s.border};font-weight:700;">${s.label}</span>`;
}

// Global Toast Notifications
function showToast(msg, type = 'success') {
    const colors = { success: '#16A34A', danger: '#DC2626', info: '#4338CA', warning: '#F59E0B' };
    const icons = { success: 'fa-check-circle', danger: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-circle' };
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;align-items:center;gap:10px;max-width:360px;animation:slideIn 0.3s ease;';
    toast.style.background = colors[type] || '#1E293B';
    toast.style.color = 'white';
    toast.innerHTML = `<i class="fas ${icons[type] || 'fa-bell'}"></i> ${msg}`;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// Category FontAwesome Icon Mapper
function getCategoryIcon(categoryStr) {
    if (!categoryStr) return 'map-marker-alt';
    const cat = categoryStr.toLowerCase();
    if (cat.includes('beach') || cat.includes('island')) return 'umbrella-beach';
    if (cat.includes('waterfall') || cat.includes('fall') || cat.includes('river') || cat.includes('lake') || cat.includes('spring')) return 'water';
    if (cat.includes('mountain') || cat.includes('hill') || cat.includes('hiking') || cat.includes('volcano') || cat.includes('cave')) return 'mountain';
    if (cat.includes('historical') || cat.includes('heritage') || cat.includes('monument') || cat.includes('landmark')) return 'landmark';
    if (cat.includes('religious') || cat.includes('church') || cat.includes('temple')) return 'church';
    if (cat.includes('ecotourism') || cat.includes('eco') || cat.includes('park') || cat.includes('nature') || cat.includes('forest') || cat.includes('sanctuary')) return 'tree';
    if (cat.includes('farm') || cat.includes('agri') || cat.includes('grape') || cat.includes('garden')) return 'seedling';
    if (cat.includes('food') || cat.includes('dining')) return 'utensils';
    if (cat.includes('resort')) return 'hotel';
    if (cat.includes('shopping')) return 'shopping-cart';
    return 'location-dot';
}

// Category Color Badge Mapper
function getCategoryColor(categoryStr) {
    if (!categoryStr) return { bg: '#F8FAFC', text: '#475569', border: '#E2E8F0' };
    const cat = categoryStr.toLowerCase();
    if (cat.includes('beach')) return { bg: '#EFF6FF', text: '#1D4ED8', border: '#BFDBFE' };
    if (cat.includes('waterfall') || cat.includes('spring')) return { bg: '#ECFEFF', text: '#0E7490', border: '#A5F3FC' };
    if (cat.includes('mountain') || cat.includes('hiking')) return { bg: '#F0FDF4', text: '#15803D', border: '#BBF7D0' };
    if (cat.includes('historical') || cat.includes('heritage')) return { bg: '#FFFBEB', text: '#B45309', border: '#FDE68A' };
    if (cat.includes('religious') || cat.includes('church') || cat.includes('temple')) return { bg: '#FAF5FF', text: '#6B21A8', border: '#E9D5FF' };
    if (cat.includes('farm') || cat.includes('grape')) return { bg: '#F7FEE7', text: '#3F6212', border: '#D9F99D' };
    return { bg: '#F8FAFC', text: '#475569', border: '#E2E8F0' };
}

// HTML XSS Escaping Helper
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions available globally on window object
window.getClassificationStyle = getClassificationStyle;
window.getClassificationBadgeHTML = getClassificationBadgeHTML;
window.showToast = showToast;
window.getCategoryIcon = getCategoryIcon;
window.getCategoryColor = getCategoryColor;
window.escapeHtml = escapeHtml;
