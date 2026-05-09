<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/order.php";
require_once __DIR__ . "/../Model/payment.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];
$orderId = $_GET['order_id'];

$orderModel = new Order($db, $userId);
$order = $orderModel->getById($orderId);

if (!$order) {
    header("Location: orders.php");
    exit;
}

$paymentModel = new Payment($db);
$payment = $paymentModel->getByOrderId($orderId);

$paymentStatus = $payment ? $payment['status'] : "Not Paid";
$paymentMethod = $payment ? $payment['payment_method'] : "N/A";
$paymentMessage = $_SESSION['payment_success'] ?? "";
unset($_SESSION['payment_success']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Success | BonnaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260509_ui_tweak_v3" />
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

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
      <a href="admin.php">Admin</a>
    <?php endif; ?>

    <form method="POST" action="../Controller/AuthController.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <section class="success">
    <?php if ($paymentStatus === "Pending"): ?>
      <h1>Payment Under Review ⏳</h1>
      <p class="ok">Your Instapay proof was uploaded. The admin will approve the order after confirming the money.</p>
    <?php else: ?>
      <h1>Order Confirmed ✅</h1>
    <?php endif; ?>

    <?php if (!empty($paymentMessage)): ?>
      <p class="ok"><?php echo htmlspecialchars($paymentMessage); ?></p>
    <?php endif; ?>

    <p>
      Order Number:
      <b>#<?php echo htmlspecialchars($order['order_id']); ?></b>
    </p>

    <p>
      Order Status:
      <b><?php echo htmlspecialchars($order['status']); ?></b>
    </p>

    <p>
      Payment Status:
      <b><?php echo htmlspecialchars($paymentStatus); ?></b>
    </p>

    <p>
      Payment Method:
      <b><?php echo htmlspecialchars($paymentMethod); ?></b>
    </p>

    <p>
      Tracking Number:
      <b><?php echo htmlspecialchars($order['tracking_number']); ?></b>
    </p>

    <p>
      Total Amount:
      <b>$<?php echo number_format($order['total_amount'], 2); ?></b>
    </p>

    <a class="btn" href="tracking.php?order_id=<?php echo $order['order_id']; ?>">
      Track Order
    </a>

    <a class="btn outline" href="shop.php">
      Continue Shopping
    </a>
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