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
$existingPayment = $paymentModel->getByOrderId($orderId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment | BonnaVerse</title>
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

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
      <a href="admin.php">Admin</a>
    <?php endif; ?>

    <form method="POST" action="../Controller/test.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Payment</h1>

  <section class="panel">
    <h2>Order #<?php echo htmlspecialchars($order['order_id']); ?></h2>

    <p>Status: <b><?php echo htmlspecialchars($order['status']); ?></b></p>
    <p>Tracking Number: <b><?php echo htmlspecialchars($order['tracking_number']); ?></b></p>

    <h3>Final Total: $<?php echo number_format($order['total_amount'], 2); ?></h3>
  </section>

  <section class="panel">
    <h2>Payment Methods</h2>

    <?php if ($existingPayment): ?>

      <p class="ok">This order is already paid.</p>
      <p>Payment Method: <b><?php echo htmlspecialchars($existingPayment['payment_method']); ?></b></p>
      <p>Status: <b><?php echo htmlspecialchars($existingPayment['status']); ?></b></p>

      <a class="btn" href="success.php?order_id=<?php echo $orderId; ?>">Continue</a>

    <?php else: ?>

      <form method="POST" action="../Controller/test.php">

        <input type="hidden" name="action" value="create_payment">
        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderId); ?>">

        <label>
          <input type="radio" name="payment_method" value="Visa" required>
          Visa
        </label>

        <label>
          <input type="radio" name="payment_method" value="Instapay" required>
          Instapay
        </label>

        <label>
          <input type="radio" name="payment_method" value="Cash" required>
          Cash
        </label>

        <input type="text" name="card_number" placeholder="Card number">
        <input type="text" name="expiry_date" placeholder="Expiry date">
        <input type="text" name="cvv" placeholder="CVV">

        <button type="submit" class="btn">Confirm Order</button>

      </form>

    <?php endif; ?>

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