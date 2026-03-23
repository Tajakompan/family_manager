// food_storage forma autofill

const fsNameInp   = document.querySelector('#add_product_form input[name="product_name"]');
const fsAmountInp = document.querySelector('#add_product_form input[name="product_amount"]');
const fsUnitInp   = document.querySelector('#add_product_form input[name="product_unit"]');
const fsCatSel    = document.querySelector('#add_product_form select[name="product_category"]');

let fsT = null;

function fsSetHint(msg, isOk = true){
  if(!fsNameInp) return;

  let el = document.getElementById("fs_product_hint");
  if(!el){
    el = document.createElement("div");
    el.id = "fs_product_hint";
    el.style.marginTop = "6px";
    fsNameInp.insertAdjacentElement("afterend", el);
  }

  el.textContent = msg;
  el.classList.toggle("warn", !isOk);
  el.style.display = msg ? "block" : "none";
}

function fsDebouncedCheck(){
  if(fsT) clearTimeout(fsT);
  fsT = setTimeout(fsCheckName, 250);
}

async function fsCheckName(){
  if(!fsNameInp) return;

  const name = fsNameInp.value.trim();
  if(name.length < 2){
    fsSetHint("");
    return;
  }

  const url = new URL("check_product.php", window.location.href);
  url.searchParams.set("name", name);

  const res = await fetch(url.toString());
  if(!res.ok){
    fsSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  const data = await res.json();
  if(!data.ok){
    fsSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  if(data.exists){

    fsSetHint("Izdelek že obstaja - podatki so bili predizpolnjeni.", true);

    // predizpolni samo prazna polja
    if (fsAmountInp && fsAmountInp.value.trim() === "")
      fsAmountInp.value = data.product.amount ?? "";

    if (fsUnitInp && fsUnitInp.value.trim() === "")
      fsUnitInp.value = data.product.unit ?? "";

    if (fsCatSel && data.product.product_category_id)
      fsCatSel.value = String(data.product.product_category_id);

    const hid = document.querySelector('input[name="product_id_existing"]');
    if(hid) hid.value = data.product.id ?? "";

  } else {
    fsSetHint("Nov izdelek - vnesi podatke.", true);
  }
}

// listenerji
fsNameInp?.addEventListener("input", fsDebouncedCheck);
fsNameInp?.addEventListener("blur", fsCheckName);


document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("add_product_form");
  if (!form) return;

  const storageInput = form.querySelector('input[name="storage_id"]');
  const nameInput = form.querySelector('input[name="product_name"]');
  const amountInput = form.querySelector('input[name="product_amount"]');
  const unitInput = form.querySelector('input[name="product_unit"]');
  const quantityInput = form.querySelector('input[name="product_quantity"]');
  const categorySelect = form.querySelector('select[name="product_category"]');
  const expiresInput = form.querySelector('input[name="product_expires_on"]');
  const statusSelect = form.querySelector('select[name="product_status"]');

  const fields = [
    nameInput,
    amountInput,
    unitInput,
    quantityInput,
    categorySelect,
    expiresInput,
    statusSelect
  ];

  function clearErrors() {
    fields.forEach(field => field?.classList.remove("red"));
  }

  function isPositiveDecimal(value) {
    const normalized = value.replace(",", ".").trim();
    if (normalized === "") return false;
    const num = Number(normalized);
    return Number.isFinite(num) && num > 0;
  }

  function isPositiveInteger(value) {
    const trimmed = value.trim();
    if (trimmed === "") return false;
    const num = Number(trimmed);
    return Number.isInteger(num) && num > 0;
  }

  form.addEventListener("submit", function (event) {
    clearErrors();

    let isValid = true;

    const storageValue = Number(storageInput?.value || 0);
    const nameValue = nameInput?.value.trim() || "";
    const amountValue = amountInput?.value || "";
    const unitValue = unitInput?.value.trim() || "";
    const quantityValue = quantityInput?.value || "";
    const categoryValue = Number(categorySelect?.value || 0);
    const expiresValue = expiresInput?.value || "";
    const statusValue = statusSelect?.value || "";

    if (storageValue <= 0) {
      isValid = false;
      alert("Najprej izberi lokacijo shranjevanja.");
    }

    if (nameValue === "") {
      nameInput?.classList.add("red");
      isValid = false;
    }

    if (!isPositiveDecimal(amountValue)) {
      amountInput?.classList.add("red");
      isValid = false;
    }

    if (unitValue === "") {
      unitInput?.classList.add("red");
      isValid = false;
    }

    if (!isPositiveInteger(quantityValue)) {
      quantityInput?.classList.add("red");
      isValid = false;
    }

    if (categoryValue <= 0) {
      categorySelect?.classList.add("red");
      isValid = false;
    }

    if (statusValue === "") {
      statusSelect?.classList.add("red");
      isValid = false;
    }

    if (!isValid) {
      event.preventDefault();
    }
  });

  fields.forEach(field => {
    if (!field) return;

    const eventName = field.tagName === "SELECT" ? "change" : "input";
    field.addEventListener(eventName, () => {
      field.classList.remove("red");
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const categoryForm = document.getElementById("add_category_form");
  if (!categoryForm) return;

  const categoryInput = categoryForm.querySelector('input[name="new_category"]');
  if (!categoryInput) return;

  categoryForm.addEventListener("submit", function (event) {
    categoryInput.classList.remove("red");

    const value = categoryInput.value.trim();

    if (value === "") {
      categoryInput.classList.add("red");
      event.preventDefault();
    }
  });

  categoryInput.addEventListener("input", function () {
    categoryInput.classList.remove("red");
  });
});



document.addEventListener("DOMContentLoaded", function () {
  const storageForm = document.getElementById("add_storage_form");
  if (!storageForm) return;

  const storageInput = storageForm.querySelector('input[name="new_storage_location"]');
  if (!storageInput) return;

  storageForm.addEventListener("submit", function (event) {
    storageInput.classList.remove("red");

    if (storageInput.value.trim() === "") {
      storageInput.classList.add("red");
      event.preventDefault();
    }
  });

  storageInput.addEventListener("input", function () {
    storageInput.classList.remove("red");
  });
});

