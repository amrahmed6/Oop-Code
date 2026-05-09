<?php

session_start();

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == "admin") {
        header("Location: admin.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

$error = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | BonnaVerse</title>
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

  <h1>Login</h1>

  <section class="auth panel">

    <?php if (!empty($error)): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="../Controller/AuthController.php">
      <input type="hidden" name="action" value="login">

      <input 
        type="email" 
        name="email" 
        placeholder="Email" 
        required
      >

      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        required
      >

      <div class="auth-options">
        <label class="remember-check">
          <input type="checkbox" name="remember">
          <span>Remember me</span>
        </label>
        <a href="forgot.php">Forgot password?</a>
      </div>

      <button type="submit" class="btn auth-submit full">Login</button>
    </form>

    <div class="auth-switch">
      <span>New customer?</span>
      <a href="register.php">Create account</a>
    </div>

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