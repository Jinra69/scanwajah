-- Jalankan SQL ini di HeidiSQL (bawaan Laragon) atau phpMyAdmin

CREATE DATABASE IF NOT EXISTS scanwajah CHARACTER SET utf8mb4;
USE scanwajah;

CREATE TABLE mahasiswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    umur INT NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    foto VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
