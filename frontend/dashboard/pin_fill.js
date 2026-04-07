async function fetchDashboardJson(url, options = {}) {
  return fetch(url, { credentials: "same-origin", ...options })
    .then((response) => {
      if (!response.ok) return null;
      return response.json().catch(() => null);
    })
    .catch(() => null);
}

function localDateKey() {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function setEmpty(listElement) {
  const container = listElement?.parentElement;
  if (!container) return;
  container.classList.add("is-empty");
  container.innerHTML = "<div class='empty_text'>Prazno</div>";
}

function createListItem(text) {
  const item = document.createElement("li");
  item.className = "dashboard-item";
  item.textContent = text;
  return item;
}

function createGroupItem(title, items) {
  const groupItem = document.createElement("li");
  groupItem.className = "dashboard-group";

  const groupTitle = document.createElement("div");
  groupTitle.className = "dashboard-group-title";
  groupTitle.textContent = title;

  const groupList = document.createElement("ul");
  groupList.className = "dashboard-sublist";

  items.forEach((itemName) => {
    const item = document.createElement("li");
    item.className = "dashboard-subitem";
    item.textContent = itemName;
    groupList.appendChild(item);
  });

  groupItem.appendChild(groupTitle);
  groupItem.appendChild(groupList);

  return groupItem;
}

function createSectionItem(title, groups) {
  const sectionItem = document.createElement("li");
  sectionItem.className = "dashboard-section";

  const sectionTitle = document.createElement("div");
  sectionTitle.className = "dashboard-section-title";
  sectionTitle.textContent = title;

  const sectionList = document.createElement("ul");
  sectionList.className = "dashboard-section-list";

  groups.forEach((group) => {
    sectionList.appendChild(group);
  });

  sectionItem.appendChild(sectionTitle);
  sectionItem.appendChild(sectionList);

  return sectionItem;
}

function getMealLabel(category) {
  if (category === "breakfast") return "Zajtrk";
  if (category === "lunch") return "Kosilo";
  if (category === "dinner") return "Večerja";
  return category;
}

function capitalizeFirst(text) {
  if (!text) return "";
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

  function updateLayoutMode() {
    layout.classList.remove("is-column");
  }


  function updateClock() {
    const now = new Date();

    const hours = (now.getHours() % 12) + now.getMinutes() / 60;
    const minutes = now.getMinutes() + now.getSeconds() / 60;
    const seconds = now.getSeconds() + now.getMilliseconds() / 1000;

    hourHand.style.transform = `translateX(-50%) rotate(${hours * 30}deg)`;
    minuteHand.style.transform = `translateX(-50%) rotate(${minutes * 6}deg)`;
    secondHand.style.transform = `translateX(-50%) rotate(${seconds * 6}deg)`;

    timeEl.textContent = now.toLocaleTimeString("sl-SI", {hour: "2-digit", minute: "2-digit"});

    dayNameEl.textContent = capitalizeFirst(
      now.toLocaleDateString("sl-SI", { weekday: "long" })
    );

    dateEl.textContent = now.toLocaleDateString("sl-SI", {day: "2-digit", month: "long", year: "numeric"});
  }

  updateLayoutMode();
  updateClock();
  setInterval(updateClock, 1000);

  if (typeof ResizeObserver !== "undefined") {
    const observer = new ResizeObserver(updateLayoutMode);
    observer.observe(dayTimePin);
  } else {
    window.addEventListener("resize", updateLayoutMode);
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  const taskList = document.getElementById("task_list");
  const eventsList = document.getElementById("events_list");
  const shoppingList = document.getElementById("shopping_list");
  const storageList = document.getElementById("storage_list");
  const mealsList = document.getElementById("meals_list");

  setupDashboardClock();

  if (taskList) {
    taskList.innerHTML = "";
    const tasks = await fetchDashboardJson("../tasks/get_my_tasks.php");

    if (!Array.isArray(tasks) || tasks.length === 0) {
      setEmpty(taskList);
    } else {
      tasks.forEach((task) => {
        taskList.appendChild(createListItem(task.name));
      });
    }
  }

  if (eventsList) {
    eventsList.innerHTML = "";
    const events = await fetchDashboardJson("../calendar/get_today_events.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `date_key=${encodeURIComponent(localDateKey())}`
    });

    if (!Array.isArray(events) || events.length === 0) {
      setEmpty(eventsList);
    } else {
      events.forEach((event) => {
        eventsList.appendChild(createListItem(event.name));
      });
    }
  }

  if (shoppingList) {
    shoppingList.innerHTML = "";

    const shops = await fetchDashboardJson("../shopping_list/get_shops.php");
    let hasItems = false;

    if (Array.isArray(shops)) {
      for (const shop of shops) {
        const items = await fetchDashboardJson(`../shopping_list/get_shopping_list_high.php?shop_id=${shop.id}`);

        if(!Array.isArray(items) || items.length === 0) {
          continue;
        }

        hasItems = true;
        shoppingList.appendChild(
          createGroupItem(shop.name, items.map((item) => item.name))
        );
      }
    }

    if(!hasItems) {setEmpty(shoppingList);}
  }

  if(mealsList) {
    mealsList.innerHTML = "";

    const meals = await fetchDashboardJson("../meals/get_meals.php");
    const todayMeals = Array.isArray(meals)
      ? meals.filter((meal) => meal.date === localDateKey())
      : [];

    ["breakfast", "lunch", "dinner"].forEach((category) => {
      const meal = todayMeals.find((item) => item.meal_category === category);
      const mealName = meal?.name?.trim() || " - ";
      mealsList.appendChild(createListItem(`${getMealLabel(category)}: ${mealName}`));
    });
  }

  if(storageList) {
    storageList.innerHTML = "";

    const storages = await fetchDashboardJson("../food_storage/get_storage_locations.php");
    const expiringGroups = [];
    const expiredGroups = [];

    if(Array.isArray(storages)) {
      for (const storage of storages) {
        const expiring = await fetchDashboardJson(`../food_storage/get_expiring_storage.php?storage_id=${storage.id}&expired=0`);
        const expired = await fetchDashboardJson(`../food_storage/get_expiring_storage.php?storage_id=${storage.id}&expired=1`);

        if(Array.isArray(expiring) && expiring.length > 0) {
          expiringGroups.push(
            createGroupItem(storage.name, expiring.map((product) => product.name))
          );
        }

        if (Array.isArray(expired) && expired.length > 0) {
          expiredGroups.push(
            createGroupItem(storage.name, expired.map((product) => product.name))
          );
        }
      }
    }

    if (expiringGroups.length > 0) {
      storageList.appendChild(createSectionItem("Rok se bo kmalu iztekel", expiringGroups));
    }

    if (expiredGroups.length > 0) {
      storageList.appendChild(createSectionItem("Rok je že potekel", expiredGroups));
    }

    if (expiringGroups.length === 0 && expiredGroups.length === 0) {
      setEmpty(storageList);
    }
  }
});
