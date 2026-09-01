<?php
$pageTitle = 'Download App';
$hideBottomNav = true;
?>
<style>
  #bottom-navigation { display: none !important; }
  .app-header { display: none !important; }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      font-family: 'Inter', sans-serif;
      background:
        radial-gradient(ellipse at 85% 5%, rgba(0, 242, 254, 0.35) 0%, transparent 55%),
        radial-gradient(ellipse at 15% 45%, rgba(56, 189, 248, 0.3) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 80%, rgba(63, 125, 183, 0.4) 0%, transparent 60%),
        linear-gradient(180deg, #1e3a8a 0%, #3f7db7 30%, #0284c7 65%, #06b6d4 90%, #00f2fe 100%) !important;
      background-attachment: fixed !important;
      color: #ffffff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      overflow-x: hidden;
    }

    .page-wrapper {
      position: relative; z-index: 1;
      width: 100%; max-width: 520px;
      padding: 0 20px 60px;
    }

    /* Header */
    .header {
      text-align: center;
      padding: 48px 0 32px;
    }
    .logo-ring {
      width: 96px; height: 96px;
      border-radius: 50%;
      background: linear-gradient(135deg, #00f2fe, #0284c7);
      padding: 3px;
      margin: 0 auto 18px;
      box-shadow: 0 0 40px rgba(0, 242, 254, 0.35), 0 8px 24px rgba(10,25,60,0.4);
    }
    .logo-inner {
      width: 100%; height: 100%;
      border-radius: 50%;
      background: #ffffff;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
    }
    .logo-inner img {
      width: 80%; height: 80%;
      object-fit: contain;
      border-radius: 50%;
    }
    .logo-inner i {
      font-size: 38px;
      color: #0284c7;
    }
    .header h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 28px; font-weight: 900;
      letter-spacing: -0.8px; margin-bottom: 6px;
      color: #ffffff;
    }
    .header h1 span { color: #00f2fe; }
    .header p {
      font-size: 14px;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 400;
      line-height: 1.5;
    }

    /* Version badge */
    .version-row {
      display: flex; justify-content: center; gap: 10px;
      margin-top: 14px; flex-wrap: wrap;
    }
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 11px; font-weight: 800;
      padding: 4px 12px; border-radius: 100px;
      border: none !important; outline: none !important;
    }
    .badge.blue { background: rgba(0, 242, 254, 0.25); color: #ffffff; }
    .badge.green { background: rgba(52, 199, 89, 0.25); color: #ffffff; }
    .badge.purple { background: rgba(168, 85, 247, 0.25); color: #ffffff; }

    /* Download card */
    .download-card {
      background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
      border: none !important;
      outline: none !important;
      border-radius: 24px;
      padding: 28px 24px;
      margin-top: 28px;
      backdrop-filter: blur(20px);
      text-align: center;
      box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
    }

    .download-btn {
      display: flex; align-items: center; justify-content: center; gap: 12px;
      width: 100%;
      padding: 16px 24px;
      border: none !important; outline: none !important; border-radius: 16px;
      background: linear-gradient(135deg, #00f2fe 0%, #0284c7 100%) !important;
      color: white;
      font-family: 'Inter', sans-serif;
      font-size: 16px; font-weight: 800;
      letter-spacing: -0.2px;
      cursor: pointer;
      transition: all 0.25s ease;
      box-shadow: 0 8px 24px rgba(0, 242, 254, 0.35);
      text-decoration: none;
    }
    .download-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 32px rgba(0, 242, 254, 0.5);
    }
    .download-btn:active { transform: translateY(0); }
    .download-btn i { font-size: 20px; }

    .file-info {
      display: flex; justify-content: center; gap: 18px;
      margin-top: 14px;
      font-size: 12px; color: rgba(255, 255, 255, 0.85); font-weight: 600;
    }
    .file-info span { display: flex; align-items: center; gap: 4px; }
    .file-info i { font-size: 11px; color: #00f2fe; }

    /* QR Section */
    .qr-section {
      background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
      border: none !important;
      outline: none !important;
      border-radius: 24px;
      padding: 28px 24px;
      margin-top: 16px;
      text-align: center;
      box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
    }
    .qr-section h3 {
      font-size: 15px; font-weight: 800;
      color: #ffffff; margin-bottom: 4px;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .qr-section p {
      font-size: 12px; color: rgba(255, 255, 255, 0.85);
      margin-bottom: 18px; font-weight: 400;
    }
    .qr-box {
      width: 200px; height: 200px;
      background: white;
      border-radius: 16px;
      margin: 0 auto;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 8px 24px rgba(0,0,0,0.3);
      overflow: hidden;
      border: none !important; outline: none !important;
    }
    .qr-box img { width: 100%; height: 100%; object-fit: contain; }
    .qr-box canvas { border-radius: 12px; }

    /* Install steps */
    .install-section {
      background: linear-gradient(135deg, #1e3a8a 0%, #3f7db7 100%) !important;
      border: none !important;
      outline: none !important;
      border-radius: 24px;
      padding: 24px;
      margin-top: 16px;
      box-shadow: 0 8px 24px rgba(10, 25, 60, 0.25) !important;
    }
    .install-section h3 {
      font-size: 15px; font-weight: 800; color: #ffffff;
      margin-bottom: 16px;
      display: flex; align-items: center; gap: 8px;
    }
    .install-step {
      display: flex; gap: 14px; align-items: flex-start;
      margin-bottom: 14px;
    }
    .install-step:last-child { margin-bottom: 0; }
    .step-circle {
      width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
      background: rgba(0, 242, 254, 0.25);
      border: none !important; outline: none !important;
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 900; color: #00f2fe;
    }
    .step-text h4 {
      font-size: 14px; font-weight: 700; color: #ffffff;
      margin-bottom: 2px;
    }
    .step-text p {
      font-size: 12.5px; color: rgba(255, 255, 255, 0.85);
      line-height: 1.5; font-weight: 400;
    }

    /* Requirements */
    .req-row {
      display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
      margin-top: 16px;
    }
    .req-card {
      background: rgba(255, 255, 255, 0.1);
      border: none !important; outline: none !important;
      border-radius: 16px;
      padding: 14px;
      text-align: center;
    }
    .req-card i { font-size: 20px; margin-bottom: 8px; display: block; color: #00f2fe; }
    .req-card h4 { font-size: 12px; font-weight: 700; color: #ffffff; margin-bottom: 2px; }
    .req-card p { font-size: 11px; color: rgba(255, 255, 255, 0.8); font-weight: 500; }

    /* Warning card */
    .warning-card {
      background: rgba(251, 191, 36, 0.15);
      border: none !important; outline: none !important;
      border-radius: 16px;
      padding: 14px 16px;
      margin-top: 16px;
      display: flex; gap: 10px; align-items: flex-start;
    }
    .warning-card i { color: #fbbf24; font-size: 14px; margin-top: 2px; flex-shrink: 0; }
    .warning-card p { font-size: 12px; color: #fde68a; line-height: 1.5; }

    /* Footer */
    .footer {
      text-align: center;
      margin-top: 36px;
      padding-top: 24px;
      border-top: 1px solid rgba(255,255,255,0.06);
    }
    .footer p {
      font-size: 12px;
      color: rgba(100, 116, 139, 0.6);
      font-weight: 400;
    }

    /* Animations */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .header { animation: fadeInUp 0.5s ease forwards; }
    .download-card { animation: fadeInUp 0.5s ease 0.1s both; }
    .qr-section { animation: fadeInUp 0.5s ease 0.2s both; }
    .install-section { animation: fadeInUp 0.5s ease 0.3s both; }

    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3); }
      50% { box-shadow: 0 8px 32px rgba(56, 189, 248, 0.5), 0 0 16px rgba(56, 189, 248, 0.2); }
    }
    .download-btn { animation: pulse-glow 2.5s ease-in-out infinite; }
  </style>
</head>
<body>

<div class="page-wrapper">

  <!-- HEADER -->
  <div class="header">
    <div class="logo-ring">
      <div class="logo-inner">
        <img id="dl-logo" src="assets/img/logo.png" alt="Intan Elyu Logo" onerror="this.style.display='none'; this.parentElement.innerHTML='<i class=\'fa-solid fa-compass\'></i>';">
      </div>
    </div>
    <h1>Download <span>Intan Elyu</span></h1>
    <p>Your ultimate travel companion for La Union, Philippines.<br>Available for Android devices.</p>
    <div class="version-row">
      <span class="badge blue"><i class="fa-solid fa-code-branch"></i> v1.1.0</span>
      <span class="badge green"><i class="fa-solid fa-shield-check"></i> Signed APK</span>
      <span class="badge purple"><i class="fab fa-android"></i> Android 7.0+</span>
    </div>
  </div>

  <!-- DOWNLOAD CARD -->
  <div class="download-card">
    <a href="index.php?action=download_apk" download="intan-elyu.apk" class="download-btn" id="download-btn">
      <i class="fab fa-android"></i>
      Download APK for Android
    </a>
    <?php
      $localApk = __DIR__ . '/../downloads/intan-elyu.apk';
      if (!file_exists($localApk)) {
          $localApk = dirname(__DIR__, 2) . '/public/downloads/intan-elyu.apk';
      }
      $apkSizeStr = file_exists($localApk) ? '~' . round(filesize($localApk) / (1024 * 1024), 1) . ' MB' : '~39.4 MB';
    ?>
    <div class="file-info">
      <span><i class="fa-solid fa-file-zipper"></i> APK File</span>
      <span><i class="fa-solid fa-hard-drive"></i> <?= $apkSizeStr ?></span>
      <span><i class="fa-solid fa-lock"></i> Signed Release</span>
    </div>
    <div style="margin-top: 12px; font-size: 12px; color: rgba(148,163,184,0.75);">
      Having trouble? <a href="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk" target="_blank" rel="noopener" style="color: #38bdf8; font-weight: 600; text-decoration: underline;">Download via Cloud Mirror (R2)</a>
    </div>
  </div>

  <!-- QR CODE -->
  <div class="qr-section">
    <h3><i class="fa-solid fa-qrcode" style="color:#38bdf8;"></i> Scan to Download</h3>
    <p>Open your phone camera and point it at this QR code</p>
    <div class="qr-box">
      <div id="qr-canvas-wrap" style="width:180px;height:180px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#94a3b8;">Loading QR...</div>
    </div>
  </div>

  <!-- INSTALL INSTRUCTIONS -->
  <div class="install-section">
    <h3><i class="fa-solid fa-wrench" style="color:#38bdf8;"></i> How to Install</h3>

    <div class="install-step">
      <div class="step-circle">1</div>
      <div class="step-text">
        <h4>Download the APK</h4>
        <p>Tap the download button above or scan the QR code on your Android phone.</p>
      </div>
    </div>

    <div class="install-step">
      <div class="step-circle">2</div>
      <div class="step-text">
        <h4>Allow Unknown Sources</h4>
        <p>If prompted, go to <strong>Settings → Security</strong> and enable <strong>"Install from unknown sources"</strong> for your browser.</p>
      </div>
    </div>

    <div class="install-step">
      <div class="step-circle">3</div>
      <div class="step-text">
        <h4>Open the APK File</h4>
        <p>Open the downloaded <strong>.apk</strong> file from your notifications or file manager and tap <strong>"Install"</strong>.</p>
      </div>
    </div>

    <div class="install-step">
      <div class="step-circle">4</div>
      <div class="step-text">
        <h4>Launch the App</h4>
        <p>Once installed, tap <strong>"Open"</strong> or find <strong>Intan Elyu</strong> in your app drawer. Create an account or sign in!</p>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="footer">
    <p>Intan Elyu Tourism Management System &copy; 2026<br>La Union, Philippines</p>
  </div>

</div>

<!-- QR Code Generator -->
<script>
(function(){
  // Determine download URL
  // If testing on localhost/127.0.0.1, phone cameras cannot connect to "http://localhost",
  // so we direct the QR scanner to the live cloud APK mirror so scanning works immediately from any phone!
  var isLocal = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname.startsWith('192.168.') || window.location.hostname.startsWith('10.');
  var publicApkUrl = 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk';
  var scanDownloadUrl = isLocal ? publicApkUrl : ((window.location.origin || 'https://app.intan-elyu.online') + '/index.php?action=download_apk');

  // Direct button download (for the device viewing this page)
  var btn = document.getElementById('download-btn');
  if (btn) {
    btn.href = 'index.php?action=download_apk';
    btn.setAttribute('download', 'intan-elyu.apk');
  }

  // Generate QR code using lightweight SVG generator
  var qrContainer = document.getElementById('qr-canvas-wrap');
  if (qrContainer) {
    function renderSvgQr() {
      try {
        var qr = qrcode(0, 'M');
        qr.addData(scanDownloadUrl);
        qr.make();
        qrContainer.innerHTML = qr.createSvgTag({ scalable: true, margin: 1 });
        var svg = qrContainer.querySelector('svg');
        if (svg) {
          svg.style.width = '100%';
          svg.style.height = '100%';
          svg.style.display = 'block';
          svg.style.background = '#ffffff';
        }
      } catch (e) {
        useImageFallback();
      }
    }

    function useImageFallback() {
      var img = document.createElement('img');
      img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent(scanDownloadUrl) + '&margin=1';
      img.alt = 'Scan QR Code to Download APK';
      img.style.width = '100%';
      img.style.height = '100%';
      img.style.objectFit = 'contain';
      img.style.display = 'block';
      img.style.background = '#ffffff';
      img.onerror = function() {
        // Tertiary fallback to Google Charts
        this.onerror = null;
        this.src = 'https://chart.googleapis.com/chart?cht=qr&chs=180x180&chl=' + encodeURIComponent(scanDownloadUrl) + '&chld=M|1';
      };
      qrContainer.innerHTML = '';
      qrContainer.appendChild(img);
    }

    if (typeof qrcode !== 'undefined') {
      renderSvgQr();
    } else {
      var script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js';
      script.onload = function() { renderSvgQr(); };
      script.onerror = function() { useImageFallback(); };
      document.head.appendChild(script);
    }
  }
})();
</script>
