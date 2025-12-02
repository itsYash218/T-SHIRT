<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Safe output
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$loggedIn = isset($_SESSION['user']);
$cartCount = $_SESSION['cart_count'] ?? 0;

// ❌ IDOR VULNERABILITY — user controls which profile they edit
$userId = isset($_GET['id']) ? intval($_GET['id']) : $_SESSION['user']['id'];

$success = '';
$error = '';

// Fetch ANY user info (IDOR)
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Update profile — IDOR again
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($username) || empty($email)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } else {

        // ❌ IDOR — updating ANY user's data without permission
        $updateStmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
        $updateStmt->bind_param("ssi", $username, $email, $userId);

        if ($updateStmt->execute()) {
            $success = "Profile updated successfully!";

            // If editing own account, update session
            if ($userId == $_SESSION['user']['id']) {
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['email'] = $email;
            }

        } else {
            $error = "Failed to update profile. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body { background:#000; color:#eee; font-family:Arial, sans-serif; margin:0; }

/* Navbar */
.navbar {
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#111;
    padding:20px;
    position:sticky;
    top:0;
    z-index:100;
    flex-wrap:wrap;
}
.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }
.nav-links { list-style:none; display:flex; align-items:center; gap:20px; margin:0; padding:0; }
.nav-links li a { color:#e0e0e0; text-decoration:none; font-weight:500; transition:0.2s; }
.nav-links li a:hover { color:#00b0ff; }
.cart-badge { background:#00b0ff; color:#000; padding:2px 6px; border-radius:50%; font-size:0.8em; }

.profile-btn { display:flex; align-items:center; gap:5px; }
.profile-dropdown { display:none; position:absolute; right:0; background:#111; border-radius:10px; list-style:none; padding:10px 0; }
.user-profile:hover .profile-dropdown { display:block; }

.container {
    max-width:450px;
    margin:80px auto;
    background:#111;
    padding:30px;
    border-radius:10px;
}
h2 { text-align:center; color:#00b0ff; margin-bottom:25px; }

form { display:flex; flex-direction:column; gap:15px; }
input, button { padding:12px; border-radius:5px; border:none; font-size:1em; }
input { background:#222; color:#fff; }
button { background:#002335; color:#000; font-weight:bold; cursor:pointer; }
button:hover { background:#00b0ff; color:#000; }

.message { padding:12px; border-radius:5px; margin-bottom:15px; text-align:center; font-weight:bold; }
.success { background:#0f0; color:#000; }
.error { background:#f00; color:#fff; }

.back-btn { display:block; text-align:center; margin-top:20px; color:#00b0ff; text-decoration:none; }
.back-btn:hover { text-decoration:underline; color:#00fff0; }
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>

    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
        <?php if($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
      </a></li>

      <?php if($loggedIn): ?>
      <li class="user-profile" style="position:relative;">
        <a class="profile-btn"><i class="fas fa-user-circle"></i> <?= h($_SESSION['user']['username']) ?> <i class="fas fa-caret-down"></i></a>
        <ul class="profile-dropdown">
          <li><a href="profile.php"><i class="fas fa-id-card"></i> My Profile</a></li>
          <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
          <?php if(!empty($_SESSION['user']['is_admin'])): ?>
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
    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>

    <?php if($success): ?>
        <div class="message success"><?= h($success) ?></div>
    <?php elseif($error): ?>
        <div class="message error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" value="<?= h($user['username']) ?>" required>
        <input type="email" name="email" value="<?= h($user['email']) ?>" required>
        <button type="submit"><i class="fas fa-save"></i> Save Changes</button>
    </form>

    <a href="profile.php?id=<?= $userId ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Profile</a>
</div>

</body>
</html>
