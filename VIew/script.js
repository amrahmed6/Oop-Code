// ===============================
// Dark Mode
// ===============================
function toggleDark() {
  document.body.classList.toggle("dark");
  localStorage.setItem("dark", document.body.classList.contains("dark") ? "true" : "false");
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
    const value = input.value.trim().toLowerCase();

    document.querySelectorAll(".card").forEach(function (card) {
      const text = card.textContent.toLowerCase();
      card.style.display = text.includes(value) ? "" : "none";
    });

    document.querySelectorAll("table tr").forEach(function (row) {
      // Never hide table header rows.
      if (row.querySelector("th")) return;

      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(value) ? "" : "none";
    });
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

      const dangerousActions = [
        "delete_product",
        "delete_user",
        "delete_coupon",
        "remove_cart_item",
        "remove_wishlist",
        "cancel_order"
      ];

      if (dangerousActions.includes(actionInput.value)) {
        const ok = confirm("Are you sure?");
        if (!ok) e.preventDefault();
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
    const showCardFields = selected && selected.value === "Visa";

    [cardNumber, expiryDate, cvv].forEach(function (field) {
      field.style.display = showCardFields ? "" : "none";
      field.required = !!showCardFields;
    });
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
