// assets/js/search.js

document.addEventListener('DOMContentLoaded', () => {
    performSearch(); // Initial load

    document.getElementById('searchBtn').addEventListener('click', performSearch);

    document.getElementById('clearBtn').addEventListener('click', () => {
        document.getElementById('filterOrigin').value = '';
        document.getElementById('filterDestination').value = '';
        document.getElementById('filterDate').value = '';
        document.getElementById('filterFemaleOnly').checked = false;
        performSearch();
    });
});

async function performSearch() {
    const container = document.getElementById('searchResults');
    container.innerHTML = '<div class="loading"><div class="spinner"></div>Searching...</div>';

    const payload = {
        origin: document.getElementById('filterOrigin').value || null,
        destination: document.getElementById('filterDestination').value || null,
        date: document.getElementById('filterDate').value || null,
        female_only: document.getElementById('filterFemaleOnly').checked
    };

    try {
        const response = await fetch('api/search_rides.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (!data.success || data.rides.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No rides found</h3>
                    <p>Try adjusting your filters.</p>
                </div>`;
            return;
        }

        container.innerHTML = data.rides.map(ride => `
            <div class="ride-card" onclick="window.location.href='ride-details.html?id=${ride.id}'">
                ${ride.is_female_only ? '<span class="badge badge-female">Female only</span>' : ''}
                <h3>${ride.origin} → ${ride.destination}</h3>
                <p class="meta">Departs: ${new Date(ride.departure_time).toLocaleString()}</p>
                <p class="meta">${ride.seats_available} seats available</p>
                <div class="driver">
                    <span>${ride.driver_name}</span>
                    <span>⭐ ${ride.driver_rating || 'New'}</span>
                </div>
            </div>
        `).join('');
    } catch (err) {
        container.innerHTML = '<p class="error-message">Failed to search</p>';
        console.error(err);
    }
}