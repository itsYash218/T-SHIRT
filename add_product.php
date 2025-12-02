<?php
session_start();
require_once 'config.php';

/*
    ------------------------------------------------------------
    ❗ BROKEN ACCESS CONTROL (INTENTIONAL)
    - Admin UI link only shown for admins
    - BUT back-end does NOT enforce is_admin
    - Any logged-in user can add products
    ------------------------------------------------------------
*/

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$loggedIn = true;
$isAdmin  = !empty($_SESSION['user']['is_admin']); // UI only

/* -------------------- CART COUNT -------------------- */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $c) {
        $cartCount += $c['quantity'];
    }
}

/* -------------------- FORM HANDLER -------------------- */
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = $_POST['name'];
    $description = $_POST['description'];
    $price       = $_POST['price'];
    $stock       = $_POST['stock'];

    /* ---------------------------------------------------------
       ❗ VULNERABLE FILE UPLOAD
       - No file type check
       - Can upload .php webshell
       - File stored at full Windows path
       --------------------------------------------------------- */
    $imagePath = '';

    if (!empty($_FILES['image']['name'])) {

       $uploadDir = "/var/www/html/T-SHIRT/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['image']['name']);
        $target   = $uploadDir . $fileName;

        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $imagePath = $target;

        $success .= "Uploaded: $fileName<br>";
    }

    /* -------------------- DATABASE INSERT (NO VALIDATION) -------------------- */
    $stmt = $conn->prepare("
        INSERT INTO products (name, description, price, stock, image, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssdss", $name, $description, $price, $stock, $imagePath);
    $stmt->execute();

    $success .= "Product added successfully!<br>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Productasssss</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body { margin:0; background:#000; color:#fff; font-family:'Segoe UI',Tahoma, Geneva, Verdana,sans-serif; }
.navbar {
    background:#111;
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
}
.nav-brand a { font-size:1.5rem; color:#00b0ff; text-decoration:none; font-weight:bold; }
.nav-links { list-style:none; display:flex; gap:18px; padding:0; margin:0; flex-wrap:wrap; }
.nav-links a { color:#fff; text-decoration:none; font-weight:500; }
.nav-links a:hover { color:#00b0ff; }
.cart-badge { background:#00b0ff; color:#000; border-radius:50%; padding:3px 7px; margin-left:5px; }
.container {
    max-width:600px;
    margin:50px auto;
    background:#111;
    padding:30px;
    border-radius:14px;
    box-shadow:0 0 15px rgba(0,0,0,0.4);
}
h2 { color:#00b0ff; margin-bottom:20px; }
input, textarea {
    width:100%;
    padding:10px;
    margin:8px 0;
    background:#222;
    border:none;
    border-radius:8px;
    color:#fff;
}
input[type="file"] { padding:4px; }
.btn {
    width:100%;
    padding:12px;
    background:#0091ea;
    color:#000;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:bold;
}
.btn:hover { background:#00b0ff; }
.alert { padding:10px 15px; margin-bottom:15px; border-radius:8px; }
.alert-success { background:#003300; color:#00ff44; }
.alert-error { background:#330000; color:#ff4444; }
</style>
</head>
<body>

<header>
<nav class="navbar">
    <div class="nav-brand">
        <a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a>
    </div>

    <ul class="nav-links">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>

        <?php if ($isAdmin): ?>
            <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
        <?php endif; ?>

        <li>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i>
                Cart <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
            </a>
        </li>

        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
</header>

<div class="container">

    <h2><i class="fas fa-plus-square"></i> Add New Product</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Description</label>
        <textarea name="description" rows="4"></textarea>

        <label>Price (₹)</label>
        <input type="number" name="price" step="0.01">

        <label>Stock Quantity</label>
        <input type="number" name="stock">

        <label>Product Image (No Validation)</label>
        <input type="file" name="image">

        <button type="submit" class="btn"><i class="fas fa-plus"></i> Add Product</button>
    </form>

    <br>
    <a href="admin.php" style="color:#00b0ff;">⬅ Back to Admin</a>
</div>

</body>
</html>
