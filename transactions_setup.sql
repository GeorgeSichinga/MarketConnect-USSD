-- Market Connect: Step 4 - Transaction confirmation + Activity log
-- Run this once in phpMyAdmin on the market_connect database (SQL tab).

CREATE TABLE IF NOT EXISTS transactions (
  Transaction_ID INT AUTO_INCREMENT PRIMARY KEY,
  Listing_Type ENUM('sell','buy') NOT NULL,
  Listing_ID INT NOT NULL,
  Initiator_User_ID INT NOT NULL,
  Owner_User_ID INT NOT NULL,
  Initiator_Confirmed ENUM('pending','yes','no') DEFAULT 'pending',
  Owner_Confirmed ENUM('pending','yes','no') DEFAULT 'pending',
  Status ENUM('pending','completed','declined') DEFAULT 'pending',
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (Initiator_User_ID) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (Owner_User_ID) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS activity_log (
  Log_ID INT AUTO_INCREMENT PRIMARY KEY,
  User_ID INT NULL,
  Action VARCHAR(100) NOT NULL,
  Details TEXT NULL,
  CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (User_ID) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
