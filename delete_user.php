<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user'])) {
    die("Not logged in");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ❗ Vulnerable: still no CSRF check
    // Always delete the currently logged-in user
    $userId = $_SESSION['user']['id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "<h2>User with ID $userId was DELETED ✔</h2>";
        echo "<p>This happened because the form had NO CSRF protection.</p>";

        session_destroy(); // Log them out
    } else {
        echo "<h2>Failed to delete user.</h2>";
    }

    exit;
}
?>
<h2>Invalid request</h2>
