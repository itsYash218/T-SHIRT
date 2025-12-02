<?php
session_start();
require_once 'config.php';

/*
    ❗ BROKEN ACCESS CONTROL (INTENTIONAL)
    - Any logged-in user can access add_user.php directly
    - No admin verification
    - Plaintext passwords
    - No CSRF protection
*/

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$loggedIn = true;
$isAdmin  = !empty($_SESSION['user']['is_admin']); // UI only

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];  // ❗ STORED IN PLAINTEXT
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, is_admin, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param("sssi", $username, $email, $password, $is_admin);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add User (Vulnerable)</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body { 
    margin:0; 
    background:#000; 
    color:#fff; 
    font-family:'Segoe UI',Tahoma, Geneva, Verdana,sans-serif;
}

/* FIXED NAVBAR */
.navbar {
    background:#111;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:nowrap;
    width:100%;
    box-sizing:border-box;
}

.navbar a {
    text-decoration:none;
}

.nav-brand a {
    font-size:1.5rem; 
    color:#00b0ff; 
    font-weight:bold; 
}

.nav-links { 
    list-style:none; 
    display:flex; 
    gap:25px; 
    padding:0; 
    margin:0; 
}

.nav-links a { 
    color:#fff; 
    text-decoration:none; 
    font-weight:500; 
    transition:0.2s;
}

.nav-links a:hover { 
    color:#00b0ff; 
}

.cart-badge { 
    background:#00b0ff; 
    color:#000; 
    border-radius:50%; 
    padding:3px 7px; 
    margin-left:5px; 
}

/* FORM + PAGE */
.container {
    width: 450px;
    margin: 50px auto;
    background: #111;
    padding: 25px;
    border-radius: 12px;
}

h2 {
    margin-bottom: 20px;
    color: #00b0ff;
}

input[type=text],
input[type=password],
input[type=email] {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    background: #222;
    border: none;
    color: #fff;
    border-radius: 6px;
}

button {
    width: 100%;
    padding: 12px;
    background: #0091ea;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
}

button:hover {
    background: #00b0ff;
}

a {
    color: #00b0ff;
    text-decoration: none;
}
</style>

</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a>
    </div>

    <div class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <?php if ($isAdmin): ?>
            <a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>


<div class="container">
    <h2><i class="fas fa-user-plus"></i> Add User</h2>

    <form method="POST">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password (stored plaintext)</label>
        <input type="text" name="password" required>

        <label>
            <input type="checkbox" name="is_admin">
            Make user an admin <span style="color:#ff4444"></span>
        </label>

        <br><br>

        <button type="submit"><i class="fas fa-plus"></i> Add User</button>
    </form>

    <br>
    <a href="admin.php"><i class="fas fa-arrow-left"></i> Back to Admin Panel</a>
</div>

</body>
</html>
