// assets/js/dashboard.js
// Load the logged-in user's name
async function loadUserName() {
    try {
        const res = await fetch('api/get_profile.php');
        const data = await res.json();
        if (data.success) {
            document.getElementById('userName').innerText = data.user.full_name;
        } else {
            document.getElementById('userName').innerText = 'Guest';
        }
    } catch (err) {
        document.getElementById('userName').innerText = 'User';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    loadUserName();  
    loadPassengerRides();

    document.getElementById('tabPassenger').addEventListener('click', () => {
        document.getElementById('passengerView').style.display = 'block';
        document.getElementById('driverView').style.display = 'none';
        document.getElementById('tabPassenger').className = 'btn-primary';
        document.getElementById('tabDriver').className = 'btn-secondary';
        loadPassengerRides();
    });

    document.getElementById('tabDriver').addEventListener('click', () => {
        document.getElementById('passengerView').style.display = 'none';
        document.getElementById('driverView').style.display = 'block';
        document.getElementById('tabDriver').className = 'btn-primary';
        document.getElementById('tabPassenger').className = 'btn-secondary';
        loadDriverRides();
    });
});

async function loadPassengerRides() {
    const container = document.getElementById('passengerRides');
    try {
        const response = await fetch('api/get_my_rides.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'booked' })
        });
        const data = await response.json();

        if (!data.success || data.rides.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No bookings yet</h3>
                    <p>Search for a ride to get started.</p>
                </div>`;
            return;
        }

        container.innerHTML = data.rides.map(ride => {
            // Build action buttons based on status
            let actionButtons = '';
            if (ride.booking_status === 'confirmed') {
                actionButtons = `<button class="btn-secondary btn-small" onclick="event.stopPropagation(); openCancelModal(${ride.booking_id})">Cancel</button>`;
            } else if (ride.booking_status === 'completed') {
                actionButtons = `<button class="btn-primary btn-small" onclick="event.stopPropagation(); openRateModal(${ride.ride_id}, ${ride.driver_id}, '${ride.driver_name}')">Rate Driver</button>`;
            }

            return `
            <div class="ride-card" onclick="window.location.href='ride-details.html?id=${ride.ride_id}'">
                <span class="badge badge-${ride.booking_status}">${ride.booking_status}</span>
                <h3>${ride.origin} → ${ride.destination}</h3>
                <p class="meta">Departs: ${new Date(ride.departure_time).toLocaleString()}</p>
                <p class="meta">Driver: ${ride.driver_name} ⭐ ${ride.driver_rating || 'N/A'}</p>
                <div style="margin-top:12px;">
                    ${actionButtons}
                </div>
            </div>`;
        }).join('');
    } catch (err) {
        container.innerHTML = '<p class="error-message">Failed to load rides</p>';
        console.error(err);
    }
}

async function loadDriverRides() {
    const container = document.getElementById('driverRides');
    try {
        const response = await fetch('api/get_my_rides.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: 'posted' })
        });
        const data = await response.json();

        if (!data.success || data.rides.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No posted rides</h3>
                    <p>Post a ride to start driving.</p>
                    <a href="post-ride.html" class="btn-primary" style="display:inline-block; margin-top:16px; text-decoration:none;">Post a Ride</a>
                </div>`;
            return;
        }

        container.innerHTML = data.rides.map(ride => `
            <div class="ride-card">
                <span class="badge badge-${ride.ride_status}">${ride.ride_status}</span>
                <h3>${ride.origin} → ${ride.destination}</h3>
                <p class="meta">Departs: ${new Date(ride.departure_time).toLocaleString()}</p>
                <p class="meta">Seats left: ${ride.seats_available} • Booked: ${ride.confirmed_bookings}</p>
            </div>
        `).join('');
    } catch (err) {
        container.innerHTML = '<p class="error-message">Failed to load rides</p>';
        console.error(err);
    }
}