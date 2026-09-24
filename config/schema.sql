-- CampusConnect schema
CREATE DATABASE IF NOT EXISTS campusconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusconnect;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('system_admin','student_moderator') NOT NULL DEFAULT 'student_moderator',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- The forum's real author identity. This table is NEVER joined into any
-- moderator-facing query for posts marked anonymous (see ReportModel::getQueue()).
CREATE TABLE IF NOT EXISTS post_authors (
    post_id INT UNSIGNED PRIMARY KEY,
    student_user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('visible','hidden','deleted') NOT NULL DEFAULT 'visible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    reporter_display_id VARCHAR(20) NOT NULL, -- e.g. "R-2291", never the real reporter identity
    reason VARCHAR(255) NOT NULL,
    ai_flagged TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('pending','approved','hidden','deleted') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_by INT UNSIGNED NULL,
    resolved_at TIMESTAMP NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Immutable audit trail for every moderation/admin action
CREATE TABLE IF NOT EXISTS moderation_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    report_id INT UNSIGNED NULL,
    details VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample seed data
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Ada Lovelace', 'admin@campusconnect.test', '$2y$10$examplehashexamplehashexamplehashexampleha', 'system_admin'),
('Sam Rivera', 'mod@campusconnect.test', '$2y$10$examplehashexamplehashexamplehashexampleha', 'student_moderator');
