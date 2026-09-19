<?php
$pageTitle = 'Intan Elyu — Official Tourism Portal of La Union';
$hideBottomNav = true;

// Direct APK file size calculation
$localApk = dirname(__DIR__, 2) . '/public/downloads/intan-elyu.apk';
if (!file_exists($localApk)) {
    $localApk = __DIR__ . '/../downloads/intan-elyu.apk';
}
$apkSizeStr = file_exists($localApk) ? '~' . round(filesize($localApk) / (1024 * 1024), 1) . ' MB' : '~111.6 MB';
?>
<style>
  /* Reset and Base */
  #bottom-navigation { display: none !important; }
  .app-header { display: none !important; }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  
  html {
    scroll-behavior: smooth;
    color-scheme: light;
  }

  body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #ffffff !important;
    color: #1e293b;
    min-height: 100vh;
    overflow-x: hidden;
    line-height: 1.6;
    margin: 0;
    padding: 0;
  }

  /* Full-width container reset */
  #app-container, #main-content {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Typography */
  h1, h2, h3, h4, .font-heading {
    font-family: 'Outfit', 'Inter', sans-serif;
    letter-spacing: -0.025em;
  }

  /* ─────────────────────────────────────────────────────────────────────────────
     1. BLUE HEADER & HERO
     ───────────────────────────────────────────────────────────────────────────── */
  .portal-header-wrapper {
    background: linear-gradient(135deg, #1e3a8a 0%, #203f8d 45%, #2563eb 100%);
    color: #ffffff;
    position: relative;
    overflow: hidden;
  }

  /* Sticky Navigation Bar */
  .portal-nav {
    position: sticky;
    top: 0;
    z-index: 999;
    background: rgba(30, 58, 138, 0.88);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    transition: all 0.3s ease;
  }

  .nav-inner {
    max-width: 1240px;
    margin: 0 auto;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
  }

  .brand-group {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #ffffff;
  }

  .brand-logo-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22);
    overflow: hidden;
    flex-shrink: 0;
  }

  .brand-logo-badge img {
    width: 86%;
    height: 86%;
    object-fit: contain;
  }

  .brand-text-col h1 {
    font-size: 20px;
    font-weight: 900;
    line-height: 1.15;
    color: #ffffff;
    letter-spacing: -0.5px;
  }

  .brand-text-col span {
    font-size: 11px;
    font-weight: 700;
    color: #38bdf8;
    letter-spacing: 0.6px;
    text-transform: uppercase;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 22px;
  }

  .nav-link {
    color: rgba(255, 255, 255, 0.88);
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    transition: all 0.2s ease;
    padding: 6px 4px;
    position: relative;
  }

  .nav-link:hover {
    color: #ffffff;
  }

  .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #38bdf8;
    transition: width 0.25s ease;
  }

  .nav-link:hover::after {
    width: 100%;
  }

  .nav-cta-btn {
    background: #ffffff;
    color: #1e3a8a;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 800;
    padding: 9px 20px;
    border-radius: 100px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .nav-cta-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    background: #f0f9ff;
    color: #0284c7;
  }

  /* Hero Content */
  .portal-hero {
    max-width: 1240px;
    margin: 0 auto;
    padding: 70px 24px 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
  }

  .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #ffffff;
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 700;
    margin-bottom: 24px;
    backdrop-filter: blur(8px);
  }

  .hero-badge i {
    color: #38bdf8;
  }

  .hero-title {
    font-size: 48px;
    font-weight: 900;
    line-height: 1.15;
    max-width: 900px;
    margin-bottom: 18px;
    color: #ffffff;
  }

  .hero-title span.accent-cyan {
    background: linear-gradient(135deg, #38bdf8 0%, #00f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .hero-subtitle {
    font-size: 17px;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.92);
    max-width: 760px;
    margin-bottom: 36px;
  }

  .hero-action-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 50px;
  }

  .btn-primary-hero {
    background: #ffffff;
    color: #1e3a8a;
    font-size: 15px;
    font-weight: 800;
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 100px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .btn-primary-hero:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
    background: #f8fafc;
    color: #0284c7;
  }

  .btn-secondary-hero {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 15px;
    font-weight: 700;
    text-decoration: none;
    padding: 14px 28px;
    border-radius: 100px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    backdrop-filter: blur(10px);
    transition: all 0.25s ease;
  }

  .btn-secondary-hero:hover {
    background: rgba(255, 255, 255, 0.22);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-3px);
  }

  /* Metric Ticker */
  .hero-stats-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    width: 100%;
    max-width: 960px;
    background: rgba(15, 23, 42, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 22px 28px;
    border-radius: 20px;
    backdrop-filter: blur(16px);
  }

  .stat-col {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stat-num {
    font-size: 32px;
    font-weight: 900;
    color: #38bdf8;
    line-height: 1;
    margin-bottom: 4px;
    font-family: 'Outfit', sans-serif;
  }

  .stat-label {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.85);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* Curved Wave Transition */
  .hero-wave-divider {
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
    z-index: 3;
  }

  .hero-wave-divider svg {
    position: relative;
    display: block;
    width: calc(100% + 1.3px);
    height: 54px;
  }

  .hero-wave-divider .shape-fill {
    fill: #ffffff;
  }

  /* ─────────────────────────────────────────────────────────────────────────────
     2. WHITE MIDDLE BODY
     ───────────────────────────────────────────────────────────────────────────── */
  .portal-body {
    background: #ffffff;
    color: #1e293b;
    position: relative;
    z-index: 10;
  }

  .portal-container {
    max-width: 1240px;
    margin: 0 auto;
    padding: 70px 24px;
  }

  .section-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 5px 14px;
    border-radius: 100px;
    margin-bottom: 12px;
  }

  .section-title {
    font-size: 36px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.2;
    margin-bottom: 14px;
  }

  .section-subtitle {
    font-size: 16px;
    color: #64748b;
    max-width: 680px;
    line-height: 1.6;
    margin-bottom: 40px;
  }

  .text-center { text-align: center; }
  .center-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  /* Section A: About La Union Pillars */
  .pillars-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-bottom: 80px;
  }

  .pillar-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 26px 22px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
  }

  .pillar-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 34px rgba(30, 58, 138, 0.1);
    border-color: #93c5fd;
  }

  .pillar-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: #eff6ff;
    color: #1e3a8a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 18px;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.08);
  }

  .pillar-card h3 {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 10px;
  }

  .pillar-card p {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.6;
  }

  /* Section B: 20 Municipalities Directory */
  .muni-controls-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 30px;
  }

  .filter-pills {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .pill-btn {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .pill-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
  }

  .pill-btn.active {
    background: #1e3a8a;
    color: #ffffff;
    border-color: #1e3a8a;
    box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
  }

  .muni-search-input {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 100px;
    padding: 9px 20px 9px 40px;
    font-size: 13.5px;
    color: #0f172a;
    outline: none;
    min-width: 260px;
    transition: all 0.2s ease;
  }

  .muni-search-input:focus {
    border-color: #2563eb;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
  }

  .search-wrapper {
    position: relative;
    display: inline-block;
  }

  .search-wrapper i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 14px;
  }

  .muni-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 22px;
    margin-bottom: 80px;
  }

  .muni-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
  }

  .muni-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(30, 58, 138, 0.12);
    border-color: #93c5fd;
  }

  .muni-img-wrap {
    width: 100%;
    height: 160px;
    background: #f1f5f9;
    position: relative;
    overflow: hidden;
  }

  .muni-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }

  .muni-card:hover .muni-img-wrap img {
    transform: scale(1.06);
  }

  .district-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 100px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .muni-content {
    padding: 18px 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
  }

  .muni-title {
    font-size: 16.5px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }

  .muni-tagline {
    font-size: 12px;
    color: #0284c7;
    font-weight: 700;
    margin-bottom: 8px;
  }

  .muni-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .muni-spots-pill {
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    background: #f8fafc;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
  }

  /* Section C: Tourist Spots Showcase & Classification Badges */
  .spots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 80px;
  }

  .spot-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
  }

  .spot-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(30, 58, 138, 0.12);
    border-color: #93c5fd;
  }

  .spot-img-wrap {
    width: 100%;
    height: 200px;
    position: relative;
    background: #e2e8f0;
    overflow: hidden;
  }

  .spot-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
  }

  .spot-card:hover .spot-img-wrap img {
    transform: scale(1.05);
  }

  /* Classification Badges */
  .class-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    padding: 4px 12px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }

  .class-emerging {
    background: #ef4444 !important;
    color: #ffffff !important;
  }

  .class-existing {
    background: #2563eb !important;
    color: #ffffff !important;
  }

  .class-potential {
    background: #10b981 !important;
    color: #ffffff !important;
  }

  .spot-cat-badge {
    position: absolute;
    bottom: 12px;
    left: 14px;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(8px);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 8px;
  }

  .spot-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
  }

  .spot-muni-sub {
    font-size: 12px;
    font-weight: 700;
    color: #0284c7;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .spot-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
  }

  .spot-desc {
    font-size: 13px;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .spot-meta-row {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
  }

  .spot-fee {
    font-size: 12.5px;
    font-weight: 700;
    color: #059669;
  }

  .spot-points-tag {
    font-size: 11.5px;
    font-weight: 800;
    color: #1e3a8a;
    background: #eff6ff;
    padding: 3px 9px;
    border-radius: 6px;
    border: 1px solid #bfdbfe;
  }

  /* Section D: Discounts & Partner Vouchers */
  .vouchers-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 80px;
  }

  .voucher-card {
    background: #ffffff;
    border: 1px dashed #93c5fd;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 18px rgba(37, 99, 235, 0.05);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: all 0.3s ease;
  }

  .voucher-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(30, 58, 138, 0.1);
    border-color: #2563eb;
  }

  .voucher-discount-val {
    font-size: 28px;
    font-weight: 900;
    color: #1d4ed8;
    line-height: 1;
    margin-bottom: 8px;
    font-family: 'Outfit', sans-serif;
  }

  .voucher-partner {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
  }

  .voucher-muni {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 14px;
  }

  .voucher-info-box {
    background: #f8fafc;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 12px;
    color: #475569;
    line-height: 1.5;
    margin-bottom: 14px;
  }

  .voucher-bottom-row {
    margin-top: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
  }

  .pts-cost-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 800;
  }

  /* Section E: Gamified Points Mechanism */
  .gamify-banner {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 28px;
    padding: 48px 36px;
    margin-bottom: 80px;
  }

  .steps-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    margin-top: 36px;
    position: relative;
  }

  .step-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 28px 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: all 0.3s ease;
  }

  .step-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 28px rgba(30, 58, 138, 0.1);
    border-color: #93c5fd;
  }

  .step-number-ring {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #1e3a8a;
    color: #ffffff;
    font-size: 16px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.25);
  }

  .step-card h4 {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
  }

  .step-card p {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
  }

  /* Section F: Clean QR Code Mobile App Card (Just a QR Code as requested) */
  .qr-download-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 2px solid #bfdbfe;
    border-radius: 32px;
    padding: 50px 30px;
    max-width: 680px;
    margin: 0 auto 40px;
    text-align: center;
    box-shadow: 0 16px 40px rgba(30, 58, 138, 0.08);
  }

  .qr-box-wrapper {
    width: 220px;
    height: 220px;
    background: #ffffff;
    border-radius: 20px;
    padding: 12px;
    margin: 24px auto;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .qr-box-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 12px;
  }

  .qr-scan-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    color: #1e3a8a;
    font-size: 13px;
    font-weight: 800;
    padding: 6px 18px;
    border-radius: 100px;
    margin-bottom: 8px;
  }

  /* ─────────────────────────────────────────────────────────────────────────────
     3. BLUE FOOTER
     ───────────────────────────────────────────────────────────────────────────── */
  .portal-footer {
    background: linear-gradient(180deg, #1e3a8a 0%, #172554 50%, #0b1120 100%);
    color: #ffffff;
    padding: 70px 24px 30px;
    position: relative;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
  }

  .footer-inner {
    max-width: 1240px;
    margin: 0 auto;
  }

  .footer-top-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 40px;
    padding-bottom: 50px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    margin-bottom: 36px;
  }

  .footer-col h3 {
    font-size: 16px;
    font-weight: 800;
    color: #ffffff;
    margin-bottom: 18px;
    letter-spacing: -0.2px;
  }

  .footer-col p {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 16px;
  }

  .footer-col ul {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .footer-col ul li a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    font-size: 13.5px;
    transition: color 0.2s ease;
  }

  .footer-col ul li a:hover {
    color: #38bdf8;
  }

  .accreditation-box {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 16px;
    backdrop-filter: blur(8px);
  }

  .accreditation-title {
    font-size: 11px;
    font-weight: 800;
    color: #38bdf8;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 8px;
  }

  .accreditation-list {
    font-size: 13px;
    color: #ffffff;
    line-height: 1.5;
    font-weight: 600;
  }

  .footer-bottom-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.65);
    flex-wrap: wrap;
    gap: 16px;
  }

  /* ─────────────────────────────────────────────────────────────────────────────
     4. SMOOTH SCROLL REVEAL ANIMATIONS
     ───────────────────────────────────────────────────────────────────────────── */
  .reveal-on-scroll {
    opacity: 0;
    transform: translateY(28px);
    transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    will-change: opacity, transform;
  }

  .reveal-on-scroll.is-revealed {
    opacity: 1;
    transform: translateY(0);
  }

  /* Stagger delays */
  .delay-1 { transition-delay: 0.08s; }
  .delay-2 { transition-delay: 0.16s; }
  .delay-3 { transition-delay: 0.24s; }
  .delay-4 { transition-delay: 0.32s; }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .pillars-grid { grid-template-columns: repeat(2, 1fr); }
    .muni-grid { grid-template-columns: repeat(3, 1fr); }
    .spots-grid { grid-template-columns: repeat(2, 1fr); }
    .vouchers-grid { grid-template-columns: repeat(2, 1fr); }
    .steps-grid { grid-template-columns: repeat(2, 1fr); }
    .footer-top-grid { grid-template-columns: 1fr 1fr; }
    .hero-title { font-size: 38px; }
  }

  @media (max-width: 768px) {
    .nav-links { display: none; }
    .pillars-grid { grid-template-columns: 1fr; }
    .muni-grid { grid-template-columns: repeat(2, 1fr); }
    .spots-grid { grid-template-columns: 1fr; }
    .vouchers-grid { grid-template-columns: 1fr; }
    .steps-grid { grid-template-columns: 1fr; }
    .hero-stats-strip { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .footer-top-grid { grid-template-columns: 1fr; gap: 30px; }
    .hero-title { font-size: 32px; }
    .portal-hero { padding: 40px 16px 70px; }
    .portal-container { padding: 50px 16px; }
  }

  @media (max-width: 480px) {
    .muni-grid { grid-template-columns: 1fr; }
  }
</style>

<div class="portal-wrapper">

  <!-- ─────────────────────────────────────────────────────────────────────────────
       1. BLUE HEADER & HERO
       ───────────────────────────────────────────────────────────────────────────── -->
  <header class="portal-header-wrapper">
    
    <!-- Sticky Navigation Bar -->
    <nav class="portal-nav">
      <div class="nav-inner">
        <a href="index.php?view=download" class="brand-group">
          <div class="brand-logo-badge">
            <img src="assets/img/logo.png" alt="Intan Elyu Logo">
          </div>
          <div class="brand-text-col">
            <h1>Intan Elyu</h1>
            <span>Province of La Union</span>
          </div>
        </a>

        <div class="nav-links">
          <a href="#about-elyu" class="nav-link">About La Union</a>
          <a href="#municipalities" class="nav-link">Municipalities</a>
          <a href="#tourist-spots" class="nav-link">Tourist Spots</a>
          <a href="#discounts" class="nav-link">Discounts</a>
          <a href="#points-mechanism" class="nav-link">Points System</a>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
          <a href="#download-qr" class="nav-cta-btn">
            <i class="fa-solid fa-qrcode"></i> Get App
          </a>
          <a href="index.php?view=auth" class="nav-link" style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,0.15); border-radius: 100px; padding: 8px 16px;">
            <i class="fa-solid fa-arrow-right-to-bracket"></i> Login
          </a>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="portal-hero">
      <div class="hero-badge reveal-on-scroll">
        <i class="fa-solid fa-shield-halved"></i> Official Smart Tourism Platform &bull; Province of La Union
      </div>

      <h2 class="hero-title reveal-on-scroll delay-1">
        Discover, Explore & Experience <br>
        <span class="accent-cyan">The Whole of La Union</span>
      </h2>

      <p class="hero-subtitle reveal-on-scroll delay-2">
        Welcome to <strong>Intan Elyu</strong> — the premier digital companion for traveling through La Union. 
        Plan multi-stop itineraries, navigate public transportation fares, uncover hidden gems across 20 municipalities, 
        earn explorer points with GPS check-ins, and redeem partner merchant discounts.
      </p>

      <div class="hero-action-row reveal-on-scroll delay-3">
        <a href="#download-qr" class="btn-primary-hero">
          <i class="fa-solid fa-qrcode"></i> Scan QR to Get App
        </a>
        <a href="index.php?view=auth" class="btn-secondary-hero">
          <i class="fa-solid fa-compass"></i> Open Web Portal
        </a>
      </div>

      <!-- Stat Metrics Ticker -->
      <div class="hero-stats-strip reveal-on-scroll delay-4">
        <div class="stat-col">
          <div class="stat-num">20</div>
          <div class="stat-label">Municipalities & City</div>
        </div>
        <div class="stat-col">
          <div class="stat-num">100+</div>
          <div class="stat-label">Curated Spots</div>
        </div>
        <div class="stat-col">
          <div class="stat-num">3</div>
          <div class="stat-label">Spot Classifications</div>
        </div>
        <div class="stat-col">
          <div class="stat-num">100%</div>
          <div class="stat-label">Play-to-Earn Rewards</div>
        </div>
      </div>
    </section>

    <!-- Smooth Wave Divider into White Middle Section -->
    <div class="hero-wave-divider">
      <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,90 350,-40 500,60 C650,140 900,10 1200,40 L1200,120 L0,120 Z" class="shape-fill"></path>
      </svg>
    </div>
  </header>

  <!-- ─────────────────────────────────────────────────────────────────────────────
       2. WHITE MIDDLE BODY
       ───────────────────────────────────────────────────────────────────────────── -->
  <main class="portal-body">
    
    <!-- Section: About The Whole of La Union -->
    <section id="about-elyu" class="portal-container">
      <div class="center-header reveal-on-scroll">
        <span class="section-tag"><i class="fa-solid fa-map-location-dot"></i> Provincial Profile</span>
        <h2 class="section-title">The Wonders of La Union ("Elyu")</h2>
        <p class="section-subtitle">
          Nestled between the rolling Cordillera mountains and the warm blue waters of the Lingayen Gulf and South China Sea, 
          La Union is a vibrant haven of surf culture, centuries-old Ilokano heritage, refreshing waterfalls, and blooming eco-tourism.
        </p>
      </div>

      <div class="pillars-grid">
        <div class="pillar-card reveal-on-scroll delay-1">
          <div class="pillar-icon-box"><i class="fa-solid fa-water"></i></div>
          <h3>World-Class Surfing & Shores</h3>
          <p>
            Home to San Juan, the undisputed Surfing Capital of Northern Luzon, alongside pristine pebble shores in Luna and golden coastlines from Rosario to Balaoan.
          </p>
        </div>

        <div class="pillar-card reveal-on-scroll delay-2">
          <div class="pillar-icon-box"><i class="fa-solid fa-monument"></i></div>
          <h3>Centuries of Heritage & Faith</h3>
          <p>
            From the 400-year-old Agoo Basilica and Pindangan Ruins to historic watchtowers (Baluarte), traditional <em>Damili</em> pottery, and handwoven <em>Abel Iloko</em> fabrics.
          </p>
        </div>

        <div class="pillar-card reveal-on-scroll delay-3">
          <div class="pillar-icon-box"><i class="fa-solid fa-mountain-sun"></i></div>
          <h3>Waterfalls & Highland Nature</h3>
          <p>
            Trek to the majestic multi-tiered Tangadan Falls in San Gabriel, discover Balay an Samur in Santol, or hike the pristine mountain ridges of Bagulin and Burgos.
          </p>
        </div>

        <div class="pillar-card reveal-on-scroll delay-4">
          <div class="pillar-icon-box"><i class="fa-solid fa-wine-glass"></i></div>
          <h3>Agri-Tourism & Gastronomy</h3>
          <p>
            Pick sweet grapes right off the vine in Bauang, taste traditional sugarcane Basi in Naguilian, savor fresh seafood in Santo Tomas, and experience coastal dining.
          </p>
        </div>
      </div>
    </section>

    <!-- Section: 20 Municipalities & City Interactive Directory -->
    <section id="municipalities" style="background: #f8fafc; padding: 70px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
      <div class="portal-container" style="padding-top: 0; padding-bottom: 0;">
        <div class="center-header reveal-on-scroll">
          <span class="section-tag"><i class="fa-solid fa-city"></i> LGUs of La Union</span>
          <h2 class="section-title">20 Municipalities & City</h2>
          <p class="section-subtitle">
            Explore the unique charm, cultural heritage, and premier tourist attractions across all two congressional districts of La Union.
          </p>
        </div>

        <div class="muni-controls-row reveal-on-scroll">
          <div class="filter-pills" id="district-filter-pills">
            <button class="pill-btn active" onclick="filterMunicipalities('all', this)">All LGUs (20)</button>
            <button class="pill-btn" onclick="filterMunicipalities('1', this)">District 1 (9 LGUs)</button>
            <button class="pill-btn" onclick="filterMunicipalities('2', this)">District 2 (11 LGUs)</button>
          </div>

          <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="muni-search" class="muni-search-input" placeholder="Search municipality or spot..." oninput="searchMunicipalities(this.value)">
          </div>
        </div>

        <!-- Municipalities Grid -->
        <div class="muni-grid" id="municipalities-grid">
          
          <!-- San Fernando City -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="City of San Fernando San Fernando City">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1 &bull; Capital</span>
              <img src="assets/img/MUNICIPALITIES/CITY%20OF%20SAN%20FERNANDO/sfc%20Pindangan%20Ruins.jpg" alt="City of San Fernando" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">City of San Fernando</div>
              <div class="muni-tagline">Provincial Capital & Cultural Center</div>
              <div class="muni-desc">Historic Pindangan Ruins, Ma-Cho Temple, Botanical Garden, and Christ the Redeemer viewing deck.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 12+ Key Attractions</div>
            </div>
          </div>

          <!-- San Juan -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="San Juan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SAN%20JUAN/Urbiztondo%20Surf%20Area%20(1).png" alt="San Juan" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">San Juan</div>
              <div class="muni-tagline">Surfing Capital of the North</div>
              <div class="muni-desc">Urbiztondo Beach surfing breaks, vibrant cafe culture, Taboc Pottery making, and Old Watchtower.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 10+ Key Attractions</div>
            </div>
          </div>

          <!-- Bauang -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Bauang">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BAUANG/BauangBeach1.jpg" alt="Bauang" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bauang</div>
              <div class="muni-tagline">Grape Capital & Coastal Haven</div>
              <div class="muni-desc">Famous vineyard grape-picking farms, Bakawan Eco-Tourism Park, and Saints Peter and Paul Parish Church.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 8+ Key Attractions</div>
            </div>
          </div>

          <!-- Agoo -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Agoo">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/AGOO/AGOO%20BASILICA%201.jpg" alt="Agoo" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Agoo</div>
              <div class="muni-tagline">Heritage, Faith & Eco-Fun</div>
              <div class="muni-desc">Basilica Minore of Our Lady of Charity, Agoo Eco-Fun World, Museo de Iloko, and Plaza de la Virgen.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 9+ Key Attractions</div>
            </div>
          </div>

          <!-- Luna -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="Luna">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/LUNA/Baluarte%20Watchtower.jpg" alt="Luna" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Luna</div>
              <div class="muni-tagline">Pebble Capital & Spanish Baluarte</div>
              <div class="muni-desc">Luna Pebble Beach, 400-year-old Baluarte Watchtower, Bahay na Bato, and Namacpacan Shrine.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 7+ Key Attractions</div>
            </div>
          </div>

          <!-- Balaoan -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="Balaoan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BALAOAN/Immuki%20Island%201.jpg" alt="Balaoan" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Balaoan</div>
              <div class="muni-tagline">Hidden Lagoon of Immuki Island</div>
              <div class="muni-desc">Crystal mangrove tidal lagoons at Immuki Island, agricultural heritage, and historic Antonino Church.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- San Gabriel -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="1" data-name="San Gabriel">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SAN%20GABRIEL/Tangadan%20Falls%201.jpg" alt="San Gabriel" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">San Gabriel</div>
              <div class="muni-tagline">Gateway to Tangadan Falls</div>
              <div class="muni-desc">Magnificent multi-tiered Tangadan Waterfalls, Baroro river trekking, and indigenous highland trails.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- Bacnotan -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="1" data-name="Bacnotan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BACNOTAN/Quirino%20Surfing%201.jpg" alt="Bacnotan" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bacnotan</div>
              <div class="muni-tagline">Quirino Surfing & Sericulture Hub</div>
              <div class="muni-desc">Surf breaks at Quirino and Baccuit, DMMMSU-NLUC silk research, and agro-tourism trails.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- Bangar -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="Bangar">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BANGAR/Abel%20Iloko%20Weaving%201.jpg" alt="Bangar" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bangar</div>
              <div class="muni-tagline">Cradle of Abel Iloko Weaving</div>
              <div class="muni-desc">Centuries of traditional handloom weaving heritage, St. Christopher Parish, and coastal fishing.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- Santol -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="Santol">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SANTOL/Balay%20an%20Samur%20Falls%201.jpg" alt="Santol" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Santol</div>
              <div class="muni-tagline">Mountain River Ridges & Waterfalls</div>
              <div class="muni-desc">Balay an Samur Falls, pristine Amburayan River eco-trails, and breathtaking Cordillera vistas.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- Sudipen -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="1" data-name="Sudipen">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SUDIPEN/Amburayan%20River%201.jpg" alt="Sudipen" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Sudipen</div>
              <div class="muni-tagline">Silag Crafts & River Adventures</div>
              <div class="muni-desc">Amburayan river valley, Centennial Rock formation, and skilled silag woven artisanal goods.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- Pugo -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Pugo">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/PUGO/Pugad%20Adventure%201.jpg" alt="Pugo" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Pugo</div>
              <div class="muni-tagline">Adventure & Cleanest River</div>
              <div class="muni-desc">Pugad Adventure ziplines, Kultura Splash Wave, and the crystal-clear Tapuakan River.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- Naguilian -->
          <div class="muni-card reveal-on-scroll" data-district="2" data-name="Naguilian">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/NAGUILIAN/Tuddingan%20Falls%201.jpg" alt="Naguilian" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Naguilian</div>
              <div class="muni-tagline">Basi Capital of the North</div>
              <div class="muni-desc">Traditional sugarcane wine fermentation, Tuddingan Falls, and agricultural rolling hills.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- Aringay -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="2" data-name="Aringay">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/ARINGAY/Aringay%20Tunnel%201.jpg" alt="Aringay" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Aringay</div>
              <div class="muni-tagline">Centennial Tunnel & Mangroves</div>
              <div class="muni-desc">Historic century-old Aringay Railroad Tunnel, river rafting, and scenic mangrove eco-parks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- Bagulin -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Bagulin">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BAGULIN/Loslosi%20Falls%201.jpg" alt="Bagulin" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bagulin</div>
              <div class="muni-tagline">Waterfalls & Indigenous Culture</div>
              <div class="muni-desc">Hidden Loslosi and Kudal Falls, highland bamboo rafting, and vibrant cultural celebrations.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- Burgos -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Burgos">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BURGOS/Bolikewkew%20Rice%20Terraces%201.jpg" alt="Burgos" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Burgos</div>
              <div class="muni-tagline">Bolikewkew Terraces & Cordillera Ridges</div>
              <div class="muni-desc">Scenic Bolikewkew Rice Terraces, Delles Falls, and tranquil upland pine breeze.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- Caba -->
          <div class="muni-card reveal-on-scroll" data-district="2" data-name="Caba">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/CABA/Mt%20Sobredillo%201.jpg" alt="Caba" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Caba</div>
              <div class="muni-tagline">Bamboo Craft & Eco-Trails</div>
              <div class="muni-desc">Mt. Sobredillo hiking trail, coastal salt-making farms, and traditional bamboo woodcraft.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- Tubao -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="2" data-name="Tubao">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/TUBAO/Halog%20Trails%201.jpg" alt="Tubao" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Tubao</div>
              <div class="muni-tagline">Lush Valleys & Agro-Trails</div>
              <div class="muni-desc">Verdant agricultural valleys, Halog eco-trails, and scenic mountain view decks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 3+ Key Attractions</div>
            </div>
          </div>

          <!-- Rosario -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Rosario">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/ROSARIO/Gateway%201.jpg" alt="Rosario" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Rosario</div>
              <div class="muni-tagline">Gateway to Northern Luzon</div>
              <div class="muni-desc">Strategic junction of Kennon Road and Marcos Highway, agricultural trading, and tree parks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- Santo Tomas -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Santo Tomas">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/SANTO%20TOMAS/Oyster%20Farms%201.jpg" alt="Santo Tomas" onerror="this.src='assets/img/logo.png'">
            </div>
            <div class="muni-content">
              <div class="muni-title">Santo Tomas</div>
              <div class="muni-tagline">Coastal Oysters & Watchtower</div>
              <div class="muni-desc">Famous coastal oyster farming estuaries, Da-o Dam, and Spanish colonial watchtowers.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Section: Tourist Sites & Attractions Showcase with Official Classification Badges -->
    <section id="tourist-spots" class="portal-container">
      <div class="center-header reveal-on-scroll">
        <span class="section-tag"><i class="fa-solid fa-compass"></i> Curated Destinations</span>
        <h2 class="section-title">Spotlight on Tourist Attractions</h2>
        <p class="section-subtitle">
          Discover La Union's top destinations classified under official provincial tourism standards. 
          Visiting emerging hidden gems awards maximum points to encourage balanced tourism!
        </p>
      </div>

      <!-- Classification Legend -->
      <div class="reveal-on-scroll" style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 30px;">
        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #475569; background: #fff1f2; border: 1px solid #fecdd3; padding: 5px 14px; border-radius: 100px;">
          <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span> Emerging Sites (+100 Pts)
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #475569; background: #eff6ff; border: 1px solid #bfdbfe; padding: 5px 14px; border-radius: 100px;">
          <span style="width: 10px; height: 10px; border-radius: 50%; background: #2563eb;"></span> Existing Sites (+50 Pts)
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #475569; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 5px 14px; border-radius: 100px;">
          <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></span> Potential Sites (+75 Pts)
        </span>
      </div>

      <div class="spots-grid">
        
        <!-- Spot 1: Tangadan Falls (Emerging) -->
        <div class="spot-card reveal-on-scroll">
          <div class="spot-img-wrap">
            <span class="class-badge class-emerging"><i class="fa-solid fa-gem"></i> Emerging</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-droplet"></i> Waterfalls</span>
            <img src="assets/img/MUNICIPALITIES/SAN%20GABRIEL/Tangadan%20Falls%201.jpg" alt="Tangadan Falls" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> San Gabriel, La Union</div>
            <h3 class="spot-title">Tangadan Falls</h3>
            <p class="spot-desc">Majestic 50-foot cascading two-tiered waterfall with crystal clear natural swimming lagoons, cliff jumps, and scenic bamboo rafting.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Fee: ₱30 Eco-Fee</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#ef4444;"></i> +100 Pts</span>
            </div>
          </div>
        </div>

        <!-- Spot 2: Urbiztondo Surf Beach (Existing) -->
        <div class="spot-card reveal-on-scroll delay-1">
          <div class="spot-img-wrap">
            <span class="class-badge class-existing"><i class="fa-solid fa-star"></i> Existing</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-water"></i> Surf & Beach</span>
            <img src="assets/img/MUNICIPALITIES/SAN%20JUAN/Urbiztondo%20Surf%20Area%20(1).png" alt="Urbiztondo Beach" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> San Juan, La Union</div>
            <h3 class="spot-title">Urbiztondo Surf Beach</h3>
            <p class="spot-desc">The beating heart of Northern Luzon surf culture. Consistent point breaks, surfing academies, craft cafes, and sunset coastal gatherings.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Free Public Access</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#2563eb;"></i> +50 Pts</span>
            </div>
          </div>
        </div>

        <!-- Spot 3: Immuki Island (Emerging) -->
        <div class="spot-card reveal-on-scroll delay-2">
          <div class="spot-img-wrap">
            <span class="class-badge class-emerging"><i class="fa-solid fa-gem"></i> Emerging</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-tree"></i> Eco-Island</span>
            <img src="assets/img/MUNICIPALITIES/BALAOAN/Immuki%20Island%201.jpg" alt="Immuki Island" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> Balaoan, La Union</div>
            <h3 class="spot-title">Immuki Island Lagoons</h3>
            <p class="spot-desc">A serene eco-sanctuary featuring natural tidal rock pools, mangrove canals, and turquoise snorkeling lagoons tucked away along the coastline.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Fee: ₱20 Environmental</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#ef4444;"></i> +100 Pts</span>
            </div>
          </div>
        </div>

        <!-- Spot 4: Luna Baluarte Watchtower (Existing) -->
        <div class="spot-card reveal-on-scroll">
          <div class="spot-img-wrap">
            <span class="class-badge class-existing"><i class="fa-solid fa-star"></i> Existing</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-landmark"></i> Historical</span>
            <img src="assets/img/MUNICIPALITIES/LUNA/Baluarte%20Watchtower.jpg" alt="Luna Baluarte" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> Luna, La Union</div>
            <h3 class="spot-title">Luna Baluarte & Pebble Beach</h3>
            <p class="spot-desc">A 400-year-old Spanish fortress sentinel standing guard over the world-famous pebble stone shoreline and the shimmering West Philippine Sea.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Free Public Access</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#2563eb;"></i> +50 Pts</span>
            </div>
          </div>
        </div>

        <!-- Spot 5: Bauang Grape Farms (Existing) -->
        <div class="spot-card reveal-on-scroll delay-1">
          <div class="spot-img-wrap">
            <span class="class-badge class-existing"><i class="fa-solid fa-star"></i> Existing</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-seedling"></i> Agri-Tourism</span>
            <img src="assets/img/MUNICIPALITIES/BAUANG/BauangBakawan%20Eco-Tourism%20Park1.jpg" alt="Bauang Grape Farms" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> Bauang, La Union</div>
            <h3 class="spot-title">Bauang Vineyards & Agri-Parks</h3>
            <p class="spot-desc">Lush grape vineyards where visitors can harvest fresh Red Cardinal grapes right off the vine and taste locally pressed fruit wines.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Fee: ₱25-₱50 Entrance</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#2563eb;"></i> +50 Pts</span>
            </div>
          </div>
        </div>

        <!-- Spot 6: Aringay Historic Railroad Tunnel (Potential) -->
        <div class="spot-card reveal-on-scroll delay-2">
          <div class="spot-img-wrap">
            <span class="class-badge class-potential"><i class="fa-solid fa-compass"></i> Potential</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-train"></i> Eco-Heritage</span>
            <img src="assets/img/MUNICIPALITIES/ARINGAY/Aringay%20Tunnel%201.jpg" alt="Aringay Rail Tunnel" onerror="this.src='assets/img/logo.png'">
          </div>
          <div class="spot-content">
            <div class="spot-muni-sub"><i class="fa-solid fa-location-dot"></i> Aringay, La Union</div>
            <h3 class="spot-title">Aringay Centennial Tunnel</h3>
            <p class="spot-desc">A 500-meter historic railroad tunnel engineered during the Spanish and American colonial era carving through lush mountain foothills.</p>
            <div class="spot-meta-row">
              <span class="spot-fee">Free / Eco Donation</span>
              <span class="spot-points-tag"><i class="fa-solid fa-trophy" style="color:#10b981;"></i> +75 Pts</span>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- Section: Discounts & Partner Merchant Vouchers -->
    <section id="discounts" style="background: #f8fafc; padding: 70px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
      <div class="portal-container" style="padding-top: 0; padding-bottom: 0;">
        <div class="center-header reveal-on-scroll">
          <span class="section-tag"><i class="fa-solid fa-tags"></i> Travel Savings</span>
          <h2 class="section-title">Discounts & Partner Vouchers</h2>
          <p class="section-subtitle">
            Earn Elyu Points while visiting tourist sites and redeem them for real savings at top local restaurants, surf schools, resorts, and souvenir shops!
          </p>
        </div>

        <div class="vouchers-grid">
          
          <!-- Voucher 1 -->
          <div class="voucher-card reveal-on-scroll">
            <div class="voucher-discount-val">15% OFF</div>
            <div class="voucher-partner">Elyu Surfside Dining & Cafe</div>
            <div class="voucher-muni"><i class="fa-solid fa-location-dot"></i> San Juan Surfing Strip</div>
            <div class="voucher-info-box">
              Valid on all food and craft beverages. Present your digital voucher QR at checkout.
            </div>
            <div class="voucher-bottom-row">
              <span>Expires: 30 Days after claim</span>
              <span class="pts-cost-badge"><i class="fa-solid fa-coins"></i> 120 Points</span>
            </div>
          </div>

          <!-- Voucher 2 -->
          <div class="voucher-card reveal-on-scroll delay-1">
            <div class="voucher-discount-val">₱200 OFF</div>
            <div class="voucher-partner">North Swell Surfing Academy</div>
            <div class="voucher-muni"><i class="fa-solid fa-location-dot"></i> Urbiztondo Beach</div>
            <div class="voucher-info-box">
              Discount on 1-hour beginner or intermediate surf coaching with board rental included.
            </div>
            <div class="voucher-bottom-row">
              <span>Certified Surf Instructors</span>
              <span class="pts-cost-badge"><i class="fa-solid fa-coins"></i> 180 Points</span>
            </div>
          </div>

          <!-- Voucher 3 -->
          <div class="voucher-card reveal-on-scroll delay-2">
            <div class="voucher-discount-val">20% OFF</div>
            <div class="voucher-partner">Bangar Abel Iloko Handloom</div>
            <div class="voucher-muni"><i class="fa-solid fa-location-dot"></i> Bangar Heritage Center</div>
            <div class="voucher-info-box">
              Discount on authentic handwoven blankets, table runners, and beach wraps.
            </div>
            <div class="voucher-bottom-row">
              <span>Authentic Cultural Craft</span>
              <span class="pts-cost-badge"><i class="fa-solid fa-coins"></i> 150 Points</span>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Section: Gamified Points Mechanism Guide -->
    <section id="points-mechanism" class="portal-container">
      <div class="gamify-banner reveal-on-scroll">
        <div class="center-header">
          <span class="section-tag" style="background: #ffffff; color: #1e3a8a;"><i class="fa-solid fa-gamepad"></i> Play-to-Earn Tourism</span>
          <h2 class="section-title">How the Points Mechanism Works</h2>
          <p class="section-subtitle" style="color: #475569;">
            Intan Elyu turns your vacation into an interactive quest! Explore destinations across all 20 municipalities, verify visits, climb the leaderboard, and unlock real perks.
          </p>
        </div>

        <div class="steps-grid">
          <div class="step-card">
            <div class="step-number-ring">1</div>
            <h4>Explore Destinations</h4>
            <p>Travel to beaches, historical monuments, waterfalls, and eco-parks throughout La Union.</p>
          </div>

          <div class="step-card">
            <div class="step-number-ring">2</div>
            <h4>GPS & Photo Check-in</h4>
            <p>Verify your physical arrival using geolocation or AR camera check-ins to receive instant points.</p>
          </div>

          <div class="step-card">
            <div class="step-number-ring">3</div>
            <h4>Level Up & Badges</h4>
            <p>Rise from <em>Novice Explorer</em> to <em>Elyu Master</em>, unlocking badges and leaderboard ranks.</p>
          </div>

          <div class="step-card">
            <div class="step-number-ring">4</div>
            <h4>Redeem Real Perks</h4>
            <p>Exchange your accumulated points for partner food vouchers, surf lessons, and souvenirs.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Section: Scan QR Code to Get App (Streamlined as requested) -->
    <section id="download-qr" class="portal-container" style="padding-top: 0;">
      <div class="qr-download-container reveal-on-scroll">
        <div class="qr-scan-badge">
          <i class="fa-solid fa-mobile-screen"></i> Get Intan Elyu on Mobile
        </div>
        <h2 style="font-size: 32px; font-weight: 900; color: #0f172a; margin-bottom: 10px;">
          Scan QR Code to Download App
        </h2>
        <p style="font-size: 14.5px; color: #64748b; max-width: 520px; margin: 0 auto;">
          Open your phone's camera and point it at the QR code below to download the official Android APK directly.
        </p>

        <!-- QR Box -->
        <div class="qr-box-wrapper">
          <img id="portal-qr-code-img" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=https%3A%2F%2Fapp.intan-elyu.online%2Findex.php%3Faction%3Ddownload_apk&margin=1" alt="Scan QR Code to Download Intan Elyu APK">
        </div>

        <div style="display: flex; align-items: center; justify-content: center; gap: 12px; flex-wrap: wrap;">
          <a href="index.php?action=download_apk" download="intan-elyu.apk" style="display: inline-flex; align-items: center; gap: 8px; background: #1e3a8a; color: #ffffff; text-decoration: none; font-size: 13.5px; font-weight: 800; padding: 10px 22px; border-radius: 100px; box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);">
            <i class="fa-brands fa-android"></i> Direct Download APK (<?= $apkSizeStr ?>)
          </a>
          <a href="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #334155; text-decoration: none; font-size: 13.5px; font-weight: 700; padding: 10px 20px; border-radius: 100px; border: 1px solid #cbd5e1;">
            <i class="fa-solid fa-cloud-arrow-down"></i> Cloud Mirror (R2)
          </a>
        </div>
      </div>
    </section>

  </main>

  <!-- ─────────────────────────────────────────────────────────────────────────────
       3. BLUE FOOTER
       ───────────────────────────────────────────────────────────────────────────── -->
  <footer class="portal-footer">
    <div class="footer-inner">
      <div class="footer-top-grid">
        
        <!-- Col 1: Brand & Overview -->
        <div class="footer-col">
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: #ffffff; display: flex; align-items: center; justify-content: center;">
              <img src="assets/img/logo.png" alt="Logo" style="width: 80%; height: 80%; object-fit: contain;">
            </div>
            <h3 style="margin: 0; font-size: 18px;">Intan Elyu</h3>
          </div>
          <p>
            The Official Cross-Platform Smart Tourism Management System for the Provincial Government of La Union, connecting travelers, municipalities, and local businesses.
          </p>
          <div class="accreditation-box">
            <div class="accreditation-title"><i class="fa-solid fa-award"></i> Accredited To:</div>
            <div class="accreditation-list">
              Provincial Government of La Union (PGLU)<br>
              La Union Provincial Tourism Office (LUPTO)<br>
              Provincial Information and Communications Technology Office (PICTO)
            </div>
          </div>
        </div>

        <!-- Col 2: Quick Navigation -->
        <div class="footer-col">
          <h3>Quick Links</h3>
          <ul>
            <li><a href="#about-elyu">About La Union</a></li>
            <li><a href="#municipalities">20 Municipalities</a></li>
            <li><a href="#tourist-spots">Tourist Attractions</a></li>
            <li><a href="#discounts">Discounts & Vouchers</a></li>
            <li><a href="#points-mechanism">Points System</a></li>
            <li><a href="#download-qr">Download App QR</a></li>
          </ul>
        </div>

        <!-- Col 3: Academic Partnership -->
        <div class="footer-col">
          <h3>Academic Partnership</h3>
          <p style="font-size: 13px; line-height: 1.55;">
            Developed in collaboration with:<br>
            <strong>Saint Louis College</strong><br>
            Faculty of Information Technology<br>
            City of San Fernando, La Union<br>
            <em>IT Capstone Research Project</em>
          </p>
        </div>

        <!-- Col 4: Emergency Hotlines -->
        <div class="footer-col">
          <h3>Emergency Hotlines</h3>
          <ul>
            <li><span style="color:#38bdf8; font-weight:700;">Provincial Emergency:</span> 911</li>
            <li><span style="color:#38bdf8; font-weight:700;">LUPTO Tourism:</span> (072) 888-2451</li>
            <li><span style="color:#38bdf8; font-weight:700;">Police Office:</span> (072) 607-6577</li>
            <li><span style="color:#38bdf8; font-weight:700;">Provincial Hospital:</span> (072) 242-1143</li>
          </ul>
        </div>

      </div>

      <!-- Bottom Row -->
      <div class="footer-bottom-row">
        <div>
          &copy; <?= date('Y') ?> Intan Elyu &bull; Provincial Government of La Union. All rights reserved.
        </div>
        <div style="display: flex; gap: 16px;">
          <a href="index.php?view=terms" style="color: rgba(255,255,255,0.7); text-decoration: none;">Terms & Privacy</a>
          <a href="index.php?view=user_manual" style="color: rgba(255,255,255,0.7); text-decoration: none;">User Manual</a>
          <a href="index.php?view=about" style="color: rgba(255,255,255,0.7); text-decoration: none;">About System</a>
        </div>
      </div>
    </div>
  </footer>

