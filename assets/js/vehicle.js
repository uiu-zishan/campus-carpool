// assets/js/vehicle.js

document.addEventListener('DOMContentLoaded', () => {
    loadVehicle();

    document.getElementById('vehicleForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        saveVehicle();
    });

    const editBtn = document.getElementById('editVehicleBtn');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            document.getElementById('vehicleForm').style.display = 'block';
            document.getElementById('existingVehicle').style.display = 'none';
        });
    }
});

async function loadVehicle() {
    try {
        const response = await fetch('api/manage_vehicle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get' })
        });
        const data = await response.json();

        if (data.success && data.vehicle) {
            document.getElementById('vehicleInfo').innerText =
                `${data.vehicle.make} ${data.vehicle.model} (${data.vehicle.color}) — ${data.vehicle.license_plate}`;
            document.getElementById('existingVehicle').style.display = 'block';
            document.getElementById('vehicleForm').style.display = 'none';

            // Prefill form for editing
            document.getElementById('make').value = data.vehicle.make;
            document.getElementById('model').value = data.vehicle.model;
            document.getElementById('color').value = data.vehicle.color;
            document.getElementById('year').value = data.vehicle.year || '';
            document.getElementById('licensePlate').value = data.vehicle.license_plate;
            document.getElementById('capacity').value = data.vehicle.capacity;
        }
    } catch (err) {
        console.error(err);
    }
}

async function saveVehicle() {
    const errorEl = document.getElementById('vehicleError');
    errorEl.innerText = '';

    const payload = {
        make: document.getElementById('make').value,
        model: document.getElementById('model').value,
        color: document.getElementById('color').value,
        year: parseInt(document.getElementById('year').value) || null,
        license_plate: document.getElementById('licensePlate').value,
        capacity: parseInt(document.getElementById('capacity').value)
    };

    try {
        const response = await fetch('api/manage_vehicle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (data.success) {
            alert(data.message);
            loadVehicle();
        } else {
            errorEl.innerText = data.message;
        }
    } catch (err) {
        errorEl.innerText = 'Server error';
        console.error(err);
    }
}