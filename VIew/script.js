
const products = [{"id": 1, "name": "Nike Dunk Low Panda", "brand": "Nike", "category": "Sneakers", "price": 145, "rating": 4.8, "img": "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop"}, {"id": 2, "name": "Adidas Campus 00s", "brand": "Adidas", "category": "Sneakers", "price": 110, "rating": 4.6, "img": "https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=900&auto=format&fit=crop"}, {"id": 3, "name": "Supreme Box Logo Hoodie", "brand": "Supreme", "category": "Hoodies", "price": 280, "rating": 4.9, "img": "https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=900&auto=format&fit=crop"}, {"id": 4, "name": "Essentials Sweatpants", "brand": "Essentials", "category": "Pants", "price": 95, "rating": 4.5, "img": "https://images.unsplash.com/photo-1506629905607-d9d297d3ff98?q=80&w=900&auto=format&fit=crop"}, {"id": 5, "name": "Jordan 1 Retro High", "brand": "Jordan", "category": "Sneakers", "price": 220, "rating": 4.9, "img": "https://images.unsplash.com/photo-1552346154-21d32810aba3?q=80&w=900&auto=format&fit=crop"}, {"id": 6, "name": "Yeezy Slide Bone", "brand": "Yeezy", "category": "Slides", "price": 130, "rating": 4.4, "img": "https://images.unsplash.com/photo-1624006389438-c03488175975?q=80&w=900&auto=format&fit=crop"}, {"id": 7, "name": "Bape Shark Tee", "brand": "Bape", "category": "T-Shirts", "price": 120, "rating": 4.3, "img": "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=900&auto=format&fit=crop"}, {"id": 8, "name": "New Balance 550", "brand": "New Balance", "category": "Sneakers", "price": 140, "rating": 4.7, "img": "https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=900&auto=format&fit=crop"}];
let cart = JSON.parse(localStorage.getItem('cart') || '[]');
let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');

function save() {
  localStorage.setItem('cart', JSON.stringify(cart));
  localStorage.setItem('wishlist', JSON.stringify(wishlist));
}

function toggleDark() {
  document.body.classList.toggle('dark');
  localStorage.setItem('dark', document.body.classList.contains('dark'));
}
if(localStorage.getItem('dark') === 'true') document.body.classList.add('dark');

function productCard(p) {
  return `<div class="card">
    <a href="product.html"><img src="${p.img}" alt="${p.name}"></a>
    <p class="muted">${p.brand} · ${p.category}</p>
    <h3>${p.name}</h3>
    <p>⭐ ${p.rating} <b style="float:right">$${p.price}</b></p>
    <button class="btn" onclick="addToCart(${p.id})">Add Cart</button>
    <button class="btn outline" onclick="addToWishlist(${p.id})">♡</button>
  </div>`;
}

function renderProducts() {
  const grid = document.querySelector('.productGrid');
  if(!grid) return;
  let list = [...products];
  const brand = document.getElementById('brandFilter')?.value.toLowerCase() || '';
  const cat = document.getElementById('categoryFilter')?.value.toLowerCase() || '';
  const max = Number(document.getElementById('maxPrice')?.value || 999999);
  const rating = Number(document.getElementById('minRating')?.value || 0);
  const search = document.getElementById('searchInput')?.value.toLowerCase() || '';
  list = list.filter(p => p.brand.toLowerCase().includes(brand) && p.category.toLowerCase().includes(cat) && p.price <= max && p.rating >= rating && p.name.toLowerCase().includes(search));
  const sort = document.getElementById('sortSelect')?.value;
  if(sort === 'price') list.sort((a,b)=>a.price-b.price);
  if(sort === 'name') list.sort((a,b)=>a.name.localeCompare(b.name));
  if(sort === 'rating') list.sort((a,b)=>b.rating-a.rating);
  grid.innerHTML = list.map(productCard).join('');
}

function addToCart(id) {
  const item = cart.find(x=>x.id===id);
  if(item) item.qty++;
  else cart.push({id, qty:1});
  save(); alert('Added to cart');
}

function addToWishlist(id) {
  if(!wishlist.includes(id)) wishlist.push(id);
  save(); alert('Added to wishlist');
}

function renderCart() {
  const box = document.getElementById('cartList'); if(!box) return;
  let total = 0;
  box.innerHTML = cart.map(item => {
    const p = products.find(x=>x.id===item.id);
    total += p.price * item.qty;
    return `<div class="panel" style="margin-bottom:12px">
      <b>${p.name}</b><p>$${p.price} x 
      <input type="number" min="1" value="${item.qty}" style="width:80px" onchange="changeQty(${p.id}, this.value)"> 
      = $${p.price*item.qty}</p>
      <button class="btn outline" onclick="removeCart(${p.id})">Delete</button>
    </div>`;
  }).join('') || '<div class="panel">Cart is empty.</div>';
  document.getElementById('subtotal').textContent = '$' + total;
}

function changeQty(id, qty) {
  const item = cart.find(x=>x.id===id); if(item) item.qty = Number(qty);
  save(); renderCart();
}
function removeCart(id) { cart = cart.filter(x=>x.id!==id); save(); renderCart(); }

function renderWishlist() {
  const box = document.getElementById('wishlistList'); if(!box) return;
  const list = wishlist.map(id=>products.find(p=>p.id===id)).filter(Boolean);
  box.innerHTML = list.map(p => `<div class="card"><img src="${p.img}"><h3>${p.name}</h3><p>$${p.price}</p><button class="btn" onclick="addToCart(${p.id})">Move to Cart</button><button class="btn outline" onclick="removeWishlist(${p.id})">Remove</button></div>`).join('') || '<div class="panel">Wishlist is empty.</div>';
}
function removeWishlist(id) { wishlist = wishlist.filter(x=>x!==id); save(); renderWishlist(); }

document.getElementById('sortSelect')?.addEventListener('change', renderProducts);
document.getElementById('searchInput')?.addEventListener('input', renderProducts);
renderProducts(); renderCart(); renderWishlist();
