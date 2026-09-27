-- TrustFlix Digital — Niveau 1 (MySQL / MariaDB)
-- Exécuter dans phpMyAdmin ou : mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS `trustflix_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `trustflix_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `default_slots` TINYINT UNSIGNED NOT NULL DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `service_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `renewal_date` DATE NOT NULL,
  `status` ENUM('Actif', 'Inactif') NOT NULL DEFAULT 'Actif',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_accounts_service`
    FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX `idx_accounts_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slots` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT UNSIGNED NOT NULL,
  `slot_name` VARCHAR(100) NOT NULL,
  `pin_code` CHAR(4) NOT NULL DEFAULT '0000',
  `status` ENUM('Libre', 'Occupé') NOT NULL DEFAULT 'Libre',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_slots_account`
    FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX `idx_slots_account` (`account_id`),
  INDEX `idx_slots_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `whatsapp` VARCHAR(30) NOT NULL,
  `service_id` INT UNSIGNED NOT NULL,
  `slot_id` INT UNSIGNED NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('Actif', 'Expiré') NOT NULL DEFAULT 'Actif',
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_clients_service`
    FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_clients_slot`
    FOREIGN KEY (`slot_id`) REFERENCES `slots` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  INDEX `idx_clients_service` (`service_id`),
  INDEX `idx_clients_slot` (`slot_id`),
  INDEX `idx_clients_end_date` (`end_date`),
  INDEX `idx_clients_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `description` VARCHAR(255) NOT NULL DEFAULT 'Paiement client',
  `payment_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_payments_client`
    FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX `idx_payments_client` (`client_id`),
  INDEX `idx_payments_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`name`, `code`, `default_slots`) VALUES
  ('Netflix', 'netflix', 5),
  ('Spotify', 'spotify', 6),
  ('Apple Music', 'apple_music', 5)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `default_slots` = VALUES(`default_slots`);

-- Identifiants par défaut : admin / admin123
INSERT INTO `users` (`username`, `password_hash`) VALUES
  ('admin', '$2y$10$hRKEmPfwyfxZoKeYllI2KuiKPPLN3tnXGMsdcB5eKclYY7og9VUOS')
ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`);
