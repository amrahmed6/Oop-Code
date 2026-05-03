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

<div class="breadcrumb">Home / Shop</div>
<div class="pageHead">
  <h1>Shop Products</h1>
  <select id="sortSelect">
    <option value="">Sort by</option>
    <option value="price">Price</option>
    <option value="name">Name</option>
    <option value="rating">Rating</option>
  </select>
</div>

<div class="shopLayout">
  <aside class="filters">
    <h3>Filters</h3>
    <input id="brandFilter" placeholder="Brand" />
    <input id="categoryFilter" placeholder="Category" />
    <input id="maxPrice" type="number" placeholder="Max price" />
    <input id="minRating" type="number" step="0.1" placeholder="Min rating" />
    <button class="btn full" onclick="renderProducts()">Apply</button>
  </aside>
  <section>
    <div class="grid productGrid"></div>
    <div class="pagination"><button>Prev</button><button class="active">1</button><button>2</button><button>Next</button></div>
  </section>
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