</div>

<!-- ─────────────────────────────────────────────────────────────────────────────
     4. JAVASCRIPT: SMOOTH SCROLL REVEAL & INTERACTIVITY
     ───────────────────────────────────────────────────────────────────────────── -->
<script>
  // 1. Smooth Scroll Reveal on Scroll via IntersectionObserver
  document.addEventListener('DOMContentLoaded', function() {
    initScrollReveal();
  });

  function initScrollReveal() {
    const revealElements = document.querySelectorAll('.reveal-on-scroll');
    if (!revealElements.length) return;

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            obs.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
      });

      revealElements.forEach(el => observer.observe(el));
    } else {
      // Fallback for older browsers
      revealElements.forEach(el => el.classList.add('is-revealed'));
    }
  }

  // Trigger immediately in case elements are already visible
  setTimeout(initScrollReveal, 100);

  // 2. Municipalities Filtering by District
  function filterMunicipalities(district, btn) {
    const pills = document.querySelectorAll('#district-filter-pills .pill-btn');
    pills.forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const searchVal = (document.getElementById('muni-search')?.value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('#municipalities-grid .muni-card');

    cards.forEach(card => {
      const cardDistrict = card.getAttribute('data-district');
      const cardName = (card.getAttribute('data-name') || '').toLowerCase();
      const textMatch = !searchVal || cardName.includes(searchVal);
      const districtMatch = district === 'all' || cardDistrict === district;

      if (textMatch && districtMatch) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  // 3. Search Municipalities
  function searchMunicipalities(val) {
    const searchVal = (val || '').toLowerCase().trim();
    const activePill = document.querySelector('#district-filter-pills .pill-btn.active');
    let currentDistrict = 'all';
    if (activePill && activePill.textContent.includes('District 1')) currentDistrict = '1';
    if (activePill && activePill.textContent.includes('District 2')) currentDistrict = '2';

    const cards = document.querySelectorAll('#municipalities-grid .muni-card');
    cards.forEach(card => {
      const cardDistrict = card.getAttribute('data-district');
      const cardName = (card.getAttribute('data-name') || '').toLowerCase();
      const textMatch = !searchVal || cardName.includes(searchVal);
      const districtMatch = currentDistrict === 'all' || cardDistrict === currentDistrict;

      if (textMatch && districtMatch) {
        card.style.display = 'flex';
      } else {
        card.style.display = 'none';
      }
    });
  }

  // 4. Fallback QR Code Generator
  (function() {
    const qrImg = document.getElementById('portal-qr-code-img');
    if (!qrImg) return;
    
    // Resolve absolute download URL
    const apkUrl = window.location.origin + '/index.php?action=download_apk';
    const qrSource = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(apkUrl) + '&margin=1';
    
    qrImg.src = qrSource;
    qrImg.onerror = function() {
      // Google charts fallback
      this.src = 'https://chart.googleapis.com/chart?cht=qr&chs=220x220&chl=' + encodeURIComponent(apkUrl) + '&chld=M|1';
    };
  })();
</script>
