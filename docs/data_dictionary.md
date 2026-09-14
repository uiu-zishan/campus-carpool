# Campus Carpool - Frontend Data Dictionary (P3 - Week 1)

## Table: USERS
Columns needed for LOGIN form:
- `email` (VARCHAR) -> HTML input id="email"
- `password_hash` (VARCHAR) -> HTML input id="password"

Columns needed for DASHBOARD display:
- `full_name` (VARCHAR) -> "Welcome, {full_name}"
- `role` (ENUM) -> Student or Staff badge
- `avg_rating` (DECIMAL) -> "Rating: 4.5"

## Table: RIDES
Columns needed for RIDE CARDS:
- `origin` (VARCHAR) -> "From: Main Gate"
- `destination` (VARCHAR) -> "To: Library"
- `departure_time` (TIMESTAMP) -> "Departs: 09:00 AM"
- `seats_available` (INT) -> "3 Seats Left"
- `is_female_only` (BOOLEAN) -> Pink "Female Only" badge

## Week 2 Updates (P3 - Data Dictionary v2)

### Page: Dashboard (My Rides)
Columns needed:
- From `rides`: id, origin, destination, departure_time, status, seats_available
- From `bookings`: id, ride_id, status, booked_at
- From `users` (driver): full_name, avg_rating

**Complex query for P2:**
-- Passenger's booked rides
SELECT r.id, r.origin, r.destination, r.departure_time, r.status, 
       u.full_name AS driver_name, u.avg_rating AS driver_rating,
       b.id AS booking_id, b.status AS booking_status
FROM bookings b
JOIN rides r ON b.ride_id = r.id
JOIN users u ON r.driver_id = u.id
WHERE b.passenger_id = ?
ORDER BY r.departure_time DESC;

-- Driver's posted rides
SELECT r.id, r.origin, r.destination, r.departure_time, r.status,
       r.seats_available,
       (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'confirmed') AS confirmed_bookings
FROM rides r
WHERE r.driver_id = ?
ORDER BY r.departure_time DESC;

### Page: Search Rides
Columns needed:
- From `rides`: id, origin, destination, departure_time, seats_available, is_female_only
- From `users`: full_name (driver), avg_rating (driver)

**Complex query for P2:**
SELECT r.id, r.origin, r.destination, r.departure_time, r.seats_available, r.is_female_only,
       u.full_name AS driver_name, u.avg_rating AS driver_rating
FROM rides r
JOIN users u ON r.driver_id = u.id
WHERE r.status = 'scheduled'
  AND r.seats_available > 0
  AND (? IS NULL OR r.origin LIKE CONCAT('%', ?, '%'))
  AND (? IS NULL OR r.destination LIKE CONCAT('%', ?, '%'))
  AND (? IS NULL OR DATE(r.departure_time) = ?)
  AND (? = 0 OR r.is_female_only = 0 OR ? = u.gender)
ORDER BY r.departure_time ASC;

### Page: Ride Details
Columns needed:
- From `rides`: all columns
- From `users` (driver): full_name, avg_rating, gender
- From `checkpoints`: sequence_number, location_name, latitude, longitude
- From `vehicles` (driver's car): make, model, color, license_plate

**Complex query for P2:**
SELECT r.*, u.full_name AS driver_name, u.avg_rating AS driver_rating,
       v.make, v.model, v.color, v.license_plate
FROM rides r
JOIN users u ON r.driver_id = u.id
LEFT JOIN vehicles v ON v.driver_id = u.id
WHERE r.id = ?;

-- Checkpoints for the ride
SELECT sequence_number, location_name, latitude, longitude
FROM checkpoints
WHERE ride_id = ?
ORDER BY sequence_number ASC;

### Page: Profile
Columns needed:
- From `users`: id, email, full_name, gender, role, avg_rating, penalty_points, created_at

**Complex query for P2:**
SELECT id, email, full_name, gender, role, avg_rating, penalty_points, created_at
FROM users
WHERE id = ?;

-- Recent ratings received
SELECT rt.score, rt.comment, rt.created_at, u.full_name AS rater_name
FROM ratings rt
JOIN users u ON rt.rater_id = u.id
WHERE rt.ratee_id = ?
ORDER BY rt.created_at DESC
LIMIT 5;

### Page: Vehicle Management
Columns needed:
- From `vehicles`: id, make, model, color, license_plate, capacity, year

**Complex query for P2:**
SELECT id, make, model, color, license_plate, capacity, year
FROM vehicles
WHERE driver_id = ?;

### Page: Favorites Modal
Columns needed:
- From `favorites`: id
- From `users` (driver): id, full_name, avg_rating

**Complex query for P2:**
SELECT u.id, u.full_name, u.avg_rating
FROM favorites f
JOIN users u ON f.driver_id = u.id
WHERE f.user_id = ?;

### Page: Rate Modal
Columns needed:
- From `rides`: id
- From `users`: id, full_name

**Complex query for P2:**
-- Who should be rated? (the other party in the ride)
SELECT u.id, u.full_name, u.role
FROM users u
WHERE u.id IN (
    SELECT driver_id FROM rides WHERE id = ?
    UNION
    SELECT passenger_id FROM bookings WHERE ride_id = ? AND passenger_id != ?
);