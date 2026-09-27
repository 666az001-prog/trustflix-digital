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
