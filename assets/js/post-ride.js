// assets/js/post-ride.js

let checkpointCount = 0;

document.addEventListener('DOMContentLoaded', async () => {
    // BUSINESS RULE: Check if user has a registered vehicle first
    try {
        const res = await fetch('api/manage_vehicle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get' })
        });
        const data = await res.json();

        if (!data.vehicle) {
            // No vehicle — show notice, hide form
            document.getElementById('noVehicleNotice').style.display = 'block';
            document.getElementById('postRideForm').style.display = 'none';
            return;
        }
    } catch (err) {
        console.error('Vehicle check failed:', err);
    }

    // User has a vehicle — proceed normally
    addCheckpointRow();
    addCheckpointRow();

    document.getElementById('addCheckpointBtn').addEventListener('click', addCheckpointRow);

    document.getElementById('postRideForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        submitRide();
    });
});

function addCheckpointRow() {
    checkpointCount++;
    const container = document.getElementById('checkpointsContainer');
    const row = document.createElement('div');
    row.className = 'checkpoint-row';
    row.innerHTML = `
        <input type="text" placeholder="Location name (e.g., Main Gate)" class="checkpoint-input" required>
        <button type="button" class="remove-btn">✕</button>
    `;
    row.querySelector('.remove-btn').addEventListener('click', () => row.remove());
    container.appendChild(row);
}

async function submitRide() {
    const errorEl = document.getElementById('postError');
    errorEl.innerText = '';

    const checkpointInputs = document.querySelectorAll('.checkpoint-input');
    if (checkpointInputs.length < 2) {
        errorEl.innerText = 'At least 2 checkpoints required';
        return;
    }

    const checkpoints = Array.from(checkpointInputs).map((input, idx) => ({
        sequence_number: idx + 1,
        location_name: input.value,
        latitude: 23.8103 + (idx * 0.001), // Placeholder coordinates
        longitude: 90.4125 + (idx * 0.001)
    }));

    const payload = {
        origin: checkpoints[0].location_name,
        destination: checkpoints[checkpoints.length - 1].location_name,
        departure_time: document.getElementById('departureTime').value.replace('T', ' ') + ':00',
        seats_available: parseInt(document.getElementById('seatsAvailable').value),
        is_female_only: document.getElementById('isFemaleOnly').checked,
        checkpoints: checkpoints
    };

    try {
        const response = await fetch('api/create_ride.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (data.success) {
            alert('Ride posted successfully!');
            window.location.href = 'dashboard.html';
        } else {
            errorEl.innerText = data.message;
        }
    } catch (err) {
        errorEl.innerText = 'Server error. Please try again.';
        console.error(err);
    }
}