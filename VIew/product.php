<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Product Details | BonnaVerse</title>
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

<div class="breadcrumb">Home / Shop / Product</div>
<section class="details">
  <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop" />
  <div>
    <p class="tag">Nike / Sneakers</p>
    <h1>Nike Dunk Low Panda</h1>
    <h2>$145</h2>
    <p>Clean black and white sneaker with a simple streetwear look.</p>
    <p>Rating: ⭐ 4.8 / 5 — 245 reviews</p>
    <button class="btn" onclick="addToCart(1)">Add to Cart</button>
    <button class="btn outline" onclick="addToWishlist(1)">Add to Wishlist</button>
  </div>
</section>
<h2>Similar Products</h2><div class="grid productGrid"></div>
<section class="panel"><h2>Reviews</h2><p>Great quality and fast delivery. Looks exactly like the pictures.</p></section>

</main>

<footer class="footer">
  <div><b>BonnaVerse</b><p>Simple multi-brand marketplace front-end.</p></div>
  <div><b>Links</b><p>Shop · Orders · Account · Support</p></div>
  <div><b>Brands</b><p>Nike · Adidas · Jordan · Supreme · Yeezy</p></div>
</footer>

<script src="script.js"></script>
</body>
</html>