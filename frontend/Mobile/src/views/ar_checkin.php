<!-- AR Check-in View -->
<?php
$pageTitle = 'AR Check-in';
$hideBottomNav = true; // Hide bottom nav for full immersive view
?>

<?php include __DIR__ . '/../components/header.php'; ?>



<div id="camera-container">
    <video id="camera-view" autoplay playsinline></video>
</div>

<div class="ar-overlay">
    <div class="ar-header">
        <button class="btn-close-ar" onclick="history.back()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="status-pill" id="ar-status">
            <i class="fa-solid fa-satellite-dish spinning"></i> Locating...
        </div>
    </div>

    <div class="ar-reticle" id="ar-reticle"></div>

    <div class="ar-footer" id="ar-footer">
        <button class="btn-scan" id="btn-scan" onclick="triggerCheckIn()" style="display:none;"></button>
        <div style="text-align:center; margin-top:12px; font-size:12px; color:rgba(255,255,255,0.6); font-weight:600;" id="scan-hint">
            Move closer to the spot to unlock check-in
        </div>
    </div>
</div>

<!-- Modal Container (hidden by default) -->
<div id="trivia-modal" style="position:fixed; inset:0; z-index:50; background:rgba(0,0,0,0.8); display:none; align-items:center; justify-content:center; padding:20px;">
    <div class="trivia-card w-100" style="max-width:400px;">
        
        <!-- Question State -->
        <div id="trivia-state">
            <div style="width:50px;height:50px;background:rgba(56,189,248,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#38bdf8;font-size:24px;">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <h3 style="margin:0 0 8px;font-size:18px;font-weight:800;">Location Unlocked!</h3>
            <p style="font-size:14px;color:rgba(255,255,255,0.7);margin:0 0 20px;line-height:1.4;" id="trivia-question-text"></p>
            
            <div class="trivia-options" id="trivia-options-container"></div>
        </div>

        <!-- Result State -->
        <div id="result-state" style="display:none;">
            <div id="result-icon" style="font-size:48px; margin-bottom:16px;">🎉</div>
            <h3 id="result-title" style="margin:0 0 8px;font-size:20px;font-weight:800;">Correct!</h3>
            <div id="result-points" style="font-size:24px; font-weight:900; color:#fbbf24; margin-bottom:12px;">+150 Points</div>
            <p id="result-message" style="font-size:13px;color:rgba(255,255,255,0.7);margin:0 0 20px;"></p>
            
            <button onclick="history.back()" style="width:100%;padding:14px;border-radius:14px;background:linear-gradient(135deg,#6366f1,#38bdf8);border:none;color:#fff;font-weight:800;font-size:14px;cursor:pointer;">
                Return to Map
            </button>
        </div>

    </div>
</div>

