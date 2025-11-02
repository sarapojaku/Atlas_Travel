-- Database creation
CREATE DATABASE IF NOT EXISTS `agencydb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Switch to the created database
USE `agencydb`;

-- Create Client table
CREATE TABLE IF NOT EXISTS `client` (
  `ClientID` INT AUTO_INCREMENT PRIMARY KEY,
  `ClientName` VARCHAR(50),
  `ClientSurname` VARCHAR(50),
  `Username` VARCHAR(50) UNIQUE,
  `Email` VARCHAR(100),
  `Gender` ENUM('Male', 'Female', 'Other'),
  `Phone` VARCHAR(20),
  `Password` VARCHAR(255),
  `Type` VARCHAR(50),
  `Reviews` TEXT,
  `ProfileImage` VARCHAR(255),
  `Spending` INT,
  `Rating`  INT
);

-- Create Country table
CREATE TABLE IF NOT EXISTS `country` (
  `CountryID` INT AUTO_INCREMENT PRIMARY KEY,
  `CountryName` VARCHAR(100),
  `CountryInfo` TEXT
);

-- Create Destination table
CREATE TABLE IF NOT EXISTS `destination` (
  `DestinationID` INT AUTO_INCREMENT PRIMARY KEY,
  `DestinationName` VARCHAR(100),
  `DestinationInfo` TEXT,
  `DestinationPlaces` TEXT,
  `DestinationImage` VARCHAR(255),
  `DestinationPrice` DECIMAL(10, 2),
  `StartDate` DATE,
  `EndDate` DATE,
  `Type` ENUM('Adventure', 'Relaxation', 'Historical', 'Cultural', 'Other'),
  `Revenue` DECIMAL(10, 2),
  `CountryID` INT,
  FOREIGN KEY (`CountryID`) REFERENCES `Country`(`CountryID`)
);

-- Create Staff table
CREATE TABLE IF NOT EXISTS `staff` (
    `StaffID` INT AUTO_INCREMENT PRIMARY KEY,
    `StaffName` VARCHAR(50) NOT NULL,
    `StaffSurname` VARCHAR(50) NOT NULL,
    `Username` VARCHAR(50) NOT NULL UNIQUE,
    `Email` VARCHAR(100) NOT NULL UNIQUE,
    `Gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `Phone` VARCHAR(20),
    `Password` VARCHAR(255) NOT NULL,
    `Type` ENUM('Admin', 'Staff', 'Manager') NOT NULL,
    `DateEmployed` DATE NOT NULL,
    `ProfileImage` VARCHAR(255),
);

-- Create Booking table
CREATE TABLE IF NOT EXISTS `booking` (
  `BookingID` INT AUTO_INCREMENT PRIMARY KEY,
  `ClientID` INT,
  `DestinationID` INT,
  FOREIGN KEY (`ClientID`) REFERENCES `client`(`ClientID`),
  FOREIGN KEY (`DestinationID`) REFERENCES `destination`(`DestinationID`)
);

--Create messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `name` VARCHAR(255),
  `contact` TEXT,
  `message` TEXT
)