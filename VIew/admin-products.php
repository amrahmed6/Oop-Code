<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/admin.php";
require_once __DIR__ . "/../Model/product.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$adminId = $_SESSION['user_id'];

$admin = new Admin($db, $adminId);

if (!$admin->isAdmin($adminId)) {
    header("Location: index.php");
    exit;
}

$productModel = new Product($db);
$products = $productModel->getAll();
$editProduct = null;

if (isset($_GET['edit'])) {
    $editProduct = $productModel->getById((int)$_GET['edit']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Product Management | BonnaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=20260509_ui_tweak_v3" />
</head>
<body>

<header class="header admin-header">
  <a class="logo" href="index.php">Bonna<span>Verse</span></a>

  <nav>
    <a href="admin.php">Dashboard</a>
    <a href="admin-products.php">Products</a>
    <a href="admin-orders.php">Orders</a>
    <a href="admin-users.php">Users</a>
    <a href="admin-coupons.php">Coupons</a>
    <a href="reports.php">Reports</a>
    <a href="shop.php">Shop</a>

    <form method="POST" action="../Controller/AuthController.php" class="inline-form">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="darkBtn">Logout</button>
    </form>

    <button type="button" class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>Product Management</h1>

  <section class="panel">
    <h2>Add Product</h2>

    <form method="POST" action="../Controller/AdminController.php" class="formGrid">
      <input type="hidden" name="action" value="add_product">
      <input type="hidden" name="admin_id" value="<?php echo $adminId; ?>">

      <input type="text" name="name" placeholder="Product Name" required>
      <input type="text" name="description" placeholder="Description" required>
      <input type="text" name="brand" placeholder="Brand" required>
      <input type="text" name="category" placeholder="Category" required>
      <input type="number" name="price" placeholder="Price" step="0.01" min="0" required>
      <input type="number" name="stock_count" placeholder="Stock" min="0" required>
      <input type="text" name="image" placeholder="Image Name / URL">

      <button type="submit" class="btn">Add Product</button>
    </form>
  </section>


  <?php if ($editProduct): ?>
    <section class="panel highlight-panel">
      <h2>Edit Product</h2>

      <form method="POST" action="../Controller/AdminController.php" class="formGrid">
        <input type="hidden" name="action" value="update_product">
        <input type="hidden" name="product_id" value="<?php echo $editProduct['product_id']; ?>">

        <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($editProduct['name']); ?>" required>
        <input type="text" name="description" placeholder="Description" value="<?php echo htmlspecialchars($editProduct['description']); ?>" required>
        <input type="text" name="brand" placeholder="Brand" value="<?php echo htmlspecialchars($editProduct['brand']); ?>" required>
        <input type="text" name="category" placeholder="Category" value="<?php echo htmlspecialchars($editProduct['category']); ?>" required>
        <input type="number" step="0.01" min="0" name="price" placeholder="Price" value="<?php echo htmlspecialchars($editProduct['price']); ?>" required>
        <input type="number" min="0" name="stock_count" placeholder="Stock" value="<?php echo htmlspecialchars($editProduct['stock_count']); ?>" required>
        <input type="text" name="image" placeholder="Image URL" value="<?php echo htmlspecialchars($editProduct['image']); ?>">

        <div class="action-row">
          <button type="submit" class="btn">Save Changes</button>
          <a href="admin-products.php" class="btn outline">Cancel Edit</a>
        </div>
      </form>
    </section>
  <?php endif; ?>

  <br>

  <section class="panel">
    <h2>All Products</h2>

    <input placeholder="Search product" id="productSearch">

    <br><br>

    <table>
      <tr>
        <th>Product</th>
        <th>Brand</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
      </tr>

      <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
          <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['brand']); ?></td>
            <td><?php echo htmlspecialchars($product['category']); ?></td>
            <td>$<?php echo htmlspecialchars($product['price']); ?></td>
            <td><?php echo htmlspecialchars($product['stock_count']); ?></td>
            <td>
              <div class="table-actions">
                <a class="btn outline" href="product.php?id=<?php echo $product['product_id']; ?>">View</a>
                <a class="btn outline" href="admin-products.php?edit=<?php echo $product['product_id']; ?>">Edit</a>

                <form method="POST" action="../Controller/AdminController.php" class="inline-form">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                  <button type="submit" class="btn outline danger">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6">No products found</td>
        </tr>
      <?php endif; ?>

    </table>
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