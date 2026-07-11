-- Attendance Management System
-- Database design normalized to Third Normal Form (3NF):
--   - Every table has a single-column primary key with no repeating groups.
--   - All non-key columns depend only on the primary key (no partial dependencies).
--   - No column depends on another non-key column (no transitive dependencies):
--     e.g. attendance does not repeat the person's name/department, it only
--     references persons.id via a foreign key.
-- Sensitive columns (names, ID numbers, emails, phone numbers, course/department,
-- and attendance status) are stored as *_enc columns containing AES-256-CBC
-- ciphertext produced by the application (classes/Encryption.php), never plaintext.

CREATE DATABASE IF NOT EXISTS attendance_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_system;

-- Login-capable accounts (Admin / Teacher)
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,       -- bcrypt hash (password_hash()), not reversible
    email_enc     TEXT NOT NULL,               -- AES-256-CBC encrypted
    role          ENUM('admin','teacher') NOT NULL DEFAULT 'teacher',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Students and employees whose attendance is tracked
CREATE TABLE IF NOT EXISTS persons (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    person_type   ENUM('student','employee') NOT NULL,
    full_name_enc TEXT NOT NULL,
    id_number_enc TEXT NOT NULL,
    email_enc     TEXT NOT NULL,
    phone_enc     TEXT NOT NULL,
    category_enc  TEXT NOT NULL,               -- course (student) or department (employee)
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Daily attendance records
CREATE TABLE IF NOT EXISTS attendance (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    person_id        INT NOT NULL,
    attendance_date  DATE NOT NULL,
    status_enc       TEXT NOT NULL,            -- AES-256-CBC encrypted "Present"/"Absent"/"Leave"
    marked_by        INT NOT NULL,             -- FK to users.id (who marked it)
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_person_date (person_id, attendance_date),
    FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed a default admin account: username "admin", password "Admin@123"
-- (bcrypt hash below corresponds to "Admin@123" - CHANGE THIS after first login)
-- Email is AES-256 encrypted with the default Config::ENCRYPTION_KEY - if you
-- change that key before seeding, re-run seed.php instead of this literal insert.
-- Run php seed.php from the project root to create this safely, OR use the
-- insert below only if you have NOT changed the encryption key.
