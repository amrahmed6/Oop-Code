<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Policies | BonnaVerse</title>
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
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="account.php">Account</a>
    <?php else: ?>
      <a href="login.php">Login</a>
    <?php endif; ?>
    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">
  <section class="policy-hero">
    <span class="tag">Store policies</span>
    <h1>Terms & Privacy Policy</h1>
    <p>
      Please read these policies before creating an account or placing an order. They explain how BonnaVerse handles user accounts, orders, payments, returns, and personal data.
    </p>
  </section>

  <section class="policy-grid">
    <article class="policy-card" id="terms">
      <h2>Terms & Conditions</h2>
      <ul>
        <li>Users must provide correct account, contact, and delivery information.</li>
        <li>Product prices, availability, and discounts may change based on stock and admin updates.</li>
        <li>Orders are confirmed only after checkout is completed successfully.</li>
        <li>The customer can cancel an order only if the order has not been processed or shipped yet.</li>
        <li>Coupons must be valid and not expired before they can be applied.</li>
      </ul>
    </article>

    <article class="policy-card" id="privacy">
      <h2>Privacy Policy</h2>
      <ul>
        <li>The system stores user information such as name, email, phone number, and delivery address.</li>
        <li>User data is used only for login, order processing, delivery, and account management.</li>
        <li>Passwords should be stored securely by the back-end using hashing before the final deployment.</li>
        <li>Customer order and payment information must not be shared with unauthorized users.</li>
        <li>Admins can access user and order data only for store management purposes.</li>
      </ul>
    </article>

    <article class="policy-card" id="returns">
      <h2>Return & Refund Policy</h2>
      <ul>
        <li>Returned products should be unused and in their original condition.</li>
        <li>Refunds depend on the selected payment method and order status.</li>
        <li>Damaged or wrong products should be reported as soon as possible after delivery.</li>
      </ul>
    </article>

    <article class="policy-card" id="shipping">
      <h2>Shipping Policy</h2>
      <ul>
        <li>Delivery time depends on the selected address and product availability.</li>
        <li>The customer is responsible for entering a correct delivery address.</li>
        <li>The order tracking number may appear after the order is processed by the admin.</li>
      </ul>
    </article>

    <article class="policy-card full-card">
      <h2>Need help?</h2>
      <p>
        If you have any question about these policies, please contact the store support team or go back to the registration page.
      </p>
      <div class="action-row">
        <a class="btn" href="register.php">Back to Register</a>
        <a class="btn outline" href="shop.php">Continue Shopping</a>
      </div>
    </article>
  </section>
</main>

<footer class="footer">
  <div>
    <b>BonnaVerse</b>
    <p>Simple multi-brand marketplace front-end.</p>
  </div>

  <div>
    <b>Links</b>
    <p>Shop · Orders · Account · <a href="policies.php">Policies</a></p>
  </div>

  <div>
    <b>Brands</b>
    <p>Nike · Adidas · Jordan · Supreme · Yeezy</p>
  </div>
</footer>

<script src="script.js"></script>
</body>
</html>
