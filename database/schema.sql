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
<<<<<<< HEAD
    admin_id INT NULL,
=======
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    contact_number VARCHAR(30),
    email VARCHAR(255) UNIQUE,
    is_verified BOOLEAN DEFAULT FALSE,
<<<<<<< HEAD
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_hospital_admin FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE
    SET NULL
=======
    created_at DATETIME NOT NULL
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
) ENGINE = InnoDB;
-- 3. Donor Profiles
CREATE TABLE donor_profiles (
    donor_id INT PRIMARY KEY,
    blood_type ENUM(
        'A',
        'B',
        'AB',
        'O',
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-'
    ) NOT NULL,
    weight_kg DECIMAL(3, 2) NOT NULL,
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
    status ENUM(
        'Pending',
        'In Progress',
        'Completed',
        'Cancelled',
<<<<<<< HEAD
        'Allowed'
=======
        'No-show'
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
    ) DEFAULT 'Pending',
    notes TEXT NULL,
    CONSTRAINT fk_appointment_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE CASCADE,
    CONSTRAINT fk_appointment_center FOREIGN KEY (center_id) REFERENCES blood_centers(center_id) ON DELETE CASCADE
) ENGINE = InnoDB;
-- 6. Donations
CREATE TABLE donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    center_id INT NOT NULL,
    volume_ml INT NOT NULL,
    hemoglobin_level DECIMAL(4, 2) NOT NULL,
    temperature DECIMAL(4, 2) NULL,
    blood_pressure VARCHAR(20) NULL,
    status ENUM(
        'Pending Lab',
        'Approved',
        'Rejected',
        'Dispatched'
    ) DEFAULT 'Pending Lab',
    donated_at DATETIME NOT NULL,
    CONSTRAINT fk_donation_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE CASCADE,
    CONSTRAINT fk_donation_center FOREIGN KEY (center_id) REFERENCES blood_centers(center_id) ON DELETE CASCADE
) ENGINE = InnoDB;
-- 7. Blood Inventory
CREATE TABLE blood_inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL UNIQUE,
    blood_type ENUM(
        'A',
        'B',
        'AB',
        'O',
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-'
    ) NOT NULL,
    expiry_date DATE NOT NULL,
    current_location_id INT NOT NULL,
    status ENUM(
        'Available',
        'Reserved',
        'Dispatched',
        'Expired',
        'Discarded'
    ) DEFAULT 'Available',
    CONSTRAINT fk_inventory_donation FOREIGN KEY (donation_id) REFERENCES donations(donation_id) ON DELETE CASCADE,
    CONSTRAINT fk_inventory_center FOREIGN KEY (current_location_id) REFERENCES blood_centers(center_id) ON DELETE CASCADE
) ENGINE = InnoDB;
-- 8. Blood Requests
CREATE TABLE blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    blood_type_required ENUM(
        'A',
        'B',
        'AB',
        'O',
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-',
        'O+',
        'O-'
    ) NOT NULL,
    units_requested INT NOT NULL DEFAULT 1,
    urgency_level ENUM('Normal', 'Urgent', 'Emergency') NOT NULL,
    patient_identifier VARCHAR(50) DEFAULT NULL,
    status ENUM(
        'Open',
        'In Progress',
        'Fulfilled',
        'Expired',
        'Cancelled'
    ) DEFAULT 'Open',
<<<<<<< HEAD
    donor_id INT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_request_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE,
    CONSTRAINT fk_request_donor FOREIGN KEY (donor_id) REFERENCES donor_profiles(donor_id) ON DELETE
    SET NULL
=======
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_request_hospital FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
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
INSERT INTO users (
<<<<<<< HEAD
        first_name,
        last_name,
        email,
        phone,
        password_hash,
        role,
        gender,
        date_of_birth,
        last_login,
        created_at
    )
VALUES (
        'Super',
        'Admin',
        'admin@redhope.com',
        '+20123456789',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        -- password: 12345678
        'Super Admin',
        'Male',
        '1990-01-01',
        NOW(),
        NOW()
    );
=======
    first_name,
    last_name,
    email,
    phone,
    password_hash,
    role,
    gender,
    date_of_birth,
    last_login,
    created_at
)
VALUES (
    'Super',
    'Admin',
    'admin@redhope.com',
    '+20123456789',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 12345678
    'Super Admin',
    'Male',
    '1990-01-01',
    NOW(),
    NOW()
);
>>>>>>> 9ed3f29124c19bcff361c5c8cc79ace33ba2cf7b
