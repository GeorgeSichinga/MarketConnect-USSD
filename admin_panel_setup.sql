-- Market Connect - Admin Panel setup
-- Adds the columns/data needed to tell an Admin apart from a regular user.
-- Run against the `market_connect` database (click it in phpMyAdmin first,
-- then the "SQL" tab, paste, "Go").

-- 1. Seed the usertypes lookup table (the table already existed, unused until now).
INSERT IGNORE INTO usertypes (id, Usertype) VALUES
  (1, 'Farmer/Seller'),
  (2, 'Buyer/Trader'),
  (3, 'Admin');

-- 2. Add usertype_id (defaults every existing/new user to Farmer/Seller)
--    and is_verified (0 = not verified yet) to users.
ALTER TABLE users
  ADD COLUMN usertype_id INT DEFAULT 1,
  ADD COLUMN is_verified TINYINT(1) DEFAULT 0;

ALTER TABLE users
  ADD CONSTRAINT fk_users_usertype FOREIGN KEY (usertype_id) REFERENCES usertypes(id);

-- 3. Promote your own account to Admin so the "Admin Panel" menu option
--    shows up when you log in. Replace the phone number below with the
--    one you actually registered/log in with.
UPDATE users SET usertype_id = 3, is_verified = 1 WHERE phone_number = 'YOUR_PHONE_NUMBER_HERE';
