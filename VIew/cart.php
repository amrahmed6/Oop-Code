<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/cart.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];

$cart = new Cart($db, $userId, session_id());

$items = $cart->getItems();
$subtotal = $cart->getTotal();
$itemCount = 0;
foreach ($items as $cartItem) {
    $itemCount += (int)$cartItem['quantity'];
}
$delivery = !empty($items) ? 10 : 0;
$estimatedTotal = $subtotal + $delivery;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart | BonnaVerse</title>
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

<main class="container cart-page">

  <div class="pageHead cart-hero-head">
    <div>
      <span class="tag">Shopping cart</span>
      <h1>Your Cart</h1>
      <p>Review your items. Quantity changes are saved automatically.</p>
    </div>
    <a class="btn outline continue-shopping-btn" href="shop.php">Continue Shopping</a>
  </div>

  <?php if (!empty($items)): ?>
    <div class="cart-layout-modern">
      <section class="panel cart-items-panel">
        <div class="cart-panel-title">
          <div>
            <h2>Cart Items</h2>
            <p><?php echo $itemCount; ?> item<?php echo $itemCount == 1 ? '' : 's'; ?> in your cart</p>
          </div>
          <span class="cart-pill">Auto update enabled</span>
        </div>

        <div id="cartList" class="cart-list-modern">
          <?php foreach ($items as $item): ?>
            <article class="cart-item-card">
              <div class="cart-item-image-wrap">
                <?php if (!empty($item['image'])): ?>
                  <img
                    src="<?php echo htmlspecialchars($item['image']); ?>"
                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                  >
                <?php else: ?>
                  <div class="cart-image-placeholder">No Image</div>
                <?php endif; ?>
              </div>

              <div class="cart-item-info">
                <div>
                  <span class="small-label">Product</span>
                  <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                </div>

                <div class="cart-price-grid">
                  <div>
                    <span>Unit price</span>
                    <b>$<?php echo number_format((float)$item['price'], 2); ?></b>
                  </div>
                  <div>
                    <span>Line total</span>
                    <b>$<?php echo number_format((float)$item['total'], 2); ?></b>
                  </div>
                </div>
              </div>

              <div class="cart-actions-modern">
                <form method="POST" action="../Controller/CartController.php" class="quantity-form auto-quantity-form">
                  <input type="hidden" name="action" value="update_cart_quantity">
                  <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                  <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">

                  <label>Quantity</label>
                  <div class="qty-stepper">
                    <button type="button" class="qty-btn" data-qty-minus aria-label="Decrease quantity">−</button>
                    <input
                      class="auto-qty-input"
                      type="number"
                      name="quantity"
                      value="<?php echo (int)$item['quantity']; ?>"
                      min="1"
                      inputmode="numeric"
                    >
                    <button type="button" class="qty-btn" data-qty-plus aria-label="Increase quantity">+</button>
                  </div>
                </form>

                <form method="POST" action="../Controller/CartController.php" class="remove-form-modern">
                  <input type="hidden" name="action" value="remove_cart_item">
                  <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                  <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                  <button type="submit" class="btn outline remove-cart-btn">Remove</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <aside class="summary cart-summary-modern">
        <span class="tag">Summary</span>
        <h2>Order Summary</h2>

        <div class="summary-lines">
          <div>
            <span>Items</span>
            <b><?php echo $itemCount; ?></b>
          </div>
          <div>
            <span>Subtotal</span>
            <b>$<?php echo number_format($subtotal, 2); ?></b>
          </div>
          <div>
            <span>Delivery</span>
            <b>$<?php echo number_format($delivery, 2); ?></b>
          </div>
          <div class="summary-total-line">
            <span>Estimated total</span>
            <b>$<?php echo number_format($estimatedTotal, 2); ?></b>
          </div>
        </div>

        <form method="POST" action="checkout.php" class="checkout-summary-form">
          <label>Coupon code</label>
          <input type="text" name="coupon_code" placeholder="Enter coupon code">
          <button type="submit" class="btn checkout-btn-modern">Proceed to Checkout</button>
        </form>

        <p class="muted secure-note">Secure checkout · Easy order tracking · Fast confirmation</p>
      </aside>
    </div>
  <?php else: ?>
    <section class="panel empty-cart-modern">
      <div class="empty-cart-icon">🛒</div>
      <h2>Your cart is empty</h2>
      <p>Start shopping and add products to your cart.</p>
      <a href="shop.php" class="btn">Go Shopping</a>
    </section>
  <?php endif; ?>

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
  document.querySelectorAll('.auto-quantity-form').forEach((form) => {
    const input = form.querySelector('.auto-qty-input');
    const minus = form.querySelector('[data-qty-minus]');
    const plus = form.querySelector('[data-qty-plus]');
    let timer;

    function submitQuantity() {
      const value = parseInt(input.value, 10);
      if (!value || value < 1) {
        input.value = 1;
      }
      form.submit();
    }

    function delayedSubmit() {
      clearTimeout(timer);
      timer = setTimeout(submitQuantity, 650);
    }

    input.addEventListener('input', delayedSubmit);
    input.addEventListener('change', submitQuantity);

    minus.addEventListener('click', () => {
      const current = parseInt(input.value, 10) || 1;
      input.value = Math.max(1, current - 1);
      submitQuantity();
    });

    plus.addEventListener('click', () => {
      const current = parseInt(input.value, 10) || 1;
      input.value = current + 1;
      submitQuantity();
    });
  });
</script>
</body>
</html>
