// MUNICIPAL User Management API Functions

const MTO_API = window.API_CONFIG?.MUNICIPAL || 'http://' + (window.location.hostname || '127.0.0.1') + ':8000/api/municipal';

export async function fetchUsersData() {
    try {
        const response = await fetch(`${MTO_API}/users`, { credentials: 'include' });
        if (!response.ok) throw new Error('Failed to fetch users');
        return await response.json();
    } catch (err) {
        console.error('Error fetching users:', err);
        throw err;
    }
}

export async function updateUser(id, role, status) {
    try {
        const response = await fetch(`${MTO_API}/users/${id}`, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role, status })
        });
        return await response.json();
    } catch (err) {
        console.error(err);
        throw err;
    }
}

export async function resetPassword(id, password) {
    try {
        const response = await fetch(`${MTO_API}/users/${id}/password`, {
            method: 'PATCH',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password })
        });
        return await response.json();
    } catch (err) {
        console.error(err);
        throw err;
    }
}
