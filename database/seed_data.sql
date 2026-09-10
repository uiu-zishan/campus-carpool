-- Campus Carpool Seed Data
-- P1: Database Architect

USE campus_carpool;

-- Insert Test Users (Using real student email for testing)
INSERT INTO users (email, password_hash, full_name, gender, role, avg_rating, penalty_points) 
VALUES 
('mmunna2330175@bscse.uiu.ac.bd', 'dummy_hash_123', 'MD MUNNA', 'M', 'student', 0, 0),
('driver_test@campus.com', 'dummy_hash_456', 'Driver Test', 'F', 'staff', 4.5, 0),
('passenger_test@campus.com', 'dummy_hash_789', 'Passenger Test', 'M', 'student', 0, 0);

-- Insert Rides (Driver ID 2 is Driver Test)
INSERT INTO rides (driver_id, origin, destination, departure_time, seats_available, is_female_only, recurring_pattern, status) 
VALUES 
(2, 'Main Gate', 'Library', '2026-09-15 09:00:00', 3, false, '["Monday","Wednesday"]', 'scheduled'),
(2, 'Hostel A', 'City Mall', '2026-09-16 14:00:00', 2, true, '["Friday"]', 'scheduled');

-- Insert Checkpoints for Ride 1
INSERT INTO checkpoints (ride_id, sequence_number, location_name, latitude, longitude) 
VALUES 
(1, 1, 'Main Gate', 23.8103, 90.4125),
(1, 2, 'Academic Building', 23.8110, 90.4130),
(1, 3, 'Library', 23.8120, 90.4140);

-- Insert Checkpoints for Ride 2
INSERT INTO checkpoints (ride_id, sequence_number, location_name, latitude, longitude) 
VALUES 
(2, 1, 'Hostel A', 23.8150, 90.4200),
(2, 2, 'City Mall', 23.8200, 90.4250);

-- Insert a Test Booking (Passenger ID 1 books Ride 1)
INSERT INTO bookings (ride_id, passenger_id, pickup_checkpoint_id, dropoff_checkpoint_id, pickup_sequence, dropoff_sequence, status) 
VALUES 
(1, 1, 1, 3, 1, 3, 'confirmed');

-- Insert a Test Rating
INSERT INTO ratings (ride_id, rater_id, ratee_id, score, comment) 
VALUES 
(1, 1, 2, 5, 'Excellent driver, very punctual!');

-- Insert a Test Favorite (Passenger 1 favorites Driver 2)
INSERT INTO favorites (user_id, driver_id) 
VALUES 
(1, 2);