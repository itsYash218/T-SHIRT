<?php
session_start();
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // -----------------------
    // Development Mode Only
    // -----------------------
    $dev_mode = true;  // Set to false on real server
    $allowed_dev_ips = ['10.180.139.76', '::1'];

    // Print your IP for debugging
    $client_ip = $_SERVER['REMOTE_ADDR'];

    // -----------------------
    // Optional: Dev-only quick reset
    // -----------------------
    if ($dev_mode && in_array($client_ip, $allowed_dev_ips)) {
        if (isset($_POST['dev_reset']) && $_POST['dev_reset'] === "true") {
            echo "<h2 style='color:red'>DEV MODE PASSWORD RESET</h2>";
            echo "<p>This reset is allowed only on localhost during development.</p>";
            exit;
        }
    }

    // -----------------------
    // Normal Reset Procedure
    // -----------------------
    $input = trim($_POST['email']);

    if ($input !== "") {

        // Check if email or username exists
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $email = $row['email'];
            $_SESSION['reset_email'] = $email;

            // Create OTP
            $otp = random_int(100000, 999999);

            // Store OTP in DB
            $stmt_update = $conn->prepare("
                UPDATE users 
                SET reset_otp = ?, otp_expires = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                WHERE email = ?
            ");
            $stmt_update->bind_param("is", $otp, $email);
            $stmt_update->execute();
            $stmt_update->close();

            $message = "OTP sent.";

        } else {
            $message = "No user found with that email or username.";
        }

        $stmt->close();

    } else {
        $message = "Please enter a valid email or username.";
    }
}

$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Forgot Password</title>
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
input, button {
    width: 90%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: none;
    font-size: 16px;
}
input {
    background-color: #222;
    color: #fff;
}
button {
    background-color: #00b0ff;
    color: #000;
    font-weight: bold;
    cursor: pointer;
}
button:hover {
    background-color: #0091ea;
}
p.message {
    color: #ff5252;
}
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
    <h2>Forgot Password</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Enter Email" required>
        <button type="submit">Send OTP</button>
    </form>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</div>
</body>
</html>
