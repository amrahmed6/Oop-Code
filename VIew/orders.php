<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/order.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];

$orderModel = new Order($db, $userId);
$orders = $orderModel->getUserOrders();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders | BonnaVerse</title>
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
    <a href="shop.php">Shop</a>
    <a href="wishlist.php">Wishlist</a>
    <a href="cart.php">Cart</a>
    <a href="orders.php">Orders</a>
    <a href="account.php">Account</a>

    <form method="POST" action="../Controller/test.php" style="display:inline;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>My Orders</h1>

  <section class="panel">
    <table>
      <tr>
        <th>Order</th>
        <th>Status</th>
        <th>Date</th>
        <th>Total</th>
        <th>Tracking</th>
        <th>Action</th>
      </tr>

      <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td>#<?php echo $order['order_id']; ?></td>

            <td>
              <span class="badge">
                <?php echo htmlspecialchars($order['status']); ?>
              </span>
            </td>

            <td>
              <?php echo htmlspecialchars($order['order_date']); ?>
            </td>

            <td>
              $<?php echo number_format($order['total_amount'], 2); ?>
            </td>

            <td>
              <?php echo htmlspecialchars($order['tracking_number']); ?>
            </td>

            <td>
              <a class="btn outline" href="tracking.php?order_id=<?php echo $order['order_id']; ?>">
                View
              </a>

              <?php if ($order['status'] == "Processing"): ?>
                <form method="POST" action="../Controller/test.php" style="display:inline;">
                  <input type="hidden" name="action" value="cancel_order">
                  <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                  <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

                  <button type="submit" class="btn outline">Cancel</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6">No orders found</td>
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