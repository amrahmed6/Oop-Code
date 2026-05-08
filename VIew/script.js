// ===============================
// Dark Mode
// ===============================
function toggleDark() {
  document.body.classList.toggle("dark");

  if (document.body.classList.contains("dark")) {
    localStorage.setItem("dark", "true");
  } else {
    localStorage.setItem("dark", "false");
  }
}

if (localStorage.getItem("dark") === "true") {
  document.body.classList.add("dark");
}

// ===============================
// Simple Search
// ===============================
function setupSearch(inputId) {
  const input = document.getElementById(inputId);

  if (!input) return;

  input.addEventListener("input", function () {
    const value = input.value.toLowerCase();

    const cards = document.querySelectorAll(".card");
    const rows = document.querySelectorAll("table tr");

    if (cards.length > 0) {
      cards.forEach(function (card) {
        const text = card.textContent.toLowerCase();

        if (text.includes(value)) {
          card.style.display = "";
        } else {
          card.style.display = "none";
        }
      });
    }

    if (rows.length > 0) {
      rows.forEach(function (row, index) {
        if (index === 0) return;

        const text = row.textContent.toLowerCase();

        if (text.includes(value)) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    }
  });
}

// ===============================
// Confirm Delete Buttons
// ===============================
function setupDeleteConfirm() {
  const forms = document.querySelectorAll("form");

  forms.forEach(function (form) {
    form.addEventListener("submit", function (e) {
      const actionInput = form.querySelector("input[name='action']");

      if (!actionInput) return;

      const action = actionInput.value;

      if (
        action === "delete_product" ||
        action === "delete_user" ||
        action === "delete_coupon" ||
        action === "remove_cart_item" ||
        action === "remove_wishlist" ||
        action === "cancel_order"
      ) {
        const ok = confirm("Are you sure?");

        if (!ok) {
          e.preventDefault();
        }
      }
    });
  });
}

// ===============================
// Payment Fields
// ===============================
function setupPaymentFields() {
  const paymentRadios = document.querySelectorAll("input[name='payment_method']");
  const cardNumber = document.querySelector("input[name='card_number']");
  const expiryDate = document.querySelector("input[name='expiry_date']");
  const cvv = document.querySelector("input[name='cvv']");

  if (!paymentRadios.length || !cardNumber || !expiryDate || !cvv) return;

  function toggleCardFields() {
    const selected = document.querySelector("input[name='payment_method']:checked");

    if (!selected) return;

    if (selected.value === "Visa") {
      cardNumber.style.display = "";
      expiryDate.style.display = "";
      cvv.style.display = "";

      cardNumber.required = true;
      expiryDate.required = true;
      cvv.required = true;
    } else {
      cardNumber.style.display = "none";
      expiryDate.style.display = "none";
      cvv.style.display = "none";

      cardNumber.required = false;
      expiryDate.required = false;
      cvv.required = false;
    }
  }

  paymentRadios.forEach(function (radio) {
    radio.addEventListener("change", toggleCardFields);
  });

  toggleCardFields();
}

// ===============================
// Run
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  setupSearch("searchInput");
  setupSearch("productSearch");
  setupSearch("userSearch");

  setupDeleteConfirm();
  setupPaymentFields();
});