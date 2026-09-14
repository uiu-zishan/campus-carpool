// assets/js/ride-details.js
let currentRide = null;
let selectedPickup = null;
let selectedDropoff = null;

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const rideId = params.get('id');

    if (!rideId) {
        document.getElementById('rideDetails').innerHTML = '<p class="error-message">No ride specified</p>';
        return;
    }

    loadRideDetails(rideId);
});

async function loadRideDetails(rideId) {
    const container = document.getElementById('rideDetails');
    try {
        const response = await fetch('api/get_ride_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ride_id: rideId })
        });
        const data = await response.json();

        if (!data.success) {
            container.innerHTML = `<p class="error-message">${data.message}</p>`;
            return;
        }

        currentRide = data.ride;
        renderRideDetails(currentRide);
        setupBookingForm(currentRide);
    } catch (err) {
        container.innerHTML = '<p class="error-message">Failed to load ride</p>';
        console.error(err);
    }
}

function renderRideDetails(ride) {
    const container = document.getElementById('rideDetails');
    container.innerHTML = `
        <div class="ride-card" style="cursor:default; padding: 28px;">
            <span class="badge badge-${ride.status}">${ride.status}</span>
            ${ride.is_female_only ? '<span class="badge badge-female">Female only</span>' : ''}
            <h1 style="margin: 12px 0;">${ride.origin} → ${ride.destination}</h1>
            <p class="meta">Departs: ${new Date(ride.departure_time).toLocaleString()}</p>
            <p class="meta">Seats available: ${ride.seats_available}</p>

            <h3 style="margin-top: 24px;">Driver</h3>
            <p>${ride.driver_name} • ⭐ ${ride.driver_rating || 'New'}</p>

            ${ride.make ? `
                <h3 style="margin-top: 20px;">Vehicle</h3>
                <p>${ride.make} ${ride.model} (${ride.color}) — ${ride.license_plate}</p>
            ` : ''}

            <h3 style="margin-top: 24px;">Route</h3>
            <ol style="padding-left: 20px;">
                ${ride.checkpoints.map(cp => `<li>${cp.location_name}</li>`).join('')}
            </ol>

            <button class="btn-primary" id="showBookingBtn" style="margin-top: 24px;">Book This Ride</button>
            <button class="btn-secondary" id="favDriverBtn" data-driver="${ride.driver_id}" style="margin-top: 24px;">❤ Favorite Driver</button>
        </div>
    `;

    // Wire up buttons
    document.getElementById('showBookingBtn').addEventListener('click', () => {
        document.getElementById('bookingForm').style.display = 'block';
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });

    document.getElementById('favDriverBtn').addEventListener('click', async (e) => {
        const driverId = e.target.dataset.driver;
        const res = await fetch('api/favorite_driver.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ driver_id: driverId })
        });
        const result = await res.json();
        alert(result.message || (result.action === 'added' ? 'Added to favorites' : 'Removed from favorites'));
    });
}

function setupBookingForm(ride) {
    const pickupSelect = document.getElementById('pickupCheckpoint');
    const dropoffSelect = document.getElementById('dropoffCheckpoint');

    ride.checkpoints.forEach(cp => {
        pickupSelect.innerHTML += `<option value="${cp.id}">${cp.sequence_number}. ${cp.location_name}</option>`;
        dropoffSelect.innerHTML += `<option value="${cp.id}">${cp.sequence_number}. ${cp.location_name}</option>`;
    });

    document.getElementById('confirmBookingBtn').addEventListener('click', async () => {
        const errorEl = document.getElementById('bookingError');
        errorEl.innerText = '';

        const response = await fetch('api/book_ride.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ride_id: ride.id,
                pickup_checkpoint_id: pickupSelect.value,
                dropoff_checkpoint_id: dropoffSelect.value
            })
        });
        const data = await response.json();

        if (data.success) {
            alert('Booking confirmed! Redirecting to dashboard.');
            window.location.href = 'dashboard.html';
        } else {
            errorEl.innerText = data.message;
        }
    });
}