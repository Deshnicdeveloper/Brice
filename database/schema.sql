-- Create database if not exists
CREATE DATABASE IF NOT EXISTS school_results CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_results;

-- Teachers table
CREATE TABLE teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(10) UNIQUE NOT NULL,
    pin CHAR(8) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    assigned_class VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Admins table
CREATE TABLE admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(10) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    role ENUM('super_admin', 'sub_admin') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP
);

-- Parents table
CREATE TABLE parents (
    parent_id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(10) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Pupils table
CREATE TABLE pupils (
    pupil_id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(10) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    parent_id INT,
    class VARCHAR(20) NOT NULL,
    admission_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    FOREIGN KEY (parent_id) REFERENCES parents(parent_id)
);

-- Subjects table
CREATE TABLE subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(10) UNIQUE,
    class VARCHAR(20) NOT NULL,
    coefficient DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    category VARCHAR(50)
);

-- Results table
CREATE TABLE results (
    result_id INT PRIMARY KEY AUTO_INCREMENT,
    pupil_id INT,
    subject_id INT,
    academic_year VARCHAR(9) NOT NULL,
    term TINYINT NOT NULL,
    first_sequence_marks DECIMAL(5,2) DEFAULT NULL,
    second_sequence_marks DECIMAL(5,2) DEFAULT NULL,
    exam_marks DECIMAL(5,2) DEFAULT NULL,
    total_marks DECIMAL(5,2) DEFAULT NULL,
    term_average DECIMAL(5,2) DEFAULT NULL,
    ranking INT DEFAULT NULL,
    teacher_comment TEXT,
    FOREIGN KEY (pupil_id) REFERENCES pupils(pupil_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
);

-- Create indexes
CREATE INDEX idx_pupil_class ON pupils(class);
CREATE INDEX idx_results_academic_year ON results(academic_year);
CREATE INDEX idx_results_term ON results(term);

-- Insert default admin user
INSERT INTO admins (matricule, username, password, email, role) 
VALUES ('ADM001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@school.com', 'super_admin');
-- Default password is 'password'

-- Add after existing tables

-- Audit Logs table
CREATE TABLE audit_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    user_type VARCHAR(20),
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Request Logs table
CREATE TABLE request_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    method VARCHAR(10) NOT NULL,
    url VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    request_data JSON,
    response_code INT,
    execution_time FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Settings table
CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('academic_year', YEAR(CURRENT_DATE)),
('current_term', '1'),
('results_per_page', '25');

-- Marking periods table
CREATE TABLE marking_periods (
    period_id INT PRIMARY KEY AUTO_INCREMENT,
    academic_year VARCHAR(9) NOT NULL,
    term TINYINT NOT NULL,
    sequence TINYINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); 