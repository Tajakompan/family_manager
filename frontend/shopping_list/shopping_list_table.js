function openProductWindowForShop(shopId) {
  const addSomethingView = document.getElementById("add_something_view");
  const form = document.getElementById("add_product_form");
  const shopIdInput = document.getElementById("product_shop_id");
  const productError = document.getElementById("add_product_error");

  if (!form || !shopIdInput) return;
  
  form.reset();
  shopIdInput.value = String(shopId);

  if (productError) {
    productError.textContent = "";
    productError.hidden = true;
  }

  showWindow("add_product_window");

  if (addSomethingView) {
    addSomethingView.classList.add("active");
  }
}

async function loadShoppingPins() {
  const container = document.getElementById("pins_container");
  const template = document.getElementById("shopping_pin_template");

  if (!container || !template) return;

  container.innerHTML = "";
  const response = await fetch("get_shops.php");

  if (!response.ok) {
    throw new Error("get_shops.php HTTP " + response.status);
  }

  const shops = await response.json();

  if (!Array.isArray(shops) || shops.length === 0) {
    container.innerHTML = "<p>Ni dodanih trgovin.</p>";
    return;
  }

  for (let i = 0; i < shops.length; i++) {
    const shop = shops[i];
    const clone = template.content.cloneNode(true);

    const pin = clone.querySelector(".pin");
    const shopNameElement = clone.querySelector(".shop_name");
    const tbody = clone.querySelector(".shopping_list_table_body");
    const addButton = clone.querySelector(".add_btn");

    if (pin) pin.dataset.shopId = shop.id;
    if (shopNameElement) {
      shopNameElement.textContent = shop.name;
      shopNameElement.dataset.shopId = shop.id;
    }
    if (tbody) tbody.id = "shop_" + shop.id + "_tbody";
    if (addButton) {
      addButton.addEventListener("click", function (e) {
        e.preventDefault();
        openProductWindowForShop(shop.id);
      });
    }
    container.appendChild(clone);
    loadShoppingListForShop(shop.id);
  }
}

function loadShoppingListForShop(shopId) {
  fetch("get_shopping_list.php?shop_id=" + encodeURIComponent(shopId))
    .then(function (response) {
        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }
        return response.text();
    })
    .then(function (html) {
        const tbody = document.getElementById("shop_" + shopId + "_tbody");

        if (!tbody) return;
        if (html) tbody.innerHTML = html;
        else tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; border: none; height: 60px">Prazen seznam</td></tr>';
    })
    .catch(function (error) {
        console.error("Napaka pri shop_id", shopId, error);
        const tbody = document.getElementById("shop_" + shopId + "_tbody");
        if (tbody) tbody.innerHTML = '<tr><td colspan="4">Napaka pri nalaganju.</td></tr>';
    });
}

document.addEventListener("change", async function (e) {
  if (!e.target.classList.contains("check-item")) return;
  
  const checkbox = e.target;
  const id = checkbox.dataset.id;
  const purchased = checkbox.checked ? 1 : 0;
  const row = checkbox.closest("tr");

  if (!id || !row) return;
  
  await fetch("update_item_purchased.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "id=" + encodeURIComponent(id) + "&purchased=" + encodeURIComponent(purchased)
  });

  row.classList.toggle("done", purchased === 1);

  const tbody = row.parentElement;

  if (!tbody) return;

  if (purchased === 1) tbody.appendChild(row);
  else tbody.prepend(row);

  if (typeof loadStorePins === "function") loadStorePins().catch(console.error);
});

const qtyTimers = new Map();

document.addEventListener("input", function (e) {
  if (!e.target.classList.contains("qty-input")) return;
  
  const input = e.target;
  const id = input.dataset.id;

  if (!id) return;
  
  if (qtyTimers.has(id)) {
      clearTimeout(qtyTimers.get(id));
  }

  qtyTimers.set(
    id,
    setTimeout(async function () {
      const quantity = parseInt(input.value, 10);

      if (Number.isNaN(quantity) || quantity < 1) return;

      try {
        const response = await fetch("update_quantity.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body:
              "id=" +
              encodeURIComponent(id) +
              "&quantity=" +
              encodeURIComponent(quantity)
        });

        const text = await response.text();

        if (!response.ok) {
            throw new Error("HTTP " + response.status + ": " + text);
        }

        const data = JSON.parse(text);

        if (!data.ok) {
            throw new Error(data.error || "Update failed");
        }

        input.classList.add("saved");

        setTimeout(function () {
            input.classList.remove("saved");
        }, 300);
      } 
      catch (error) {
        console.error(error);
        input.classList.add("save-error");

        setTimeout(function () {
            input.classList.remove("save-error");
        }, 800);
      }
    }, 350));
});

window.addEventListener("DOMContentLoaded", function () {
  loadShoppingPins().catch(console.error);
});
