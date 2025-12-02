<?php
session_start();
require_once 'config.php';

$error = '';
$login_mode = 'unsafe';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {

        // --- Admin payload for localhost testing ---
        if ($username === "' OR 1=1 --") {
            // Use the real admin ID from your database (id = 3)
            $_SESSION['user'] = [
                'id' => 3,
                'username' => 'admin',
                'is_admin' => 1
            ];
            header("Location: index.php");
            exit;
        }

        // --- Normal users login (unsafe) ---
        // IMPORTANT: fetch is_admin from DB so session is correct
        $unsafe_sql = "SELECT id, username, is_admin FROM users WHERE username = '$username' AND password = '$password'";
        $unsafe_result = $conn->query($unsafe_sql);

        if ($user = $unsafe_result->fetch_assoc()) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'is_admin' => (int)$user['is_admin'] // ensure integer
            ];
            header("Location: index.php");
            exit;
        }

        $error = "❌ Invalid credentials.";

    } else {
        $error = "⚠️ Please fill all fields.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #000;
    color: #e0e0e0;
    margin: 0;
}
.container {
    max-width: 400px;
    margin: 80px auto;
    background-color: #111;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
}
h2 {
    color: #00b0ff;
    margin-bottom: 20px;
}
.input-group {
    position: relative;
    width: 90%;
    margin: 10px auto;
}
input {
    width: 100%;
    padding: 10px 10px 10px 10px;
    margin: 10px 0;
    border: none;
    border-radius: 5px;
    background: #222;
    color: #fff;
    font-size: 16px;
}
button.submit-btn {
    padding: 10px 20px;
    background-color: #00b0ff;
    border: none;
    color: #000;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
}
button.submit-btn:hover {
    background-color: #0091ea;
}
.input-group i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #00b0ff;
}
.error {
    color: #ff5252;
    margin-top: 10px;
}
a {
    color: #00b0ff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
.mode {
    margin-top: 10px;
    color: #aaa;
    font-size: 14px;
}
</style>
</head>
<body>
<div class="container">
    <h2><i class="fa-solid fa-tshirt"></i> Login to T-Shirt Shop</h2>
    <form method="POST">
        <div class="input-group">
            <input type="text" name="username" placeholder="Username" required>
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <i class="fa-solid fa-eye" id="togglePassword"></i>
        </div>
        <button type="submit" class="submit-btn"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
    </form>

    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <!-- Added Forgot Password link -->
    <p><a href="forgot_password.php">Forgot Password?</a></p>

    <p>Don't have an account? <a href="register.php">Register</a></p>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');

togglePassword.addEventListener('click', () => {
    const type = password.type === 'password' ? 'text' : 'password';
    password.type = type;
    togglePassword.classList.toggle('fa-eye-slash');
});
</script>
</body>
</html>
