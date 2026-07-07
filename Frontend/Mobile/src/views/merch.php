<?php
$pageTitle = 'Official Merch';
$backRoute = 'dashboard';
?>

<!-- Include Header Component -->
<?php include __DIR__ . '/../components/header.php'; ?>

<div class="merch-page-container has-header animate-fade-in">
    <!-- Hero Section -->
    <div class="merch-hero">
        <h2>Intan Elyu Gear</h2>
        <p>Exchange your hard-earned XP for official merch! Reserve online and claim your rewards at Mabanag Hall.</p>
    </div>

    <!-- Category Filters -->
    <div class="merch-categories" id="merch-categories">
        <button class="merch-category-btn active" onclick="filterMerch('All')">All Items</button>
        <button class="merch-category-btn" onclick="filterMerch('Apparel')">Apparel</button>
        <button class="merch-category-btn" onclick="filterMerch('Accessories')">Accessories</button>
        <button class="merch-category-btn" onclick="filterMerch('Souvenirs')">Souvenirs</button>
    </div>

    <!-- Merchandise Grid -->
    <div class="merch-grid" id="merch-grid">
        <!-- Rendered via JS -->
    </div>
</div>

<!-- Modal / Toast for Reservation Success -->
<div id="reservation-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:999; justify-content:center; align-items:center;">
    <div style="background:var(--glass-bg); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); border:1px solid var(--glass-border); border-radius:24px; padding:28px 24px; width:90%; max-width:380px; box-shadow:0 20px 40px rgba(0,0,0,0.3); text-align:center;">
        <div style="font-size:48px; margin-bottom:12px; color:#34c759;"><i class="fa-solid fa-circle-check"></i></div>
        <h3 style="margin:0 0 8px;">Reward Claimed!</h3>
        <p style="font-size:14px; color:#8E8E93; margin-bottom:24px; line-height:1.5;">Your item has been reserved using your XP. Please proceed to <strong>Mabanag Hall, Provincial Capitol</strong> to claim your reward.</p>
        
        <button class="btn-primary" style="width:100%; padding:16px; margin-bottom:12px; font-size:15px;" onclick="document.getElementById('reservation-modal').style.display='none'">
            Awesome!
        </button>
    </div>
</div>

<script>
(function() {
    let backendUrl = window.backendUrl || 'http://localhost:8000';
    let allMerch = [];

    async function fetchMerchData() {
        try {
            const response = await fetch(backendUrl + '/api/tourist/merch', {
                headers: {
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                allMerch = result.data || [];
                renderMerch('All');
            } else {
                console.error('Failed to fetch merch', response.status);
                document.getElementById('merch-grid').innerHTML = '<p style="grid-column: span 2; text-align: center; color: #ef4444; padding: 20px;">Failed to load items.</p>';
            }
        } catch (error) {
            console.error('Error fetching merch', error);
        }
    }

    function renderMerch(filterCategory = 'All') {
        const grid = document.getElementById('merch-grid');
        grid.innerHTML = '';
        
        let items = allMerch;
        if (filterCategory !== 'All') {
            items = allMerch.filter(item => item.category === filterCategory);
        }
        
        if (items.length === 0) {
            grid.innerHTML = '<p style="grid-column: span 2; text-align: center; color: #94a3b8; padding: 20px;">No items found in this category.</p>';
            return;
        }
        
        items.forEach(item => {
            const card = document.createElement('div');
            card.className = 'merch-card animate-slide-up';
            
            let badgeHtml = '';
            if (item.badge) {
                badgeHtml = `<div class="merch-badge">${item.badge}</div>`;
            }
            
            // Handle images (absolute url or local asset)
            let imgUrl = item.image;
            if (imgUrl && !imgUrl.startsWith('http')) {
                imgUrl = backendUrl + '/' + imgUrl;
            } else if (!imgUrl) {
                imgUrl = 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80'; // fallback
            }
            
            card.innerHTML = `
                <div class="merch-image-container">
                    ${badgeHtml}
                    <img src="${imgUrl}" alt="${item.title}" loading="lazy">
                </div>
                <div class="merch-info">
                    <h3 class="merch-title">${item.title}</h3>
                    <span class="merch-category">${item.category}</span>
                    <div class="merch-price-row">
                        <span class="merch-price"><i class="fa-solid fa-star" style="color:#f59e0b; font-size:12px;"></i> ${item.price_xp} XP</span>
                        <div class="merch-reserve-btn" onclick="reserveItem(${item.id})">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                </div>
            `;
            
            grid.appendChild(card);
        });
    }

    window.filterMerch = function(category) {
        const buttons = document.querySelectorAll('.merch-category-btn');
        buttons.forEach(btn => {
            if (btn.textContent.trim() === category || (category === 'All' && btn.textContent.trim() === 'All Items')) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        
        renderMerch(category);
    };

    window.reserveItem = async function(id) {
        // Basic loading feedback
        const btn = event.currentTarget;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        btn.style.pointerEvents = 'none';
        
        try {
            const response = await fetch(backendUrl + '/api/tourist/merch/reserve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': 'Bearer ' + localStorage.getItem('intan_elyu_token')
                },
                body: JSON.stringify({ merchandise_id: id })
            });
            
            const result = await response.json();
            
            btn.innerHTML = oldHtml;
            btn.style.pointerEvents = 'auto';
            
            if (response.ok) {
                // Update auth user XP locally
                let authUser = JSON.parse(localStorage.getItem('auth_user') || '{}');
                if(authUser) {
                    authUser.xp = result.new_xp;
                    localStorage.setItem('auth_user', JSON.stringify(authUser));
                }
                
                // Refresh merch list (stock changed)
                fetchMerchData();
                
                // Show modal
                document.getElementById('reservation-modal').style.display = 'flex';
            } else {
                alert('Error: ' + (result.message || 'Could not reserve item.'));
            }
        } catch (error) {
            console.error('Reserve error', error);
            btn.innerHTML = oldHtml;
            btn.style.pointerEvents = 'auto';
            alert('An error occurred during reservation.');
        }
    };

    fetchMerchData();
})();
</script>
