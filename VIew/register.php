<?php

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = $_SESSION['register_error'] ?? "";
unset($_SESSION['register_error']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | BonnaVerse</title>
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

  <h1>Register</h1>

  <section class="auth panel">

    <?php if (!empty($error)): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="../Controller/AuthController.php">
      <input type="hidden" name="action" value="register">

      <input 
        type="text" 
        name="first_name" 
        placeholder="First name" 
        required
      >

      <input 
        type="text" 
        name="last_name" 
        placeholder="Last name" 
        required
      >

      <input 
        type="email" 
        name="email" 
        placeholder="Email" 
        required
      >

      <input 
        type="text" 
        name="phone" 
        placeholder="Phone" 
        required
      >

      <input 
        type="text" 
        name="delivery_address" 
        placeholder="Delivery address" 
        required
      >

      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        required
      >

      <input 
        type="password" 
        name="confirm_password" 
        placeholder="Confirm password" 
        required
      >

      <label class="policy-check">
        <input type="checkbox" name="terms" required>
        <span>
          I agree to the
          <a href="policies.php#terms" target="_blank" rel="noopener">Terms & Conditions</a>
          and
          <a href="policies.php#privacy" target="_blank" rel="noopener">Privacy Policy</a>.
        </span>
      </label>

      <button type="submit" class="btn auth-submit full">Create Account</button>
    </form>

    <br>

    <a href="login.php">Already have an account? Login</a>

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