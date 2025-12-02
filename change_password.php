<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Navbar variables
$loggedIn = isset($_SESSION['user']);
$cartCount = $_SESSION['cart_count'] ?? 0;

// Safe output
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// ❌ IDOR — user can supply ?id= to change any password
$userId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user']['id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch any user's current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if (!$user) {
        $error = "User not found.";
    } 
    // ❌ IDOR — skipping current password check for other users
    elseif ($userId == $_SESSION['user']['id'] && !password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } 
    elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } 
    elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $newHash = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $updateStmt->bind_param("si", $newHash, $userId);
        if ($updateStmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Failed to update password. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Change Password</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
/* ... keep your previous CSS unchanged ... */
/* Reset & Base */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #000;
    color: #eee;
    line-height: 1.5;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #111;
    padding: 10px 20px;
    position: sticky;
    top: 0;
    z-index: 100;
    flex-wrap: wrap;
}

.nav-brand a {
    font-size: 1.5em;
    font-weight: bold;
    color: #00b0ff;
    text-decoration: none;
}

.search-form {
    flex: 1;
    display: flex;
    justify-content: center;
    margin: 10px 0;
}

.search-form input {
    padding: 8px 16px;
    border-radius: 20px;
    border: none;
    background: #222;
    color: #fff;
    width: 60%;
    min-width: 200px;
}

.search-form button {
    padding: 8px 16px;
    background: #002335;
    color: #000;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    transition: 0.2s;
}

.search-form button:hover {
    background: #00b0ff;
    color: #000;
}

.nav-links {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-links li a {
    color: #e0e0e0;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
}

.nav-links li a:hover {
    color: #00b0ff;
}

.user-profile {
    position: relative;
}

.profile-btn {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #e0e0e0;
    text-decoration: none;
    font-weight: 500;
    cursor: pointer;
}

.profile-btn:hover {
    color: #00b0ff;
}

.profile-dropdown {
    display: none;
    position: absolute;
    right: 0;
    background: #111;
    border-radius: 10px;
    list-style: none;
    padding: 10px 0;
    min-width: 180px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
    z-index: 999;
}

.profile-dropdown li {
    padding: 8px 15px;
}

.profile-dropdown li a {
    color: #e0e0e0;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-dropdown li a:hover {
    color: #00b0ff;
}

.user-profile:hover .profile-dropdown {
    display: block;
}

.cart-badge {
    background: #00b0ff;
    color: #000;
    padding: 2px 6px;
    border-radius: 50%;
    font-size: 0.8em;
    margin-left: 5px;
}

/* Container */
.container {
    max-width: 450px;
    margin: 80px auto;
    background: #111;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
}

/* Headings */
h2 {
    text-align: center;
    color: #00b0ff;
    margin-bottom: 25px;
}

/* Forms */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input[type="password"] {
    padding: 12px;
    border-radius: 5px;
    border: none;
    background: #222;
    color: #fff;
    font-size: 1em;
}

button {
    padding: 12px;
    background: #002335;
    color: #000;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.2s;
    font-size: 1em;
}

button:hover {
    background: #00b0ff;
    color: #000;
    transform: translateY(-2px);
}

/* Messages */
.message {
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
    font-weight: bold;
}

.success {
    background: #0f0;
    color: #000;
}

.error {
    background: #f00;
    color: #fff;
}

/* Back button */
.back-btn {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: #00b0ff;
    font-weight: 500;
    transition: 0.2s;
}

.back-btn:hover {
    text-decoration: underline;
    color: #00fff0;
}

/* Responsive */
@media(max-width: 500px) {
    .search-form input {
        width: 50%;
    }
    .nav-links {
        flex-direction: column;
        gap: 10px;
    }
}

</style>
</head>
<body>
<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div> 

    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
        <?php if($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a></li>

      <?php if ($loggedIn): ?>
      <li class="user-profile">
        <a href="#" class="profile-btn">
          <i class="fas fa-user-circle"></i>
          <?= h($_SESSION['user']['username'] ?? 'User') ?>
          <i class="fas fa-caret-down" style="margin-left:5px;"></i>
        </a>
        <ul class="profile-dropdown">
          <li><a href="profile.php"><i class="fas fa-id-card"></i> My Profile</a></li>
          <?php if (!empty($_SESSION['user']['is_admin'])): ?>
            <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
          <?php endif; ?>
          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </li>
      <?php else: ?>
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<div class="container">
    <h2><i class="fas fa-key"></i> Change Password</h2>

    <?php if($success): ?>
        <div class="message success"><?= h($success) ?></div>
    <?php elseif($error): ?>
        <div class="message error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="password" name="current_password" placeholder="Current Password" <?= $userId == $_SESSION['user']['id'] ? 'required' : '' ?>>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit"><i class="fas fa-save"></i> Change Password</button>
    </form>

    <a href="profile.php?id=<?= $userId ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
</div>
</body>
</html>
