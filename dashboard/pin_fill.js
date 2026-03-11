async function fetchJson(url, options = {}) {
  const res = await fetch(url, { credentials: "same-origin", ...options });
  if (!res.ok) throw new Error(url + " HTTP " + res.status);
  return await res.json();
}

function localDateKey() {
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, "0");
  const d = String(now.getDate()).padStart(2, "0");
  return `${y}-${m}-${d}`;
}

function setEmpty(listEl) {
  const miniMain = listEl.parentElement;
  miniMain.classList.add("is-empty");
  miniMain.innerHTML = "<div class='empty_text'>Prazno</div>";
}

function createSimpleItem(text) {
  const li = document.createElement("li");
  li.className = "dashboard-item";
  li.textContent = text;
  return li;
}

function createGroupItem(title, items) {
  const group = document.createElement("li");
  group.className = "dashboard-group";

  const heading = document.createElement("div");
  heading.className = "dashboard-group-title";
  heading.textContent = title;

  const nested = document.createElement("ul");
  nested.className = "dashboard-sublist";

  for (const itemName of items) {
    const li = document.createElement("li");
    li.className = "dashboard-subitem";
    li.textContent = itemName;
    nested.appendChild(li);
  }

  group.appendChild(heading);
  group.appendChild(nested);
  return group;
}

function capitalizeFirst(text) {
  if (!text) return text;
  return text.charAt(0).toUpperCase() + text.slice(1);
}

function setupDashboardClock() {
  const dayTimePin = document.getElementById("day_time");
  const layout = document.getElementById("clock_layout");
  const hourHand = document.getElementById("clock_hour_hand");
  const minuteHand = document.getElementById("clock_minute_hand");
  const secondHand = document.getElementById("clock_second_hand");
  const timeEl = document.getElementById("time");
  const dayNameEl = document.getElementById("day_name");
  const dateEl = document.getElementById("date");

  if (!dayTimePin || !layout || !hourHand || !minuteHand || !secondHand || !timeEl || !dayNameEl || !dateEl) {
    return;
  }

  const updateLayoutMode = () => {
    layout.classList.toggle("is-column", dayTimePin.clientWidth < 320);
  };

  const updateClock = () => {
    const now = new Date();

    const hours = (now.getHours() % 12) + now.getMinutes() / 60;
    const minutes = now.getMinutes() + now.getSeconds() / 60;
    const seconds = now.getSeconds() + now.getMilliseconds() / 1000;

    hourHand.style.transform = `translateX(-50%) rotate(${hours * 30}deg)`;
    minuteHand.style.transform = `translateX(-50%) rotate(${minutes * 6}deg)`;
    secondHand.style.transform = `translateX(-50%) rotate(${seconds * 6}deg)`;

    timeEl.textContent = now.toLocaleTimeString("sl-SI", {
      hour: "2-digit",
      minute: "2-digit"
    });
    dayNameEl.textContent = capitalizeFirst(now.toLocaleDateString("sl-SI", { weekday: "long" }));
    dateEl.textContent = now.toLocaleDateString("sl-SI", {
      day: "2-digit",
      month: "long",
      year: "numeric"
    });
  };

  updateLayoutMode();
  updateClock();
  setInterval(updateClock, 1000);

  if (typeof ResizeObserver !== "undefined") {
    const ro = new ResizeObserver(updateLayoutMode);
    ro.observe(dayTimePin);
  } else {
    window.addEventListener("resize", updateLayoutMode);
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  const taskList = document.getElementById("task_list");
  const eventsList = document.getElementById("events_list");
  const shoppingList = document.getElementById("shopping_list");
  const storageList = document.getElementById("storage_list");
  setupDashboardClock();

  taskList.innerHTML = "";
  const tasks = await fetchJson("../tasks/get_my_tasks.php");
  if (!tasks.length) {
    setEmpty(taskList);
  } else {
    for (const task of tasks) {
      taskList.appendChild(createSimpleItem(task.name));
    }
  }

  eventsList.innerHTML = "";
  const events = await fetchJson("../calendar/get_today_events.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `date_key=${encodeURIComponent(localDateKey())}`
  });
  if (!events.length) {
    setEmpty(eventsList);
  } else {
    for (const event of events) {
      eventsList.appendChild(createSimpleItem(event.name));
    }
  }

  shoppingList.innerHTML = "";
  const shops = await fetchJson("../shopping_list/get_shops.php");
  let hasShoppingItems = false;
  for (const shop of shops) {
    const items = await fetchJson(`../shopping_list/get_shopping_list_high.php?shop_id=${shop.id}`);
    if (!items.length) continue;
    hasShoppingItems = true;

    shoppingList.appendChild(createGroupItem(shop.name, items.map(item => item.name)));
  }
  if (!hasShoppingItems) setEmpty(shoppingList);

  storageList.innerHTML = "";
  const storages = await fetchJson("../food_storage/get_storage_locations.php");
  let hasStorageItems = false;
  for (const storage of storages) {
    const products = await fetchJson(`../food_storage/get_expiring_storage.php?storage_id=${storage.id}`);
    if (!products.length) continue;
    hasStorageItems = true;

    storageList.appendChild(createGroupItem(storage.name, products.map(product => product.name)));
  }
  if (!hasStorageItems) setEmpty(storageList);
});
