<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors',1);

// Get cart items from session
$cartItems = $_SESSION['cart'] ?? [];
$total = 0;
foreach($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES,'UTF-8'); }

// Get total item count for cart badge
$cartCount = array_sum(array_column($cartItems, 'quantity'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
<style>
body { margin:0; font-family:'Segoe UI', sans-serif; background:#000; color:#e0e0e0; }
.navbar { display:flex; align-items:center; justify-content:space-between; background:#111; padding:20px; position:sticky; top:0; z-index:100; flex-wrap:wrap; }
.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }
.nav-links { list-style:none; display:flex; align-items:center; gap:15px; margin:0; padding:0; }
.nav-links li a { color:#e0e0e0; text-decoration:none; font-weight:500; }
.nav-links li a:hover { color:#00b0ff; }

.checkout-container { max-width:600px; margin:40px auto; background:#111; padding:20px; border-radius:10px; }
input, textarea { width:100%; padding:8px; margin:5px 0; border-radius:5px; border:none; background:#222; color:#fff; }
.btn { padding:10px 20px; border:none; border-radius:5px; background:#00b0ff; color:#000; cursor:pointer; margin-top:10px; }
.btn:hover { background:#0091ea; color:#fff; }
.order-summary { background:#222; padding:15px; border-radius:10px; margin-top:20px; }
a { color:#00b0ff; text-decoration:none; }
a:hover { text-decoration:underline; }
.cart-badge {
    background:#00b0ff;
    color:#000;
    font-size:0.8em;
    padding:2px 6px;
    border-radius:50%;
    margin-left:5px;
}
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>
    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li>
        <a href="cart.php">
          <i class="fas fa-shopping-cart"></i> Cart
          <?php if($cartCount > 0): ?>
            <span class="cart-badge"><?= $cartCount ?></span>
          <?php endif; ?>
        </a>
      </li>
    </ul>
  </nav>
</header>

<div class="checkout-container">
<h1><i class="fas fa-credit-card"></i> Checkout</h1>

<?php if(empty($cartItems)): ?>
    <p>Your cart is empty. <a href="index.php">Go back to shop</a></p>
<?php else: ?>
<form method="post" action="place_order.php">
<h2>Billing Information</h2>
<input type="text" name="full_name" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="address" placeholder="Address" required>
<input type="text" name="city" placeholder="City" required>
<input type="text" name="zip" placeholder="ZIP / Postal Code" required>

<!-- ⭐ Hidden field: send total price in POST -->
<input type="hidden" name="total_price" value="<?= $total ?>">

<h2>Order Summary</h2>
<div class="order-summary">
<?php foreach($cartItems as $item): ?>
<p><i class="fas fa-tshirt"></i> <?= h($item['name']) ?> - <?= $item['quantity'] ?> : ₹<?= number_format($item['price']*$item['quantity'],2) ?></p>
<?php endforeach; ?>
<p><strong>Total: ₹<?= number_format($total,2) ?></strong></p>
</div>

<button type="submit" class="btn"><i class="fas fa-check"></i> Place Order</button>
</form>
<?php endif; ?>
</div>
</body>
</html>
