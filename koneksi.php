
<?php
// ========================================
// KONFIGURASI DATABASE WEDORA
// ========================================

$host = "localhost";
$username = "root";
$password = "";
$database = "wedora_db";

// Membuat koneksi ke database
$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Mengatur karakter agar mendukung UTF-8
$conn->set_charset("utf8mb4");

?>
