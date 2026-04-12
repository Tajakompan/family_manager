const navMenu = document.getElementById("nav_menu");
const rowMenu = document.getElementById("row_menu");
const foodOverlay = document.getElementById("add_something_view");
const productWindow = document.querySelector(".add_product_window");
const productForm = document.getElementById("add_product_form");

let rightClickedStorageId = null;
let rightClickedRowId = null;

function positionMenu(menu, event) {
  if (!menu) return;

  menu.style.display = "flex";

  const width = menu.offsetWidth;
  const height = menu.offsetHeight;

  let left = event.clientX;
  let top = event.clientY;

  if (left + width > window.innerWidth) {
    left = window.innerWidth - width - 5;
  }

  if (top + height > window.innerHeight) {
    top = window.innerHeight - height - 5;
  }

  menu.style.left = `${left}px`;
  menu.style.top = `${top}px`;
}

function closeNavMenu() {
  if (navMenu) navMenu.style.display = "none";
  
  rightClickedStorageId = null;

  document.querySelectorAll(".nav_item").forEach((item) => {
    item.classList.remove("context-active");
  });
}

function closeRowMenu() {
  if (rowMenu) rowMenu.style.display = "none";
  rightClickedRowId = null;
}

async function openEditFoodLocation(foodLocationId) {
  if (!productForm || !foodLocationId) return;

  const response = await fetch(`get_food_location.php?id=${encodeURIComponent(foodLocationId)}`, {
    credentials: "same-origin"
  });

  if (!response.ok) {
    alert("Nalaganje zapisa ni uspelo.");
    return;
  }

  const data = await response.json();

  if (!data.ok || !data.row) {
    alert("Nalaganje zapisa ni uspelo.");
    return;
  }

  const row = data.row;

  const title = document.querySelector(".add_product_window .title");
  const submitBtn = document.getElementById("add_new_product_btn");

  const storageInput = productForm.querySelector('input[name="storage_id"]');
  const foodLocationInput = productForm.querySelector('input[name="food_location_id"]');
  const existingProductInput = productForm.querySelector('input[name="product_id_existing"]');

  const productNameInput = productForm.querySelector('input[name="product_name"]');
  const productAmountInput = productForm.querySelector('input[name="product_amount"]');
  const productUnitInput = productForm.querySelector('input[name="product_unit"]');
  const productQuantityInput = productForm.querySelector('input[name="product_quantity"]');
  const productCategorySelect = productForm.querySelector('select[name="product_category"]');
  const productExpiresInput = productForm.querySelector('input[name="product_expires_on"]');
  const productStatusSelect = productForm.querySelector('select[name="product_status"]');

  productForm.action = "update_food_location.php";

  if (title) title.textContent = "Uredi zapis v zalogi:";
  if (submitBtn) submitBtn.textContent = "Shrani";

  if (foodLocationInput) foodLocationInput.value = String(row.food_location_id ?? "");
  if (storageInput) storageInput.value = String(row.storage_id ?? "");
  if (existingProductInput) existingProductInput.value = String(row.product_id ?? "");

  if (productNameInput) productNameInput.value = row.product_name ?? "";
  if (productAmountInput) productAmountInput.value = row.product_amount ?? "";
  if (productUnitInput) productUnitInput.value = row.product_unit ?? "";
  if (productQuantityInput) productQuantityInput.value = row.quantity ?? "";
  if (productCategorySelect) productCategorySelect.value = String(row.product_category_id ?? "");
  if (productExpiresInput) productExpiresInput.value = row.product_expires_on ?? "";
  if (productStatusSelect) productStatusSelect.value = row.product_status ?? "new";

  foodOverlay?.classList.add("active");
  productWindow?.classList.add("active");
}

document.addEventListener("contextmenu", (event) => {
  const target = event.target.closest ? event.target : event.target.parentElement;
  console.log("contextmenu target:", event.target);



  const navItem = target?.closest(".nav_item");
  if (navItem) {
    event.preventDefault();

    document.querySelectorAll(".nav_item").forEach((item) => {
      item.classList.remove("context-active");
    });

    navItem.classList.add("context-active");
    rightClickedStorageId = navItem.dataset.storageId || null;

    if (rightClickedStorageId) positionMenu(navMenu, event);
    return;
  }

  const row = target?.closest("#storage_table_body tr");
  if (row) {
    event.preventDefault();

    rightClickedRowId = row.dataset.rowId || null;

    if (rightClickedRowId) positionMenu(rowMenu, event);
  }
});



navMenu?.addEventListener("contextmenu", (event) => {
  event.preventDefault();
});

rowMenu?.addEventListener("contextmenu", (event) => {
  event.preventDefault();
});

navMenu?.addEventListener("click", (event) => {
  event.stopPropagation();
});

rowMenu?.addEventListener("click", (event) => {
  event.stopPropagation();
});

document.addEventListener("click", () => {
  closeNavMenu();
  closeRowMenu();
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeNavMenu();
    closeRowMenu();
  }
});

navMenu?.querySelector(".delete")?.addEventListener("click", () => {
  if (!rightClickedStorageId) return;

  const confirmed = confirm("Ali si prepričan, da želiš izbrisati to lokacijo? S tem boš iz lokacije odvrgel tudi vse izdelke.");
  if (!confirmed) return;

  fetch("delete_storage.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    credentials: "same-origin",
    body: `storage_id=${encodeURIComponent(rightClickedStorageId)}`
  }).then((response) => {
    if (response.ok) {
      location.reload();
    } else {
      alert("Brisanje lokacije ni uspelo.");
    }
  });

  closeNavMenu();
});

rowMenu?.querySelector(".edit")?.addEventListener("click", async () => {
  if (!rightClickedRowId) return;

  await openEditFoodLocation(rightClickedRowId);
  closeRowMenu();
});

rowMenu?.querySelector(".delete")?.addEventListener("click", () => {
  if (!rightClickedRowId) return;

  const confirmed = confirm("Ali si prepričan, da želiš izbrisati ta zapis?");
  if (!confirmed) return;

  fetch("delete_food_location.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    credentials: "same-origin",
    body: `id=${encodeURIComponent(rightClickedRowId)}`
  }).then((response) => {
    if (!response.ok) {
      alert("Brisanje zapisa ni uspelo.");
      return;
    }

    const chosen = document.querySelector(".nav_item.chosen_storage");

    if (chosen && typeof loadFood === "function") {
      loadFood(chosen.dataset.storageId);
    } else {
      location.reload();
    }
  });

  closeRowMenu();
});

window.addEventListener("scroll", () => {
  closeNavMenu();
  closeRowMenu();
});

window.addEventListener("resize", () => {
  closeNavMenu();
  closeRowMenu();
});

