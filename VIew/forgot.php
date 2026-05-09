<?php
session_start();

$message = $_SESSION['forgot_message'] ?? "";
unset($_SESSION['forgot_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password | BonnaVerse</title>
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
    <a href="login.php">Login</a>
    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Reset Password</h1>

  <section class="auth panel">
    <form method="POST" action="../Controller/AuthController.php">
      <input type="hidden" name="action" value="forgot_password">

      <input 
        type="email" 
        name="email" 
        placeholder="Email" 
        required
      >

      <button type="submit" class="btn">Send Reset Link</button>
    </form>

    <?php if (!empty($message)): ?>
      <p class="ok"><?php echo htmlspecialchars($message); ?></p>
    <?php else: ?>
      <p class="ok">Success message will appear here.</p>
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