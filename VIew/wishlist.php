<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/Wishlist.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];

$wishlistModel = new Wishlist($db);
$items = $wishlistModel->getByUserId($userId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wishlist | BonnaVerse</title>
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

    <form method="POST" action="../Controller/test.php" style="display:inline;">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Wishlist</h1>

  <div id="wishlistList" class="grid">

    <?php if (!empty($items)): ?>
      <?php foreach ($items as $item): ?>

        <?php
          $image = !empty($item['image'])
            ? $item['image']
            : "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop";
        ?>

        <div class="card">
          <a href="product.php?id=<?php echo $item['product_id']; ?>">
            <img 
              src="<?php echo htmlspecialchars($image); ?>" 
              alt="<?php echo htmlspecialchars($item['name']); ?>"
            >
          </a>

          <p class="muted">
            <?php echo htmlspecialchars($item['brand']); ?> ·
            <?php echo htmlspecialchars($item['category']); ?>
          </p>

          <h3><?php echo htmlspecialchars($item['name']); ?></h3>

          <p>
            ⭐ <?php echo htmlspecialchars($item['average_rating']); ?>
            <b style="float:right">
              $<?php echo htmlspecialchars($item['price']); ?>
            </b>
          </p>

          <p>
            Stock: <?php echo htmlspecialchars($item['stock_count']); ?>
          </p>

          <a class="btn outline" href="product.php?id=<?php echo $item['product_id']; ?>">
            View
          </a>

          <?php if ($item['stock_count'] > 0): ?>
            <form method="POST" action="../controllers/PostController.php" style="display:inline;">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
              <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
              <input type="hidden" name="quantity" value="1">
              <input type="hidden" name="redirect_to" value="../View/wishlist.php">

              <button type="submit" class="btn">Add Cart</button>
            </form>
          <?php else: ?>
            <button class="btn" disabled>Out of Stock</button>
          <?php endif; ?>

          <form method="POST" action="../controllers/PostController.php" style="display:inline;">
            <input type="hidden" name="action" value="remove_wishlist">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">

            <button type="submit" class="btn outline">Remove</button>
          </form>
        </div>

      <?php endforeach; ?>

    <?php else: ?>

      <section class="panel">
        <p>Your wishlist is empty.</p>
        <a href="shop.php" class="btn">Go To Shop</a>
      </section>

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