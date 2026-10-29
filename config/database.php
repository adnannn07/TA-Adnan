<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_spk_komputer';

// Aktifkan mode exception mysqli supaya error query lebih mudah dilacak
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
