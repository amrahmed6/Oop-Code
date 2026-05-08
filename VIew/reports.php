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

// Sales Report
$salesQuery = "SELECT 
                COALESCE(SUM(total_amount), 0) AS total_sales,
                COUNT(*) AS total_orders
               FROM Orders
               WHERE status != 'Cancelled'";

$salesStmt = $db->prepare($salesQuery);
$salesStmt->execute();
$salesReport = $salesStmt->fetch(PDO::FETCH_ASSOC);

// Users Report
$usersQuery = "SELECT COUNT(*) AS total_users FROM Users";
$usersStmt = $db->prepare($usersQuery);
$usersStmt->execute();
$totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Products Report
$productsQuery = "SELECT COUNT(*) AS total_products FROM Product";
$productsStmt = $db->prepare($productsQuery);
$productsStmt->execute();
$totalProducts = $productsStmt->fetch(PDO::FETCH_ASSOC)['total_products'];

// Top Products
$topProductsQuery = "SELECT 
                        p.name,
                        p.brand,
                        p.category,
                        SUM(oi.quantity) AS total_sold,
                        SUM(oi.total) AS total_revenue
                     FROM Order_Item oi
                     INNER JOIN Product p ON oi.product_id = p.product_id
                     INNER JOIN Orders o ON oi.order_id = o.order_id
                     WHERE o.status != 'Cancelled'
                     GROUP BY p.product_id
                     ORDER BY total_sold DESC
                     LIMIT 5";

$topProductsStmt = $db->prepare($topProductsQuery);
$topProductsStmt->execute();
$topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

// Order Status Report
$statusQuery = "SELECT status, COUNT(*) AS total
                FROM Orders
                GROUP BY status";

$statusStmt = $db->prepare($statusQuery);
$statusStmt->execute();
$orderStatuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports | BonnaVerse</title>
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

  <h1>Reports</h1>

  <div class="grid miniGrid">
    <div class="panel">
      <b>Sales Reports</b>
      <p>Total Sales:</p>
      <h2>$<?php echo number_format($salesReport['total_sales'], 2); ?></h2>
    </div>

    <div class="panel">
      <b>Order Report</b>
      <p>Total Orders:</p>
      <h2><?php echo $salesReport['total_orders']; ?></h2>
    </div>

    <div class="panel">
      <b>Users Report</b>
      <p>Total Users:</p>
      <h2><?php echo $totalUsers; ?></h2>
    </div>

    <div class="panel">
      <b>Products Report</b>
      <p>Total Products:</p>
      <h2><?php echo $totalProducts; ?></h2>
    </div>
  </div>

  <section class="panel">
    <h2>Top Products</h2>

    <table>
      <tr>
        <th>Product</th>
        <th>Brand</th>
        <th>Category</th>
        <th>Sold</th>
        <th>Revenue</th>
      </tr>

      <?php if (!empty($topProducts)): ?>
        <?php foreach ($topProducts as $product): ?>
          <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['brand']); ?></td>
            <td><?php echo htmlspecialchars($product['category']); ?></td>
            <td><?php echo htmlspecialchars($product['total_sold']); ?></td>
            <td>$<?php echo number_format($product['total_revenue'], 2); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5">No sales data found</td>
        </tr>
      <?php endif; ?>
    </table>
  </section>

  <section class="panel">
    <h2>Orders Status Report</h2>

    <table>
      <tr>
        <th>Status</th>
        <th>Total Orders</th>
      </tr>

      <?php if (!empty($orderStatuses)): ?>
        <?php foreach ($orderStatuses as $status): ?>
          <tr>
            <td><?php echo htmlspecialchars($status['status']); ?></td>
            <td><?php echo htmlspecialchars($status['total']); ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="2">No orders found</td>
        </tr>
      <?php endif; ?>
    </table>
  </section>

  <button class="btn" onclick="window.print()">Export / Print</button>

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