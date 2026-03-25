// polnjenje pinov s seznami
async function loadShoppingPins() {
  const container = document.getElementById("pins_container");
  const template = document.getElementById("shopping_pin_template");
  

  container.innerHTML = "";

  //dobi trgovine
  const res = await fetch("get_shops.php");
  if (!res.ok) throw new Error("get_shops.php HTTP " + res.status);
  const shops = await res.json();
  //če ni nobene
  if (shops.length === 0) {
    container.innerHTML = "<p>Ni dodanih trgovin.</p>";
    return;
  }

  //za vsako trgovino nardi pin
  for (const shop of shops) {
    const clone = template.content.cloneNode(true);

    const pin = clone.querySelector(".pin");
    if (pin) pin.dataset.shopId = shop.id;

    const shopNameEl = clone.querySelector(".shop_name");
    const tbody = clone.querySelector(".shopping_list_table_body");
    const addBtn = clone.querySelector(".add_btn");

    shopNameEl.textContent = shop.name;
    shopNameEl.dataset.shopId = shop.id;
    tbody.id = `shop_${shop.id}_tbody`;

    addBtn.addEventListener("click", (e) => {
      e.preventDefault();

      showWindow("add_product_window");
      document.getElementById("add_something_view")?.classList.add("active");

      const form = document.getElementById("add_product_form");
      if (!form) return;

      form.reset();

      const hidden = form.querySelector("input[name='shop_id']");
      if (!hidden) return;

      hidden.value = String(shop.id);
      console.log("hidden shop set to:", hidden.value);
    });

    container.appendChild(clone);
    loadShoppingListForShop(shop.id);
  }
}

function loadShoppingListForShop(shop_id) {
  fetch(`get_shopping_list.php?shop_id=${shop_id}`)
    .then(r => r.ok ? r.text() : Promise.reject("HTTP " + r.status))
    .then(html => {
      const tbody = document.getElementById(`shop_${shop_id}_tbody`);
      if (tbody) {
        tbody.innerHTML = html || `<tr><td colspan="4" style="text-align: center; border: none; height: 200px">Prazen seznam</td></tr>`;
      }
    })
    .catch(err => {
      console.error("Napaka pri shop_id", shop_id, err);
      const tbody = document.getElementById(`shop_${shop_id}_tbody`);
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="4">Napaka pri nalaganju.</td></tr>`;
      }
    });
}

document.addEventListener("change", async (e) => {
  if (!e.target.classList.contains("check-item")) return;

  const cb = e.target;
  const id = cb.dataset.id;
  const purchased = cb.checked ? 1 : 0;

  const res = await fetch("update_item_purchased.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `id=${id}&purchased=${purchased}`
  });

  const row = cb.closest("tr");
  row.classList.toggle("done", purchased === 1);

  const tbody = row.parentElement;
  if (purchased === 1) {
    tbody.appendChild(row);
  } else {
    tbody.prepend(row);
  }
  if (typeof loadStorePins === "function") {
    loadStorePins().catch(console.error);
  }
});

const qtyTimers = new Map();

document.addEventListener("input", (e) => {
  if (!e.target.classList.contains("qty-input")) return;

  const inp = e.target;
  const id = inp.dataset.id;
  const val = parseInt(inp.value, 10);

  if (!id) return;

  // basic clamp
  if (Number.isNaN(val) || val < 1) return;

  // debounce na isti id
  if (qtyTimers.has(id)) clearTimeout(qtyTimers.get(id));

  qtyTimers.set(id, setTimeout(async () => {
    try {
      const res = await fetch("update_quantity.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `id=${encodeURIComponent(id)}&quantity=${encodeURIComponent(val)}`
      });

      const text = await res.text();
      if (!res.ok) throw new Error(`HTTP ${res.status}: ${text}`);

      let data;
      try { data = JSON.parse(text); } catch { throw new Error("Invalid JSON: " + text); }
      if (!data.ok) throw new Error(data.error || "Update failed");

      // optional: mali “saved” feedback
      inp.classList.add("saved");
      setTimeout(() => inp.classList.remove("saved"), 300);
    } catch (err) {
      console.error(err);
      inp.classList.add("save-error");
      setTimeout(() => inp.classList.remove("save-error"), 800);
    }
  }, 350));
});


window.addEventListener("DOMContentLoaded", () => {
  loadShoppingPins().catch(console.error);
});
