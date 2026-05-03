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

<h1>Checkout</h1>
<div class="formGrid">
  <section class="panel"><h2>Shipping Address</h2><input placeholder="Full address"><input placeholder="City"><input placeholder="ZIP code"></section>
  <section class="panel"><h2>Contact Info</h2><input placeholder="Email"><input placeholder="Phone"></section>
</div>
<div class="summary"><h3>Order Summary</h3><p>Delivery: $10</p><a class="btn" href="payment.html">Continue to Payment</a></div>

</main>

<footer class="footer">
  <div><b>BonnaVerse</b><p>Simple multi-brand marketplace front-end.</p></div>
  <div><b>Links</b><p>Shop · Orders · Account · Support</p></div>
  <div><b>Brands</b><p>Nike · Adidas · Jordan · Supreme · Yeezy</p></div>
</footer>

<script src="script.js"></script>
</body>
</html>