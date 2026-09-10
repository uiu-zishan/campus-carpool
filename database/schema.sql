-- Campus Carpool Database Schema
-- P1: Database Architect

CREATE DATABASE IF NOT EXISTS campus_carpool;
USE campus_carpool;

-- 1. USERS (Stores both drivers and passengers)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    role ENUM('student', 'staff') NOT NULL,
    avg_rating DECIMAL(2,1) DEFAULT 0.0,
    penalty_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. RIDES
CREATE TABLE rides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    origin VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    departure_time TIMESTAMP NOT NULL,
    seats_available INT NOT NULL CHECK (seats_available >= 0),
    is_female_only BOOLEAN DEFAULT FALSE,
    recurring_pattern JSON,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- 3. CHECKPOINTS
CREATE TABLE checkpoints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    sequence_number INT NOT NULL,
    location_name TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE CASCADE
);

-- 4. BOOKINGS (Contains intentional denormalization for performance)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    passenger_id INT NOT NULL,
    pickup_checkpoint_id INT NOT NULL,
    dropoff_checkpoint_id INT NOT NULL,
    pickup_sequence INT NOT NULL,
    dropoff_sequence INT NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE RESTRICT,
    FOREIGN KEY (passenger_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (pickup_checkpoint_id) REFERENCES checkpoints(id),
    FOREIGN KEY (dropoff_checkpoint_id) REFERENCES checkpoints(id)
);

-- 5. RATINGS (Linked to ride and users, NOT bookings)
CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ride_id INT NOT NULL,
    rater_id INT NOT NULL,
    ratee_id INT NOT NULL,
    score TINYINT NOT NULL CHECK (score BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ride_id) REFERENCES rides(id) ON DELETE RESTRICT,
    FOREIGN KEY (rater_id) REFERENCES users(id),
    FOREIGN KEY (ratee_id) REFERENCES users(id),
    UNIQUE KEY unique_rating (ride_id, rater_id, ratee_id)
);

-- 6. CANCELLATIONS
CREATE TABLE cancellations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    cancelled_by INT NOT NULL,
    cancelled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    penalty_applied BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
    FOREIGN KEY (cancelled_by) REFERENCES users(id)
);

-- 7. FAVORITES
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    driver_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, driver_id)
);