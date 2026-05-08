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

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cart | BonnaVerse</title>
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

  <h1>Cart</h1>

  <div id="cartList">

    <?php if (!empty($items)): ?>

      <?php foreach ($items as $item): ?>
        <section class="panel">
          <h2><?php echo htmlspecialchars($item['name']); ?></h2>

          <?php if (!empty($item['image'])): ?>
            <img 
              src="<?php echo htmlspecialchars($item['image']); ?>" 
              alt="<?php echo htmlspecialchars($item['name']); ?>" 
              style="max-width:120px;"
            >
          <?php endif; ?>

          <p>Price: <b>$<?php echo htmlspecialchars($item['price']); ?></b></p>
          <p>Total: <b>$<?php echo htmlspecialchars($item['total']); ?></b></p>

          <form method="POST" action="../Controller/test.php" class="inline-form">
            <input type="hidden" name="action" value="update_cart_quantity">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">

            <input 
              type="number" 
              name="quantity" 
              value="<?php echo $item['quantity']; ?>" 
              min="1"
            >

            <button type="submit" class="btn">Update</button>
          </form>

          <form method="POST" action="../Controller/test.php" class="inline-form">
            <input type="hidden" name="action" value="remove_cart_item">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">

            <button type="submit" class="btn outline">Remove</button>
          </form>
        </section>
      <?php endforeach; ?>

    <?php else: ?>

      <section class="panel">
        <p>Your cart is empty.</p>
        <a href="shop.php" class="btn">Go Shopping</a>
      </section>

    <?php endif; ?>

  </div>

  <div class="summary">
    <h3>Order Summary</h3>

    <p>
      Subtotal:
      <b id="subtotal">$<?php echo number_format($subtotal, 2); ?></b>
    </p>

    <?php if (!empty($items)): ?>
      <form method="POST" action="checkout.php">
        <input type="text" name="coupon_code" placeholder="Coupon code">
        <button type="submit" class="btn">Proceed to Checkout</button>
      </form>
    <?php endif; ?>
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
</body>
</html>