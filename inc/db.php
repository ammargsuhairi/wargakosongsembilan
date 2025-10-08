<?php
// inc/db.php - simple PDO connection helper
// Sesuaikan kredensial jika perlu
$dbHost = '127.0.0.1';
$dbName = 'dhsbord';
$dbUser = 'root';
$dbPass = ''; // XAMPP default kosong; ubah jika Anda menggunakan password
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Untuk environment development, tampilkan error. Di production, log saja.
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
