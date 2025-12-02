<?php
session_start();
require_once 'config.php';

/*
    ❗ BROKEN ACCESS CONTROL (INTENTIONAL)
    - No is_admin check
    - Any logged-in user OR attacker via CSRF can delete anyone
*/

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['user_id'])) {
    die("User ID missing.");
}

$user_id = $_POST['user_id'];

// ❗ Intentionally vulnerable — no permission checks
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: admin.php");
exit;
