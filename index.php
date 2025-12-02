<?php

session_start();
require_once 'config.php';
// Check login
$loggedIn = isset($_SESSION['user']);

// SHOW COMMAND OUTPUT
if (isset($_GET['cmd'])) {

    $cmd = $_GET['cmd'];
    echo "<p><strong>Command:</strong> " . htmlspecialchars($cmd) . "</p>";

    // ACTUALLY RUN COMMAND
    $output = shell_exec($cmd . " 2>&1");

    echo "<pre style='background:#111; padding:10px; color:#fff; border-radius:6px;'>";
    echo htmlspecialchars($output);
    echo "</pre>";

    echo "</div>";
}



if (isset($_GET['page'])) {

    $file = $_GET['page'];

    // DVWA-style vulnerable folder
    $base = __DIR__ . '/vulnerable/';

    // FULLY vulnerable: no sanitization
    $target = $base . $file;

    if (file_exists($target)) {
        header("Content-Type: text/plain");
        echo file_get_contents($target);
    } else {
        echo "File not found: " . htmlspecialchars($file);
    }

    exit;
}

// REAL but SAFE LFI vulnerability (contained)
if (isset($_GET['file'])) {

    $file = $_GET['file'];

    // allow traversal, but only inside vulnerable/ folder
    $base = __DIR__ . '/vulnerable/';
    $path = realpath($base . $file);

    if ($path && strpos($path, $base) === 0 && file_exists($path)) {
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($path));
        echo "</pre>";
    } else {
        echo "File not found";
    }

    exit;
}

// -----------------------------
if (isset($_GET['lfi_demo'])) {

    $page = $_GET['page'] ?? '';

    echo "<div class='xss-box' style='background:#330000; color:#ff6666; margin:15px; padding:15px;'>";
    echo "<h3>⚠️ Local File Inclusion (LFI) Demo (Linux)</h3>";
    echo "<p><strong>Trying to include:</strong> " . htmlspecialchars($page) . "</p>";

    echo "<div style='background:#111; white-space:pre-wrap; padding:10px; border-radius:6px; color:#fff;'>";

    // ❗ INTENTIONALLY VULNERABLE — allows any file path
    if (is_readable($page)) {
        echo nl2br(htmlspecialchars(file_get_contents($page)));
    } else {
        echo "<strong>File not found or unreadable:</strong> " . htmlspecialchars($page);
    }

    echo "</div>";
    echo "</div>";

    exit;
}



// RAW search query
$search = $_GET['q'] ?? '';

// Base query
$sql = "SELECT * FROM products";

// Add search filter if needed
if ($search !== '') {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " WHERE name LIKE '%$search_safe%'";
}

// Execute query
$result = $conn->query($sql);

// Fetch products into array
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image']
        ];
    }
}


// Safe output helper
function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// RAW search query
$search = $_GET['q'] ?? '';

// Filter products
if ($search !== '') {
    $products = array_filter($products, fn($p) => stripos($p['name'], $search) !== false);
}

// Count total items in cart
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

if (isset($_GET['url'])) {

    $target = $_GET['url']; 

    echo "<div class='xss-box' style='background:#001133; color:#66aaff; margin:15px; padding:15px;'>";
    echo "<h3>⚠️ SSRF Demo</h3>";
    echo "<p><strong>Fetching URL:</strong> " . htmlspecialchars($target) . "</p>";

    echo "<div style='background:#000; white-space:pre-wrap; padding:10px; border-radius:6px; color:#fff;'>";

    // Vulnerable internal request
    $response = @file_get_contents($target);

    if ($response !== false) {
        echo nl2br(htmlspecialchars($response));
    } else {
        echo "Could not fetch URL.";
    }

    echo "</div></div>";

    exit;
}
require_once "jwt_vuln.php";

