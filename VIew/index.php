<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home | BonnaVerse</title>
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

<section class="hero">
  <div>
    <p class="tag">Multi-brand marketplace</p>
    <h1>Buy trending sneakers & streetwear</h1>
    <p>Simple design inspired by clean resale websites: white space, cards, filters, and clear prices.</p>
    <a class="btn" href="shop.html">Start Shopping</a>
  </div>
</section>

<h2>Featured Products</h2>
<div class="grid productGrid"></div>

<h2>Categories / Brands</h2>
<div class="chips">
  <span>Nike</span><span>Adidas</span><span>Jordan</span><span>Supreme</span><span>Yeezy</span><span>New Balance</span>
</div>

<h2>New Arrivals / Best Sellers</h2>
<div class="grid miniGrid">
  <div class="panel"><b>New Arrivals</b><p>Fresh drops added weekly.</p></div>
  <div class="panel"><b>Best Sellers</b><p>Most popular products right now.</p></div>
</div>

</main>

<footer class="footer">
  <div><b>BonnaVerse</b><p>Simple multi-brand marketplace front-end.</p></div>
  <div><b>Links</b><p>Shop · Orders · Account · Support</p></div>
  <div><b>Brands</b><p>Nike · Adidas · Jordan · Supreme · Yeezy</p></div>
</footer>

<script src="script.js"></script>
</body>
</html>