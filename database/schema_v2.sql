-- ==========================================================
-- Campus Carpool - Schema v2
-- Week 2 Evolution: Adds VEHICLES, PASSWORD_RESETS, 
-- triggers for aggregates, and indexes for search performance.
-- P1: Database Architect
-- ==========================================================

USE campus_carpool;

-- ==========================================================
-- 1. VEHICLES TABLE
-- Stores vehicle information for each driver.
-- A driver can have multiple vehicles (e.g., a car and a bike).
-- ==========================================================
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    capacity INT NOT NULL CHECK (capacity > 0),
    year INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================================
-- 2. PASSWORD_RESETS TABLE
-- Enables secure token-based password recovery.
-- Tokens expire after 1 hour and can only be used once.
-- ==========================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ==========================================================
-- 3. TRIGGERS FOR AGGREGATE MAINTENANCE
-- These auto-update cached aggregates when ratings,
-- cancellations, or bookings change.
-- ==========================================================

DELIMITER //

-- 3a. Trigger: after a new rating is inserted,
-- recalculate the ratee's avg_rating.
DROP TRIGGER IF EXISTS after_rating_insert //
CREATE TRIGGER after_rating_insert
AFTER INSERT ON ratings
FOR EACH ROW
BEGIN
    UPDATE users
    SET avg_rating = (
        SELECT ROUND(AVG(score), 1)
        FROM ratings
        WHERE ratee_id = NEW.ratee_id
    )
    WHERE id = NEW.ratee_id;
END //

-- 3b. Trigger: after a cancellation with penalty,
-- increment the canceler's penalty_points by 10.
DROP TRIGGER IF EXISTS after_cancellation_insert //
CREATE TRIGGER after_cancellation_insert
AFTER INSERT ON cancellations
FOR EACH ROW
BEGIN
    IF NEW.penalty_applied = TRUE THEN
        UPDATE users
        SET penalty_points = penalty_points + 10
        WHERE id = NEW.cancelled_by;
    END IF;
END //

-- 3c. Trigger: after a new booking, decrement seats_available.
DROP TRIGGER IF EXISTS after_booking_insert //
CREATE TRIGGER after_booking_insert
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    IF NEW.status = 'confirmed' THEN
        UPDATE rides
        SET seats_available = seats_available - 1
        WHERE id = NEW.ride_id;
    END IF;
END //

-- 3d. Trigger: after a booking is cancelled, increment seats back.
DROP TRIGGER IF EXISTS after_booking_cancel //
CREATE TRIGGER after_booking_cancel
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelled' AND OLD.status = 'confirmed' THEN
        UPDATE rides
        SET seats_available = seats_available + 1
        WHERE id = NEW.ride_id;
    END IF;
END //

DELIMITER ;

-- ==========================================================
-- 4. INDEXES FOR SEARCH PERFORMANCE
-- Speeds up the most common queries: search by origin,
-- destination, date, and status.
-- ==========================================================

CREATE INDEX idx_rides_origin ON rides(origin);
CREATE INDEX idx_rides_destination ON rides(destination);
CREATE INDEX idx_rides_departure ON rides(departure_time);
CREATE INDEX idx_rides_status ON rides(status);
CREATE INDEX idx_bookings_passenger ON bookings(passenger_id);
CREATE INDEX idx_bookings_ride ON bookings(ride_id);
CREATE INDEX idx_ratings_ratee ON ratings(ratee_id);
CREATE INDEX idx_password_resets_token ON password_resets(token);