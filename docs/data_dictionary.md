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