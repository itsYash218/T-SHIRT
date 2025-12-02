<?php
session_start();
require_once "config.php";

/* ------------------------------------
   MUST BE LOGGED IN
------------------------------------ */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$currentUserId = $_SESSION['user']['id'];
$isAdmin = !empty($_SESSION['user']['is_admin']);


/* ------------------------------------
   CHECK PRODUCT ID
------------------------------------ */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID.");
}

$productId = intval($_GET['id']);


/* ------------------------------------
   FETCH PRODUCT OWNER + IMAGE
------------------------------------ */
$stmt = $conn->prepare(
    "SELECT id, user_id, image_path 
     FROM products 
     WHERE id = ?"
);
$stmt->bind_param("i", $productId);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Product not found.");
}

$product = $res->fetch_assoc();


/* ------------------------------------
   PERMISSION CHECK
   - Admin can delete any product
   - User can delete only his own product
------------------------------------ */
if (!$isAdmin && $product['user_id'] != $currentUserId) {
    die("Access denied: You can only delete your own product.");
}


/* ------------------------------------
   DELETE PRODUCT
------------------------------------ */
$del = $conn->prepare("DELETE FROM products WHERE id = ?");
$del->bind_param("i", $productId);

if (!$del->execute()) {
    die("Error deleting product.");
}


/* ------------------------------------
   DELETE UPLOADED IMAGE (if exists)
------------------------------------ */
if (!empty($product['image_path'])) {
    $file = "uploads/" . $product['image_path'];

    if (file_exists($file)) {
        unlink($file);
    }
}


/* ------------------------------------
   REDIRECT WITH SUCCESS MESSAGE
------------------------------------ */
$_SESSION['message'] = "Product deleted successfully.";

if ($isAdmin) {
    header("Location: admin_products.php");
} else {
    header("Location: my_products.php");
}
exit;
