<?php
session_start();
require_once 'config.php';

/* ---------------------------------------------------------
   ❗ BROKEN ACCESS CONTROL (BACKEND ONLY)
   - Navbar stays normal (admin link hidden for users)
   - Backend does NOT check is_admin
   - Any logged-in user can access this page directly
----------------------------------------------------------- */

// Allow ANY logged-in user
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

// Get is_admin but DO NOT enforce it
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

/* -------------------- FETCH USERS -------------------- */
$users_result = $conn->query("SELECT id, username, email, password, is_admin, created_at, last_login FROM users ORDER BY id ASC");

/* -------------------- FETCH PRODUCTS (uploaded ones) -------------------- */
$products_result = $conn->query("SELECT id, name, price, image, created_at FROM products ORDER BY id DESC");

/* -------------------- CART COUNT -------------------- */
$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $c) {
        $cartCount += $c['quantity'];
    }
}

$loggedIn = isset($_SESSION['user']);
$isAdmin  = !empty($_SESSION['user']['is_admin']); // UI only
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - T-Shirt Shop</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
<style>
body {
    margin: 0;
    background: #000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
}
.navbar {
    background: #111;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
}
.nav-brand a {
    font-size: 1.5rem;
    font-weight: bold;
    color: #00b0ff;
    text-decoration: none;
}
.nav-links { list-style: none; display: flex; gap: 18px; margin: 0; padding: 0; }
.nav-links a { color: #fff; text-decoration: none; font-weight: 500; }
.nav-links a:hover { color: #00b0ff; }
.cart-badge { background: #00b0ff; color: #000; padding: 2px 6px; border-radius: 50%; font-size: 0.8rem; margin-left: 4px; }

.container { background: #111; max-width: 1000px; margin: 40px auto; padding: 30px; border-radius: 14px; }
h2 { color: #00b0ff; }
h3 { color: #0091ea; margin-top: 30px; }
.btn { padding: 10px 18px; background: #002335; color: #00b0ff; border-radius: 20px; text-decoration: none; }
.btn:hover { background: #0091ea; }
.btn-danger { background: #330000; color: #ff4444; }
.btn-danger:hover { background: #660000; }

table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th { color: #00b0ff; border-bottom: 2px solid #222; padding: 12px; }
td { padding: 12px; border-bottom: 1px solid #1a1a1a; }
tr:hover td { background: #1a1a1a; }

img.thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
</style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="nav-brand"><a href="index.php"><i class="fas fa-tshirt"></i> T-Shirt Shop</a></div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>

            <?php if ($isAdmin): ?>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin</a></li>
            <?php endif; ?>

            <li>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart
                    <?php if ($cartCount > 0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
                </a>
            </li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>
</header>

<div class="container">

    <h2>Admin Panel</h2>

    <div style="display:flex; gap:10px; margin-bottom:20px;">
        <a href="add_user.php" class="btn"><i class="fas fa-user-plus"></i> Add User</a>
        <a href="add_product.php" class="btn"><i class="fas fa-plus-square"></i> Add Product</a>
    </div>

    <!-- ================= USERS LIST ================= -->
    <h3>Registered Users</h3>

    <table>
        <tr>
            <th>ID</th><th>Username</th><th>Password</th><th>Email</th><th>Admin</th><th>Created</th><th>Last Login</th><th>Actions</th>
        </tr>

        <?php while ($u = $users_result->fetch_assoc()): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['password']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['is_admin'] ? 'Yes' : 'No' ?></td>
            <td><?= $u['created_at'] ?></td>
            <td><?= $u['last_login'] ?? 'Never' ?></td>
            <td>
                <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn">Edit</a>

                <form method="POST" action="delete_user.php" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button class="btn btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>


    <!-- ================= PRODUCTS LIST ================= -->
    <h3>Uploaded Products</h3>

    <table>
        <tr>
            <th>ID</th><th>Image</th><th>Name</th><th>Price</th><th>Created</th><th>Actions</th>
        </tr>

        <?php if ($products_result->num_rows > 0): ?>
            <?php while ($p = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><img class="thumb" src="uploads/<?= htmlspecialchars($p['image']) ?>"></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td>₹<?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['created_at'] ?></td>
                    <td>
                        <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn">Edit</a>

                        <form method="POST" action="delete_product.php" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No uploaded products found.</td></tr>
        <?php endif; ?>

    </table>

</div>

</body>
</html>
