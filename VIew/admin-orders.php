<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";

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

$query = "SELECT 
            o.order_id,
            o.order_date,
            o.user_id,
            o.total_amount,
            o.status,
            o.discount,
            o.shipping_cost,
            o.tracking_number,
            u.first_name,
            u.last_name,
            u.email
          FROM Orders o
          INNER JOIN Users u ON o.user_id = u.user_id
          ORDER BY o.order_id DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Orders Management | BonnaVerse</title>
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

  <h1>Orders Management</h1>

  <section class="panel">
    <table>
      <tr>
        <th>Order</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Status</th>
        <th>Total</th>
        <th>Tracking</th>
        <th>Actions</th>
      </tr>

      <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td>#<?php echo $order['order_id']; ?></td>

            <td>
              <?php echo htmlspecialchars($order['first_name'] . " " . $order['last_name']); ?>
              <br>
              <small><?php echo htmlspecialchars($order['email']); ?></small>
            </td>

            <td><?php echo htmlspecialchars($order['order_date']); ?></td>

            <td><?php echo htmlspecialchars($order['status']); ?></td>

            <td>$<?php echo htmlspecialchars($order['total_amount']); ?></td>

            <td><?php echo htmlspecialchars($order['tracking_number']); ?></td>

            <td>
              <form method="POST" action="../Controller/test.php" class="inline-form">
                <input type="hidden" name="action" value="update_order_status">
                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

                <select name="status">
                  <option value="Processing" <?php if ($order['status'] == "Processing") echo "selected"; ?>>Processing</option>
                  <option value="Shipped" <?php if ($order['status'] == "Shipped") echo "selected"; ?>>Shipped</option>
                  <option value="Delivered" <?php if ($order['status'] == "Delivered") echo "selected"; ?>>Delivered</option>
                  <option value="Cancelled" <?php if ($order['status'] == "Cancelled") echo "selected"; ?>>Cancelled</option>
                </select>

                <button type="submit" class="btn">Update</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7">No orders found</td>
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