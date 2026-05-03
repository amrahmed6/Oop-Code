<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Orders | BonnaVerse</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<header class="header">
  <a class="logo" href="index.html">Bonna<span>Verse</span></a>
  <div class="search"><input id="searchInput" placeholder="Search sneakers, apparel, brands..." /></div>
  <nav>
    <a href="shop.html">Shop</a>
    <a href="wishlist.html">Wishlist</a>
    <a href="cart.html">Cart</a>
    <a href="login.html">Login</a>
    <button class="darkBtn" onclick="toggleDark()">☾</button>
  </nav>
</header>

<main class="container">

<h1>My Orders</h1><table><tr><th>Order</th><th>Status</th><th>Date</th><th>Total</th><th>Action</th></tr><tr><td>#STX1024</td><td><span class="badge">Shipped</span></td><td>Today</td><td>$155</td><td><a href="tracking.html">View</a></td></tr></table>

</main>

<footer class="footer">
  <div><b>BonnaVerse</b><p>Simple multi-brand marketplace front-end.</p></div>
  <div><b>Links</b><p>Shop · Orders · Account · Support</p></div>
  <div><b>Brands</b><p>Nike · Adidas · Jordan · Supreme · Yeezy</p></div>
</footer>

<script src="script.js"></script>
</body>
</html>