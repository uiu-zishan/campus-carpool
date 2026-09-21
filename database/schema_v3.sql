-- ==========================================================
-- Campus Carpool - Schema v3
-- Week 3: Security tables, audit log, notifications, and views
-- P1: Database Architect
-- ==========================================================

USE campus_carpool;

-- ==========================================================
-- 1. LOGIN_ATTEMPTS TABLE
-- Tracks failed login attempts for rate limiting.
-- Prevents brute-force attacks.
-- ==========================================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_email_time (email, attempted_at),
    INDEX idx_login_ip_time (ip_address, attempted_at)
);

-- ==========================================================
-- 2. AUDIT_LOG TABLE
-- Tracks admin and security-critical actions.
-- ==========================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id INT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_time (created_at)
);

-- ==========================================================
-- 3. NOTIFICATIONS TABLE
-- In-app notifications for users (booking confirmations,
-- cancellations, ratings, etc.)
-- ==========================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user_read (user_id, is_read),
    INDEX idx_notif_time (created_at)
);

-- ==========================================================
-- 4. VIEW: v_user_summary
-- Aggregated user stats for admin dashboard
-- ==========================================================
CREATE OR REPLACE VIEW v_user_summary AS
SELECT 
    u.id,
    u.email,
    u.full_name,
    u.role,
    u.avg_rating,
    u.penalty_points,
    u.created_at,
    (SELECT COUNT(*) FROM rides WHERE driver_id = u.id) AS rides_driven,
    (SELECT COUNT(*) FROM bookings WHERE passenger_id = u.id AND status = 'completed') AS rides_taken,
    (SELECT COUNT(*) FROM ratings WHERE ratee_id = u.id) AS ratings_received
FROM users u;

-- ==========================================================
-- 5. VIEW: v_ride_stats
-- Aggregated ride statistics
-- ==========================================================
CREATE OR REPLACE VIEW v_ride_stats AS
SELECT 
    r.id,
    r.origin,
    r.destination,
    r.departure_time,
    r.status,
    r.seats_available,
    u.full_name AS driver_name,
    (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'confirmed') AS confirmed_bookings,
    (SELECT COUNT(*) FROM bookings WHERE ride_id = r.id AND status = 'cancelled') AS cancelled_bookings
FROM rides r
JOIN users u ON r.driver_id = u.id;

-- ==========================================================
-- 6. VIEW: v_booking_stats
-- Booking trends for admin reporting
-- ==========================================================
CREATE OR REPLACE VIEW v_booking_stats AS
SELECT 
    DATE(booked_at) AS booking_date,
    COUNT(*) AS total_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed
FROM bookings
GROUP BY DATE(booked_at)
ORDER BY booking_date DESC;