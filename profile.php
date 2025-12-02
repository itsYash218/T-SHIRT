<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/* ----------------------------------------------------------
   ❌ IDOR VULNERABILITY (INTENTIONALLY ADDED)
   Any logged-in user can view ANY profile by changing ?id=
----------------------------------------------------------- */
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);      // attacker-controlled
} else {
    $userId = $_SESSION['user']['id']; // fallback
}

// Fetch user info
$stmt = $conn->prepare("SELECT id, username, email, is_admin, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows) {
    $user = $res->fetch_assoc();
} else {
    die("User not found");
}

// Fetch recent orders (optional)
$orderStmt = $conn->prepare("SELECT id, status, total, created_at FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$orderStmt->bind_param("i", $userId);
$orderStmt->execute();
$orderRes = $orderStmt->get_result();
$orders = $orderRes->fetch_all(MYSQLI_ASSOC);

$loggedIn = isset($_SESSION['user']);
$cartCount = $_SESSION['cart_count'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

<style>
body { margin:0; background:#000; font-family:Arial; color:#eee; }

/* Navbar */
.navbar { display:flex; align-items:center; justify-content:space-between;
    background:#111; padding:12px 20px; position:sticky; top:0; z-index:100; }

.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }

.nav-links { display:flex; list-style:none; gap:20px; }
.nav-links li a { color:#e0e0e0; font-weight:500; text-decoration:none; }
.nav-links li a:hover { color:#00b0ff; }

.cart-badge { background:#00b0ff; color:#000; padding:2px 6px; border-radius:50%; font-size:.8em; }

/* User dropdown */
.user-profile { position:relative; }
.profile-btn { color:#e0e0e0; display:flex; align-items:center; gap:5px; cursor:pointer; }
.profile-btn:hover { color:#00b0ff; }
.profile-dropdown {
    display:none; position:absolute; right:0; top:100%; background:#111;
    border-radius:10px; list-style:none; padding:10px 0; min-width:170px;
    box-shadow:0 5px 15px rgba(0,0,0,0.5);
}
.user-profile:hover .profile-dropdown { display:block; }
.profile-dropdown li a { color:#e0e0e0; display:flex; gap:10px; text-decoration:none; padding:8px 15px; }
.profile-dropdown li a:hover { color:#00b0ff; }

/* Container */
.container {
    max-width:700px; margin:50px auto; background:#111;
    padding:30px; border-radius:10px;
}
h2 { text-align:center; color:#00b0ff; }
.profile-item { padding:12px 0; border-bottom:1px solid #222; display:flex; justify-content:space-between; }
.action-btn, .back-btn {
    display:inline-block; background:#002335; padding:10px 15px;
    color:#00b0ff; text-decoration:none; border-radius:5px; margin-top:10px;
}
.action-btn:hover, .back-btn:hover { background:#0091ea; color:#000; }
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand">
        <a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a>
    </div>

    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
        <?php if($cartCount > 0): ?>
            <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a></li>

      <li class="user-profile">
        <a class="profile-btn">
            <i class="fas fa-user-circle"></i>
            <?= h($_SESSION['user']['username']) ?>
            <i class="fas fa-caret-down"></i>
        </a>
        <ul class="profile-dropdown">
          <li><a href="profile.php"><i class="fas fa-id-card"></i> My Profile</a></li>

          <?php if (!empty($_SESSION['user']['is_admin'])): ?>
            <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
          <?php endif; ?>

          <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
      </li>
    </ul>
  </nav>
</header>

<div class="container">

    <h2><i class="fas fa-user-circle"></i> Profile</h2>

    <div class="profile-item">
        <strong>Username:</strong> <?= h($user['username']) ?>
    </div>

    <div class="profile-item">
        <strong>Email:</strong> <?= h($user['email']) ?>
    </div>

    <div class="profile-item">
        <strong>Account Type:</strong> <?= $user['is_admin'] ? 'Admin' : 'Customer' ?>
    </div>

    <div class="profile-item">
        <strong>Member Since:</strong> <?= h($user['created_at']) ?>
    </div>

    <!-- buttons -->
    <a href="edit_profile.php?id=<?= $user['id'] ?>" class="action-btn"><i class="fas fa-edit"></i> Edit Profile</a>
    <a href="change_password.php" class="action-btn"><i class="fas fa-key"></i> Change Password</a>
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>

</div>

</body>
</html>
