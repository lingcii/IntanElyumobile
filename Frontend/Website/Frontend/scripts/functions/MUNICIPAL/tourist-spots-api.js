// MUNICIPAL Tourist Spots API
// Classic script (no ES module syntax) — attaches everything to window.TouristSpotsAPI
// so it works even if the server doesn't send correct MIME types for module scripts.
(function (global) {
    const API_BASE = window.API_CONFIG?.MUNICIPAL ? (window.API_CONFIG.MUNICIPAL + '/tourist-spots') : 'http://' + (window.location.hostname || '127.0.0.1') + ':8000/api/municipal/tourist-spots';

    async function fetchAPI(action, method = 'GET', data = null) {
        // Map legacy actions to REST paths
        const actionMap = {
            'get_municipality_info': `${API_BASE}`,
            'get_municipality_spots': `${API_BASE}`,
            'upload_image': `${API_BASE}/upload-image`,
            'create_spot': `${API_BASE}`,
            'update_spot': data && data.id ? `${API_BASE}/${data.id}` : `${API_BASE}`,
            'delete_spot': data && data.id ? `${API_BASE}/${data.id}` : `${API_BASE}`,
        };

        // For get_spot with id appended
        let url;
        if (action.startsWith('get_spot&id=')) {
            const id = action.split('=')[1];
            url = `${API_BASE}/${id}`;
            method = 'GET';
            data = null;
        } else {
            url = actionMap[action] || `${API_BASE}/${action.replace(/_/g, '-')}`;
        }

        const methodMap = { 'create_spot': 'POST', 'update_spot': 'PUT', 'delete_spot': 'DELETE' };
        const resolvedMethod = method !== 'GET' ? method : (methodMap[action] || 'GET');

        const options = {
            method: resolvedMethod,
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        };
        if (data && resolvedMethod !== 'GET') {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
        const response = await fetch(url, options);
        if (!response.ok) {
            let errorMsg = `HTTP error! status: ${response.status}`;
            try {
                const errBody = await response.json();
                if (errBody && errBody.error) errorMsg = errBody.error;
            } catch (_) { }
            throw new Error(errorMsg);
        }
        return await response.json();
    }

    // Upload a spot image (multipart form-data)
    async function uploadSpotImage(file) {
        const formData = new FormData();
        formData.append('image', file);
        const response = await fetch(`${API_BASE}/upload-image`, {
            method: 'POST',
            credentials: 'include',
            body: formData
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Image upload failed');
        return result.photo_url;
    }

    // Get municipality info and stats
    async function getMunicipalityInfo() {
        return await fetchAPI('get_municipality_info');
    }

    // Get municipality tourist spots
    async function getMunicipalitySpots() {
        return await fetchAPI('get_municipality_spots');
    }

    // Get single spot
    async function getSpot(id) {
        return await fetchAPI(`get_spot&id=${id}`);
    }

    // Create new spot
    async function createSpot(data) {
        return await fetchAPI('create_spot', 'POST', data);
    }

    // Update existing spot
    async function updateSpot(data) {
        return await fetchAPI('update_spot', 'POST', data);
    }

    // Delete spot
    async function deleteSpot(id) {
        return await fetchAPI('delete_spot', 'POST', { id });
    }

    global.TouristSpotsAPI = {
        getMunicipalityInfo,
        getMunicipalitySpots,
        getSpot,
        createSpot,
        updateSpot,
        deleteSpot,
        uploadSpotImage
    };
})(window);