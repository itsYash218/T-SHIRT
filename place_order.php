<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// USER MUST BE LOGGED IN
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// CART CHECK
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Cart is empty!");
}

$user = $_SESSION['user'];  
$cartItems = $_SESSION['cart'];

// ----------- PRICE TAMPERING ALLOWED ----------------
$finalItems = [];

if(isset($_POST['price'])) {
    // Use price from POST (tamperable via Burp)
    foreach ($cartItems as $index => $item) {
        $price = floatval($_POST['price'][$index]); // sanitize numeric input
        $finalItems[] = [
            "id"       => $item['id'],
            "name"     => $item['name'],
            "quantity" => $item['quantity'],
            "price"    => $price  // USER-CONTROLLED PRICE
        ];
    }
} 

// Load orders.json
$ordersFile = "orders.json";
if (!file_exists($ordersFile)) {
    file_put_contents($ordersFile, json_encode([]));
}

$orders = json_decode(file_get_contents($ordersFile), true);

// New Order Format
$orderData = [
    "order_id" => "ORD" . rand(100000, 999999),
    "username" => $user['username'],
    "date" => date("Y-m-d H:i:s"),
    "billing" => [
        "full_name" => $_POST['full_name'] ?? '',
        "email"     => $_POST['email'] ?? '',
        "address"   => $_POST['address'] ?? '',
        "city"      => $_POST['city'] ?? '',
        "zip"       => $_POST['zip'] ?? ''
    ],
    "items" => $finalItems
];

// Save order
$orders[] = $orderData;
file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));

// EMPTY CART AFTER ORDER
unset($_SESSION['cart']);

// Redirect to Order History Page
header("Location: orders.php");
exit();
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Placed</title>

<!-- ✅ Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
/* 🌌 Modern Neon Dark Theme */
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: radial-gradient(circle at center, #020202 0%, #000 100%);
    color: #e0e0e0;
    overflow-x: hidden;
}

/* Navbar */
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(15, 15, 15, 0.95);
    backdrop-filter: blur(6px);
    padding: 18px 40px;
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(0, 176, 255, 0.2);
}

.nav-brand a {
    font-size: 1.7em;
    font-weight: 700;
    color: #00b0ff;
    text-decoration: none;
    letter-spacing: 1px;
}

.nav-links {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 0;
    padding: 0;
}

.nav-links li a {
    color: #e0e0e0;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease, transform 0.2s ease;
}

.nav-links li a:hover {
    color: #00b0ff;
    transform: translateY(-2px);
}

/* Confirmation container */
.confirmation-container {
    background: linear-gradient(145deg, #0c0c0c 0%, #121212 100%);
    padding: 50px 40px;
    border-radius: 20px;
    text-align: center;
    max-width: 650px;
    margin: 60px auto;
    box-shadow: 0 0 25px rgba(0, 176, 255, 0.3);
    animation: fadeIn 1s ease-in;
    border: 1px solid rgba(0, 176, 255, 0.15);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(25px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Title + tick animation */
h1 {
    color: #00b0ff;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 2em;
}

.tick {
    font-size: 65px;
    color: #00e676;
    opacity: 0;
    transform: scale(0);
    animation: tickBounce 1s ease-out forwards;
}

@keyframes tickBounce {
    0% { opacity: 0; transform: scale(0); }
    50% { opacity: 1; transform: scale(1.3); }
    70% { transform: scale(0.9); }
    100% { opacity: 1; transform: scale(1); }
}

/* Text fade-in */
.thank-text {
    opacity: 0;
    animation: fadeUp 1.5s ease forwards;
    animation-delay: 1s;
    font-size: 1.05em;
    line-height: 1.6em;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Car animation */
.car-container {
    position: relative;
    height: 120px;
    margin-top: 40px;
    overflow: hidden;
}

.car {
    position: absolute;
    left: -120px;
    top: 20px;
    font-size: 60px;
    color: #00e676;
    animation: drive 6s linear infinite, bounce 0.5s ease-in-out infinite alternate;
    text-shadow: 0 0 20px #00e676, 0 0 40px #00e676;
}

.car::before {
    content: '';
    position: absolute;
    right: -25px;
    top: 20px;
    width: 35px;
    height: 12px;
    background: radial-gradient(circle at left, rgba(255,255,200,0.8), transparent 70%);
    transform: rotate(10deg);
    filter: blur(3px);
    opacity: 0.8;
    animation: headlights 1.5s ease-in-out infinite;
}

.car::after {
    content: '';
    position: absolute;
    left: -15px;
    top: 40px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(200, 200, 200, 0.4);
    animation: smoke 1.5s ease-in-out infinite;
}

@keyframes drive {
    0% { left: -120px; transform: rotate(0deg); }
    25% { transform: rotate(3deg); }
    50% { left: 100%; transform: rotate(-3deg); }
    100% { left: -120px; transform: rotate(0deg); }
}

@keyframes bounce {
    from { transform: translateY(0); }
    to { transform: translateY(-4px); }
}

@keyframes smoke {
    0% { opacity: 0.8; transform: translateX(0) scale(1); }
    100% { opacity: 0; transform: translateX(-30px) translateY(-20px) scale(1.5); }
}

@keyframes headlights {
    0%, 100% { opacity: 0.8; }
    50% { opacity: 0.4; }
}

/* Road animation */
.road {
    width: 100%;
    height: 10px;
    background: #333;
    margin-top: 70px;
    position: relative;
    overflow: hidden;
    border-radius: 5px;
    box-shadow: inset 0 0 10px rgba(0,0,0,0.6);
}

.road::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 200%; height: 100%;
    background: repeating-linear-gradient(
        90deg,
        #fff 0 20px,
        transparent 20px 40px
    );
    animation: roadMove 0.8s linear infinite;
    opacity: 0.9;
}

@keyframes roadMove {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* Text styling */
p { margin: 8px 0; }

/* Button */
.btn-shop {
    display: inline-block;
    margin-top: 30px;
    background: linear-gradient(90deg, #002335, #004a75);
    color: #00b0ff;
    text-decoration: none;
    border-radius: 25px;
    padding: 12px 28px;
    font-weight: 600;
    transition: 0.3s ease;
    border: 1px solid rgba(0, 176, 255, 0.3);
}

.btn-shop:hover {
    background: linear-gradient(90deg, #00b0ff, #00e676);
    color: #000;
    transform: translateY(-3px);
    box-shadow: 0 0 20px #00b0ff;
}

/* 📱 Responsive */
@media (max-width: 600px) {
    .navbar {
        flex-direction: column;
        padding: 15px;
        gap: 10px;
    }
    .confirmation-container {
        padding: 35px 20px;
        margin: 40px 10px;
    }
    h1 {
        font-size: 1.6em;
    }
    .btn-shop {
        padding: 10px 20px;
        font-size: 0.9em;
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
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
    </ul>
  </nav>
</header>

<div class="confirmation-container">
    <h1><span class="tick"><i class="fas fa-check-circle"></i></span> Order Placed!</h1>

    <div class="thank-text">
        <p>Thank you, <strong><?= $full_name ?></strong>!</p>
        <p>Your order will be delivered to:</p>
        <p><strong><?= $address ?>, <?= $city ?> - <?= $zip ?></strong></p>
    </div>

    <div class="car-container">
        <div class="car"><i class="fas fa-car-side"></i></div>
        <div class="road"></div>
    </div>

    <a href="index.php" class="btn-shop"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
</div>

</body>
</html>
