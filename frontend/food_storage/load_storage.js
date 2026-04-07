function updateStorageIdInUrl() {
  const chosen = document.querySelector(".chosen_storage");
  if (!chosen) return;

  const storageId = chosen.dataset.storageId;
  const url = new URL(window.location.href);
  url.searchParams.set("storage_id", storageId);
  window.history.replaceState({}, "", url.toString());
}

async function fetchJson(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    ...options
  });

  if (!response.ok) {
    throw new Error(url + " HTTP " + response.status);
  }

  return await response.json();
}

function createOnRightGroup(storageName, productNames) {
  const group = document.createElement("li");
  group.className = "eat_soon_group";

  const title = document.createElement("div");
  title.className = "eat_soon_group_title";
  title.textContent = storageName;

  const list = document.createElement("ul");
  list.className = "eat_soon_items";

  productNames.forEach((name) => {
    const item = document.createElement("li");
    item.textContent = name;
    list.appendChild(item);
  });

  group.appendChild(title);
  group.appendChild(list);

  return group;
}

async function loadExpirePanel(listId, emptyId, expiredValue) {
  const listEl = document.getElementById(listId);
  const emptyEl = document.getElementById(emptyId);

  if (!listEl || !emptyEl) return;

  listEl.innerHTML = "";
  emptyEl.hidden = true;

  const storages = await fetchJson("get_storage_locations.php");
  let hasItems = false;

  for (const storage of storages) {
    const products = await fetchJson(
      `get_expiring_storage.php?storage_id=${storage.id}&expired=${expiredValue}`
    );

    if (!Array.isArray(products) || products.length === 0) {
      continue;
    }

    hasItems = true;
    listEl.appendChild(
      createOnRightGroup(storage.name, products.map((product) => product.name))
    );
  }

  if (!hasItems) {
    emptyEl.hidden = false;
  }
}

function switchChosenStorage(id) {
  document.querySelectorAll(".chosen_storage").forEach((item) => {
    item.classList.remove("chosen_storage");
  });

  const chosen = document.getElementById(id);
  if (!chosen) return;

  chosen.classList.add("chosen_storage");

  const storageInput = document.getElementById("storage_id_input");
  if (storageInput) {
    storageInput.value = chosen.dataset.storageId;
  }

  updateStorageIdInUrl();
}

function loadFood(storageId) {
  fetch(`get_right_storage.php?storage_id=${storageId}`, {
    credentials: "same-origin"
  })
    .then((response) => response.text())
    .then((html) => {
      const tbody = document.getElementById("storage_table_body");
      const content = document.querySelector(".content");
      const emptyText = document.getElementById("storage_empty_text");

      if (!tbody || !content || !emptyText) return;

      tbody.innerHTML = html;

      const hasRows = tbody.querySelector("tr") !== null;
      content.classList.toggle("is-empty", !hasRows);
      emptyText.hidden = hasRows;

      colorExpireDates(5);
    })
    .catch((error) => {
      console.error("loadFood failed:", error);
    });
}

function colorExpireDates(colIndex) {
  const tbody = document.getElementById("storage_table_body");
  if (!tbody) return;

  const now = new Date();
  now.setHours(0, 0, 0, 0);

  tbody.querySelectorAll("tr").forEach((row) => {
    const cell = row.children[colIndex];
    if (!cell) return;

    const text = cell.innerText.trim();
    const time = parseDate(text);
    if (time === null) return;

    const diffDays = Math.ceil((time - now.getTime()) / (1000 * 60 * 60 * 24));

    cell.classList.remove("expired", "expiring-soon", "expiring-warning", "fresh");

    if (diffDays < 0) {
      cell.classList.add("expired");
    } else if (diffDays <= 3) {
      cell.classList.add("expiring-soon");
    } else if (diffDays <= 7) {
      cell.classList.add("expiring-warning");
    } else {
      cell.classList.add("fresh");
    }
  });
}

document.querySelectorAll(".nav_item").forEach((button) => {
  button.addEventListener("click", () => {
    switchChosenStorage(button.id);
    loadFood(button.dataset.storageId);
  });
});

window.addEventListener("DOMContentLoaded", async () => {
  const chosen = document.querySelector(".nav_item.chosen_storage");
  const storageInput = document.getElementById("storage_id_input");

  loadExpirePanel("eat_soon_list", "eat_soon_empty", 0).catch(console.error);
  loadExpirePanel("expired_list", "expired_empty", 1).catch(console.error);

  if (!chosen) return;

  if (storageInput) {
    storageInput.value = chosen.dataset.storageId;
  }

  updateStorageIdInUrl();
  loadFood(chosen.dataset.storageId);
});
