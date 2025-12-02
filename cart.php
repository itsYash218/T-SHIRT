<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Hardcoded products
$products = [
    ['id' => 1, 'name' => 'Classic Black T-Shirt', 'price' => 420.00, 'image' => 'images/black_tshirt.png'],
    ['id' => 2, 'name' => 'White Crew Neck T-Shirt', 'price' => 430.00, 'image' => 'images/white_tshirt.png'],
    ['id' => 3, 'name' => 'Graphic Tee', 'price' => 440.00, 'image' => 'images/graphic_tee.png'],
    ['id' => 4, 'name' => 'V-Neck T-Shirt', 'price' => 450.00, 'image' => 'images/vneck_tshirt.png'],
    ['id' => 5, 'name' => 'Long Sleeve Tee', 'price' => 460.00, 'image' => 'images/long_sleeve.png'],
    ['id' => 6, 'name' => 'Polo Shirt', 'price' => 470.00, 'image' => 'images/polo_shirt.png'],
    ['id' => 7, 'name' => 'Hooded Sweatshirt', 'price' => 480.00, 'image' => 'images/hoodie.png'],
    ['id' => 8, 'name' => 'Tank Top', 'price' => 410.00, 'image' => 'images/tank_top.png'],
    ['id' => 9, 'name' => 'Raglan Sleeve Tee', 'price' => 495.00, 'image' => 'images/raglan_tee.png'],
    ['id' => 10, 'name' => 'Tie-Dye T-Shirt', 'price' => 500.00, 'image' => 'images/tie_dye.png'],
];

// Initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Size options
$sizes = ['S', 'M', 'L', 'XL', 'XXL'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update quantities and sizes
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int)$id;
            $qty = max(1, (int)$qty);
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['quantity'] = $qty;
                $_SESSION['cart'][$id]['size'] = $_POST['sizes'][$id] ?? ($_SESSION['cart'][$id]['size'] ?? 'M');
            }
        }
    }

    // Remove item
    if (isset($_POST['remove_item'])) {
        $removeId = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$removeId]);
    }

    // Clear cart
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }

    // Checkout redirect
    if (isset($_POST['checkout'])) {
        header("Location: checkout.php");
        exit;
    }

    header("Location: cart.php");
    exit;
}

// Ensure all cart items have a size
foreach ($_SESSION['cart'] as $id => $item) {
    if (!isset($_SESSION['cart'][$id]['size'])) {
        $_SESSION['cart'][$id]['size'] = 'M';
    }
}

// Cart items and total
$cartItems = $_SESSION['cart'];
$total = 0;
foreach ($cartItems as $item) $total += $item['price'] * $item['quantity'];

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your Cart - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#000; color:#e0e0e0; }
.navbar { display:flex; align-items:center; justify-content:space-between; background:#111; padding:20px; position:sticky; top:0; z-index:100; flex-wrap:wrap; }
.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }
.nav-links { list-style:none; display:flex; align-items:center; gap:15px; margin:0; padding:0; }
.nav-links li a { color:#e0e0e0; text-decoration:none; font-weight:500; }
.nav-links li a:hover { color:#00b0ff; }

.cart-container { max-width:900px; margin:40px auto; background:#111; padding:20px; border-radius:10px; }
.cart-container h1 { margin-top:0; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #333; }
th { color:#00b0ff; }
td img { width:60px; border-radius:5px; vertical-align:middle; margin-right:10px; }
.qty-input { width:60px; padding:5px; border:none; border-radius:5px; background:#222; color:#fff; text-align:center; }
.select-size { padding:5px; border:none; border-radius:5px; background:#222; color:#fff; }
.btn { padding:8px 16px; border:none; border-radius:5px; font-weight:bold; cursor:pointer; transition:0.2s; }
.btn-update { background:#004d40; color:#00e676; }
.btn-update:hover { background:#00e676; color:#000; }
.btn-remove { background:#350000; color:#ff5252; }
.btn-remove:hover { background:#ff5252; color:#000; }
.btn-clear { background:#333; color:#ff9800; }
.btn-clear:hover { color:#000; background:#ff9800; }
.btn-shop { display:inline-block; margin-top:20px; background:#002335; color:#00b0ff; text-decoration:none; border-radius:20px; padding:10px 20px; font-weight:bold; transition:0.2s; }
.btn-shop:hover { background:#0091ea; color:#000; }
.btn-checkout { background:#00695c; color:#00e676; padding:10px 16px; border-radius:5px; font-weight:bold; }
.btn-checkout:hover { background:#00e676; color:#000; }
.total { text-align:right; font-size:1.2em; margin-top:20px; }
.empty { text-align:center; padding:40px; background:#222; color:#fff; }
.cart-badge { background:#00b0ff; color:#000; font-size:0.8em; padding:2px 6px; border-radius:50%; margin-left:5px; font-weight:bold; vertical-align:super; }
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
    </ul>
  </nav>
</header>

<main>
<div class="cart-container">
  <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>

  <?php if (empty($cartItems)): ?>
      <div class="empty">
          <p>Your cart is empty.</p>
          <a href="index.php" class="btn-shop"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
      </div>
  <?php else: ?>
      <form method="post">
          <table>
              <thead>
                  <tr>
                      <th>Product</th>
                      <th>Size</th>
                      <th>Price</th>
                      <th>Quantity</th>
                      <th>Total</th>
                      <th>Action</th>
                  </tr>
              </thead>
              <tbody>
                  <?php foreach ($cartItems as $item): ?>
                      <tr>
                          <td><img src="<?= h($item['image']) ?>" alt="<?= h($item['name']) ?>"> <?= h($item['name']) ?></td>
                          <td>
                              <select name="sizes[<?= $item['id'] ?>]" class="select-size">
                                  <?php foreach ($sizes as $size): ?>
                                      <option value="<?= $size ?>" <?= (isset($item['size']) && $item['size'] === $size) ? 'selected' : '' ?>><?= $size ?></option>
                                  <?php endforeach; ?>
                              </select>
                          </td>
                          <td>₹<?= number_format($item['price'],2) ?></td>
                          <td><input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" class="qty-input"></td>
                          <td>₹<?= number_format($item['price']*$item['quantity'],2) ?></td>
                          <td><button type="submit" name="remove_item" value="<?= $item['id'] ?>" class="btn btn-remove"><i class="fas fa-trash"></i></button></td>
                      </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>

          <div class="total"><strong>Total: ₹<?= number_format($total,2) ?></strong></div>

          <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
              <button type="submit" name="update_cart" class="btn btn-update"><i class="fas fa-sync"></i> Update Cart</button>
              <button type="submit" name="clear_cart" class="btn btn-clear"><i class="fas fa-times-circle"></i> Clear Cart</button>
              <button type="submit" name="checkout" class="btn btn-checkout"><i class="fas fa-credit-card"></i> Checkout / Pay</button>
          </div>
      </form>

      <a href="index.php" class="btn-shop"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
  <?php endif; ?>
</div>
</main>
</body>
</html>
