// ============================
//  CONTEXT MENUS (FULL FILE)
// ============================

// --- NAV MENU (storage locations) ---
const nav_menu = document.getElementById("nav_menu");
let rightClickedStorageId = null;

// prikaz in pozicioniranje menija
function positionMenu(menu, e) {
  if (!menu) return;

  menu.style.display = "flex";

  const w = menu.offsetWidth;
  const h = menu.offsetHeight;

  let x = e.clientX;
  let y = e.clientY;

  if (x + w > window.innerWidth) x = window.innerWidth - w - 5;
  if (y + h > window.innerHeight) y = window.innerHeight - h - 5;

  menu.style.left = `${x}px`;
  menu.style.top = `${y}px`;
}

// zapri nav_menu + odstrani highlight
function closeNavMenu() {
  if (nav_menu) nav_menu.style.display = "none";
  rightClickedStorageId = null;
  document.querySelectorAll(".nav_item").forEach(i => i.classList.remove("context-active"));
}

// delegacija: desni klik na .nav_item (deluje tudi če se nav dinamično generira)
document.addEventListener("contextmenu", (e) => {
  const item = e.target.closest(".nav_item");
  if (!item) return; // ni nav item -> pusti drugim handlerjem

  e.preventDefault();

  // highlight
  document.querySelectorAll(".nav_item").forEach(i => i.classList.remove("context-active"));
  item.classList.add("context-active");

  rightClickedStorageId = item.dataset.storageId || null;
  if (!rightClickedStorageId) return;

  positionMenu(nav_menu, e);
});

// klik v nav meniju ne sme "bubblat" na document (ker document click zapira menije)
nav_menu?.addEventListener("click", (e) => e.stopPropagation());

// zapiranje na klik zunaj
document.addEventListener("click", () => {
  closeNavMenu();
});

// zapiranje na ESC
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeNavMenu();
});

// klik na "Izbriši lokacijo"
nav_menu?.querySelector(".delete")?.addEventListener("click", () => {
  if (!rightClickedStorageId) return;

  if (!confirm("Ali si prepričan, da želiš izbrisati to lokacijo? S tem boš iz lokacije odvrgel tudi vse izdelke.")) {
    return;
  }

  fetch("delete_storage.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `storage_id=${encodeURIComponent(rightClickedStorageId)}`
  })
    .then(() => location.reload());
});


// --- ROW MENU (table rows / food_location) ---
const row_menu = document.getElementById("row_menu");
let rightClickedRowId = null;

// zapri row menu
function closeRowMenu() {
  if (row_menu) row_menu.style.display = "none";
  rightClickedRowId = null;
}

// klik v row meniju ne sme zapret menija
row_menu?.addEventListener("click", (e) => e.stopPropagation());

// delegacija: desni klik na vrstico v tbody (tbody dobi HTML iz fetcha)
const storageTbody = document.getElementById("storage_table_body");
storageTbody?.addEventListener("contextmenu", (e) => {
  const tr = e.target.closest("tr");
  if (!tr) return;

  e.preventDefault();

  rightClickedRowId = tr.dataset.rowId || null;
  if (!rightClickedRowId) return;

  positionMenu(row_menu, e);
});

// zapiranje row menija (klik zunaj / ESC)
document.addEventListener("click", () => closeRowMenu());
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeRowMenu();
});


// ============================
//  EDIT MODAL LOGIC (as you had)
// ============================

const productForm = document.getElementById("add_product_form");
const addProductBtn = document.getElementById("add_new_product_btn");
const addProductWindowTitle = document.querySelector(".add_product_window .title");
const productWindow = document.querySelector(".add_product_window");
const overlay = document.getElementById("add_something_view");

const storageInput = document.querySelector('#add_product_form input[name="storage_id"]');
const foodLocationInput = document.querySelector('#add_product_form input[name="food_location_id"]');

const productNameInput = document.querySelector('#add_product_form input[name="product_name"]');
const productAmountInput = document.querySelector('#add_product_form input[name="product_amount"]');
const productUnitInput = document.querySelector('#add_product_form input[name="product_unit"]');
const productQuantityInput = document.querySelector('#add_product_form input[name="product_quantity"]');
const productCategorySelect = document.querySelector('#add_product_form select[name="product_category"]');
const productExpiresInput = document.querySelector('#add_product_form input[name="product_expires_on"]');
const productStatusSelect = document.querySelector('#add_product_form select[name="product_status"]');
const productExistingInput = document.querySelector('#add_product_form input[name="product_id_existing"]');


async function openEditFoodLocation(foodLocationId) {
  if (!productForm || !foodLocationId) return;

  const res = await fetch(`get_food_location.php?id=${encodeURIComponent(foodLocationId)}`, {
    credentials: "same-origin"
  });
  if (!res.ok) throw new Error("get_food_location.php HTTP " + res.status);

  const data = await res.json();
  if (!data.ok || !data.row) throw new Error(data.error || "Failed loading row");

  const row = data.row;

  productForm.setAttribute("action", "update_food_location.php");
  if (addProductWindowTitle) addProductWindowTitle.textContent = "Uredi zapis v zalogi:";
  if (addProductBtn) addProductBtn.textContent = "Shrani";

  if (foodLocationInput) foodLocationInput.value = String(row.food_location_id ?? "");
  if (storageInput) storageInput.value = String(row.storage_id ?? "");
  if (productExistingInput) productExistingInput.value = String(row.product_id ?? "");

  // predizpolni formo (EDIT)
  if (productNameInput) productNameInput.value = row.product_name ?? "";
  if (productAmountInput) productAmountInput.value = row.product_amount ?? "";
  if (productUnitInput) productUnitInput.value = row.product_unit ?? "g";      // default enota
  if (productQuantityInput) productQuantityInput.value = row.quantity ?? ""; // kvantiteta ni enota

  // select name="product_category" -> tipično row.product_category ali row.product_category_id
  if (productCategorySelect) {
  const cat = row.product_category ?? row.product_category_id ?? "";
  productCategorySelect.value = String(cat);
  }

  if (productExpiresInput) productExpiresInput.value = row.product_expires_on ?? "";
  if (productStatusSelect) productStatusSelect.value = row.product_status ?? "new";

  overlay?.classList.add("active");
  productWindow?.classList.add("active");
}


// ============================
//  ROW MENU ACTIONS (edit/delete)
// ============================

// UREDI
row_menu?.querySelector(".edit")?.addEventListener("click", async () => {
  if (!rightClickedRowId) return;

  // odpri modal (openEditFoodLocation že tudi odpre, ampak to je ok)
  overlay?.classList.add("active");
  productWindow?.classList.add("active");

  await openEditFoodLocation(rightClickedRowId);
  closeRowMenu();
});

// IZBRIŠI
row_menu?.querySelector(".delete")?.addEventListener("click", () => {
  if (!rightClickedRowId) return;

  if (!confirm("Ali si prepričana, da želiš izbrisati ta zapis?")) return;

  fetch("delete_food_location.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${encodeURIComponent(rightClickedRowId)}`
  })
    .then(() => {
      // osveži samo tabelo (če imaš loadFood)
      const chosen = document.querySelector(".nav_item.chosen_storage");
      if (chosen && typeof loadFood === "function") loadFood(chosen.dataset.storageId);
      else location.reload();
    });

  closeRowMenu();
});