function formatDateSI(isoDate) {
  const [year, month, day] = isoDate.split("-").map(Number);
  const date = new Date(year, month - 1, day);

  return new Intl.DateTimeFormat("sl-SI", {
    day: "numeric",
    month: "long",
    year: "numeric"
  }).format(date);
}

const dateAndDay = document.getElementById("date_and_day");
const thisDayEventsContainer = document.querySelector(".this_day_events");
const dayNames = [
  "Nedelja",
  "Ponedeljek",
  "Torek",
  "Sreda",
  "Četrtek",
  "Petek",
  "Sobota"
];

let selectedDate = null;

function createEventInfoRow(label, value) {
  const row = document.createElement("div");
  const strong = document.createElement("b");

  strong.textContent = `${label}: `;
  row.appendChild(strong);
  row.appendChild(document.createTextNode(value ?? ""));

  return row;
}

function createEventCard(eventData) {
  const card = document.createElement("div");
  card.className = "one_event";
  card.dataset.id = eventData.id ?? "";

  const head = document.createElement("div");
  head.className = "event_head";

  const title = document.createElement("div");
  title.className = "event_title";
  title.textContent = eventData.name ?? "";

  const menuButton = document.createElement("button");
  menuButton.className = "event_menu_btn";
  menuButton.type = "button";
  menuButton.setAttribute("aria-label", "Meni");
  menuButton.textContent = "...";

  const menu = document.createElement("div");
  menu.className = "event_menu";

  const editButton = document.createElement("button");
  editButton.type = "button";
  editButton.className = "event_action";
  editButton.dataset.action = "edit";
  editButton.textContent = "Uredi";

  const deleteButton = document.createElement("button");
  deleteButton.type = "button";
  deleteButton.className = "event_action danger";
  deleteButton.dataset.action = "delete";
  deleteButton.textContent = "Izbri\u0161i";

  menu.appendChild(editButton);
  menu.appendChild(deleteButton);

  head.appendChild(title);
  head.appendChild(menuButton);
  head.appendChild(menu);

  const body = document.createElement("div");
  body.className = "event_body";
  body.appendChild(createEventInfoRow("Ura", eventData.event_time));
  body.appendChild(createEventInfoRow("Lokacija", eventData.location));
  body.appendChild(createEventInfoRow("Opis", eventData.description));
  body.appendChild(createEventInfoRow("Ustvaril", eventData.user_name));
  body.appendChild(createEventInfoRow("Opomnik", eventData.reminder_display));

  card.appendChild(head);
  card.appendChild(body);

  return card;
}

function updateSelectedDayHeader(dateStr) {
  if (!dateAndDay) return;

  const [year, month, day] = dateStr.split("-").map(Number);
  const date = new Date(year, month - 1, day);
  const dayName = dayNames[date.getDay()];

  const wrapper = document.createElement("div");
  wrapper.className = "datum";
  wrapper.appendChild(document.createTextNode(dayName));
  wrapper.appendChild(document.createElement("br"));
  wrapper.appendChild(document.createTextNode(formatDateSI(dateStr)));

  dateAndDay.replaceChildren(wrapper);
}

function clearSelectedDay() {
  const selectedCell = document.querySelector(".dan.selected");
  if (selectedCell) {
    selectedCell.classList.remove("selected");
  }

  selectedDate = null;
  showView("view-empty");

  if (dateAndDay) {
    dateAndDay.replaceChildren();
  }

  if (thisDayEventsContainer) {
    thisDayEventsContainer.replaceChildren();
  }
}

function renderEventsForDate(dateStr) {
  if (!thisDayEventsContainer) return;

  const eventsByDate = window.eventsByDate || {};
  const events = eventsByDate[dateStr] || [];

  thisDayEventsContainer.replaceChildren();

  if (events.length === 0) {
    const empty = document.createElement("div");
    empty.className = "one_event";
    empty.textContent = "Ni dogodkov.";
    thisDayEventsContainer.appendChild(empty);
    return;
  }

  events.forEach((eventData) => {
    thisDayEventsContainer.appendChild(createEventCard(eventData));
  });
}

function selectDay(cell) {
  const previousSelected = document.querySelector(".dan.selected");
  if (previousSelected && previousSelected !== cell) {
    previousSelected.classList.remove("selected");
  }

  cell.classList.add("selected");
  cell.classList.remove("to_select");

  selectedDate = cell.dataset.date || null;

  if (!selectedDate) return;

  showView("view-day-selected");
  updateSelectedDayHeader(selectedDate);
  renderEventsForDate(selectedDate);
}

document.querySelectorAll(".dan").forEach((cell) => {
  cell.addEventListener("click", () => {
    if (cell.classList.contains("selected")) {
      clearSelectedDay();
      cell.classList.add("to_select");
      return;
    }

    selectDay(cell);
  });

  cell.addEventListener("mouseenter", () => {
    if (!cell.classList.contains("selected")) {
      cell.classList.add("to_select");
    }
  });

  cell.addEventListener("mouseleave", () => {
    cell.classList.remove("to_select");
  });
});

document.querySelectorAll(".add_event").forEach((button) => {
  button.addEventListener("mouseenter", () => {
    button.classList.add("to_select");
  });

  button.addEventListener("mouseleave", () => {
    button.classList.remove("to_select");
  });

  button.addEventListener("click", () => {
    showDesno("add_event_form");
    switchEventToAddMode(window.calMonth, window.calYear);

    const dateInput = document.getElementById("date_input");
    if (dateInput && selectedDate) {
      dateInput.value = selectedDate;
    }
  });
});
