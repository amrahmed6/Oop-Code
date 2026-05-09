<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

$database = new Database();
$db = $database->connect();

$productModel = new Product($db);
$products = $productModel->getAll();

$featuredProducts = array_slice($products, 0, 6);

$isLoggedIn = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home | BonnaVerse</title>
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

    <?php if ($isLoggedIn): ?>
      <a href="orders.php">Orders</a>
      <a href="account.php">Account</a>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
      <a href="admin.php">Admin</a>
    <?php endif; ?>

      <form method="POST" action="../Controller/AuthController.php" class="inline-form">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="darkBtn">Logout</button>
      </form>
    <?php else: ?>
      <a href="login.php">Login</a>
    <?php endif; ?>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <section class="hero">
    <div>
      <p class="tag">Multi-brand marketplace</p>
      <h1>Buy trending sneakers & streetwear</h1>
      <p>Simple design inspired by clean resale websites: white space, cards, filters, and clear prices.</p>
      <a class="btn" href="shop.php">Start Shopping</a>
    </div>
  </section>

  <h2>Featured Products</h2>

  <div class="grid productGrid">

    <?php if (!empty($featuredProducts)): ?>
      <?php foreach ($featuredProducts as $product): ?>

        <div class="card panel">
          <?php if (!empty($product['image'])): ?>
            <img 
              src="<?php echo htmlspecialchars($product['image']); ?>" 
              alt="<?php echo htmlspecialchars($product['name']); ?>"
              style="width:100%; max-height:180px; object-fit:cover;"
            >
          <?php endif; ?>

          <h3><?php echo htmlspecialchars($product['name']); ?></h3>

          <p>
            <?php echo htmlspecialchars($product['brand']); ?> ·
            <?php echo htmlspecialchars($product['category']); ?>
          </p>

          <p>
            <b>$<?php echo htmlspecialchars($product['price']); ?></b>
          </p>

          <p>
            Stock: <?php echo htmlspecialchars($product['stock_count']); ?>
          </p>

          <a class="btn outline" href="product.php?id=<?php echo $product['product_id']; ?>">
            View
          </a>

          <?php if ($isLoggedIn && $product['stock_count'] > 0): ?>
            <form method="POST" action="../Controller/CartController.php" class="inline-form">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
              <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
              <input type="hidden" name="quantity" value="1">

              <button type="submit" class="btn">Add To Cart</button>
            </form>
          <?php endif; ?>
        </div>

      <?php endforeach; ?>
    <?php else: ?>

      <section class="panel">
        <p>No products found.</p>
      </section>

    <?php endif; ?>

  </div>

  <h2>Categories / Brands</h2>

  <div class="chips">
    <span>Nike</span>
    <span>Adidas</span>
    <span>Jordan</span>
    <span>Supreme</span>
    <span>Yeezy</span>
    <span>New Balance</span>
  </div>

  <h2>New Arrivals / Best Sellers</h2>

  <div class="grid miniGrid">
    <div class="panel">
      <b>New Arrivals</b>
      <p>Fresh drops added weekly.</p>
      <a href="shop.php" class="btn outline">View New Products</a>
    </div>

    <div class="panel">
      <b>Best Sellers</b>
      <p>Most popular products right now.</p>
      <a href="shop.php" class="btn outline">View Best Sellers</a>
    </div>
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