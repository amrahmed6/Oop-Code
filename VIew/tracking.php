<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/order.php";
require_once __DIR__ . "/../Model/payment.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/customer.php";

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

$orderItems = $orderModel->getOrderItems($orderId);

$paymentModel = new Payment($db);
$payment = $paymentModel->getByOrderId($orderId);

$customer = new Customer($db, $userId);
$profile = $customer->getProfile();

$address = $profile['delivery_address'] ?? "No address found";
$paymentMethod = $payment ? $payment['payment_method'] : "Not paid yet";
$paymentStatus = $payment ? $payment['status'] : "Pending";

$status = $order['status'];

function isActiveStep($currentStatus, $step) {
    $steps = [
        "Processing" => 1,
        "Packed" => 2,
        "Shipped" => 3,
        "Delivered" => 4
    ];

    if ($currentStatus == "Cancelled") {
        return false;
    }

    $currentLevel = $steps[$currentStatus] ?? 1;
    $stepLevel = $steps[$step] ?? 1;

    return $stepLevel <= $currentLevel;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Tracking | BonnaVerse</title>
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

  <h1>Order Details / Tracking</h1>

  <section class="panel">

    <h2>Order #<?php echo htmlspecialchars($order['order_id']); ?></h2>

    <p>
      Order Date:
      <b><?php echo htmlspecialchars($order['order_date']); ?></b>
    </p>

    <p>
      Order Status:
      <b><?php echo htmlspecialchars($order['status']); ?></b>
    </p>

    <p>
      Tracking Number:
      <b><?php echo htmlspecialchars($order['tracking_number']); ?></b>
    </p>

    <p>
      Shipping Address:
      <b><?php echo htmlspecialchars($address); ?></b>
    </p>

    <p>
      Payment Method:
      <b><?php echo htmlspecialchars($paymentMethod); ?></b>
    </p>

    <p>
      Payment Status:
      <b><?php echo htmlspecialchars($paymentStatus); ?></b>
    </p>

    <p>
      Total Amount:
      <b>$<?php echo number_format($order['total_amount'], 2); ?></b>
    </p>

  </section>

  <section class="panel">
    <h2>Items</h2>

    <?php if (!empty($orderItems)): ?>
      <?php foreach ($orderItems as $item): ?>
        <div class="panel">
          <p>
            <b><?php echo htmlspecialchars($item['product_name']); ?></b>
          </p>

          <p>
            Quantity:
            <?php echo htmlspecialchars($item['quantity']); ?>
          </p>

          <p>
            Unit Price:
            $<?php echo number_format($item['unit_price'], 2); ?>
          </p>

          <p>
            Total:
            $<?php echo number_format($item['total'], 2); ?>
          </p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No items found for this order.</p>
    <?php endif; ?>

  </section>

  <section class="panel">
    <h2>Tracking Timeline</h2>

    <?php if ($status == "Cancelled"): ?>

      <p class="error">This order has been cancelled.</p>

    <?php else: ?>

      <div class="timeline">
        <span class="<?php echo isActiveStep($status, 'Processing') ? 'active' : ''; ?>">
          Placed
        </span>

        <span class="<?php echo isActiveStep($status, 'Packed') ? 'active' : ''; ?>">
          Packed
        </span>

        <span class="<?php echo isActiveStep($status, 'Shipped') ? 'active' : ''; ?>">
          Shipped
        </span>

        <span class="<?php echo isActiveStep($status, 'Delivered') ? 'active' : ''; ?>">
          Delivered
        </span>
      </div>

    <?php endif; ?>

    <br>

    <?php if ($order['status'] == "Processing"): ?>
      <form method="POST" action="../Controller/test.php">
        <input type="hidden" name="action" value="cancel_order">
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

        <button type="submit" class="btn outline">Cancel Order</button>
      </form>
    <?php endif; ?>

    <br>

    <a href="orders.php" class="btn">Back To Orders</a>

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