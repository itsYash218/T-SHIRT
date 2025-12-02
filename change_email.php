<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user'])) {
    die("Not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = intval($_POST['user_id']); // vulnerable: trusts user input
    $newEmail = $_POST['email']; // vulnerable: no validation

    // ❗ This is intentionally vulnerable for demo
    // Example: pretend to update email in DB
    // $stmt = $db->prepare("UPDATE users SET email=? WHERE id=?");
    // $stmt->execute([$newEmail, $userId]);

    echo "Email for user ID $userId changed to $newEmail (demo only)";
}
