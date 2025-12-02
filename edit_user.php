<?php
session_start();
require_once 'config.php';

/*
    ❗ BROKEN ACCESS CONTROL (INTENTIONAL for VULN LAB)
    - Any logged‑in user can edit ANY user account
    - No admin check
    - Vulnerable to IDOR: edit_user.php?id=1,2,3...
*/

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("User ID missing.");
}

$id = intval($_GET['id']);

// Fetch user to edit
$stmt = $conn->prepare("SELECT id, username, email, password, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// If form submitted, process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];   // ❗ No hashing (VULNERABLE)
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    /*
        ❗ BROKEN ACCESS CONTROL HERE:
        - No check if the current user is admin
        - No validation
        - Users can promote themselves to admin
    */

    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, is_admin=? WHERE id=?");
    $stmt->bind_param("sssii", $username, $email, $password, $is_admin, $id);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit User (Vulnerable)</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body {
    background:#000;
    color:#fff;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding:40px;
}
.container {
    background:#111;
    width:450px;
    margin:auto;
    padding:25px;
    border-radius:12px;
}
h2 {
    color:#00b0ff;
    margin-bottom:20px;
}
input[type=text],
input[type=password] {
    width:100%;
    padding:10px;
    border:none;
    border-radius:6px;
    margin-bottom:15px;
    background:#222;
    color:#fff;
}
button {
    width:100%;
    padding:12px;
    background:#0091ea;
    border:none;
    border-radius:6px;
    font-size:1rem;
    cursor:pointer;
}
button:hover {
    background:#00b0ff;
}
a {
    color:#00b0ff;
    text-decoration:none;
}
</style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-user-edit"></i> Edit User</h2>

    <form method="POST">

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>">

        <label>Password (plaintext):</label>
        <input type="text" name="password" value="<?= htmlspecialchars($user['password']) ?>">

        <label>
            <input type="checkbox" name="is_admin" <?= $user['is_admin'] ? "checked" : "" ?>>
            Make Admin <span style="color:#ff4444;"></span>
        </label>
        <br><br>

        <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
    </form>

    <br>
    <a href="admin.php"><i class="fas fa-arrow-left"></i> Back to Admin Panel</a>
</div>

</body>
</html>
