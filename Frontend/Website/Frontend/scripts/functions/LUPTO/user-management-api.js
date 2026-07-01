/**
 * LUPTO User Management API Functions
 * Uses centralized API_CONFIG for real-time database operations
 */

const LUPTO_API = window.API_CONFIG?.LUPTO || 'http://localhost:8000/api/lupto';

export async function fetchUsersData() {
    try {
        const data = await window.API_CONFIG.fetch(`${LUPTO_API}/users`);
        return data;
    } catch (err) {
        console.error('[User Management] fetchUsersData error:', err);
        throw err;
    }
}

export async function updateUser(id, role, status) {
    try {
        const data = await window.API_CONFIG.put(`${LUPTO_API}/users/${id}`, { role, status });
        return data;
    } catch (err) {
        console.error('[User Management] updateUser error:', err);
        throw err;
    }
}

export async function resetPassword(id, password) {
    try {
        const data = await window.API_CONFIG.patch(`${LUPTO_API}/users/${id}/password`, { password });
        return data;
    } catch (err) {
        console.error('[User Management] resetPassword error:', err);
        throw err;
    }
}

// Real-time refresh for user table
export async function refreshUsers() {
    try {
        return await fetchUsersData();
    } catch (err) {
        console.error('[User Management] refreshUsers error:', err);
        return null;
    }
}
