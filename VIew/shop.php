<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";

$database = new Database();
$db = $database->connect();

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;

$brand = $_GET['brand'] ?? "";
$category = $_GET['category'] ?? "";
$maxPrice = $_GET['max_price'] ?? "";
$minRating = $_GET['min_rating'] ?? "";
$sort = $_GET['sort'] ?? "";

$where = [];
$params = [];

if (!empty($brand)) {
    $where[] = "brand LIKE :brand";
    $params[":brand"] = "%" . $brand . "%";
}

if (!empty($category)) {
    $where[] = "category LIKE :category";
    $params[":category"] = "%" . $category . "%";
}

if (!empty($maxPrice)) {
    $where[] = "price <= :max_price";
    $params[":max_price"] = $maxPrice;
}

if (!empty($minRating)) {
    $where[] = "average_rating >= :min_rating";
    $params[":min_rating"] = $minRating;
}

$sql = "SELECT * FROM Product";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

if ($sort == "price") {
    $sql .= " ORDER BY price ASC";
} elseif ($sort == "name") {
    $sql .= " ORDER BY name ASC";
} elseif ($sort == "rating") {
    $sql .= " ORDER BY average_rating DESC";
} else {
    $sql .= " ORDER BY product_id DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$currentUrl = "../View/shop.php";

if (!empty($_SERVER['QUERY_STRING'])) {
    $currentUrl .= "?" . $_SERVER['QUERY_STRING'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop | BonnaVerse</title>
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

      <?php if (isset($_SESSION['role']) && $_SESSION['role'] == "admin"): ?>
        <a href="admin.php">Admin</a>
      <?php endif; ?>

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
    <a href="index.php">Home</a> / Shop
  </div>

  <div class="pageHead">
    <h1>Shop Products</h1>

    <form method="GET" action="shop.php">
      <input type="hidden" name="brand" value="<?php echo htmlspecialchars($brand); ?>">
      <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
      <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($maxPrice); ?>">
      <input type="hidden" name="min_rating" value="<?php echo htmlspecialchars($minRating); ?>">

      <select id="sortSelect" name="sort" onchange="this.form.submit()">
        <option value="">Sort by</option>
        <option value="price" <?php if ($sort == "price") echo "selected"; ?>>Price</option>
        <option value="name" <?php if ($sort == "name") echo "selected"; ?>>Name</option>
        <option value="rating" <?php if ($sort == "rating") echo "selected"; ?>>Rating</option>
      </select>
    </form>
  </div>

  <div class="shopLayout">

    <aside class="filters">
      <h3>Filters</h3>

      <form method="GET" action="shop.php">
        <input 
          id="brandFilter" 
          name="brand" 
          placeholder="Brand" 
          value="<?php echo htmlspecialchars($brand); ?>"
        >

        <input 
          id="categoryFilter" 
          name="category" 
          placeholder="Category" 
          value="<?php echo htmlspecialchars($category); ?>"
        >

        <input 
          id="maxPrice" 
          name="max_price" 
          type="number" 
          placeholder="Max price" 
          value="<?php echo htmlspecialchars($maxPrice); ?>"
        >

        <input 
          id="minRating" 
          name="min_rating" 
          type="number" 
          step="0.1" 
          placeholder="Min rating" 
          value="<?php echo htmlspecialchars($minRating); ?>"
        >

        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

        <button type="submit" class="btn full">Apply</button>

        <a href="shop.php" class="btn outline full">Clear</a>
      </form>
    </aside>

    <section>
      <div class="grid productGrid">

        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product): ?>

            <?php
              $image = !empty($product['image'])
                ? $product['image']
                : "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop";
            ?>

            <div class="card">
              <a href="product.php?id=<?php echo $product['product_id']; ?>">
                <img 
                  src="<?php echo htmlspecialchars($image); ?>" 
                  alt="<?php echo htmlspecialchars($product['name']); ?>"
                >
              </a>

              <p class="muted">
                <?php echo htmlspecialchars($product['brand']); ?> ·
                <?php echo htmlspecialchars($product['category']); ?>
              </p>

              <h3><?php echo htmlspecialchars($product['name']); ?></h3>

              <p>
                ⭐ <?php echo htmlspecialchars($product['average_rating']); ?>
                <b style="float:right">
                  $<?php echo htmlspecialchars($product['price']); ?>
                </b>
              </p>

              <p>
                Stock: <?php echo htmlspecialchars($product['stock_count']); ?>
              </p>

              <a class="btn outline" href="product.php?id=<?php echo $product['product_id']; ?>">
                View
              </a>

              <?php if ($isLoggedIn): ?>

                <?php if ($product['stock_count'] > 0): ?>
                  <form method="POST" action="../Controller/test.php" style="display:inline;">
                    <input type="hidden" name="action" value="add_to_cart">
                    <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($currentUrl); ?>">

                    <button type="submit" class="btn">Add Cart</button>
                  </form>
                <?php else: ?>
                  <button class="btn" disabled>Out of Stock</button>
                <?php endif; ?>

                <form method="POST" action="../Controller/test.php" style="display:inline;">
                  <input type="hidden" name="action" value="add_wishlist">
                  <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
                  <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                  <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($currentUrl); ?>">

                  <button type="submit" class="btn outline">♡</button>
                </form>

              <?php else: ?>

                <a class="btn" href="login.php">Login to Buy</a>

              <?php endif; ?>
            </div>

          <?php endforeach; ?>
        <?php else: ?>

          <section class="panel">
            <p>No products found.</p>
          </section>

        <?php endif; ?>

      </div>
    </section>

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