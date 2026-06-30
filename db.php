<?php
// Konfigurasi koneksi database
// Default Laragon: user 'root', password kosong
$host = "localhost";
$dbname = "scanwajah";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage() . "<br>Pastikan database 'scanwajah' sudah dibuat dan Laragon (MySQL) sedang berjalan.");
}
