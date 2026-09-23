-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2026 at 10:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `market_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `Log_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Action` varchar(100) NOT NULL,
  `Details` text DEFAULT NULL,
  `CreatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`Log_ID`, `User_ID`, `Action`, `Details`, `CreatedAt`) VALUES
(1, 4, 'login', '', '2026-09-22 11:42:58'),
(2, 4, 'express_interest', 'buy #3', '2026-09-22 11:44:04'),
(3, 4, 'express_interest', 'sell #2', '2026-09-22 11:44:15'),
(4, 3, 'login', '', '2026-09-22 11:49:14'),
(5, 3, 'confirm_transaction', 'Transaction #1 -> yes (status: pending)', '2026-09-22 11:49:39'),
(6, 4, 'login', '', '2026-09-22 11:50:15'),
(7, 4, 'confirm_transaction', 'Transaction #1 -> yes (status: completed)', '2026-09-22 11:51:17'),
(8, 3, 'login', '', '2026-09-22 11:52:56');

-- --------------------------------------------------------

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Password` varchar(50) NOT NULL,
  `Status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authentication`
--

INSERT INTO `authentication` (`ID`, `User_ID`, `Password`, `Status`) VALUES
(1, 3, '1234', 'active'),
(2, 4, '1234', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `farming_tips`
--

CREATE TABLE `farming_tips` (
  `Tip_ID` int(11) NOT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `Tip_Text` text NOT NULL,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `market_info`
--

CREATE TABLE `market_info` (
  `MarketInfo_ID` int(11) NOT NULL,
  `Produce_ID` int(11) NOT NULL,
  `District` varchar(50) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL,
  `Price_Unit` enum('KG','50kg Bag') NOT NULL DEFAULT 'KG',
  `Demand_Level` varchar(20) DEFAULT NULL,
  `Supply_Level` varchar(20) DEFAULT NULL,
  `DateUpdated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `market_info`
--

INSERT INTO `market_info` (`MarketInfo_ID`, `Produce_ID`, `District`, `Price`, `Price_Unit`, `Demand_Level`, `Supply_Level`, `DateUpdated`) VALUES
(1, 4, 'Genda', 2100.00, 'KG', 'Moderate', 'Moderate', '2026-09-22 10:42:49');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `Payment_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Subscription_ID` int(11) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Payment_Date` datetime DEFAULT current_timestamp(),
  `Payment_Status` enum('pending','completed','failed') DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produce`
--

