CREATE DATABASE IF NOT EXISTS redhope_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE redhope_db;

-- 1. Users Table

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(90) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Donor', 'Hospital Admin', 'Super Admin') NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    last_login DATETIME NULL,
    created_at DATETIME NOT NULL
) ENGINE = InnoDB;

-- 2. Hospitals Table

CREATE TABLE hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    contact_number VARCHAR(30),
    email VARCHAR(255) UNIQUE,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_hospital_admin FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE
    SET NULL
) ENGINE = InnoDB;

-- 3. Donor Profiles

CREATE TABLE donor_profiles (
    donor_id INT PRIMARY KEY,
    blood_type ENUM('A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    weight_kg DECIMAL(5, 2) NOT NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    last_donation_date DATE NULL,
    medical_conditions TEXT NULL,
    CONSTRAINT fk_donor_user FOREIGN KEY (donor_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 4. Blood Centers

CREATE TABLE blood_centers (
    center_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    gps_coordinates POINT NOT NULL,
    contact_number VARCHAR(30)
) ENGINE = InnoDB;

-- 5. Appointments

CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    center_id INT NOT NULL,
    scheduled_time DATETIME NOT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled', 'No-show', 'Allowed') DEFAULT 'Pending',
    notes TEXT NULL,
    CONSTRAINT fk_appointment_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE CASCADE,
    CONSTRAINT fk_appointment_center FOREIGN KEY (center_id) REFERENCES blood_centers(center_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 6. Donations

CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    center_id INT NULL,
    hospital_id INT NULL,
    volume_ml INT NOT NULL,
    hemoglobin_level DECIMAL(4, 2) NOT NULL,
    temperature DECIMAL(4, 2) NULL,
    blood_pressure VARCHAR(20) NULL,
    status ENUM('Pending Lab', 'Approved', 'Rejected', 'Dispatched') DEFAULT 'Pending Lab',
    donated_at DATETIME NOT NULL,
    CONSTRAINT fk_donation_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE CASCADE,
    CONSTRAINT fk_donation_center FOREIGN KEY (center_id) REFERENCES blood_centers(center_id) ON DELETE
    SET NULL,
        CONSTRAINT fk_donation_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE
    SET NULL
) ENGINE = InnoDB;

-- 7. Blood Inventory

CREATE TABLE blood_inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL UNIQUE,
    blood_type ENUM('A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    expiry_date DATE NOT NULL,
    current_location_id INT NOT NULL,
    status ENUM('Available', 'Reserved', 'Dispatched', 'Expired', 'Discarded') DEFAULT 'Available',
    CONSTRAINT fk_inventory_donation FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE,
    CONSTRAINT fk_inventory_center FOREIGN KEY (current_location_id) REFERENCES blood_centers(center_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 8. Blood Requests

CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    blood_type_required ENUM('A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
    units_requested INT NOT NULL DEFAULT 1,
    urgency_level ENUM('Normal', 'Urgent', 'Emergency') NOT NULL,
    patient_identifier VARCHAR(50) DEFAULT NULL,
    status ENUM('Open','In Progress','Fulfilled','Expired','Cancelled') DEFAULT 'Open',
    donor_id INT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_request_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE,
    CONSTRAINT fk_request_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- 9. Messages

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) NULL,
    message_content TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 10. AI Chat Sessions

CREATE TABLE ai_chat_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) DEFAULT 'New Chat',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_ai_session_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 11. AI Chat Messages

CREATE TABLE ai_chat_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender ENUM('User', 'AI') NOT NULL,
    message_content TEXT NOT NULL,
    sent_at DATETIME NOT NULL,
    CONSTRAINT fk_ai_msg_session FOREIGN KEY (session_id) REFERENCES ai_chat_sessions(session_id) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Inserts

INSERT INTO users ( user_id, first_name, last_name, email, phone, password_hash, role, gender, date_of_birth, created_at)
VALUES ( 3, 'Hospital', 'Admin', 'hospital@redhope.com', "1123456789", '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hospital Admin', 'Male', '1990-01-01', NOW());
INSERT INTO donor_profiles ( donor_id, blood_type, weight_kg, is_anonymous, last_donation_date, medical_conditions)
VALUES ( 2, 'A+', 70.00, 0, '2023-01-01', 'None');
INSERT INTO users ( user_id, first_name, last_name, email, phone, password_hash, role, gender, date_of_birth, created_at)
VALUES ( 1, 'Super', 'Admin', 'admin@redhope.com', "123456789", '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Admin', 'Male', '1990-01-01', NOW());
INSERT INTO users ( user_id, first_name, last_name, email, phone, password_hash, role, gender, date_of_birth, created_at)
VALUES ( 2, 'Donor', 'User', 'donor@redhope.com', "1223456789", '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Donor', 'Male', '1990-01-01', NOW());
