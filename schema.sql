CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `netflix_accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `renewal_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `account_id` INT NOT NULL,
  `profile_name` VARCHAR(50) NOT NULL,
  `pin_code` VARCHAR(4) DEFAULT '0000',
  `status` VARCHAR(20) DEFAULT 'Libre',
  FOREIGN KEY (`account_id`) REFERENCES `netflix_accounts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `whatsapp` VARCHAR(20) NOT NULL,
  `formula` ENUM('Premium','Standard') NOT NULL,
  `price` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Actif',
  `notes` TEXT DEFAULT NULL,
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `amount` INT NOT NULL,
  `payment_date` DATE NOT NULL,
  FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(255) NOT NULL,
  `amount` INT NOT NULL,
  `expense_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`username`, `password`) VALUES ('admin', '$2y$10$tZ2R8F1m7yBsh.AOnU6OseFfJt3v1L7Z.kR30I8pS2tE6hDCH6T0.')
ON DUPLICATE KEY UPDATE `username`=`username`;
