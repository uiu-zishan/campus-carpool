-- P2 Backend: Week 2 API Query Reference
-- These are the exact DML queries used in the Week 2 PHP endpoints.

-- ========== CREATE_RIDE.PHP ==========
INSERT INTO rides (driver_id, origin, destination, departure_time, seats_available, is_female_only, status)
VALUES (?, ?, ?, ?, ?, ?, 'scheduled');

INSERT INTO checkpoints (ride_id, sequence_number, location_name, latitude, longitude)
VALUES (?, ?, ?, ?, ?);

-- ========== SEARCH_RIDES.PHP ==========
SELECT r.id, r.origin, r.destination, r.departure_time, r.seats_available,
       u.full_name AS driver_name, u.avg_rating AS driver_rating
FROM rides r
JOIN users u ON r.driver_id = u.id
WHERE r.status = 'scheduled' AND r.seats_available > 0
ORDER BY r.departure_time ASC;

-- ========== BOOK_RIDE.PHP (CRITICAL - ROW LOCKING) ==========
SELECT id, driver_id, seats_available, status FROM rides WHERE id = ? FOR UPDATE;

INSERT INTO bookings (ride_id, passenger_id, pickup_checkpoint_id, dropoff_checkpoint_id,
                      pickup_sequence, dropoff_sequence, status)
VALUES (?, ?, ?, ?, ?, ?, 'confirmed');

-- ========== CANCEL_BOOKING.PHP ==========
UPDATE bookings SET status='cancelled' WHERE id=?;

INSERT INTO cancellations (booking_id, cancelled_by, penalty_applied) VALUES (?, ?, ?);

-- ========== SUBMIT_RATING.PHP ==========
INSERT INTO ratings (ride_id, rater_id, ratee_id, score, comment) VALUES (?, ?, ?, ?, ?);

-- ========== MANAGE_VEHICLE.PHP ==========
INSERT INTO vehicles (driver_id, make, model, color, license_plate, capacity, year)
VALUES (?, ?, ?, ?, ?, ?, ?);

UPDATE vehicles SET make=?, model=?, color=?, license_plate=?, capacity=?, year=?
WHERE driver_id=?;

-- ========== FORGOT_PASSWORD.PHP ==========
INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?);

-- ========== RESET_PASSWORD.PHP ==========
UPDATE users SET password_hash = ? WHERE id = ?;
UPDATE password_resets SET used = TRUE WHERE id = ?;