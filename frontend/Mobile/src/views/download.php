<?php
// Standalone Official Tourism Portal of La Union
$localApk = dirname(__DIR__, 2) . '/public/downloads/intan-elyu.apk';
if (!file_exists($localApk)) {
    $localApk = __DIR__ . '/../downloads/intan-elyu.apk';
}
$apkSizeStr = file_exists($localApk) ? '~' . round(filesize($localApk) / (1024 * 1024), 1) . ' MB' : '~111.6 MB';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union</title>
  <meta name="description" content="Official smart tourism mobile platform and portal of the Provincial Government of La Union (PGLU). Discover 20 municipalities, attractions, surf spots, discounts, travel fares, and download the Intan Elyu mobile app.">
  <meta name="keywords" content="Intan Elyu, Intan Elyu mobile, Intan Elyu app, Intan Elyu download, Intan Elyu APK, La Union tourism, Elyu, San Juan surfing, PGLU, LUPTO, PICTO, Tangadan Falls, Balaoan Immuki Island, Luna Pebble Beach, Bauang grapes, La Union travel guide, mobile tourism app, Northern Luzon">
  <meta name="author" content="Provincial Government of La Union (PGLU)">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  <meta name="googlebot" content="index, follow">
  <meta name="bingbot" content="index, follow">
  <link rel="canonical" href="https://app.intan-elyu.online/?view=download">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#1e3a8a">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Intan Elyu">
  <meta name="application-name" content="Intan Elyu">
  <meta name="format-detection" content="telephone=no">
  
  <!-- Open Graph / Social Sharing -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Intan Elyu">
  <meta property="og:url" content="https://app.intan-elyu.online/?view=download">
  <meta property="og:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
  <meta property="og:description" content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
  <meta property="og:image" content="https://app.intan-elyu.online/assets/img/logo.png">
  <meta property="og:locale" content="en_PH">
  
  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Intan Elyu — Official Tourism Portal & Mobile App | Province of La Union">
  <meta name="twitter:description" content="Discover, explore, and experience the whole of La Union with Intan Elyu. Plan itineraries, discover 20 municipalities, view tourist spots, discounts, and earn gamified rewards.">
  <meta name="twitter:image" content="https://app.intan-elyu.online/assets/img/logo.png">
  
  <!-- Schema.org JSON-LD Structured Data for Search Engines (Brave, Google, Bing) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "SoftwareApplication",
        "name": "Intan Elyu",
        "alternateName": "Intan Elyu Mobile App",
        "operatingSystem": "Android 8.0+",
        "applicationCategory": "TravelApplication",
        "description": "The Official Smart Tourism Mobile Application for the Provincial Government of La Union (PGLU). Features 20 municipalities, curated tourist attractions, public transport fares, offline maps, and merchant discount vouchers.",
        "offers": {
          "@type": "Offer",
          "price": "0",
          "priceCurrency": "PHP"
        },
        "downloadUrl": "https://app.intan-elyu.online/index.php?action=download_apk",
        "publisher": {
          "@type": "GovernmentOrganization",
          "name": "Provincial Government of La Union (PGLU)",
          "url": "https://launion.gov.ph"
        }
      },
      {
        "@type": "WebSite",
        "name": "Intan Elyu — Official Tourism Portal of La Union",
        "url": "https://app.intan-elyu.online/",
        "description": "Official tourism portal of La Union featuring 20 municipalities, classified tourist spots, travel discounts, and points rewards.",
        "potentialAction": {
          "@type": "SearchAction",
          "target": "https://app.intan-elyu.online/index.php?view=download&q={search_term_string}",
          "query-input": "required name=search_term_string"
        }
      },
      {
        "@type": "TouristDestination",
        "name": "Province of La Union",
        "alternateName": "Elyu",
        "description": "Premier surfing, cultural heritage, and eco-tourism province in Northern Luzon, Philippines."
      }
    ]
  }
  </script>

  <link rel="icon" type="image/png" href="assets/img/logo.png">
  <link rel="apple-touch-icon" href="assets/img/logo.png">
  
  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <link rel="stylesheet" href="assets/css/views/download.css?v=<?= time() ?>">
