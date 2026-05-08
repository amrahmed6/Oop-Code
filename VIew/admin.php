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

// ===============================
// KPIs
// ===============================

$totalSalesQuery = "SELECT COALESCE(SUM(total_amount), 0) AS total_sales 
                    FROM Orders 
                    WHERE status != 'Cancelled'";
$totalSalesStmt = $db->prepare($totalSalesQuery);
$totalSalesStmt->execute();
$totalSales = $totalSalesStmt->fetch(PDO::FETCH_ASSOC)['total_sales'];

$ordersQuery = "SELECT COUNT(*) AS total_orders FROM Orders";
$ordersStmt = $db->prepare($ordersQuery);
$ordersStmt->execute();
$totalOrders = $ordersStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

$usersQuery = "SELECT COUNT(*) AS total_users FROM Users";
$usersStmt = $db->prepare($usersQuery);
$usersStmt->execute();
$totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$productsQuery = "SELECT COUNT(*) AS total_products FROM Product";
$productsStmt = $db->prepare($productsQuery);
$productsStmt->execute();
$totalProducts = $productsStmt->fetch(PDO::FETCH_ASSOC)['total_products'];

// ===============================
// Latest Orders
// ===============================

$latestOrdersQuery = "SELECT 
                        o.order_id,
                        o.status,
                        o.total_amount,
                        u.first_name,
                        u.last_name
                      FROM Orders o
                      INNER JOIN Users u ON o.user_id = u.user_id
                      ORDER BY o.order_id DESC
                      LIMIT 5";

$latestOrdersStmt = $db->prepare($latestOrdersQuery);
$latestOrdersStmt->execute();
$latestOrders = $latestOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

// ===============================
// Low Stock Products
// ===============================

$lowStockQuery = "SELECT 
                    product_id,
                    name,
                    stock_count
                  FROM Product
                  WHERE stock_count <= 5
                  ORDER BY stock_count ASC
                  LIMIT 5";

$lowStockStmt = $db->prepare($lowStockQuery);
$lowStockStmt->execute();
$lowStockProducts = $lowStockStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | BonnaVerse</title>
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

  <h1>Admin Dashboard</h1>

  <div class="kpis">
    <div>
      Total Sales<br>
      <b>$<?php echo number_format($totalSales, 2); ?></b>
    </div>

    <div>
      Orders<br>
      <b><?php echo $totalOrders; ?></b>
    </div>

    <div>
      Users<br>
      <b><?php echo $totalUsers; ?></b>
    </div>

    <div>
      Products<br>
      <b><?php echo $totalProducts; ?></b>
    </div>
  </div>

  <section class="panel">
    <h2>Latest Orders</h2>

    <?php if (!empty($latestOrders)): ?>
      <?php foreach ($latestOrders as $order): ?>
        <p>
          #<?php echo $order['order_id']; ?>
          -
          <?php echo htmlspecialchars($order['first_name'] . " " . $order['last_name']); ?>
          -
          <?php echo htmlspecialchars($order['status']); ?>
          -
          $<?php echo htmlspecialchars($order['total_amount']); ?>
        </p>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No orders found</p>
    <?php endif; ?>

    <br>
    <a href="admin-orders.php" class="btn outline">View All Orders</a>
  </section>

  <section class="panel">
    <h2>Low Stock Alerts</h2>

    <?php if (!empty($lowStockProducts)): ?>
      <?php foreach ($lowStockProducts as $product): ?>
        <p>
          <?php echo htmlspecialchars($product['name']); ?>
          -
          <?php echo htmlspecialchars($product['stock_count']); ?> left
        </p>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No low stock products</p>
    <?php endif; ?>

    <br>
    <a href="admin-products.php" class="btn outline">Manage Products</a>
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