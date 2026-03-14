-- 1. Database Creation
CREATE DATABASE IF NOT EXISTS organics_tilapia;
USE organic_tilapia;

-- 2. Users Table (Admin / Manager / Staff)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


-- Sample insertion sa users table
INSERT INTO users (full_name, email, password, role) VALUES
('Juan Dela Cruz', 'admin@company.com', 'admin123', 'admin'),
('Maria Santos', 'manager@company.com', 'manager123', 'manager'),
('Pedro Reyes', 'staff1@company.com', 'staff123', 'staff'),
('Ana Lopez', 'staff2@company.com', 'staff123', 'staff');



-- 3. Ponds Table (Company-owned ponds)
CREATE TABLE ponds (
    pond_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_name VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. Pond Readings Table (IoT sensor data)
CREATE TABLE pond_readings (
    reading_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_id INT NOT NULL,
    organic_level FLOAT NOT NULL,
    water_temperature FLOAT NOT NULL,
    ph_level FLOAT NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pond_id) REFERENCES ponds(pond_id)
);

-- 5. Notifications Table (Alerts for abnormal readings)
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    pond_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    status ENUM('unread','read') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pond_id) REFERENCES ponds(pond_id)
);

-- 6. Optional: Roles Permissions Table (fine control)
CREATE TABLE roles_permissions (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name ENUM('admin','manager','staff') NOT NULL,
    can_create_account BOOLEAN DEFAULT FALSE,
    can_monitor BOOLEAN DEFAULT FALSE,
    can_manage_notifications BOOLEAN DEFAULT FALSE
);