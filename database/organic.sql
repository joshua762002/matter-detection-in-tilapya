-- 1. Database Creation
CREATE DATABASE IF NOT EXISTS organic_tilapia;
USE organic_tilapia;

-- 2. Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') NOT NULL,
    assigned_pond INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME
);

-- 3. Roles Permissions Table
CREATE TABLE roles_permissions (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name ENUM('admin','manager','staff') NOT NULL,
    can_create_account TINYINT(1) DEFAULT 0,
    can_monitor TINYINT(1) DEFAULT 0,
    can_manage_notifications TINYINT(1) DEFAULT 0
);

-- 4. Ponds Table
CREATE TABLE ponds (
    pond_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. User-Ponds Assignments Table
CREATE TABLE user_ponds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pond_name VARCHAR(50) NOT NULL,
    organic_mg_l FLOAT,
    temperature_c FLOAT,
    ph_level FLOAT,
    detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- 6. Pond Readings Table
CREATE TABLE readings (
    reading_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_name VARCHAR(50),
    temperature FLOAT,
    ph FLOAT,
    organic FLOAT,
    detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    staff_id INT,
    FOREIGN KEY (staff_id) REFERENCES users(user_id)
);

-- 7. Detections Table
CREATE TABLE detections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sample_code VARCHAR(50),
    pond_id INT,
    organic_level FLOAT,
    water_temperature FLOAT,
    ph_level FLOAT,
    status VARCHAR(20),
    created_by INT,
    detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pond_id) REFERENCES ponds(pond_id),
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- 8. Notifications Table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_id INT,
    message VARCHAR(255) NOT NULL,
    status ENUM('unread','read') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pond_id) REFERENCES ponds(pond_id)
);