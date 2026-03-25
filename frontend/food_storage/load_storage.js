function update_storage_id_in_url() {
  const chosen = document.querySelector(".chosen_storage");
  const storageId = chosen.dataset.storageId;
  const url = new URL(window.location.href);
  url.searchParams.set("storage_id", storageId);
  window.history.replaceState({}, "", url.toString());
}

async function fetchJson(url, options = {}) {
  const res = await fetch(url, { credentials: "same-origin", ...options });
  if (!res.ok) throw new Error(url + " HTTP " + res.status);
  return await res.json();
}

function createOnRightGroup(storageName, productNames) {
  const group = document.createElement("li");
  group.className = "eat_soon_group";

  const title = document.createElement("div");
  title.className = "eat_soon_group_title";
  title.textContent = storageName;

  const list = document.createElement("ul");
  list.className = "eat_soon_items";

  for (const name of productNames) {
    const item = document.createElement("li");
    item.textContent = name;
    list.appendChild(item);
  }

  group.appendChild(title);
  group.appendChild(list);
  return group;
}

async function loadEatSoonPanel() {
  const listEl = document.getElementById("eat_soon_list");
  const emptyEl = document.getElementById("eat_soon_empty");
  if (!listEl || !emptyEl) return;

  listEl.innerHTML = "";
  emptyEl.hidden = true;

  const storages = await fetchJson("get_storage_locations.php");
  let hasItems = false;

  for (const storage of storages) {
    const products = await fetchJson(`get_expiring_storage.php?storage_id=${storage.id}&expired=${0}`);
    if (!products.length) continue;

    hasItems = true;
    listEl.appendChild(createOnRightGroup(storage.name, products.map(product => product.name)));
  }

  if (!hasItems) {
    emptyEl.hidden = false;
  }
}

async function loadExpiredPanel() {
  const listEl = document.getElementById("expired_list");
  const emptyEl = document.getElementById("expired_empty");
  if (!listEl || !emptyEl) return;

  listEl.innerHTML = "";
  emptyEl.hidden = true;

  const storages = await fetchJson("get_storage_locations.php");
  let hasItems = false;

  for (const storage of storages) {
    const products = await fetchJson(`get_expiring_storage.php?storage_id=${storage.id}&expired=${1}`);
    if (!products.length) continue;

    hasItems = true;
    listEl.appendChild(createOnRightGroup(storage.name, products.map(product => product.name)));
  }

  if (!hasItems) {
    emptyEl.hidden = false;
  }
}

function switch_chosen_storage(id){
    document.querySelectorAll('.chosen_storage').forEach(v => v.classList.remove('chosen_storage'));
    document.getElementById(id).classList.add('chosen_storage');
    document.getElementById('storage_id_input').value = document.getElementById(id).dataset.storageId;
    update_storage_id_in_url();
}

function loadFood(storageId) {
  fetch(`get_right_storage.php?storage_id=${storageId}`)
    .then(r => r.text())
    .then(html => {
      const tbody = document.getElementById('storage_table_body');
      const content = document.querySelector('.content');
      const emptyText = document.getElementById('storage_empty_text');

      tbody.innerHTML = html;
      const hasRows = tbody.querySelector('tr') !== null;

      content.classList.toggle('is-empty', !hasRows);
      if (emptyText) emptyText.hidden = hasRows;

      colorExpireDates(5); 
    });
}


document.querySelectorAll('.nav_item').forEach(btn => {
    btn.addEventListener('click', () => {
        switch_chosen_storage(btn.id);
        const storageId = Number(btn.dataset.storageId);
        loadFood(storageId);
    })
})

window.addEventListener('DOMContentLoaded', () => {
  const chosen = document.querySelector('.nav_item.chosen_storage');
  loadEatSoonPanel().catch(console.error);
  loadExpiredPanel().catch(console.error);
  if (!chosen) return;
  const storageId = chosen.dataset.storageId;
  document.getElementById('storage_id_input').value = document.querySelector('.nav_item.chosen_storage').dataset.storageId;
  update_storage_id_in_url();
  loadFood(storageId);
});

function colorExpireDates(colIndex) {
  const tbody = document.querySelector("#storage_table_body");
  if (!tbody) return;

  const now = new Date();
  now.setHours(0, 0, 0, 0); // danes ob polnoči

  tbody.querySelectorAll("tr").forEach(row => {
    const cell = row.children[colIndex];
    if (!cell) return;

    const txt = cell.innerText.trim();
    const time = parseDate(txt);
    if (time === null) return;

    const diffDays = Math.ceil((time - now.getTime()) / (1000 * 60 * 60 * 24));

    cell.classList.remove(
      "expired",
      "expiring-soon",
      "expiring-warning",
      "fresh"
    );

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


