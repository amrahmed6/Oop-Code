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
$orderId = (int)$_GET['order_id'];

$orderModel = new Order($db, $userId);
$order = $orderModel->getById($orderId);

if (!$order) {
    header("Location: orders.php");
    exit;
}

$paymentModel = new Payment($db);
$existingPayment = $paymentModel->getByOrderId($orderId);
$instapayTransfer = $existingPayment ? $paymentModel->getInstapayByOrderId($orderId) : null;
$visaDetails = $existingPayment ? $paymentModel->getVisaByOrderId($orderId) : null;
$paymentError = $_SESSION['payment_error'] ?? "";
unset($_SESSION['payment_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment | BonnaVerse</title>
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

<main class="container payment-page payment-page-modern">

  <div class="pageHead payment-head">
    <div>
      <span class="tag">Secure checkout</span>
      <h1>Payment</h1>
      <p>Choose a payment method. Extra fields appear only for the selected method.</p>
    </div>
  </div>

  <?php if (!empty($paymentError)): ?>
    <div class="alert error-alert"><?php echo htmlspecialchars($paymentError); ?></div>
  <?php endif; ?>

  <div class="payment-layout-modern">
    <section class="panel payment-summary-card payment-summary-modern-card">
      <div>
        <span class="small-label">Current order</span>
        <h2>Order #<?php echo htmlspecialchars($order['order_id']); ?></h2>
        <p>Status: <b><?php echo htmlspecialchars($order['status']); ?></b></p>
        <p>Tracking Number: <b><?php echo htmlspecialchars($order['tracking_number']); ?></b></p>
      </div>
      <div class="payment-total-box">
        <span>Final Total</span>
        <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
      </div>
    </section>

    <section class="panel payment-panel payment-panel-modern">
      <div class="payment-panel-title">
        <div>
          <span class="tag">Method</span>
          <h2>Payment Methods</h2>
        </div>
        <span class="cart-pill">Demo checkout</span>
      </div>

      <?php if ($existingPayment): ?>

        <?php if ($existingPayment['payment_method'] === 'Instapay' && $existingPayment['status'] === 'Pending'): ?>
          <div class="alert pending-alert">
            Instapay transfer uploaded successfully. Waiting for admin approval.
          </div>
        <?php elseif ($existingPayment['status'] === 'Completed'): ?>
          <div class="alert ok-alert">
            Payment completed successfully.
          </div>
        <?php else: ?>
          <div class="alert error-alert">
            Payment status: <?php echo htmlspecialchars($existingPayment['status']); ?>
          </div>
        <?php endif; ?>

        <div class="payment-status-grid">
          <div>
            <span>Method</span>
            <b><?php echo htmlspecialchars($existingPayment['payment_method']); ?></b>
          </div>
          <div>
            <span>Status</span>
            <b><?php echo htmlspecialchars($existingPayment['status']); ?></b>
          </div>
          <?php if ($visaDetails): ?>
            <div>
              <span>Visa Card</span>
              <b>**** <?php echo htmlspecialchars($visaDetails['card_last4']); ?></b>
            </div>
            <div>
              <span>Expiry</span>
              <b><?php echo htmlspecialchars(str_pad($visaDetails['expiry_month'], 2, '0', STR_PAD_LEFT) . '/' . $visaDetails['expiry_year']); ?></b>
            </div>
          <?php endif; ?>
          <?php if ($instapayTransfer): ?>
            <div>
              <span>Sender Phone</span>
              <b><?php echo htmlspecialchars($instapayTransfer['sender_phone']); ?></b>
            </div>
            <div>
              <span>Admin Review</span>
              <b><?php echo htmlspecialchars($instapayTransfer['admin_status']); ?></b>
            </div>
          <?php endif; ?>
        </div>

        <a class="btn" href="success.php?order_id=<?php echo $orderId; ?>">Continue</a>

      <?php else: ?>

        <form method="POST" action="../Controller/PaymentController.php" enctype="multipart/form-data" class="payment-form modern-payment-form">

          <input type="hidden" name="action" value="create_payment">
          <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($orderId); ?>">

          <div class="payment-methods-list">
            <label class="pay-option-card" data-payment-card>
              <input class="pay-radio" style="display:none !important;" type="radio" name="payment_method" value="Visa" required data-target="visa-fields">
              <span class="pay-dot" aria-hidden="true"></span>
              <span class="pay-icon">💳</span>
              <span class="pay-text">
                <strong>Visa</strong>
                <small>Enter card details for the demo payment.</small>
              </span>
            </label>

            <label class="pay-option-card" data-payment-card>
              <input class="pay-radio" style="display:none !important;" type="radio" name="payment_method" value="Instapay" required data-target="instapay-fields">
              <span class="pay-dot" aria-hidden="true"></span>
              <span class="pay-icon">📲</span>
              <span class="pay-text">
                <strong>Instapay</strong>
                <small>Upload transfer proof. Admin will approve after confirming money.</small>
              </span>
            </label>

            <label class="pay-option-card" data-payment-card>
              <input class="pay-radio" style="display:none !important;" type="radio" name="payment_method" value="Cash" required data-target="cash-fields">
              <span class="pay-dot" aria-hidden="true"></span>
              <span class="pay-icon">💵</span>
              <span class="pay-text">
                <strong>Cash</strong>
                <small>Pay when the order arrives.</small>
              </span>
            </label>
          </div>

          <div id="visa-fields" class="payment-extra-fields visa-card-fields is-hidden" hidden aria-hidden="true">
            <div class="extra-title-row">
              <div>
                <h3>Visa Card Details</h3>
                <p class="muted">This is a student-project simulation. CVV is checked but not stored.</p>
              </div>
              <span class="secure-chip">Secure demo</span>
            </div>

            <div class="visa-form-grid">
              <label>
                Cardholder name
                <input type="text" name="cardholder_name" placeholder="Name on card" data-required="true" disabled>
              </label>

              <label>
                Card number
                <input id="card-number" type="text" name="card_number" placeholder="1234 5678 9012 3456" inputmode="numeric" maxlength="23" data-required="true" disabled>
              </label>

              <label>
                Expiry month
                <select name="expiry_month" data-required="true" disabled>
                  <option value="">Month</option>
                  <?php for ($month = 1; $month <= 12; $month++): ?>
                    <option value="<?php echo $month; ?>"><?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?></option>
                  <?php endfor; ?>
                </select>
              </label>

              <label>
                Expiry year
                <select name="expiry_year" data-required="true" disabled>
                  <option value="">Year</option>
                  <?php for ($year = (int)date('Y'); $year <= (int)date('Y') + 10; $year++): ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                  <?php endfor; ?>
                </select>
              </label>

              <label>
                CVV
                <input type="password" name="card_cvv" placeholder="123" inputmode="numeric" maxlength="4" data-required="true" disabled>
              </label>
            </div>
          </div>

          <div id="instapay-fields" class="payment-extra-fields instapay-only-box is-hidden" hidden aria-hidden="true">
            <div class="extra-title-row">
              <div>
                <h3>Instapay Transfer Details</h3>
                <p class="muted">These fields appear only when Instapay is selected.</p>
              </div>
              <span class="secure-chip">Admin review</span>
            </div>
            <div class="miniGrid">
              <label>
                Sender phone number
                <input type="text" name="instapay_phone" placeholder="Phone number used for transfer" data-required="true" disabled>
              </label>
              <label class="upload-box modern-upload-box">
                <span>
                  <b>Upload transfer image</b>
                  <small>JPG, PNG, or WEBP only</small>
                </span>
                <input type="file" name="transfer_proof" accept="image/*" data-required="true" disabled>
              </label>
            </div>
          </div>

          <div id="cash-fields" class="payment-extra-fields compact-note cash-note-box is-hidden" hidden aria-hidden="true">
            <h3>Cash on Delivery</h3>
            <p class="muted">No extra details are needed. The order will be confirmed directly.</p>
          </div>

          <button type="submit" class="btn modern-pay-btn payment-submit-clean">
            Confirm Payment
          </button>

        </form>

      <?php endif; ?>

    </section>
  </div>

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
<script>
  const methodInputs = document.querySelectorAll('.pay-radio');
  const extraBoxes = document.querySelectorAll('.payment-extra-fields');
  const methodCards = document.querySelectorAll('[data-payment-card]');
  const cardNumber = document.getElementById('card-number');

  function updatePaymentFields() {
    const selected = document.querySelector('.pay-radio:checked');
    const targetId = selected ? selected.dataset.target : '';

    methodCards.forEach((card) => {
      const radio = card.querySelector('.pay-radio');
      card.classList.toggle('selected', radio && radio.checked);
    });

    extraBoxes.forEach((box) => {
      const shouldShow = box.id === targetId;
      box.hidden = !shouldShow;
      box.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
      box.classList.toggle('is-hidden', !shouldShow);

      box.querySelectorAll('input, select, textarea').forEach((field) => {
        field.disabled = !shouldShow;
        field.required = shouldShow && field.dataset.required === 'true';
      });
    });
  }

  methodInputs.forEach((input) => {
    input.addEventListener('change', updatePaymentFields);
  });

  if (cardNumber) {
    cardNumber.addEventListener('input', () => {
      const digits = cardNumber.value.replace(/\D/g, '').slice(0, 19);
      cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
    });
  }

  updatePaymentFields();
</script>
</body>
</html>
