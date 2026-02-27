function update_storage_id_in_url() {
  const chosen = document.querySelector(".chosen_storage");
  const storageId = chosen.dataset.storageId;
  const url = new URL(window.location.href);
  url.searchParams.set("storage_id", storageId);
  window.history.replaceState({}, "", url.toString());
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
      document.getElementById('storage_table_body').innerHTML = html;

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


