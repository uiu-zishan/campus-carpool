// assets/js/profile.js

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('api/get_my_rides.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'profile' }) // Add this type to P2's API later
        });
        // For now, fall back to a user info endpoint:
        loadProfile();
    } catch (err) {
        console.error(err);
    }
});

async function loadProfile() {
    // Temporary: fetch from your existing endpoints. Ideally add a get_profile.php API.
    const container = document.getElementById('recentRatings');
    container.innerHTML = '<p class="meta">Profile data will appear once the get_profile API is added.</p>';

    // If P2 added a profile endpoint, call it here. Otherwise hardcode from session.
    // Placeholder:
    document.getElementById('profileName').innerText = '—';
    document.getElementById('profileRole').innerText = '—';
    document.getElementById('profileRating').innerText = '—';
    document.getElementById('profilePenalty').innerText = '—';
}