function formatAmount(value) {
  return Number(value)
    .toFixed(2)        // 2 decimalki
    .replace(/\.?0+$/, ""); // odstrani .00 ali odvečne 0
}

async function loadStorePins() {
  const pins = document.querySelector("#scheduling_container .item_pins");
  const tpl = document.getElementById("store_item_template");
  if (!pins || !tpl) return;

  pins.innerHTML = "";

  // fetch obeh jsonov
  const [storRes, itemsRes] = await Promise.all([
    fetch("get_storages.php"),
    fetch("get_items_for_store.php")
  ]);

  if (!storRes.ok) throw new Error("get_storages.php HTTP " + storRes.status);
  if (!itemsRes.ok) throw new Error("get_items_for_store.php HTTP " + itemsRes.status);

  /** storages: [{id, name}, ...] */
  const storages = await storRes.json();

  /** items: [{id, product_id, quantity, name, amount, unit}, ...] */
  const items = await itemsRes.json();

  if (!items || items.length === 0) {
    pins.innerHTML = `<div class="empty_list_warning">Prazno.</p>`;
    return;
  }

  for (const it of items) {
    const clone = tpl.content.cloneNode(true);

    const pnameEl = clone.querySelector(".pname");
    const pamount = clone.querySelector(".pamount");
    const punit = clone.querySelector(".punit");
    const pqty = clone.querySelector(".pqty");
    const expiresEl = clone.querySelector(".expires_on");
    const storageSel = clone.querySelector(".storage_id");
    const btn = clone.querySelector(".store_btn");

    // ime izdelka
    pnameEl.textContent = it.name;
    pamount.textContent = formatAmount(it.amount);
    punit.textContent = it.unit;
    pqty.textContent = it.quantity + "x";

    // napolni select lokacij
    storageSel.innerHTML = "";
    if (!storages || storages.length === 0) {
      const opt = document.createElement("option");
      opt.value = "";
      opt.textContent = "Ni lokacij";
      storageSel.appendChild(opt);
      storageSel.disabled = true;
      btn.disabled = true;
    } else {
        const opt = document.createElement("option");
        opt.value = "0";
        opt.textContent = "-Izberi lokacijo-";
        storageSel.appendChild(opt);
      for (const s of storages) {
        const opt = document.createElement("option");
        opt.value = s.id;
        opt.textContent = s.name;
        storageSel.appendChild(opt);
      }
    }

    // shrani id shopping_list zapisa (za kasnejši store_item.php)
    btn.dataset.shoppingListId = it.id;
    // če rabiš tudi product_id:
    btn.dataset.productId = it.product_id;

    pins.appendChild(clone);
  }
}
document.addEventListener("click", async (e) => {
  const btn = e.target.closest(".store_btn");
  if (!btn) return;

  e.preventDefault();

  const pin = btn.closest(".store_pin");
  if (!pin || btn.dataset.busy === "1") return;

  const listId = btn.dataset.shoppingListId;         // id iz shopping_list
  const storageId = pin.querySelector(".storage_id")?.value;
  const expiresOn = pin.querySelector(".expires_on")?.value || "";

  if (!listId || Number(listId) <= 0) return alert("Manjka id artikla!");
  if (!storageId || Number(storageId) <= 0) return alert("Izberi lokacijo!");

  btn.dataset.busy = "1";
  btn.disabled = true;

  try {
    const body = new URLSearchParams();
    body.set("id", String(listId));
    body.set("storage_id", String(storageId));
    body.set("expires_on", expiresOn); // lahko prazen

    const res = await fetch("store_item.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: body.toString()
    });

    const text = await res.text();

    // ZA DEBUG: vedno pokaži odgovor (kasneje lahko odstraniš)
    if (!res.ok) {
      alert("NAPAKA!\nStatus: " + res.status + "\nOdgovor: " + text);
      return;
    }

    // če je OK, odstrani pin
    pin.remove();

  } catch (err) {
    alert("NETWORK ERROR: " + err.message);
  } finally {
    btn.dataset.busy = "0";
    btn.disabled = false;
  }
});





window.addEventListener("DOMContentLoaded", () => {
  loadStorePins().catch(console.error);
});