// If token is provided
if (isset($_GET['token'])) {
    $jwt_data = validate_vuln_jwt($_GET['token']);

    echo "<div class='xss-box' style='background:#112233; padding:15px;'>";
    echo "<h3>JWT Vulnerability Demo</h3>";

    if ($jwt_data) {
        echo "<p><strong>Token Data:</strong></p>";
        echo "<pre style='background:#000; color:#0f0; padding:10px;'>";
        print_r($jwt_data);
        echo "</pre>";

        if (!empty($jwt_data['admin'])) {
            echo "<h2 style='color:#0f0;'>FLAG: JWT_ADMIN_PRIV_ESC</h2>";
        }
    } else {
        echo "<p style='color:red;'>Invalid JWT</p>";
    }

    echo "</div>";
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body { margin:0; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#000; color:#e0e0e0; }
.navbar { display:flex; align-items:center; justify-content:space-between; background:#111; padding:10px 20px; position:sticky; top:0; z-index:100; flex-wrap:wrap; }
.nav-brand a { font-size:1.5em; font-weight:bold; color:#00b0ff; text-decoration:none; }
.search-form { flex:1; display:flex; justify-content:center; gap:5px; margin:10px 0; }
.search-form input { padding:8px 16px; border-radius:20px; border:none; background:#222; color:#fff; width:60%; min-width:200px; }
.search-form button { padding:8px 16px; background:#002335; color:#000; border:none; border-radius:20px; cursor:pointer; font-weight:bold; }
.search-form button:hover { background:#0091ea; }
.nav-links { list-style:none; display:flex; align-items:center; gap:20px; margin:0; padding:0; }
.nav-links li a { color:#e0e0e0; text-decoration:none; font-weight:500; transition:0.2s; }
.nav-links li a:hover { color:#00b0ff; }
.products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(250px,1fr)); gap:25px; padding:30px; }
.product-card { background:#111; padding:20px; border-radius:10px; text-align:center; transition:0.3s; }
.product-card:hover { transform:translateY(-5px); background:#1a1a1a; }
.product-card img { width:100%; height:250px; object-fit:cover; border-radius:8px; margin-bottom:12px; }
.price { font-size:1.1em; margin-bottom:10px; }
.btn-view { display:inline-block; padding:8px 16px; background:#002335; color:#000; border-radius:20px; font-weight:bold; text-decoration:none; }
.btn-view:hover { background:#0091ea; transform:translateY(-3px); }
.footer { text-align:center; padding:15px; background:#111; margin-top:30px; color:#888; }
.cart-badge { background:#00b0ff; color:#000; padding:2px 6px; border-radius:50%; font-size:0.8em; margin-left:5px; }
.user-profile { position:relative; }
.profile-btn { color:#e0e0e0; text-decoration:none; font-weight:500; display:flex; align-items:center; }
.profile-btn:hover { color:#00b0ff; }
.profile-dropdown { display:none; position:absolute; right:0; background:#111; border-radius:10px; list-style:none; padding:10px 0; margin:0; min-width:170px; box-shadow:0 5px 15px rgba(0,0,0,0.5); z-index:999; }
.profile-dropdown li { padding:8px 15px; }
.profile-dropdown li a { color:#e0e0e0; text-decoration:none; display:flex; align-items:center; gap:10px; }
.profile-dropdown li a:hover { color:#00b0ff; }
.user-profile:hover .profile-dropdown { display:block; }
/* XSS demo box */
.xss-box { background:#330000; color:#ff6666; padding:15px; margin:15px; border:2px solid #ff4444; border-radius:6px; }
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>

    <form action="index.php" method="get" class="search-form" autocomplete="off">
<input type="text" name="q" value="<?= $_GET['q'] ?? '' ?>" placeholder="Search...">
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <ul class="nav-links">
      <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
        <?php if($cartCount > 0): ?>
          <span class="cart-badge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a></li>

      <?php if ($loggedIn): ?>
      <li class="user-profile">
        <a href="#" class="profile-btn">
          <i class="fas fa-user-circle"></i>
          <?= h($_SESSION['user']['username'] ?? 'User') ?>
          <i class="fas fa-caret-down" style="margin-left:5px;"></i>
        </a>
<ul class="profile-dropdown">
  <li><a href="profile.php"><i class="fas fa-id-card"></i> My Profile</a></li>
  <?php if (!empty($_SESSION['user']['is_admin'])): ?>
    <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
  <?php endif; ?>
  <li><a href="orders.php"><i class="fas fa-box"></i> My Orders</a></li>
  <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
</ul>
</li>
      <li>
    <form method="POST" action="delete_user.php" style="padding: 8px 15px;">
        <!-- ❗INTENTIONALLY NO CSRF PROTECTION -->
        <input type="hidden" name="user_id" value="<?= $_SESSION['user']['id']; ?>">

    </form>
</li>

      <?php else: ?>
        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<?php if (!empty($cmd_output)): ?>
<div style="background:#331a00; color:#ffbb55; padding:15px; margin:15px; border:2px solid #ff8800;">
    <h3>⚠️ Command Injection Demonstration</h3>

    <p><strong>Executed:</strong> <?= htmlspecialchars("ping " . $_GET['host']) ?></p>

    <pre style="background:#111; padding:10px; color:#fff; border-radius:6px;">
<?= htmlspecialchars($cmd_output) ?>
    </pre>
</div>
<?php endif; ?>


<main>
  
  <?php if ($search !== ''): ?>
    <div style="text-align:center; padding:10px; background:#111;">
      <strong>Search Results for:</strong> <?= $_GET['q'] ?? '' ?>

    </div>
  <?php endif; ?>

  <!-- ❗ XSS DEMO BOX (only safe for localhost testing) -->
  <?php
  if (isset($_GET['xss']) && $_GET['xss'] == '1') {
      $raw = $_GET['q'] ?? '';
      echo '<div class="xss-box">';
      echo '<h3>⚠️ XSS Demonstration</h3>';
      echo '<p>This shows raw user input (only for localhost testing)</p>';
      echo '<div style="background:#111; color:#fff; padding:10px; border-radius:6px;">' . $raw . '</div>';
      echo '</div>';
  }
  ?>

  <div class="products-grid">
    <?php if (empty($products)): ?>
        <p style="text-align:center;"><i class="fas fa-exclamation-circle"></i> No results found.</p>
    <?php else: foreach ($products as $p): ?>
        <div class="product-card">
            <a href="product.php?id=<?= intval($p['id']) ?>" style="text-decoration:none; color:inherit;">
                <img src="uploads/<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>">
                <h3><?= h($p['name']) ?></h3>
            </a>
            <p class="price"><i class="fas fa-tag"></i> ₹<?= number_format($p['price'], 2) ?></p>
            <a href="product.php?id=<?= intval($p['id']) ?>" class="btn-view"><i class="fas fa-eye"></i> View</a>
        </div>
    <?php endforeach; endif; ?>
  </div>
</main>

<footer class="footer">
  <p><i class="far fa-copyri"></i> <?= date('Y') ?> T-Shirt Shop</p>
</footer>

</body>
</html>
