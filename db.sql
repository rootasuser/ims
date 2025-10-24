-- ============================================
-- DATABASE: ims2k25
-- ============================================
CREATE DATABASE IF NOT EXISTS ims2k25 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE ims2k25;

CREATE TABLE ims_users (
  usrid INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('ict','sgod', 'cid', 'sds', 'hr', 'admin', 'supply', 'health', 'bac', 'accounting', 'budget', 'asds', 'als', 'cashier') DEFAULT 'ict',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- ============================================
-- SAMPLE ADMIN ACCOUNT (default password: 1234)
-- ============================================
INSERT INTO ims_users (username, password, role)
VALUES (
    'admin',
    '$2y$10$wb/KNb7o72oC9RRWRE5ACOFEcQfBgebFM.ML8qorz.qYbO6oUyRSe',
    'ict'
);


CREATE TABLE `ims_devices` (
    `d_uid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `serial_number` VARCHAR(100) NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `device_condition` ENUM('New', 'Good', 'Needs Repair', 'Damaged') NOT NULL DEFAULT 'New',
    `current_status` ENUM('In Use', 'Available', 'Under Maintenance') NOT NULL DEFAULT 'Available',
    `pr` VARCHAR(100) DEFAULT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
     `borrower` VARCHAR (300),
    `usrid` INT UNSIGNED NOT NULL,
    `role` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`d_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ims_device_logs` (
    `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `d_uid` INT UNSIGNED NOT NULL,
    `usrid` INT UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL, 
    `old_data` JSON NOT NULL,
    `new_data` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ims_archive` (
    `archive_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `d_uid` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100),
    `brand` VARCHAR(100),
    `model` VARCHAR(100),
    `serial_number` VARCHAR(100),
    `category` VARCHAR(100),
    `device_condition` ENUM('New', 'Good', 'Needs Repair', 'Damaged'),
    `current_status` ENUM('In Use', 'Available', 'Under Maintenance'),
    `pr` VARCHAR(100),
    `quantity` INT UNSIGNED,
    `borrower` VARCHAR (300),
    `archived_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE ims_archive
ADD COLUMN usrid INT UNSIGNED NOT NULL AFTER borrower,
ADD COLUMN role VARCHAR(50) NOT NULL AFTER usrid;





