<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/product.php";

if (!isset($_GET['id'])) {
    header("Location: shop.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$productId = $_GET['id'];

$productModel = new Product($db);
$product = $productModel->getById($productId);

if (!$product) {
    header("Location: shop.php");
    exit;
}

$reviews = $productModel->getReviews($productId);

$similarProducts = $productModel->getByCategory($product['category']);
$similarProducts = array_filter($similarProducts, function ($item) use ($productId) {
    return $item['product_id'] != $productId;
});

$isLoggedIn = isset($_SESSION['user_id']);

$image = !empty($product['image'])
    ? $product['image']
    : "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($product['name']); ?> | BonnaVerse</title>
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

    <?php if ($isLoggedIn): ?>
      <a href="orders.php">Orders</a>
      <a href="account.php">Account</a>

      <form method="POST" action="../Controller/test.php" style="display:inline;">
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

  <div class="breadcrumb">
    <a href="index.php">Home</a> / 
    <a href="shop.php">Shop</a> / 
    <?php echo htmlspecialchars($product['name']); ?>
  </div>

  <section class="details">
    <img 
      src="<?php echo htmlspecialchars($image); ?>" 
      alt="<?php echo htmlspecialchars($product['name']); ?>"
    />

    <div>
      <p class="tag">
        <?php echo htmlspecialchars($product['brand']); ?> /
        <?php echo htmlspecialchars($product['category']); ?>
      </p>

      <h1><?php echo htmlspecialchars($product['name']); ?></h1>

      <h2>$<?php echo htmlspecialchars($product['price']); ?></h2>

      <p>
        <?php echo htmlspecialchars($product['description']); ?>
      </p>

      <p>
        Rating:
        ⭐ <?php echo htmlspecialchars($product['average_rating']); ?> / 5
        —
        <?php echo htmlspecialchars($product['review_count']); ?> reviews
      </p>

      <p>
        Stock:
        <b><?php echo htmlspecialchars($product['stock_count']); ?></b>
      </p>

      <?php if ($isLoggedIn): ?>

        <?php if ($product['stock_count'] > 0): ?>
          <form method="POST" action="../Controller/test.php" style="display:inline;">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

            <input type="number" name="quantity" value="1" min="1">

            <button type="submit" class="btn">Add to Cart</button>
          </form>
        <?php else: ?>
          <button class="btn" disabled>Out of Stock</button>
        <?php endif; ?>

        <form method="POST" action="../Controller/test.php" style="display:inline;">
          <input type="hidden" name="action" value="add_wishlist">
          <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
          <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

          <button type="submit" class="btn outline">Add to Wishlist</button>
        </form>

      <?php else: ?>

        <a class="btn" href="login.php">Login to Buy</a>

      <?php endif; ?>

    </div>
  </section>

  <h2>Similar Products</h2>

  <div class="grid productGrid">
    <?php if (!empty($similarProducts)): ?>
      <?php foreach ($similarProducts as $similar): ?>
        <div class="card panel">
          <?php if (!empty($similar['image'])): ?>
            <img 
              src="<?php echo htmlspecialchars($similar['image']); ?>" 
              alt="<?php echo htmlspecialchars($similar['name']); ?>"
              style="width:100%; max-height:180px; object-fit:cover;"
            >
          <?php endif; ?>

          <h3><?php echo htmlspecialchars($similar['name']); ?></h3>

          <p>
            <?php echo htmlspecialchars($similar['brand']); ?> /
            <?php echo htmlspecialchars($similar['category']); ?>
          </p>

          <p>
            <b>$<?php echo htmlspecialchars($similar['price']); ?></b>
          </p>

          <a class="btn outline" href="product.php?id=<?php echo $similar['product_id']; ?>">
            View
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <section class="panel">
        <p>No similar products found.</p>
      </section>
    <?php endif; ?>
  </div>

  <section class="panel">
    <h2>Reviews</h2>

    <?php if (!empty($reviews)): ?>
      <?php foreach ($reviews as $review): ?>
        <div class="panel">
          <p>
            <b>
              <?php echo htmlspecialchars($review['first_name'] . " " . $review['last_name']); ?>
            </b>
          </p>

          <p>
            Rating: ⭐ <?php echo htmlspecialchars($review['rating']); ?> / 5
          </p>

          <p>
            <?php echo htmlspecialchars($review['comment']); ?>
          </p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No reviews yet.</p>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
      <br>

      <h3>Add Review</h3>

      <form method="POST" action="../Controller/test.php">
        <input type="hidden" name="action" value="add_review">
        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">

        <input type="number" name="rating" min="1" max="5" placeholder="Rating from 1 to 5" required>

        <input type="text" name="comment" placeholder="Write your review" required>

        <button type="submit" class="btn">Submit Review</button>
      </form>
    <?php else: ?>
      <p>
        <a href="login.php">Login</a> to write a review.
      </p>
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