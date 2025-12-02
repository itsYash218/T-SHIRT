<?php
session_start();
require_once "config.php";

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Get product ID
$id = (int)($_GET['id'] ?? 0);

// Fetch product
$stmt = $conn->prepare("SELECT id, name, description, price, stock, image FROM products WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity
        ];
    }

    header("Location: cart.php");
    exit;
}

// Submit comment (STORED XSS VULNERABLE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $username = $_POST['username'];
    $comment  = $_POST['comment']; // RAW HTML (XSS)

    $stmt = $conn->prepare("INSERT INTO comments (product_id, author, body) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $id, $username, $comment);
    $stmt->execute();

    header("Location: product.php?id=$id");
    exit;
}

// Fetch comments
$stmt = $conn->prepare("SELECT author, body FROM comments WHERE product_id=? ORDER BY id DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$comments = $stmt->get_result();

// Cart count
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#000; color:#e0e0e0; }
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #111;
    padding:20px;
    position: sticky;
    top: 0;
    z-index: 100;
    flex-wrap: wrap; 
}
.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }
.nav-links { list-style:none; display:flex; align-items:center; gap:15px; margin:0; padding:0; }
.nav-links li a { color:#e0e0e0; text-decoration:none; font-weight:500; }
.nav-links li a:hover { color:#00b0ff; }

.product-container { max-width:900px; margin:40px auto; background:#111; padding:20px; border-radius:10px; display:flex; gap:20px; flex-wrap:wrap; }
.product-container img { width:100%; max-width:350px; border-radius:10px; }
.product-details { flex:1; }

.product-details h1 { margin-top:0; }
.product-details p { margin:10px 0; }

.btn { display:inline-block; padding:10px 20px; border-radius:20px; text-decoration:none; font-weight:bold; transition:0.2s; }
.btn-back { background:#002335; color:#00b0ff; }
.btn-back:hover { background:#0091ea; color:#000; }
.btn-cart { background:#004d40; color:#00e676; margin-top:10px; border:none; cursor:pointer; }
.btn-cart:hover { background:#00e676; color:#000; }

.comments-section { max-width:900px; margin:40px auto; background:#1a1a1a; padding:20px; border-radius:10px; }
.comment { border-bottom:1px solid #333; padding:10px 0; }
.comment .user { font-weight:bold; color:#00b0ff; }
.comment .time { font-size:0.9em; color:#888; }
.comment-form input, .comment-form textarea { width:98%; padding:10px; margin-bottom:10px; border-radius:5px; border:none; background:#222; color:#fff; }
.comment-form button { padding:10px 20px; border:none; border-radius:5px; background:#002335; color:#00b0ff; font-weight:bold; cursor:pointer; transition:0.2s; }
.comment-form button:hover { background:#0091ea; color:#000; }

i{
    color:#00b0ff;
}
@media (max-width:768px) { 
    .product-container { flex-direction:column; gap:15px; }
}
.cart-badge {
    background:#00b0ff;
    color:#000;
    font-size:0.8em;
    padding:2px 6px;
    border-radius:50%;
    margin-left:5px;
    font-weight:bold;
    vertical-align:super;
}
</style>
<meta charset="UTF-8">
<title><?= h($product['name']) ?> - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>
    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
        <?php if($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
        </a>
      </li>
    </ul>
  </nav>
</header>

<div class="product-container">
    <img src="<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
    <div class="product-details">
        <h1><?= h($product['name']) ?></h1>
        <p><i class="fas fa-tag"></i> Price: ₹<?= number_format($product['price'], 2) ?></p>
        <p><?= h($product['description']) ?></p>

        <form method="post">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" value="1" min="1">
            <button type="submit" name="add_to_cart" class="btn-cart"><i class="fas fa-cart-plus"></i> Add to Cart</button>
        </form>

        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="comments-section">
    <h2><i class="fas fa-comments"></i> Comments</h2>

    <?php if ($comments->num_rows === 0): ?>
        <p>No comments yet. Be the first to comment!</p>
    <?php else: ?>
        <?php while ($c = $comments->fetch_assoc()): ?>
            <div class="comment">
                <strong><?= h($c['author']) ?></strong>
                <p>
                    <?= $c['body'] ?>  
                    <!-- STORED XSS ALLOWED -->
                </p>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <form method="post" class="comment-form">
        <input type="text" name="username" placeholder="Your name" required>
        <textarea name="comment" rows="4" placeholder="Write a comment..." required></textarea>
        <button type="submit"><i class="fas fa-paper-plane"></i> Submit</button>
    </form>
</div>

</body>
</html>