</head>
<body>

  <!-- ─────────────────────────────────────────────────────────────────────────────
       1. LOCKED FIXED NAVIGATION BAR
       ───────────────────────────────────────────────────────────────────────────── -->
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
    </div>
  </nav>

  <!-- ─────────────────────────────────────────────────────────────────────────────
       2. BLUE HEADER & HERO SECTION
       ───────────────────────────────────────────────────────────────────────────── -->
  <header class="portal-header-wrapper">
    <!-- Hero Section with QR Code Directly in Hero -->
    <section class="portal-hero">
      <div class="hero-left-col">
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

        <!-- Stat Metrics Ticker -->
        <div class="hero-stats-strip reveal-on-scroll delay-3">
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
            <div class="stat-label">Classifications</div>
          </div>
          <div class="stat-col">
            <div class="stat-num">100%</div>
            <div class="stat-label">Play-to-Earn</div>
          </div>
        </div>
      </div>

      <div class="hero-right-col reveal-on-scroll delay-2" id="hero-qr-card-wrap">
        <div class="hero-qr-card">
          <div class="hero-qr-badge">
            <i class="fa-brands fa-android"></i> Official Mobile App
          </div>
          <div class="hero-qr-box">
            <img id="portal-hero-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https%3A%2F%2Fapp.intan-elyu.online%2Findex.php%3Faction%3Ddownload_apk&margin=1" alt="Scan QR Code to Download Intan Elyu APK">
          </div>
          <div class="hero-qr-title">Scan or Tap to Download</div>
          <p class="hero-qr-caption">
            Scan with another device's camera, or tap the button below to download directly to this phone.
          </p>
          <a href="index.php?action=download_apk" download="intan-elyu.apk" class="btn-hero-dl">
            <i class="fa-brands fa-android"></i> Download APK
          </a>
          <div class="hero-mirror-row">
            <a href="https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev/apks/intan-elyu.apk" target="_blank" rel="noopener" class="hero-mirror-link">
              <i class="fa-solid fa-cloud-arrow-down"></i> Cloud Mirror (R2)
            </a>
            <span class="hero-ver-tag">v0.0.0 &bull; Android 8.0+</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Smooth Wave Divider into White Middle Section -->
    <div class="hero-wave-divider">
      <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0 C150,90 350,-40 500,60 C650,140 900,10 1200,40 L1200,120 L0,120 Z" fill="#ffffff" class="shape-fill"></path>
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
            Discover historic Spanish watchtowers, the Basilica Minore of Our Lady of Charity in Agoo, Pindangan Ruins in San Fernando, and authentic Abel Iloko handlooms in Bangar.
          </p>
        </div>

        <div class="pillar-card reveal-on-scroll delay-3">
          <div class="pillar-icon-box"><i class="fa-solid fa-mountain-sun"></i></div>
          <h3>Eco-Adventures & Valleys</h3>
          <p>
            Trek to the breathtaking cascading Tangadan Falls in San Gabriel, zipline through lush mountain valleys in Pugo, or relax in Bauang's fruitful vineyards.
          </p>
        </div>
      </div>
    </section>

    <!-- Section: 20 Municipalities of La Union -->
    <section id="municipalities" style="background: #f8fafc; padding: 70px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
      <div class="portal-container" style="padding-top: 0; padding-bottom: 0;">
        <div class="center-header reveal-on-scroll">
          <span class="section-tag"><i class="fa-solid fa-landmark"></i> Provincial Directory</span>
          <h2 class="section-title">Explore All 20 Municipalities & City</h2>
          <p class="section-subtitle">
            From the bustling capital City of San Fernando to the highland ridges of Santol and Burgos, each town offers a unique blend of culture, culinary delights, and breathtaking landscapes.
          </p>
        </div>

        <!-- Filter Controls -->
        <div class="muni-controls-row reveal-on-scroll">
          <div class="filter-pills">
            <button type="button" class="pill-btn active" data-filter="all">All 20 Municipalities</button>
            <button type="button" class="pill-btn" data-filter="1">District 1 (North / Central)</button>
            <button type="button" class="pill-btn" data-filter="2">District 2 (South / Foothills)</button>
          </div>
          <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="muni-search" class="muni-search-input" placeholder="Search municipality or attraction...">
          </div>
        </div>

        <!-- 20 Municipalities Grid (All images verified on disk) -->
        <div class="muni-grid" id="municipalities-grid">
          
          <!-- 1. San Fernando City -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="City of San Fernando San Fernando City">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1 &bull; Capital</span>
              <img src="assets/img/MUNICIPALITIES/CITY%20OF%20SAN%20FERNANDO/sfc%20Pindangan%20Ruins.jpg" alt="City of San Fernando" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">City of San Fernando</div>
              <div class="muni-tagline">Provincial Capital & Cultural Center</div>
              <div class="muni-desc">Historic Pindangan Ruins, Ma-Cho Temple, Botanical Garden, and Christ the Redeemer viewing deck.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 12+ Key Attractions</div>
            </div>
          </div>

          <!-- 2. San Juan -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="San Juan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SAN%20JUAN/Urbiztondo%20Surf%20Area%20(1).png" alt="San Juan" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">San Juan</div>
              <div class="muni-tagline">Surfing Capital of the North</div>
              <div class="muni-desc">Urbiztondo Beach surfing breaks, vibrant cafe culture, Taboc Pottery making, and Old Watchtower.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 10+ Key Attractions</div>
            </div>
          </div>

          <!-- 3. Bauang -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Bauang">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BAUANG/BauangBeach1.jpg" alt="Bauang" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bauang</div>
              <div class="muni-tagline">Grape Capital & Coastal Haven</div>
              <div class="muni-desc">Famous vineyard grape-picking farms, Bakawan Eco-Tourism Park, and Saints Peter and Paul Parish Church.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 8+ Key Attractions</div>
            </div>
          </div>

          <!-- 4. Agoo -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Agoo">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/AGOO/AGOO%20BASILICA%201.jpg" alt="Agoo" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Agoo</div>
              <div class="muni-tagline">Heritage, Faith & Eco-Fun</div>
              <div class="muni-desc">Basilica Minore of Our Lady of Charity, Agoo Eco-Fun World, Museo de Iloko, and Plaza de la Virgen.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 9+ Key Attractions</div>
            </div>
          </div>

          <!-- 5. Luna -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="Luna">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/LUNA/Baluarte%20Watchtower.jpg" alt="Luna" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Luna</div>
              <div class="muni-tagline">Pebble Capital & Spanish Baluarte</div>
              <div class="muni-desc">Luna Pebble Beach, 400-year-old Baluarte Watchtower, Bahay na Bato, and Namacpacan Shrine.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 7+ Key Attractions</div>
            </div>
          </div>

          <!-- 6. Balaoan -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="Balaoan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BALAOAN/Balaoan%20Immuki%20Island%201.jpg" alt="Balaoan" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Balaoan</div>
              <div class="muni-tagline">Hidden Lagoon of Immuki Island</div>
              <div class="muni-desc">Crystal mangrove tidal lagoons at Immuki Island, agricultural heritage, and historic Antonino Church.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- 7. San Gabriel -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="1" data-name="San Gabriel">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SAN%20GABRIEL/Tangadan%20Falls%201.png" alt="San Gabriel" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">San Gabriel</div>
              <div class="muni-tagline">Gateway to Tangadan Falls</div>
              <div class="muni-desc">Magnificent multi-tiered Tangadan Waterfalls, Baroro river trekking, and indigenous highland trails.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- 8. Bacnotan -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="1" data-name="Bacnotan">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BACNOTAN/Bacnotan%20Baroro%20Battle%20Marker%201.jpg" alt="Bacnotan" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bacnotan</div>
              <div class="muni-tagline">Quirino Surfing & Sericulture Hub</div>
              <div class="muni-desc">Surf breaks at Quirino and Baccuit, DMMMSU-NLUC silk research, and agro-tourism trails.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- 9. Bangar -->
          <div class="muni-card reveal-on-scroll" data-district="1" data-name="Bangar">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/BANGAR/DeCastrosLoomWeaving1.jpg" alt="Bangar" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bangar</div>
              <div class="muni-tagline">Cradle of Abel Iloko Weaving</div>
              <div class="muni-desc">Centuries of traditional handloom weaving heritage, St. Christopher Parish, and coastal fishing.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- 10. Santol -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="1" data-name="Santol">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SANTOL/Amburayan%20River%20(1).png" alt="Santol" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Santol</div>
              <div class="muni-tagline">Mountain River Ridges & Waterfalls</div>
              <div class="muni-desc">Balay an Samur Falls, pristine Amburayan River eco-trails, and breathtaking Cordillera vistas.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- 11. Sudipen -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="1" data-name="Sudipen">
            <div class="muni-img-wrap">
              <span class="district-badge">District 1</span>
              <img src="assets/img/MUNICIPALITIES/SUDIPEN/Kinmadilian%20Falls%20(1).png" alt="Sudipen" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Sudipen</div>
              <div class="muni-tagline">Silag Crafts & River Adventures</div>
              <div class="muni-desc">Amburayan river valley, Centennial Rock formation, and skilled silag woven artisanal goods.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- 12. Pugo -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Pugo">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/PUGO/Pugad%20Adventure%201.png" alt="Pugo" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Pugo</div>
              <div class="muni-tagline">Adventure & Cleanest River</div>
              <div class="muni-desc">Pugad Adventure ziplines, Kultura Splash Wave, and the crystal-clear Tapuakan River.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 6+ Key Attractions</div>
            </div>
          </div>

          <!-- 13. Naguilian -->
          <div class="muni-card reveal-on-scroll" data-district="2" data-name="Naguilian">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/NAGUILIAN/Baraoas%20Rapids.jpg" alt="Naguilian" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Naguilian</div>
              <div class="muni-tagline">Basi Capital of the North</div>
              <div class="muni-desc">Traditional sugarcane wine fermentation, Tuddingan Falls, and agricultural rolling hills.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- 14. Aringay -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="2" data-name="Aringay">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/ARINGAY/Aringay%20Centennial%20Tunnel%201.jpg" alt="Aringay" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Aringay</div>
              <div class="muni-tagline">Centennial Tunnel & Mangroves</div>
              <div class="muni-desc">Historic century-old Aringay Railroad Tunnel, river rafting, and scenic mangrove eco-parks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- 15. Bagulin -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Bagulin">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BAGULIN/Bagulin%20Loslosi%20Falls%201.jpg" alt="Bagulin" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Bagulin</div>
              <div class="muni-tagline">Waterfalls & Indigenous Culture</div>
              <div class="muni-desc">Hidden Loslosi and Kudal Falls, highland bamboo rafting, and vibrant cultural celebrations.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- 16. Burgos -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Burgos">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/BURGOS/BURGOS,%20Bolikewkew%20Rice%20Terraces%201.jpg" alt="Burgos" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Burgos</div>
              <div class="muni-tagline">Bolikewkew Terraces & Cordillera Ridges</div>
              <div class="muni-desc">Scenic Bolikewkew Rice Terraces, Delles Falls, and tranquil upland pine breeze.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- 17. Caba -->
          <div class="muni-card reveal-on-scroll" data-district="2" data-name="Caba">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/CABA/CABA,%20Diego%20Silang%20Monument%201.jpg" alt="Caba" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Caba</div>
              <div class="muni-tagline">Bamboo Craft & Eco-Trails</div>
              <div class="muni-desc">Mt. Sobredillo hiking trail, coastal salt-making farms, and traditional bamboo woodcraft.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 4+ Key Attractions</div>
            </div>
          </div>

          <!-- 18. Tubao -->
          <div class="muni-card reveal-on-scroll delay-1" data-district="2" data-name="Tubao">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/TUBAO/Lang-ay%20Falls%20(1).png" alt="Tubao" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Tubao</div>
              <div class="muni-tagline">Lush Valleys & Agro-Trails</div>
              <div class="muni-desc">Verdant agricultural valleys, Halog eco-trails, and scenic mountain view decks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 3+ Key Attractions</div>
            </div>
          </div>

          <!-- 19. Rosario -->
          <div class="muni-card reveal-on-scroll delay-2" data-district="2" data-name="Rosario">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/ROSARIO/Rosario%20Gateway%201.webp" alt="Rosario" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
            </div>
            <div class="muni-content">
              <div class="muni-title">Rosario</div>
              <div class="muni-tagline">Gateway to Northern Luzon</div>
              <div class="muni-desc">Strategic junction of Kennon Road and Marcos Highway, agricultural trading, and tree parks.</div>
              <div class="muni-spots-pill"><i class="fa-solid fa-location-dot" style="color:#2563eb;"></i> 5+ Key Attractions</div>
            </div>
          </div>

          <!-- 20. Santo Tomas -->
          <div class="muni-card reveal-on-scroll delay-3" data-district="2" data-name="Santo Tomas">
            <div class="muni-img-wrap">
              <span class="district-badge">District 2</span>
              <img src="assets/img/MUNICIPALITIES/SANTO%20TOMAS/Bantay%20Pokles%20(1).jpg" alt="Santo Tomas" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
          Explore classified destinations categorized under the Provincial Tourism Framework into 
          <strong>Emerging</strong> (new frontiers), <strong>Existing</strong> (established icons), and <strong>Potential</strong> (eco-tourism development).
        </p>
      </div>

      <div class="spots-grid">
        
        <!-- Spot 1: Tangadan Falls (Emerging) -->
        <div class="spot-card reveal-on-scroll">
          <div class="spot-img-wrap">
            <span class="class-badge class-emerging"><i class="fa-solid fa-gem"></i> Emerging</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-droplet"></i> Waterfalls</span>
            <img src="assets/img/MUNICIPALITIES/SAN%20GABRIEL/Tangadan%20Falls%201.png" alt="Tangadan Falls" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
            <img src="assets/img/MUNICIPALITIES/SAN%20JUAN/Urbiztondo%20Surf%20Area%20(1).png" alt="Urbiztondo Beach" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
            <img src="assets/img/MUNICIPALITIES/BALAOAN/Balaoan%20Immuki%20Island%201.jpg" alt="Immuki Island" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
            <img src="assets/img/MUNICIPALITIES/LUNA/Baluarte%20Watchtower.jpg" alt="Luna Baluarte" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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

        <!-- Spot 5: Bauang Vineyards & Agri-Parks (Existing) -->
        <div class="spot-card reveal-on-scroll delay-1">
          <div class="spot-img-wrap">
            <span class="class-badge class-existing"><i class="fa-solid fa-star"></i> Existing</span>
            <span class="spot-cat-badge"><i class="fa-solid fa-seedling"></i> Agri-Tourism</span>
            <img src="assets/img/MUNICIPALITIES/BAUANG/Bauang%20Bakawan%20Eco-Tourism%20Park1.jpg" alt="Bauang Grape Farms" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
            <img src="assets/img/MUNICIPALITIES/ARINGAY/Aringay%20Centennial%20Tunnel%201.jpg" alt="Aringay Rail Tunnel" onerror="if(!this.dataset.tried){this.dataset.tried=1;this.src='assets/img/logo.png';}">
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
              <span>Expires: 30 Days</span>
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
              <span>Certified Instructors</span>
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
              <span>Min. Spend ₱800</span>
              <span class="pts-cost-badge"><i class="fa-solid fa-coins"></i> 90 Points</span>
            </div>
          </div>

          <!-- Voucher 4 -->
          <div class="voucher-card reveal-on-scroll delay-3">
            <div class="voucher-discount-val">Free Grape Wine</div>
            <div class="voucher-partner">Lomboy Grape Vineyards</div>
            <div class="voucher-muni"><i class="fa-solid fa-location-dot"></i> Bauang Vineyard Trail</div>
            <div class="voucher-info-box">
              Complimentary 250ml sample bottle of estate-grown fruit wine with any picking basket.
            </div>
            <div class="voucher-bottom-row">
              <span>Valid All Season</span>
              <span class="pts-cost-badge"><i class="fa-solid fa-coins"></i> 150 Points</span>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- Section: Gamification & Points Mechanism -->
    <section id="points-mechanism" class="portal-container">
      <div class="center-header reveal-on-scroll">
        <span class="section-tag"><i class="fa-solid fa-trophy"></i> Play-to-Earn Tourism</span>
        <h2 class="section-title">How The Points Mechanism Works</h2>
        <p class="section-subtitle">
          The more you explore La Union, the more rewards you unlock. Intan Elyu uses a gamified incentive system to distribute tourism impact across all municipalities.
        </p>
      </div>

      <div class="steps-grid reveal-on-scroll">
        <div class="step-card">
          <div class="step-number-ring">1</div>
          <h4>Explore & Travel</h4>
          <p>Browse the app, pick tourist spots across 20 municipalities, and check live travel fares.</p>
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
            <li><a href="#hero-qr-card-wrap">Download App QR</a></li>
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

        <!-- Col 4: Provincial Tourism Contact -->
        <div class="footer-col">
          <h3>Provincial Tourism Office</h3>
          <p style="font-size: 13px; line-height: 1.55;">
            <i class="fa-solid fa-location-dot" style="color:#38bdf8;"></i> Mabanag Hall, Provincial Capitol Compound, City of San Fernando, La Union<br><br>
            <i class="fa-solid fa-phone" style="color:#38bdf8;"></i> (072) 888-2457<br>
            <i class="fa-solid fa-envelope" style="color:#38bdf8;"></i> luptourismoffice@gmail.com
          </p>
        </div>

      </div>

      <div class="footer-bottom-row">
        <div>
          &copy; 2026 Provincial Government of La Union &bull; Intan Elyu Smart Tourism Management System. All rights reserved.
        </div>
        <div>
          <span>Designed with Passion for La Union ("Elyu")</span>
        </div>
      </div>
    </div>
  </footer>

  <!-- Sticky Download Bar for Mobile Viewers -->
  <div class="mobile-sticky-dock" id="mobileDock">
    <a href="#about-elyu" class="dock-brand">
      <img src="assets/img/logo.png" alt="Intan Elyu" class="dock-logo">
      <div class="dock-text">
        <span class="dock-title">Intan Elyu App</span>
        <span class="dock-sub">Official Tourism App</span>
      </div>
    </a>
    <a href="index.php?action=download_apk" download="intan-elyu.apk" class="btn-dock-dl">
      <i class="fa-brands fa-android"></i> Download APK
    </a>
  </div>

  <!-- ─────────────────────────────────────────────────────────────────────────────
       4. INTERACTIVE JAVASCRIPT
       ───────────────────────────────────────────────────────────────────────────── -->
  <script>
    // 1. Intersection Observer for Scroll Reveals
    document.addEventListener('DOMContentLoaded', () => {
      const reveals = document.querySelectorAll('.reveal-on-scroll');
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

        reveals.forEach(el => observer.observe(el));
      } else {
        reveals.forEach(el => el.classList.add('is-revealed'));
      }
    });

    // Dynamic Navbar Glassmorphism on Scroll
    const portalNav = document.querySelector('.portal-nav');
    if (portalNav) {
      window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
          portalNav.classList.add('is-scrolled');
        } else {
          portalNav.classList.remove('is-scrolled');
        }
      }, { passive: true });
    }

    // 2. Smooth Scroll for Navigation Anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (!targetId || targetId === '#') return;
        const targetEl = document.querySelector(targetId);
        if (targetEl) {
          e.preventDefault();
          const navOffset = 84;
          const elementPosition = targetEl.getBoundingClientRect().top;
          const offsetPosition = elementPosition + window.pageYOffset - navOffset;

          window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
          });
        }
      });
    });

    // 3. Municipality District Filters and Instant Search
    let currentDistrict = 'all';
    const filterBtns = document.querySelectorAll('.pill-btn');
    const searchInput = document.getElementById('muni-search');

    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentDistrict = btn.getAttribute('data-filter');
        filterMunicipalities();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        filterMunicipalities();
      });
    }

    function filterMunicipalities() {
      const searchVal = (searchInput ? searchInput.value : '').toLowerCase().trim();
      const cards = document.querySelectorAll('#municipalities-grid .muni-card');
      cards.forEach(card => {
        const cardDistrict = card.getAttribute('data-district');
        const cardText = (card.textContent || card.innerText || '').toLowerCase();
        const textMatch = !searchVal || cardText.includes(searchVal);
        const districtMatch = currentDistrict === 'all' || cardDistrict === currentDistrict;

        if (textMatch && districtMatch) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Auto-search from URL query parameter (e.g. from search engines ?q=... or ?search=...)
    const urlParams = new URLSearchParams(window.location.search);
    const queryParam = urlParams.get('q') || urlParams.get('search');
    if (queryParam && searchInput) {
      searchInput.value = queryParam;
      filterMunicipalities();
    }

    // 4. Fallback Dynamic QR Code Generator for Current Domain
    (function() {
      const qrImg = document.getElementById('portal-hero-qr');
      const hostOrigin = (window.location.hostname.indexOf('railway.app') !== -1)
        ? 'https://app.intan-elyu.online'
        : window.location.origin;
      const apkUrl = hostOrigin + '/index.php?action=download_apk';
      const qrSource = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(apkUrl) + '&margin=1';
      
      qrImg.src = qrSource;
      qrImg.onerror = function() {
        this.src = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=' + encodeURIComponent(apkUrl) + '&chld=M|1';
      };
    })();
  </script>
</body>
</html>
