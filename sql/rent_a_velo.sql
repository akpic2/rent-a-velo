-- Base Rent a Vélo : script fourni + 4 corrections (voir readme.md, partie « Base de données »)

DROP DATABASE IF EXISTS rent_a_velo;
CREATE DATABASE rent_a_velo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rent_a_velo;

CREATE TABLE `users`(
   `user_id` INT AUTO_INCREMENT,
   `firstname` VARCHAR(255) NOT NULL,
   `lastname` VARCHAR(255) NOT NULL,
   -- Correction 1 : INT -> VARCHAR, on se connecte avec une adresse email
   `login` VARCHAR(100) NOT NULL,
   -- NULL pour un usager : il réserve sans compte, il ne se connecte jamais
   `password` VARCHAR(80),
   -- Correction 2 : le rôle distingue l'usager, l'employé et l'administrateur
   `role` ENUM('citizen', 'employee', 'admin') NOT NULL DEFAULT 'citizen',
   PRIMARY KEY(`user_id`),
   UNIQUE(`login`)
);

CREATE TABLE `bikes`(
   `bike_id` INT AUTO_INCREMENT,
   `registration_number` VARCHAR(50) NOT NULL,
   `availability` BOOLEAN,
   `photo` VARCHAR(255),
   PRIMARY KEY(`bike_id`)
);

CREATE TABLE reservations (
   -- Correction 3 : res_num devient la clé primaire auto-incrémentée.
   -- Avec la clé (user_id, bike_id) du script, un usager ne pouvait réserver le même vélo qu'une seule fois.
   `res_num` INT AUTO_INCREMENT,
   `user_id` INT NOT NULL,
   `bike_id` INT NOT NULL,
   `res_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   `start_date` DATETIME NOT NULL,
   `end_date` DATETIME NOT NULL,
   status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
   PRIMARY KEY(`res_num`),
   -- Correction 4 : supprimer un compte supprime aussi ses réservations
   FOREIGN KEY(`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
   FOREIGN KEY(`bike_id`) REFERENCES `bikes`(`bike_id`) ON DELETE CASCADE
) AUTO_INCREMENT = 1001;

-- Comptes de test : admin@velo.fr / Admin123!  et  employe@velo.fr / Employe123!
-- Les mots de passe sont hachés avec password_hash()
INSERT INTO users (firstname, lastname, login, password, role) VALUES
('Alice', 'Martin', 'admin@velo.fr', '$2y$12$fDro5p919ps54QNf11fX/e/bswX7S7yL3rQ2Z713nD./rwmrxxw9W', 'admin'),
('Bruno', 'Petit', 'employe@velo.fr', '$2y$12$h.icqiwo0LZr676H4/lGJ.4ZCa9wO4wJ1Pv/r5OFWXI7DkA.UxjWm', 'employee'),
('Chloé', 'Durand', 'chloe.durand@mail.fr', NULL, 'citizen'),
('David', 'Bernard', 'david.bernard@mail.fr', NULL, 'citizen');

INSERT INTO bikes (registration_number, availability, photo) VALUES
('VEL-001', TRUE, NULL),
('VEL-002', TRUE, NULL),
('VEL-003', TRUE, NULL),
('VEL-004', FALSE, NULL),
('VEL-005', TRUE, NULL);

-- Dates calculées à partir d'aujourd'hui pour que les exemples restent dans le futur
INSERT INTO reservations (user_id, bike_id, start_date, end_date, status) VALUES
(3, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'pending'),
(4, 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'pending'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'accepted');
