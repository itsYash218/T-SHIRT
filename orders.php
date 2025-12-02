<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php'; // optional if you need DB for users

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['user']['username']; // or 'id' if using numeric ID

// Path to orders JSON
$ordersFile = "orders.json";

// Ensure file exists
if (!file_exists($ordersFile)) {
    file_put_contents($ordersFile, json_encode([]));
}

// Load orders
$orders = json_decode(file_get_contents($ordersFile), true);

// Filter orders to show only current user
$userOrders = [];
foreach ($orders as $order) {
    if (isset($order['username']) && $order['username'] === $currentUser) {
        $userOrders[] = $order;
    }
}

// Handle Cancel Order
if (isset($_GET['cancel']) && !empty($_GET['cancel'])) {
    $cancelId = $_GET['cancel'];
    foreach ($orders as $key => $order) {
        if ($order['order_id'] === $cancelId && $order['username'] === $currentUser) {
            unset($orders[$key]);
            file_put_contents($ordersFile, json_encode(array_values($orders), JSON_PRETTY_PRINT));
            header("Location: orders.php");
            exit();
        }
    }
}

// Escape output
function h($string) {
    return htmlspecialchars($string);
}

// Cart count for navbar
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $c) {
        $cartCount += $c['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<style>
body { margin:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#000; color:#e0e0e0; }
a { text-decoration:none; color:inherit; }
.navbar { display:flex; justify-content:space-between; align-items:center; background: #111; padding: 18px 40px; position: sticky; top:0; z-index:100; }
.nav-brand a { font-size:1.7em; font-weight:700; color:#00b0ff; letter-spacing:1px; }
.nav-links { list-style:none; display:flex; gap:20px; margin:0; padding:0; }
.nav-links li a:hover { color:#00b0ff; }
.container { width:95%; max-width:1200px; margin:30px auto; }
h1 { text-align:center; color:#00b0ff; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
table th, table td { padding:12px 10px; border:1px solid #333; text-align:left; }
table th { background:#111; color:#00b0ff; }
table td { background:#1a1a1a; }
.action-btn { display:inline-block; margin:2px 0; padding:5px 10px; border-radius:6px; font-weight:bold; text-align:center; color:#fff; transition:0.3s; }
.cancel-btn { background:#ff1744; }
.cancel-btn:hover { background:#ff4569; }
.change-btn { background:#00b0ff; }
.change-btn:hover { background:#00e676; color:#000; }
@media (max-width:768px) {
    table, thead, tbody, th, td, tr { display:block; }
    table tr { margin-bottom:15px; border:1px solid #333; border-radius:8px; padding:10px; }
    table th { display:none; }
    table td { display:flex; justify-content:space-between; padding:10px 5px; border:none; border-bottom:1px solid #333; }
    table td:last-child { border-bottom:none; }
    table td::before { content: attr(data-label); font-weight:bold; color:#00b0ff; }
    .action-btn { padding:5px 8px; font-size:0.8rem; }
}
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>
    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart<?php if($cartCount>0): ?> <span class="cart-badge"><?= $cartCount ?></span><?php endif; ?></a></li>
      <li><a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a></li>
    </ul>
  </nav>
</header>

<div class="container">
    <h1>My Orders</h1>

    <?php if (!empty($userOrders)): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>City</th>
                <th>ZIP</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($userOrders as $order):
                $billing = $order['billing'] ?? [];
                $full_name = $billing['full_name'] ?? "-";
                $email = $billing['email'] ?? "-";
                $address = $billing['address'] ?? "-";
                $city = $billing['city'] ?? "-";
                $zip = $billing['zip'] ?? "-";

                // Calculate total from items
                $total = 0;
                if (!empty($order['items'])) {
                    foreach ($order['items'] as $item) {
                        $itemPrice = $item['price'] ?? 0;
                        $itemQty = $item['quantity'] ?? 0;
                        $total += $itemPrice * $itemQty;
                    }
                }
            ?>
            <tr>
                <td data-label="Order ID"><?= h($order['order_id']) ?></td>
                <td data-label="Date"><?= h($order['date']) ?></td>
                <td data-label="Full Name"><?= h($full_name) ?></td>
                <td data-label="Email"><?= h($email) ?></td>
                <td data-label="Address"><?= h($address) ?></td>
                <td data-label="City"><?= h($city) ?></td>
                <td data-label="ZIP"><?= h($zip) ?></td>
                <td data-label="Total">₹<?= h($total) ?></td>
                <td data-label="Actions">
                    <a class="action-btn cancel-btn" href="orders.php?cancel=<?= h($order['order_id']) ?>" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel</a>
                    <a class="action-btn change-btn" href="change_address.php?order_id=<?= h($order['order_id']) ?>">Change Address</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p style="text-align:center; margin-top:20px; font-size:18px;">You have no orders yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
