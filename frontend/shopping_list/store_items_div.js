function formatAmount(value) {
  return Number(value).toFixed(2).replace(/\.?0+$/, "");
}

function fillStorageSelect(select, storages, button) {
  select.innerHTML = "";

  if (!Array.isArray(storages) || storages.length === 0) {
    const option = document.createElement("option");
    option.value = "";
    option.textContent = "Ni lokacij";

    select.appendChild(option);
    select.disabled = true;
    button.disabled = true;
    return;
  }

  const defaultOption = document.createElement("option");
  defaultOption.value = "0";
  defaultOption.textContent = "-Izberi lokacijo-";
  select.appendChild(defaultOption);

  for (let i = 0; i < storages.length; i++) {
    const option = document.createElement("option");
    option.value = storages[i].id;
    option.textContent = storages[i].name;
    select.appendChild(option);
  }

  select.disabled = false;
  button.disabled = false;
}

async function loadStorePins() {
  const pins = document.querySelector("#scheduling_container .item_pins");
  const template = document.getElementById("store_item_template");

  if (!pins || !template) return;

  pins.innerHTML = "";

  const responses = await Promise.all([
    fetch("get_storages.php"),
    fetch("get_items_for_store.php")
  ]);

  const storagesResponse = responses[0];
  const itemsResponse = responses[1];

  if (!storagesResponse.ok) {
    throw new Error("get_storages.php HTTP " + storagesResponse.status);
  }

  if (!itemsResponse.ok) {
    throw new Error("get_items_for_store.php HTTP " + itemsResponse.status);
  }

  const storages = await storagesResponse.json();
  const items = await itemsResponse.json();

  if (!Array.isArray(items) || items.length === 0) {
    const container = document.getElementById("scheduling_container");
    if (container) container.classList.add("is-empty");
    pins.innerHTML = '<div class="empty_list_warning">Prazno.</div>';
    return;
  }

  const container = document.getElementById("scheduling_container");
  if (container) container.classList.remove("is-empty");

  for (let i = 0; i < items.length; i++) {
    const item = items[i];
    const clone = template.content.cloneNode(true);

    const productName = clone.querySelector(".pname");
    const productAmount = clone.querySelector(".pamount");
    const productUnit = clone.querySelector(".punit");
    const productQuantity = clone.querySelector(".pqty");
    const storageSelect = clone.querySelector(".storage_id");
    const button = clone.querySelector(".store_btn");

    if (productName) productName.textContent = item.name;
    if (productAmount) productAmount.textContent = formatAmount(item.amount);
    if (productUnit) productUnit.textContent = item.unit;
    if (productQuantity) productQuantity.textContent = item.quantity + "x";
    if (storageSelect && button) fillStorageSelect(storageSelect, storages, button);
    if (button) button.dataset.shoppingListId = item.id;

    pins.appendChild(clone);
  }
}

document.addEventListener("click", async function (e) {
  const button = e.target.closest(".store_btn");
  if (!button) return;
  
  e.preventDefault();

  const pin = button.closest(".store_pin");
  if (!pin || button.dataset.busy === "1") return;
  
  const listId = button.dataset.shoppingListId;
  const storageId = pin.querySelector(".storage_id")?.value;
  const expiresOn = pin.querySelector(".expires_on")?.value || "";

  if (!listId || Number(listId) <= 0) {
    alert("Manjka id artikla!");
    return;
  }

  if (!storageId || Number(storageId) <= 0) {
    alert("Izberi lokacijo!");
    return;
  }

  button.dataset.busy = "1";
  button.disabled = true;

  try {
    const body = new URLSearchParams();
    body.set("id", String(listId));
    body.set("storage_id", String(storageId));
    body.set("expires_on", expiresOn);

    const response = await fetch("store_item.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: body.toString()
    });

    const text = await response.text();

    if (!response.ok) {
        alert("NAPAKA!\nStatus: " + response.status + "\nOdgovor: " + text);
        return;
    }

    pin.remove();
    
    const pinsContainer = document.querySelector("#scheduling_container .item_pins");
    const schedulingContainer = document.getElementById("scheduling_container");

    if (pinsContainer && !pinsContainer.querySelector(".store_pin")) {
      if (schedulingContainer) schedulingContainer.classList.add("is-empty");
      pinsContainer.innerHTML = '<div class="empty_list_warning">Prazno.</div>';
    }

  } 
  catch (error) {
    alert("NETWORK ERROR: " + error.message);
  } 
  finally {
    button.dataset.busy = "0";
    button.disabled = false;
  }
});

window.addEventListener("DOMContentLoaded", function () {
  loadStorePins().catch(console.error);
});
