-- Market Connect: Step 1 - Database user setup
-- Run this once in phpMyAdmin (SQL tab) if you're setting up the project fresh
-- on a new machine and the 'marketcon' MySQL user doesn't exist yet.
--
-- IMPORTANT: replace 'your_password_here' with a real password before running,
-- then use that same password in admin/db_connection.php.
--
-- Note: the produce list and the alerts_log table (originally also part of this
-- step) are already included in marketconnect_current.sql, so they don't need
-- to be repeated here.

CREATE USER IF NOT EXISTS 'marketcon'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON market_connect.* TO 'marketcon'@'localhost';
FLUSH PRIVILEGES;
