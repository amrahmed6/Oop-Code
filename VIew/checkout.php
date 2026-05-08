<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/customer.php";
require_once __DIR__ . "/../Model/cart.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];

$customer = new Customer($db, $userId);
$profile = $customer->getProfile();

$cart = new Cart($db, $userId, session_id());
$items = $cart->getItems();
$subtotal = $cart->getTotal();

if (empty($items)) {
    header("Location: cart.php");
    exit;
}

$shippingCost = 10;
$couponCode = $_POST['coupon_code'] ?? "";
$discountAmount = 0;
$finalTotal = $subtotal + $shippingCost;
$couponMessage = "";

if (!empty($couponCode)) {
    $couponResult = $cart->applyCoupon($couponCode);

    if ($couponResult) {
        $discountAmount = $couponResult['discount_amount'];
        $finalTotal = $couponResult['final_total'] + $shippingCost;
        $couponMessage = "Coupon applied successfully";
    } else {
        $couponMessage = "Invalid or expired coupon";
        $couponCode = "";
    }
}

$name = $profile['name'] ?? "";
$email = $profile['email'] ?? "";
$phone = $profile['phone'] ?? "";
$address = $profile['delivery_address'] ?? "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Checkout | BonnaVerse</title>
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

  <h1>Checkout</h1>

  <form method="POST" action="../Controller/test.php">

    <input type="hidden" name="action" value="create_order">
    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
    <input type="hidden" name="shipping_cost" value="<?php echo $shippingCost; ?>">
    <input type="hidden" name="coupon_code" value="<?php echo htmlspecialchars($couponCode); ?>">

    <div class="formGrid">

      <section class="panel">
        <h2>Shipping Address</h2>

        <input 
          type="text" 
          name="full_address" 
          placeholder="Full address" 
          value="<?php echo htmlspecialchars($address); ?>" 
          required
        >

        <input 
          type="text" 
          name="city" 
          placeholder="City" 
          required
        >

        <input 
          type="text" 
          name="zip_code" 
          placeholder="ZIP code" 
          required
        >
      </section>

      <section class="panel">
        <h2>Contact Info</h2>

        <input 
          type="email" 
          name="email" 
          placeholder="Email" 
          value="<?php echo htmlspecialchars($email); ?>" 
          required
        >

        <input 
          type="text" 
          name="phone" 
          placeholder="Phone" 
          value="<?php echo htmlspecialchars($phone); ?>" 
          required
        >
      </section>

    </div>

    <div class="summary">
      <h3>Order Summary</h3>

      <?php foreach ($items as $item): ?>
        <p>
          <?php echo htmlspecialchars($item['name']); ?>
          × <?php echo $item['quantity']; ?>
          =
          <b>$<?php echo number_format($item['total'], 2); ?></b>
        </p>
      <?php endforeach; ?>

      <hr>

      <p>Subtotal: <b>$<?php echo number_format($subtotal, 2); ?></b></p>

      <?php if (!empty($couponMessage)): ?>
        <p><?php echo htmlspecialchars($couponMessage); ?></p>
      <?php endif; ?>

      <?php if ($discountAmount > 0): ?>
        <p>Discount: <b>-$<?php echo number_format($discountAmount, 2); ?></b></p>
      <?php endif; ?>

      <p>Delivery: <b>$<?php echo number_format($shippingCost, 2); ?></b></p>

      <h3>Total: $<?php echo number_format($finalTotal, 2); ?></h3>

      <button type="submit" class="btn">Place Order</button>
    </div>

  </form>

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