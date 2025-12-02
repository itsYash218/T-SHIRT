<?php
session_start();
require_once 'config.php';

// Require login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get product ID
if (!isset($_GET['id'])) {
    die("Product ID not provided.");
}

$id = intval($_GET['id']);

// Fetch existing product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_product"])) {

    $name        = $_POST["name"];
    $description = $_POST["description"];
    $price       = $_POST["price"];
    $stock       = $_POST["stock"];
    $imagePath   = $product["image"]; // keep existing image unless replaced

    // Handle new image upload
    if (!empty($_FILES["image"]["name"])) {
        $uploadDir = "/var/www/html/T-SHIRT/uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES["image"]["name"]);
        $target   = $uploadDir . $fileName;

        // Only allow images
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ["jpg", "jpeg", "png", "gif", "webp"];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Images only.";
        } else {
            move_uploaded_file($_FILES["image"]["tmp_name"], $target);
            $imagePath = $fileName;
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("
            UPDATE products 
            SET name=?, description=?, price=?, stock=?, image=? 
            WHERE id=?
        ");
        $stmt->bind_param("ssdssi", $name, $description, $price, $stock, $imagePath, $id);
        $stmt->execute();

        $success = "Product updated successfully!";
    }
}

/* -------------------------
   🔥 VULNERABLE COMMENT ACTIONS
   Mode A: NO AUTHORIZATION CHECKS
--------------------------*/

// DELETE comment
if (isset($_GET["delete_comment"])) {
    $cid = intval($_GET["delete_comment"]);
    $conn->query("DELETE FROM comments WHERE id=$cid"); // ❌ no owner, no verification
}

// EDIT comment (update)
if (isset($_POST["edit_comment"])) {
    $cid = intval($_POST["cid"]);
    $newBody = $_POST["body"]; // ❌ no sanitization, stored XSS possible

    $stmt = $conn->prepare("UPDATE comments SET body=? WHERE id=?");
    $stmt->bind_param("si", $newBody, $cid);
    $stmt->execute();
}

// Fetch comments
$c = $conn->query("SELECT * FROM comments WHERE product_id=$id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>
<style>
body { background:#000; color:#fff; font-family:Arial; }
.container {
    width: 600px;
    margin: 50px auto;
    background: #111;
    padding: 25px;
    border-radius: 10px;
}
input, textarea {
    width:100%;
    padding:10px;
    margin:8px 0;
    background:#222;
    border:none;
    border-radius:5px;
    color:#fff;
}
button {
    width:100%;
    padding:12px;
    background:#0091ea;
    border:none;
    font-weight:bold;
    cursor:pointer;
}
button:hover {
    background:#00b0ff;
}
.alert {
    padding:10px; 
    margin-bottom:10px;
    border-radius:5px;
}
.success { background:#003300; color:#00ff44; }
.error { background:#330000; color:#ff4444; }

.comment {
    background:#111;
    padding:10px;
    border-radius:5px;
    margin-bottom:10px;
}

.action-btn {
    display:inline-block;
    background:#222;
    padding:5px 10px;
    margin-left:10px;
    border-radius:3px;
    color:#0af;
}
.action-btn:hover {
    background:#333;
}
</style>
</head>
<body>

<div class="container">

<h2>Edit Product</h2>

<?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="update_product" value="1">

    <label>Product Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

    <label>Description</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>

    <label>Price (₹)</label>
    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>">

    <label>Stock Quantity</label>
    <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>">

    <label>Current Image</label><br>
    <?php if ($product["image"]): ?>
        <img src="uploads/<?= htmlspecialchars($product["image"]) ?>" width="120"><br><br>
    <?php endif; ?>

    <label>Replace Image</label>
    <input type="file" name="image">

    <button type="submit">Save Changes</button>
</form>

<br>
<a href="admin.php" style="color:#00b0ff;">⬅ Back to Admin</a>

<hr><br>

<h2>Comments</h2>

<?php while ($row = $c->fetch_assoc()): ?>
<div class="comment">
    <b><?= $row["author"] ?></b><br><br>

    <!-- ❌ XSS enabled — raw output -->
    <p><?= $row["body"] ?></p>

    <!-- Delete -->
    <a class="action-btn" href="?id=<?= $id ?>&delete_comment=<?= $row["id"] ?>">Delete</a>

    <!-- Edit (popup form) -->
    <form method="POST" style="margin-top:10px;">
        <input type="hidden" name="cid" value="<?= $row["id"] ?>">
        <textarea name="body" rows="2"><?= $row["body"] ?></textarea>
        <button type="submit" name="edit_comment">Save Edit</button>
    </form>
</div>
<?php endwhile; ?>

</div>

</body>
</html>
