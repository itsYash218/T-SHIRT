<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username && $email && $password && $confirm) {
        if ($password !== $confirm) {
            $error = "⚠️ Passwords do not match.";
        } else {
            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param('ss', $username, $email);
            $check->execute();
            $res = $check->get_result();

            if ($res->num_rows > 0) {
                $error = "⚠️ Username or email already exists.";
            } else {
                // STORE PASSWORD IN PLAIN TEXT (UNSAFE – LOCAL TESTING ONLY)
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('sss', $username, $email, $password);

                if ($stmt->execute()) {
                    $success = "✅ Registration successful! <a href='login.php'>Login now</a>";
                } else {
                    $error = "❌ Error during registration.";
                }
                $stmt->close();
            }
        }
    } else {
        $error = "⚠️ Please fill in all fields.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- Body --- */
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #000;
    color: #e0e0e0;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* --- Container --- */
.container {
    max-width: 400px;
    width: 90%;
    background-color: #111;
    padding: 40px 50px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 25px rgba(0,0,0,0.7);
}

/* --- Heading --- */
h2 {
    color: #00b0ff;
    margin-bottom: 25px;
    font-size: 26px;
}

/* --- Inputs --- */
input {
    width: 100%;
    padding: 14px 12px;
    margin: 12px 0;
    border: none;
    border-radius: 6px;
    background-color: #222;
    color: #fff;
    font-size: 16px;
    transition: 0.3s;
}
input:focus {
    outline: none;
    background-color: #333;
}

/* --- Password Container & Eye Icon --- */
.password-container {
    position: relative;
    width: 100%;
    margin: 15px 0; /* more spacing between fields */
}

.password-container input {
    width: 100%;
    padding: 14px 14px 14px 12px; /* add right padding for eye icon */
    border: none;
    border-radius: 6px;
    background-color: #222;
    color: #fff;
    font-size: 16px;
    transition: background 0.2s;
}

.password-container input:focus {
    outline: none;
    background-color: #333;
}

.password-container .toggle-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #00b0ff;
    font-size: 18px;
    transition: color 0.2s ease;
}

.password-container .toggle-icon:hover {
    color: #0091ea;
}


/* --- Button --- */
button.submit-btn {
    width: 100%;
    padding: 14px;
    margin-top: 20px;
    background-color: #00b0ff;
    border: none;
    color: #000;
    font-weight: bold;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}
button.submit-btn:hover {
    background-color: #0091ea;
}

/* --- Messages --- */
.error {
    color: #ff5252;
    margin-top: 12px;
    font-size: 14px;
}
.success {
    color: #00e676;
    margin-top: 12px;
    font-size: 14px;
}

/* --- Links --- */
a {
    color: #00b0ff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="container">
    <h2>Create Your Account</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>

        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <i class="fa-solid fa-eye toggle-icon" id="togglePassword"></i>
        </div>

        <div class="password-container">
            <input type="password" id="confirm" name="confirm" placeholder="Confirm Password" required>
            <i class="fa-solid fa-eye toggle-icon" id="toggleConfirm"></i>
        </div>

        <button type="submit" class="submit-btn">Register</button>
    </form>

    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
togglePassword.addEventListener('click', () =>
