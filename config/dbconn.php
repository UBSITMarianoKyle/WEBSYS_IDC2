<?php
    
CREATE DATABASE IF NOT EXISTS barangay_health_db;

USE barangay_health_db;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','bhw','nurse','mho','resident')
        DEFAULT 'resident',
    status VARCHAR(20) DEFAULT 'active'
);

CREATE TABLE residents (
    resident_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE,
    sex VARCHAR(10),
    address TEXT,
    household_no VARCHAR(30),
    contact VARCHAR(20),
    health_condition TEXT,
    allergies TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE SET NULL
);




?>