<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";
require_once __DIR__ . "/../Model/coupon.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$adminId = $_SESSION['user_id'];

$admin = new Admin($db, $adminId);

if (!$admin->isAdmin($adminId)) {
    header("Location: index.php");
    exit;
}

$couponModel = new Coupon($db);
$coupons = $couponModel->getAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coupons Management | BonnaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="header">
  <a class="logo" href="index.php">Bonna<span>Verse</span></a>

  <div class="search">
    <input id="searchInput" placeholder="Search sneakers, apparel, brands..." />
  </div>

  <nav>
    <a href="admin.php">Dashboard</a>
    <a href="admin-products.php">Products</a>
    <a href="admin-orders.php">Orders</a>
    <a href="admin-users.php">Users</a>
    <a href="admin-coupons.php">Coupons</a>
    <a href="reports.php">Reports</a>
    <a href="shop.php">Shop</a>

    <form method="POST" action="../Controller/test.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Coupons Management</h1>

  <section class="panel">
    <h2>Create Coupon</h2>

    <form method="POST" action="../Controller/test.php" class="formGrid">
      <input type="hidden" name="action" value="create_coupon">
      <input type="hidden" name="admin_id" value="<?php echo $adminId; ?>">

      <input type="text" name="code" placeholder="Coupon Code" required>

      <input type="number" name="discount" placeholder="Discount %" min="1" max="100" required>

      <input type="date" name="expiry_date" required>

      <button type="submit" class="btn">Create Coupon</button>
    </form>
  </section>

  <br>

  <section class="panel">
    <h2>All Coupons</h2>

    <table>
      <tr>
        <th>Code</th>
        <th>Discount</th>
        <th>Expiry</th>
        <th>Action</th>
      </tr>

      <?php if (!empty($coupons)): ?>
        <?php foreach ($coupons as $coupon): ?>
          <tr>
            <td><?php echo htmlspecialchars($coupon['code']); ?></td>
            <td><?php echo htmlspecialchars($coupon['discount']); ?>%</td>
            <td><?php echo htmlspecialchars($coupon['expiry_date']); ?></td>
            <td>
              <form method="POST" action="../Controller/test.php" class="inline-form">
                <input type="hidden" name="action" value="delete_coupon">
                <input type="hidden" name="coupon_id" value="<?php echo $coupon['coupon_id']; ?>">
                <button type="submit" class="btn outline">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">No coupons found</td>
        </tr>
      <?php endif; ?>

    </table>
  </section>

</main>

<footer class="footer">
  <div>
    <b>BonnaVerse</b>
    <p>Simple multi-brand marketplace front-end.</p>
  </div>

  <div>
    <b>Links</b>
    <p>Shop · Orders · Account · Support</p>
  </div>

  <div>
    <b>Brands</b>
    <p>Nike · Adidas · Jordan · Supreme · Yeezy</p>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>