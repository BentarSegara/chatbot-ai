-- ============================================================
-- Help Desk Hybrid — Skema Database
-- Database: chatbot_ai
-- ============================================================

-- Tabel users (admin & CS staff)
CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(50) UNIQUE NOT NULL,
    `password`   VARCHAR(255) NOT NULL,
    `full_name`  VARCHAR(100) NOT NULL,
    `role`       ENUM('admin','cs') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel conversations (satu baris = satu sesi chat user)
CREATE TABLE IF NOT EXISTS `conversations` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `session_key`    VARCHAR(64) UNIQUE NOT NULL,
    `customer_name`  VARCHAR(100) DEFAULT 'Tamu',
    `customer_email` VARCHAR(150) DEFAULT NULL,
    `status`         ENUM('ai_handling','waiting_cs','cs_handling','closed') DEFAULT 'ai_handling',
    `assigned_cs_id` INT DEFAULT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_cs_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel messages (semua pesan dalam percakapan)
CREATE TABLE IF NOT EXISTS `messages` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT NOT NULL,
    `sender_role`     ENUM('user','ai','cs','system') NOT NULL,
    `sender_name`     VARCHAR(100) DEFAULT NULL,
    `content`         TEXT NOT NULL,
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