CREATE TABLE `produce` (
  `ProduceID` int(11) NOT NULL,
  `ProduceName` varchar(50) DEFAULT NULL,
  `Recommended_Price` decimal(10,2) DEFAULT NULL,
  `Recommended_Price_Date` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produce`
--

INSERT INTO `produce` (`ProduceID`, `ProduceName`, `Recommended_Price`, `Recommended_Price_Date`) VALUES
(1, 'soybean', NULL, NULL),
(2, 'tomatoes', NULL, NULL),
(3, '5', NULL, NULL),
(4, 'Soybeans', NULL, NULL),
(5, 'Chilli Pepper', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `produce_buy`
--

CREATE TABLE `produce_buy` (
  `Buy_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Produce_ID` int(11) NOT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Quantity_Value` decimal(10,2) DEFAULT NULL,
  `Quantity_Unit` varchar(20) DEFAULT NULL,
  `Price_Offered` decimal(10,2) DEFAULT NULL,
  `DateRequested` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produce_buy`
--

INSERT INTO `produce_buy` (`Buy_ID`, `User_ID`, `Produce_ID`, `Location`, `Quantity_Value`, `Quantity_Unit`, `Price_Offered`, `DateRequested`) VALUES
(1, 3, 2, 'lilongwe', 5.00, '50kg Bag', 600.00, '2026-09-21 00:00:00'),
(2, 3, 3, 'lilongwe', 4000.00, 'KG', 800.00, '2026-09-21 00:00:00'),
(3, 3, 5, 'lilongwe', 60.00, 'KG', 800.00, '2026-09-21 19:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `produce_sell`
--

CREATE TABLE `produce_sell` (
  `Sell_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Produce_ID` int(11) NOT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Quantity_Value` decimal(10,2) DEFAULT NULL,
  `Quantity_Unit` varchar(20) DEFAULT NULL,
  `Quality` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produce_sell`
--

INSERT INTO `produce_sell` (`Sell_ID`, `User_ID`, `Produce_ID`, `Location`, `Quantity_Value`, `Quantity_Unit`, `Quality`, `Price`) VALUES
(1, 3, 1, 'lilongwe', 4.00, '50kg Bag', NULL, 500.00),
(2, 3, 4, 'lilongwe', 7.00, '50kg Bag', NULL, 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `quality_tips`
--

CREATE TABLE `quality_tips` (
  `Tip_ID` int(11) NOT NULL,
  `Produce_ID` int(11) DEFAULT NULL,
  `Tip_Category` enum('harvest','storage','grading','transport') DEFAULT 'harvest',
  `Tip_Text` text NOT NULL,
  `Date_Posted` date DEFAULT curdate(),
  `Sent_As_SMS` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storage_tips`
--

CREATE TABLE `storage_tips` (
  `Tip_ID` int(11) NOT NULL,
  `Produce_ID` int(11) DEFAULT NULL,
  `Tip_Text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `Subscription_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Subscription_Type` enum('buyer','seller','weather','farming_tips') NOT NULL,
  `Start_Date` date DEFAULT curdate(),
  `End_Date` date DEFAULT NULL,
  `Status` enum('active','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `Transaction_ID` int(11) NOT NULL,
  `Listing_Type` enum('sell','buy') NOT NULL,
  `Listing_ID` int(11) NOT NULL,
  `Initiator_User_ID` int(11) NOT NULL,
  `Owner_User_ID` int(11) NOT NULL,
  `Initiator_Confirmed` enum('pending','yes','no') DEFAULT 'pending',
  `Owner_Confirmed` enum('pending','yes','no') DEFAULT 'pending',
  `Status` enum('pending','completed','declined') DEFAULT 'pending',
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`Transaction_ID`, `Listing_Type`, `Listing_ID`, `Initiator_User_ID`, `Owner_User_ID`, `Initiator_Confirmed`, `Owner_Confirmed`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'buy', 3, 4, 3, 'yes', 'yes', 'completed', '2026-09-22 11:44:04', '2026-09-22 11:51:17'),
(2, 'sell', 2, 4, 3, 'pending', 'pending', 'pending', '2026-09-22 11:44:15', '2026-09-22 11:44:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `national_id` varchar(30) DEFAULT NULL,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `TA` varchar(50) DEFAULT NULL,
  `Village` varchar(50) DEFAULT NULL,
  `nearestmarket` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `usertype_id` int(11) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone_number`, `national_id`, `firstname`, `lastname`, `district`, `TA`, `Village`, `nearestmarket`, `password`, `usertype_id`, `is_verified`) VALUES
(2, '0888897147', 'nshd', 'Cathy', 'phiri', 'dowa', 'malala', 'kape', 'kape', NULL, 1, 0),
(3, '0998831833', 'wvzer2', 'taonga', 'kacheche', 'mzimba', 'nsalu', 'swaswa', 'embangweni', NULL, 3, 1),
(4, '0883364070', '3wedr', 'kety', 'phiri', 'zomba', 'mulili', 'kathu', 'kathu', NULL, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `usertypes`
--

CREATE TABLE `usertypes` (
  `id` int(11) NOT NULL,
  `Usertype` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertypes`
--

INSERT INTO `usertypes` (`id`, `Usertype`) VALUES
(1, 'Farmer/Seller'),
(2, 'Buyer/Trader'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `weather_forecast`
--

CREATE TABLE `weather_forecast` (
  `Forecast_ID` int(11) NOT NULL,
  `District` varchar(50) DEFAULT NULL,
  `Forecast_Date` date DEFAULT NULL,
  `Summary` text DEFAULT NULL,
  `Temperature` decimal(5,2) DEFAULT NULL,
  `Rainfall` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `authentication`
--
ALTER TABLE `authentication`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`User_ID`);

--
-- Indexes for table `farming_tips`
--
ALTER TABLE `farming_tips`
  ADD PRIMARY KEY (`Tip_ID`);

--
-- Indexes for table `market_info`
--
ALTER TABLE `market_info`
  ADD PRIMARY KEY (`MarketInfo_ID`),
  ADD KEY `Produce_ID` (`Produce_ID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Subscription_ID` (`Subscription_ID`);

--
-- Indexes for table `produce`
--
ALTER TABLE `produce`
  ADD PRIMARY KEY (`ProduceID`);

--
-- Indexes for table `produce_buy`
--
ALTER TABLE `produce_buy`
  ADD PRIMARY KEY (`Buy_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Produce_ID` (`Produce_ID`);

--
-- Indexes for table `produce_sell`
--
ALTER TABLE `produce_sell`
  ADD PRIMARY KEY (`Sell_ID`),
  ADD KEY `fk_sell_user` (`User_ID`),
  ADD KEY `fk_sell_produce` (`Produce_ID`);

--
-- Indexes for table `quality_tips`
--
ALTER TABLE `quality_tips`
  ADD PRIMARY KEY (`Tip_ID`),
  ADD KEY `Produce_ID` (`Produce_ID`);

--
-- Indexes for table `storage_tips`
--
ALTER TABLE `storage_tips`
  ADD PRIMARY KEY (`Tip_ID`),
  ADD KEY `Produce_ID` (`Produce_ID`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`Subscription_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`Transaction_ID`),
  ADD KEY `Initiator_User_ID` (`Initiator_User_ID`),
  ADD KEY `Owner_User_ID` (`Owner_User_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `fk_users_usertype` (`usertype_id`);

--
-- Indexes for table `usertypes`
--
ALTER TABLE `usertypes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weather_forecast`
--
ALTER TABLE `weather_forecast`
  ADD PRIMARY KEY (`Forecast_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `authentication`
--
ALTER TABLE `authentication`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `farming_tips`
--
ALTER TABLE `farming_tips`
  MODIFY `Tip_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `market_info`
--
ALTER TABLE `market_info`
  MODIFY `MarketInfo_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produce`
--
ALTER TABLE `produce`
  MODIFY `ProduceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `produce_buy`
--
ALTER TABLE `produce_buy`
  MODIFY `Buy_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produce_sell`
--
ALTER TABLE `produce_sell`
  MODIFY `Sell_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quality_tips`
--
ALTER TABLE `quality_tips`
  MODIFY `Tip_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `storage_tips`
--
ALTER TABLE `storage_tips`
  MODIFY `Tip_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `Subscription_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `Transaction_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usertypes`
--
ALTER TABLE `usertypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `weather_forecast`
--
ALTER TABLE `weather_forecast`
  MODIFY `Forecast_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `authentication`
--
ALTER TABLE `authentication`
  ADD CONSTRAINT `fk_auth_user` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `market_info`
--
ALTER TABLE `market_info`
  ADD CONSTRAINT `market_info_ibfk_1` FOREIGN KEY (`Produce_ID`) REFERENCES `produce` (`ProduceID`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`Subscription_ID`) REFERENCES `subscriptions` (`Subscription_ID`);

--
-- Constraints for table `produce_buy`
--
ALTER TABLE `produce_buy`
  ADD CONSTRAINT `produce_buy_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produce_buy_ibfk_2` FOREIGN KEY (`Produce_ID`) REFERENCES `produce` (`ProduceID`);

--
-- Constraints for table `produce_sell`
--
ALTER TABLE `produce_sell`
  ADD CONSTRAINT `fk_sell_produce` FOREIGN KEY (`Produce_ID`) REFERENCES `produce` (`ProduceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sell_user` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quality_tips`
--
ALTER TABLE `quality_tips`
  ADD CONSTRAINT `quality_tips_ibfk_1` FOREIGN KEY (`Produce_ID`) REFERENCES `produce` (`ProduceID`);

--
-- Constraints for table `storage_tips`
--
ALTER TABLE `storage_tips`
  ADD CONSTRAINT `storage_tips_ibfk_1` FOREIGN KEY (`Produce_ID`) REFERENCES `produce` (`ProduceID`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`Initiator_User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`Owner_User_ID`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_usertype` FOREIGN KEY (`usertype_id`) REFERENCES `usertypes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
