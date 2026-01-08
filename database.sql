CREATE DATABASE IF NOT EXISTS task_manager_fr;
USE task_manager_fr;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE taches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    statut ENUM('en_attente', 'en_cours', 'termine') DEFAULT 'en_attente',
    date_echeance DATE,
    priorite ENUM('basse', 'moyenne', 'haute') DEFAULT 'moyenne',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

INSERT INTO utilisateurs (nom, email, mot_de_passe) 
VALUES ('Admin Test', 'admin@test.com', '$2y$10$YourHashedPasswordHere');
