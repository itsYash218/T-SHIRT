<?php
// config.php — XAMPP version (tested)

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // default for XAMPP
$DB_NAME = 'tshirtshop';

// turn on mysqli exceptions for easier debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Throwable $e) {
    echo "CONFIG: connection failed → " . $e->getMessage();
    exit;
}