<script>
(function() {
    const backendUrl = window.getBackendUrl ? window.getBackendUrl() : (window.backendUrl || window.location.origin);
    const token = localStorage.getItem('intan_elyu_token') || localStorage.getItem('Intan_Elyu_Token');
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token };
    
    // Get spot ID from URL query param
    const urlParams = new URLSearchParams(window.location.search);
    const spotId = urlParams.get('spot_id');
    
    let userLat = null;
    let userLng = null;
    let currentTriviaId = null;

    // ── 1. Start Camera ────────────────────────────────────────────────────────
    async function initCamera() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            document.getElementById('camera-view').srcObject = stream;
        } catch(e) {
            console.error('Camera access denied', e);
            document.getElementById('ar-status').innerHTML = '<i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> Camera Blocked';
        }
    }

    // ── 2. Track GPS Location ──────────────────────────────────────────────────
    function initGPS() {
        if (!navigator.geolocation) return;
        
        navigator.geolocation.watchPosition(pos => {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;
            
            // Just enable the button if we have GPS. The backend will enforce the 50m geofence.
            document.getElementById('ar-status').innerHTML = '<i class="fa-solid fa-location-crosshairs" style="color:#34d399;"></i> GPS Ready';
            document.getElementById('ar-reticle').classList.add('active');
            document.getElementById('btn-scan').style.display = 'flex';
            document.getElementById('scan-hint').textContent = 'Tap to Check In!';
            
        }, err => {
            console.warn('GPS Error', err);
        }, { enableHighAccuracy: true });
    }

    // ── 3. Trigger Check-in (Fetch Trivia) ─────────────────────────────────────
    window.triggerCheckIn = async function() {
        if (!spotId || !userLat || !userLng) return;
        
        document.getElementById('btn-scan').innerHTML = '<i class="fa-solid fa-spinner spinning"></i>';
        
        try {
            const res = await fetch(`${backendUrl}/api/tourist/points/ar-checkin`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ spot_id: spotId, lat: userLat, lng: userLng })
            });
            
            const data = await res.json();
            document.getElementById('btn-scan').innerHTML = '';
            
            if (data.status === 'error') {
                alert(`Too far! You are ${data.distance_m}m away. Need to be within ${data.required_m}m.`);
                return;
            }
            
            if (data.status === 'success' || data.success) {
                alert(data.message || '🎉 GPS Check-in Verified! Points awarded!');
                history.back();
            } else {
                alert(data.message || '🎉 GPS Check-in Verified! Points awarded!');
                history.back();
            }
        } catch(e) {
            console.error(e);
            document.getElementById('btn-scan').innerHTML = '';
            
            // Low-signal / Offline approach: queue checkin locally for background auto-sync
            const queue = JSON.parse(localStorage.getItem('offline_checkin_queue') || '[]');
            queue.push({
                spot_id: spotId,
                lat: userLat,
                lng: userLng,
                timestamp: new Date().toISOString()
            });
            localStorage.setItem('offline_checkin_queue', JSON.stringify(queue));
            
            alert('📡 Low Signal / Offline Area Detected:\n\nYour GPS check-in was saved locally! Points will sync automatically as soon as internet connection is restored.');
            history.back();
        }
    };

    // ── 4. Show Trivia UI ──────────────────────────────────────────────────────
    function showTrivia(q) {
        currentTriviaId = q.id;
        document.getElementById('trivia-question-text').textContent = q.question;
        
        const optsContainer = document.getElementById('trivia-options-container');
        optsContainer.innerHTML = '';
        
        q.options.forEach((opt, idx) => {
            const btn = document.createElement('button');
            btn.className = 'trivia-btn';
            btn.textContent = opt;
            btn.onclick = () => submitTrivia(idx, btn);
            optsContainer.appendChild(btn);
        });
        
        document.getElementById('trivia-modal').style.display = 'flex';
    }

    // ── 5. Submit Trivia Answer ────────────────────────────────────────────────
    async function submitTrivia(answerIndex, btnElement) {
        // Disable all buttons
        document.querySelectorAll('.trivia-btn').forEach(b => b.disabled = true);
        btnElement.innerHTML = '<i class="fa-solid fa-spinner spinning"></i> ' + btnElement.textContent;
        
        try {
            const res = await fetch(`${backendUrl}/api/tourist/points/ar-checkin`, {
                method: 'POST',
                headers,
                body: JSON.stringify({ 
                    spot_id: spotId, lat: userLat, lng: userLng,
                    trivia_question_id: currentTriviaId,
                    trivia_answer: answerIndex
                })
            });
            
            const data = await res.json();
            
            document.getElementById('trivia-state').style.display = 'none';
            const resState = document.getElementById('result-state');
            resState.style.display = 'block';
            
            if (data.trivia_correct) {
                btnElement.classList.add('correct');
                document.getElementById('result-icon').textContent = '🎉';
                document.getElementById('result-title').textContent = 'Correct!';
                document.getElementById('result-title').style.color = '#34d399';
            } else {
                btnElement.classList.add('wrong');
                document.getElementById('result-icon').textContent = '💡';
                document.getElementById('result-title').textContent = 'Not Quite!';
                document.getElementById('result-title').style.color = '#ef4444';
            }
            
            const ptsEarned = data.points_earned || data.xp_earned || 50;
            const resPtsEl = document.getElementById('result-points') || document.getElementById('result-xp');
            if (resPtsEl) resPtsEl.textContent = `+${ptsEarned} Points`;
            document.getElementById('result-message').innerHTML = 
                (data.fun_fact ? `<strong>Did you know?</strong> ${data.fun_fact}` : data.message);
                
        } catch(e) {
            console.error(e);
            alert('Failed to submit answer.');
        }
    }

    // Run
    initCamera();
    initGPS();
})();
</script>
