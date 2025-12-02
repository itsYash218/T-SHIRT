<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Helper function to escape output
function h($string) {
    return htmlspecialchars($string);
}

// Path to orders JSON
$ordersFile = "orders.json";

if (!file_exists($ordersFile)) {
    file_put_contents($ordersFile, json_encode([]));
}

// Load orders
$orders = json_decode(file_get_contents($ordersFile), true);

// Get order_id from GET
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : null;
if (!$orderId) {
    die("Invalid order ID.");
}

// Find the order
$orderKey = null;
foreach ($orders as $key => $order) {
    if ($order['order_id'] === $orderId) {
        $orderKey = $key;
        break;
    }
}

if ($orderKey === null) {
    die("Order not found.");
}

// Current billing info
$billing = $orders[$orderKey]['billing'];
$full_name = isset($billing['full_name']) ? $billing['full_name'] : "";
$email = isset($billing['email']) ? $billing['email'] : "";
$address = isset($billing['address']) ? $billing['address'] : "";
$city = isset($billing['city']) ? $billing['city'] : "";
$zip = isset($billing['zip']) ? $billing['zip'] : "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_full_name = $_POST['full_name'] ?? $full_name;
    $new_email     = $_POST['email'] ?? $email;
    $new_address   = $_POST['address'] ?? $address;
    $new_city      = $_POST['city'] ?? $city;
    $new_zip       = $_POST['zip'] ?? $zip;

    $orders[$orderKey]['billing'] = [
        'full_name' => $new_full_name,
        'email'     => $new_email,
        'address'   => $new_address,
        'city'      => $new_city,
        'zip'       => $new_zip
    ];

    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Address - <?= h($orderId) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #000;
    color: #e0e0e0;
}

a {
    text-decoration: none;
    color: inherit;
}

.container {
    width: 95%;
    max-width: 600px;
    margin: 50px auto;
    background: #111;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0,176,255,0.3);
}

h1 {
    color: #00b0ff;
    text-align: center;
    margin-bottom: 25px;
}

form label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
    color: #00b0ff;
}

form input {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 6px;
    border: 1px solid #333;
    background: #1a1a1a;
    color: #fff;
}

form button {
    margin-top: 20px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background: #00b0ff;
    color: #000;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

form button:hover {
    background: #00e676;
}

.back-btn {
    display: inline-block;
    margin-top: 15px;
    color: #00b0ff;
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="container">
    <h1>Change Address</h1>
    <form method="POST">
        <label for="full_name">Full Name</label>
        <input type="text" name="full_name" id="full_name" value="<?= h($full_name) ?>" required>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= h($email) ?>" required>

        <label for="address">Address</label>
        <input type="text" name="address" id="address" value="<?= h($address) ?>" required>

        <label for="city">City</label>
        <input type="text" name="city" id="city" value="<?= h($city) ?>" required>

        <label for="zip">ZIP</label>
        <input type="text" name="zip" id="zip" value="<?= h($zip) ?>" required>

        <button type="submit"><i class="fas fa-save"></i> Update Address</button>
    </form>
    <a class="back-btn" href="orders.php"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

</body>
</html>
