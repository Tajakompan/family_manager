const newStorageBtn = document.getElementById("add_storage_location");
const newProductBtn = document.getElementById("add_product");
const newCategoryBtn = document.getElementById("add_category");

const cancelNewStorageBtn = document.getElementById("cancel_new_storage_btn");
const cancelNewProductBtn = document.getElementById("cancel_new_product_btn");
const cancelCategoryBtn = document.getElementById("cancel_category_btn");

const overlay = document.getElementById("add_something_view");

const addStorageWindow = document.querySelector(".add_storage_location_window");
const addProductWindow = document.querySelector(".add_product_window");
const addCategoryWindow = document.querySelector(".add_category_window");

const addStorageForm = document.getElementById("add_storage_form");
const addProductForm = document.getElementById("add_product_form");
const addCategoryForm = document.getElementById("add_category_form");

function clearRedFields(form) {
  if (!form) return;

  form.querySelectorAll(".red").forEach((field) => {
    field.classList.remove("red");
  });
}

function clearProductHint() {
  const hint = document.getElementById("fs_product_hint");
  if (!hint) return;

  hint.textContent = "";
  hint.style.display = "none";
  hint.classList.remove("warn");
}

function closeAllWindows() {
  overlay?.classList.remove("active");
  addStorageWindow?.classList.remove("active");
  addProductWindow?.classList.remove("active");
  addCategoryWindow?.classList.remove("active");
}

function openWindow(windowEl) {
  closeAllWindows();
  overlay?.classList.add("active");
  windowEl?.classList.add("active");
}

function getChosenStorageId() {
  const chosen = document.querySelector(".nav_item.chosen_storage");
  return chosen?.dataset.storageId || "";
}

function switchToAddMode() {
  if (!addProductForm) return;

  const title = document.querySelector(".add_product_window .title");
  const submitBtn = document.getElementById("add_new_product_btn");
  const foodLocationInput = addProductForm.querySelector('input[name="food_location_id"]');
  const storageInput = addProductForm.querySelector('input[name="storage_id"]');
  const existingProductInput = addProductForm.querySelector('input[name="product_id_existing"]');

  addProductForm.action = "add_product_in_db.php";

  if (title) title.textContent = "Dodaj nov izdelek v zalogo:";
  if (submitBtn) submitBtn.textContent = "Dodaj";
  if (foodLocationInput) foodLocationInput.value = "";
  if (storageInput) storageInput.value = getChosenStorageId();
  if (existingProductInput) existingProductInput.value = "";

  clearProductHint();
}

window.switchToAddMode = switchToAddMode;

newStorageBtn?.addEventListener("click", () => {
  clearRedFields(addStorageForm);
  openWindow(addStorageWindow);
});

newProductBtn?.addEventListener("click", () => {
  clearRedFields(addProductForm);
  switchToAddMode();
  openWindow(addProductWindow);
});

newCategoryBtn?.addEventListener("click", () => {
  clearRedFields(addCategoryForm);
  openWindow(addCategoryWindow);
});

cancelNewStorageBtn?.addEventListener("click", () => {
  clearRedFields(addStorageForm);
  closeAllWindows();
});

cancelNewProductBtn?.addEventListener("click", () => {
  clearRedFields(addProductForm);
  switchToAddMode();
  closeAllWindows();
});

cancelCategoryBtn?.addEventListener("click", () => {
  clearRedFields(addCategoryForm);
  closeAllWindows();
});

overlay?.addEventListener("click", (e) => {
  if (e.target === overlay) {
    closeAllWindows();
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeAllWindows();
  }
});
