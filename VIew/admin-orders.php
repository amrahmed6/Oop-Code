<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";
require_once __DIR__ . "/../Model/payment.php";

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

// Creates the Instapay table if the database is an older copy.
new Payment($db);

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
            u.email,
            p.payment_id,
            p.payment_method,
            p.status AS payment_status,
            p.transaction_id,
            it.sender_phone,
            it.proof_image,
            it.admin_status AS instapay_status,
            it.admin_note,
            vd.card_last4,
            vd.expiry_month,
            vd.expiry_year
          FROM Orders o
          INNER JOIN Users u ON o.user_id = u.user_id
          LEFT JOIN Payment p ON p.order_id = o.order_id
          LEFT JOIN Instapay_Transfer it ON it.order_id = o.order_id
          LEFT JOIN Visa_Payment_Details vd ON vd.order_id = o.order_id
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
  <link rel="stylesheet" href="style.css?v=20260509_ui_tweak_v3" />
</head>
<body>

<header class="header admin-header">
  <a class="logo" href="index.php">Bonna<span>Verse</span></a>

  <nav>
    <a href="admin.php">Dashboard</a>
    <a href="admin-products.php">Products</a>
    <a href="admin-orders.php">Orders</a>
    <a href="admin-users.php">Users</a>
    <a href="admin-coupons.php">Coupons</a>
    <a href="reports.php">Reports</a>
    <a href="shop.php">Shop</a>

    <form method="POST" action="../Controller/AuthController.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container admin-orders-page">

  <div class="pageHead">
    <div>
      <span class="tag">Admin area</span>
      <h1>Orders Management</h1>
      <p>Review orders, payment status, and Instapay transfer proofs.</p>
    </div>
  </div>

  <section class="panel admin-table-panel">
    <table>
      <tr>
        <th>Order</th>
        <th>Customer</th>
        <th>Date</th>
        <th>Order Status</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Instapay Proof</th>
        <th>Actions</th>
      </tr>

      <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td>
              <b>#<?php echo $order['order_id']; ?></b>
              <br>
              <small><?php echo htmlspecialchars($order['tracking_number']); ?></small>
            </td>

            <td>
              <?php echo htmlspecialchars($order['first_name'] . " " . $order['last_name']); ?>
              <br>
              <small><?php echo htmlspecialchars($order['email']); ?></small>
            </td>

            <td><?php echo htmlspecialchars($order['order_date']); ?></td>

            <td>
              <span class="badge status-badge">
                <?php echo htmlspecialchars($order['status']); ?>
              </span>
            </td>

            <td><b>$<?php echo number_format($order['total_amount'], 2); ?></b></td>

            <td>
              <?php if (!empty($order['payment_id'])): ?>
                <b><?php echo htmlspecialchars($order['payment_method']); ?></b>
                <br>
                <span class="small-status"><?php echo htmlspecialchars($order['payment_status']); ?></span>
                <?php if ($order['payment_method'] === 'Visa' && !empty($order['card_last4'])): ?>
                  <br>
                  <small>Card: **** <?php echo htmlspecialchars($order['card_last4']); ?></small>
                <?php endif; ?>
              <?php else: ?>
                <span class="muted">No payment yet</span>
              <?php endif; ?>
            </td>

            <td>
              <?php if ($order['payment_method'] === 'Instapay' && !empty($order['proof_image'])): ?>
                <?php
                  $proofPath = $order['proof_image'];
                  $proofExtension = strtolower(pathinfo($proofPath, PATHINFO_EXTENSION));
                  $isImageProof = in_array($proofExtension, ['jpg', 'jpeg', 'png', 'webp']);
                ?>
                <div class="proof-box proof-card">
                  <?php if ($isImageProof): ?>
                    <a class="proof-image-link" target="_blank" href="<?php echo htmlspecialchars($proofPath); ?>">
                      <img class="proof-thumb" src="<?php echo htmlspecialchars($proofPath); ?>" alt="Instapay transfer proof for order #<?php echo htmlspecialchars($order['order_id']); ?>">
                    </a>
                  <?php endif; ?>
                  <span>From: <b><?php echo htmlspecialchars($order['sender_phone']); ?></b></span>
                  <span>Review: <b><?php echo htmlspecialchars($order['instapay_status']); ?></b></span>
                  <a class="btn outline mini-btn proof-open-btn" target="_blank" href="<?php echo htmlspecialchars($proofPath); ?>">Open Full Image</a>
                </div>
              <?php elseif ($order['payment_method'] === 'Instapay'): ?>
                <span class="muted">Waiting for proof</span>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>

            <td>
              <div class="admin-actions-stack">
                <?php if ($order['payment_method'] === 'Instapay' && $order['payment_status'] === 'Pending'): ?>
                  <form method="POST" action="../Controller/AdminController.php" class="inline-form">
                    <input type="hidden" name="action" value="approve_instapay_payment">
                    <input type="hidden" name="payment_id" value="<?php echo $order['payment_id']; ?>">
                    <button type="submit" class="btn mini-btn approve-btn">Approve Payment</button>
                  </form>

                  <form method="POST" action="../Controller/AdminController.php" class="inline-form">
                    <input type="hidden" name="action" value="reject_instapay_payment">
                    <input type="hidden" name="payment_id" value="<?php echo $order['payment_id']; ?>">
                    <input type="hidden" name="admin_note" value="Transfer was not confirmed">
                    <button type="submit" class="btn outline mini-btn reject-btn">Reject</button>
                  </form>
                <?php endif; ?>

                <form method="POST" action="../Controller/AdminController.php" class="inline-form table-actions">
                  <input type="hidden" name="action" value="update_order_status">
                  <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">

                  <select name="status">
                    <option value="Pending Payment" <?php if ($order['status'] == "Pending Payment") echo "selected"; ?>>Pending Payment</option>
                    <option value="Payment Rejected" <?php if ($order['status'] == "Payment Rejected") echo "selected"; ?>>Payment Rejected</option>
                    <option value="Processing" <?php if ($order['status'] == "Processing") echo "selected"; ?>>Processing</option>
                    <option value="Shipped" <?php if ($order['status'] == "Shipped") echo "selected"; ?>>Shipped</option>
                    <option value="Delivered" <?php if ($order['status'] == "Delivered") echo "selected"; ?>>Delivered</option>
                    <option value="Cancelled" <?php if ($order['status'] == "Cancelled") echo "selected"; ?>>Cancelled</option>
                  </select>

                  <button type="submit" class="btn outline mini-btn">Update</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="8">No orders found</td>
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
