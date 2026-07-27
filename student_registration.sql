-- ============================================================
-- Student Registration System - Database Schema
-- Import this file in phpMyAdmin (or run via MySQL CLI)
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_registration_db;
USE student_registration_db;

-- ------------------------------------------------------------
-- Table: students
-- Stores every student who submits the registration form
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL UNIQUE,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    course VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: admins
-- Stores admin panel login credentials
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- NOTE: We do NOT insert a default admin here with a hardcoded
-- password hash (hardcoded hashes floating around in SQL dumps
-- are a bad security habit). Instead, after importing this file,
-- open setup_admin.php ONCE in your browser to create your admin
-- account with a properly generated password hash. Full steps
-- are in README.md.
-- ------------------------------------------------------------
