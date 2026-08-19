-- Base de données : gestion d'hôpital
-- Importez ce fichier dans phpMyAdmin ou via : mysql -u root -p < hopital.sql

CREATE DATABASE IF NOT EXISTS hopital_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hopital_db;

-- ------------------------------------------------------
-- Utilisateurs (personnel autorisé à se connecter)
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Compte par défaut : admin / admin123
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$10$m1mfM3d2lcpaXGdVvzaxmu5Uf9yy9iVDqwNEgMDvBtglc2TLFOs2q', 'Administrateur', 'admin');
-- Le hash ci-dessus correspond au mot de passe : admin123

-- ------------------------------------------------------
-- Départements
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

INSERT INTO departments (name, description) VALUES
('Cardiologie', 'Prise en charge des maladies du cœur et des vaisseaux'),
('Pédiatrie', 'Soins médicaux destinés aux enfants'),
('Chirurgie générale', 'Interventions chirurgicales courantes'),
('Gynécologie', 'Santé de la femme et suivi de grossesse'),
('Urgences', 'Prise en charge des cas urgents 24h/24');

-- ------------------------------------------------------
-- Médecins
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    specialty VARCHAR(100),
    department_id INT,
    phone VARCHAR(30),
    email VARCHAR(100),
    photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO doctors (first_name, last_name, specialty, department_id, phone, email, photo) VALUES
('Kodjo', 'Agbeko', 'Cardiologue', 1, '+228 90 11 22 33', 'k.agbeko@hopital.tg', NULL),
('Ama', 'Sena', 'Pédiatre', 2, '+228 91 22 33 44', 'a.sena@hopital.tg', NULL),
('Yawa', 'Kponvi', 'Chirurgienne', 3, '+228 92 33 44 55', 'y.kponvi@hopital.tg', NULL);

-- ------------------------------------------------------
-- Patients
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(60) NOT NULL,
    last_name VARCHAR(60) NOT NULL,
    birth_date DATE DEFAULT NULL,
    gender ENUM('F','H','Autre') DEFAULT 'F',
    phone VARCHAR(30),
    email VARCHAR(100),
    address VARCHAR(255),
    blood_type VARCHAR(5),
    allergies VARCHAR(255),
    status ENUM('actif','suivi','archive') DEFAULT 'actif',
    notes TEXT,
    photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO patients (first_name, last_name, birth_date, gender, phone, email, address, blood_type, allergies, status, notes, photo) VALUES
('Aïcha', 'Bamba', '1988-04-12', 'F', '+228 90 12 34 56', 'aicha.bamba@mail.com', 'Quartier Bè, Lomé', 'O+', 'Pénicilline', 'actif', 'Suivi tension artérielle. Contrôle prévu dans 3 mois.', NULL),
('Komla', 'Adjovi', '1975-11-02', 'H', '+228 91 22 33 44', '', 'Adidogomé, Lomé', 'A+', 'Aucune connue', 'suivi', 'Diabète type 2, surveillance glycémie mensuelle.', NULL),
('Fatou', 'Diallo', '2001-02-28', 'F', '+228 92 44 55 66', 'fatou.d@mail.com', 'Tokoin, Lomé', 'B-', '', 'actif', 'Consultation de routine, RAS.', NULL);

-- ------------------------------------------------------
-- Rendez-vous
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    status ENUM('planifie','termine','annule') DEFAULT 'planifie',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES
(1, 1, '2026-08-20', '09:30:00', 'Contrôle tension artérielle', 'planifie'),
(2, 1, '2026-08-15', '14:00:00', 'Suivi diabète', 'planifie'),
(3, 2, '2026-08-18', '10:15:00', 'Consultation de routine', 'planifie');
