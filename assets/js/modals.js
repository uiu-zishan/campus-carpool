// assets/js/modals.js
// Shared modal handlers: Cancel, Rate, Favorite

// ============ MODAL OPEN/CLOSE HELPERS ============
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Close modal when clicking the overlay (outside the modal box)
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// Close modal when clicking any [data-close-modal] button
document.addEventListener('click', (e) => {
    if (e.target.hasAttribute('data-close-modal')) {
        const overlay = e.target.closest('.modal-overlay');
        if (overlay) overlay.classList.remove('active');
    }
});

// ============ CANCEL MODAL ============
let cancelBookingId = null;

function openCancelModal(bookingId) {
    cancelBookingId = bookingId;
    openModal('cancelModal');
}

document.addEventListener('DOMContentLoaded', () => {
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', async () => {
            if (!cancelBookingId) return;

            try {
                const res = await fetch('api/cancel_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ booking_id: cancelBookingId })
                });
                const data = await res.json();

                closeModal('cancelModal');

                if (data.success) {
                    alert(data.message);
                    // Reload the page to refresh the ride list
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Server error. Please try again.');
            }
        });
    }
});

// ============ RATE MODAL ============
let rateRideId = null;
let rateRateeId = null;
let selectedScore = 0;

function openRateModal(rideId, rateeId, rateeName) {
    rateRideId = rideId;
    rateRateeId = rateeId;
    selectedScore = 0;
    document.getElementById('rateeName').innerText = rateeName || 'the other party';
    document.getElementById('ratingComment').value = '';
    document.querySelectorAll('#starRating .star').forEach(s => s.classList.remove('active'));
    openModal('rateModal');
}

document.addEventListener('DOMContentLoaded', () => {
    // Star click handler
    const stars = document.querySelectorAll('#starRating .star');
    stars.forEach(star => {
        star.addEventListener('click', () => {
            selectedScore = parseInt(star.dataset.score);
            stars.forEach(s => {
                const score = parseInt(s.dataset.score);
                s.classList.toggle('active', score <= selectedScore);
            });
        });
    });

    // Submit rating
    const submitRatingBtn = document.getElementById('submitRatingBtn');
    if (submitRatingBtn) {
        submitRatingBtn.addEventListener('click', async () => {
            if (!selectedScore) {
                alert('Please select a star rating first');
                return;
            }

            try {
                const res = await fetch('api/submit_rating.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ride_id: rateRideId,
                        ratee_id: rateRateeId,
                        score: selectedScore,
                        comment: document.getElementById('ratingComment').value
                    })
                });
                const data = await res.json();
                closeModal('rateModal');

                if (data.success) {
                    alert('Rating submitted!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Server error. Please try again.');
            }
        });
    }
});

// ============ FAVORITE MODAL ============
let favoriteDriverId = null;

function openFavoriteModal(driverId, driverName) {
    favoriteDriverId = driverId;
    document.getElementById('favoriteDriverName').innerText = driverName || 'this driver';
    openModal('favoriteModal');
}

document.addEventListener('DOMContentLoaded', () => {
    const confirmFavoriteBtn = document.getElementById('confirmFavoriteBtn');
    if (confirmFavoriteBtn) {
        confirmFavoriteBtn.addEventListener('click', async () => {
            if (!favoriteDriverId) return;

            try {
                const res = await fetch('api/favorite_driver.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ driver_id: favoriteDriverId })
                });
                const data = await res.json();
                closeModal('favoriteModal');

                if (data.success) {
                    const action = data.action === 'added' ? 'Added to' : 'Removed from';
                    alert(action + ' favorites');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('Server error. Please try again.');
            }
        });
    }
});