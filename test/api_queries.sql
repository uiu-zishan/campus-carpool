-- P2 Backend: API Query Reference
-- These are the exact queries used in the PHP API files

-- QUERY 1: Check if email exists (Used in register.php)
SELECT id FROM users WHERE email = ?;

-- QUERY 2: Insert a new user (Used in register.php)
INSERT INTO users (email, password_hash, full_name, gender, role) 
VALUES (?, ?, ?, ?, ?);

-- QUERY 3: Fetch user for login (Used in login.php)
SELECT id, email, password_hash, full_name, role 
FROM users 
WHERE email = ?;