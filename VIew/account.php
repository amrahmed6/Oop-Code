<?php

session_start();

require_once __DIR__ . "/../Model/Database.php";
require_once __DIR__ . "/../Model/user.php";
require_once __DIR__ . "/../Model/customer.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->connect();

$userId = $_SESSION['user_id'];

$customer = new Customer($db, $userId);
$profile = $customer->getProfile();

if (!$profile) {
    header("Location: login.php");
    exit;
}

$name = $profile['name'] ?? ($profile['first_name'] . " " . $profile['last_name']);
$email = $profile['email'] ?? "";
$phone = $profile['phone'] ?? "";
$address = $profile['delivery_address'] ?? "No saved address";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Account | BonnaVerse</title>
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
    <button class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

  <h1>My Account</h1>

  <div class="formGrid">

    <section class="panel">
      <h2>Personal Info</h2>

      <form method="POST" action="../Controller/test.php">
        <input type="hidden" name="action" value="update_profile">
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

        <input 
          type="text" 
          name="name" 
          value="<?php echo htmlspecialchars($name); ?>" 
          placeholder="Full Name"
        >

        <input 
          type="email" 
          name="email" 
          value="<?php echo htmlspecialchars($email); ?>" 
          placeholder="Email"
          readonly
        >

        <input 
          type="text" 
          name="phone" 
          value="<?php echo htmlspecialchars($phone); ?>" 
          placeholder="Phone"
        >

        <button type="submit" class="btn">Edit Profile</button>
      </form>
    </section>

    <section class="panel">
      <h2>Saved Address</h2>

      <form method="POST" action="../Controller/test.php">
        <input type="hidden" name="action" value="update_address">
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

        <input 
          type="text" 
          name="delivery_address" 
          value="<?php echo htmlspecialchars($address); ?>" 
          placeholder="Delivery Address"
        >

        <button type="submit" class="btn">Update Address</button>
      </form>

      <br>

      <form method="POST" action="../Controller/test.php">
        <input type="hidden" name="action" value="change_password">
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">

        <input type="password" name="old_password" placeholder="Old Password">
        <input type="password" name="new_password" placeholder="New Password">

        <button type="submit" class="btn outline">Change Password</button>
      </form>
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