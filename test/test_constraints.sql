-- P4: Database Constraint Testing (Week 1)
-- These queries TRY to break the database to prove our constraints work.

-- TEST 1: Try to insert a duplicate email (Should FAIL due to UNIQUE constraint)
INSERT INTO users (email, password_hash, full_name, gender, role) 
VALUES ('mmunna2330175@bscse.uiu.ac.bd', 'any_hash', 'Duplicate Test', 'M', 'student');

-- TEST 2: Try to book a non-existent ride (Should FAIL due to FOREIGN KEY constraint)
INSERT INTO bookings (ride_id, passenger_id, pickup_checkpoint_id, dropoff_checkpoint_id, pickup_sequence, dropoff_sequence) 
VALUES (99999, 1, 1, 2, 1, 2);

-- TEST 3: Try to insert an invalid rating score of 10 (Should FAIL due to CHECK constraint)
INSERT INTO ratings (ride_id, rater_id, ratee_id, score, comment) 
VALUES (1, 1, 2, 10, 'Invalid score test');