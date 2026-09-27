-- Migration : ajouter la table des sorties (dépenses) pour la trésorerie
USE trustflix_db;

CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(255) NOT NULL,
  `amount` INT NOT NULL,
  `expense_date` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
