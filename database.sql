-- Create database if not exists
CREATE DATABASE IF NOT EXISTS mosque_db;
USE mosque_db;

-- Mosques table
CREATE TABLE IF NOT EXISTS mosques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    fajar TIME NOT NULL,
    zuhar TIME NOT NULL,
    asar TIME NOT NULL,
    maghrib TIME NOT NULL,
    ishaa TIME NOT NULL,
    juma TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pending submissions table
CREATE TABLE IF NOT EXISTS pending_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mosque_id INT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    fajar TIME NOT NULL,
    zuhar TIME NOT NULL,
    asar TIME NOT NULL,
    maghrib TIME NOT NULL,
    ishaa TIME NOT NULL,
    juma TIME NOT NULL,
    submission_type ENUM('new', 'revision', 'delete') NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mosque_id) REFERENCES mosques(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password_hash) 
VALUES ('admin', '$2y$10$YourSecureHashHere')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Add indexes for better query performance
ALTER TABLE mosques ADD INDEX idx_location (latitude, longitude);
ALTER TABLE pending_submissions ADD INDEX idx_submission_type (submission_type);
ALTER TABLE pending_submissions ADD INDEX idx_submitted_at (submitted_at);
