function showView(id) {
  document.querySelectorAll(".view").forEach((view) => {
    view.classList.remove("active");
  });

  const target = document.getElementById(id);
  if (target) {
    target.classList.add("active");
  }
}

function showDesno(id) {
  document.querySelectorAll(".desno").forEach((section) => {
    section.classList.remove("active");
  });

  const target = document.getElementById(id);
  if (target) {
    target.classList.add("active");
  }
}

function setFormError(errorId, message) {
  const errorEl = document.getElementById(errorId);
  if (errorEl) {
    errorEl.textContent = message;
  }
}

function clearFormErrors() {
  setFormError("error_name", "");
  setFormError("error_date", "");
  setFormError("error_time", "");
}

function validateEventForm(form) {
  let isValid = true;

  const nameInput = form.querySelector('input[name="name"]');
  const dateInput = form.querySelector('input[name="date"]');
  const timeInput = form.querySelector('input[name="time"]');
  const wholeDayInput = form.querySelector('input[name="whole_day"]');

  clearFormErrors();

  if (!nameInput || nameInput.value.trim() === "") {
    setFormError("error_name", "Obvezen vpis naziva dogodka!");
    isValid = false;
  }

  if (!dateInput || dateInput.value.trim() === "") {
    setFormError("error_date", "Obvezen vpis datuma dogodka!");
    isValid = false;
  }

  const wholeDayChecked = wholeDayInput ? wholeDayInput.checked : false;

  if (!wholeDayChecked) {
    if (!timeInput || timeInput.value.trim() === "") {
      setFormError("error_time", "Obvezen vpis ure dogodka!");
      isValid = false;
    }
  }

  return isValid;
}

function fillEventForm(eventData) {
  const form = document.querySelector("#add_event_form form");
  if (!form || !eventData) return;

  const nameInput = form.querySelector('input[name="name"]');
  const dateInput = form.querySelector('input[name="date"]');
  const wholeDayInput = form.querySelector('input[name="whole_day"]');
  const timeInput = form.querySelector('input[name="time"]');
  const locationInput = form.querySelector('input[name="location"]');
  const descriptionInput = form.querySelector('input[name="description"]');
  const reminderInput = form.querySelector('input[name="reminder"]');
  const justForCreatorInput = form.querySelector('input[name="just_for_creator"]');

  if (nameInput) nameInput.value = eventData.name || "";
  if (dateInput) dateInput.value = selectedDate || "";
  if (wholeDayInput) wholeDayInput.checked = Number(eventData.whole_day) === 1;

  if (timeInput) {
    if (Number(eventData.whole_day) === 1) {
      timeInput.value = "";
    } else {
      timeInput.value = eventData.event_time_raw || "";
    }
  }

  if (locationInput) locationInput.value = eventData.location && eventData.location !== "/" ? eventData.location : "";
  if (descriptionInput) descriptionInput.value = eventData.description && eventData.description !== "/" ? eventData.description : "";
  if (reminderInput) reminderInput.value = eventData.reminder_input || "";
  if (justForCreatorInput) justForCreatorInput.checked = Number(eventData.just_for_creator) === 1;
}

function resetCalendarFormToAddMode() {
  switchEventToAddMode(window.calMonth, window.calYear);
  clearFormErrors();
}

function restoreRightPanelAfterCancel() {
  showDesno("podrobnosti_dneva");

  if (selectedDate) {
    showView("view-day-selected");
    renderEventsForDate(selectedDate);
  } else {
    showView("view-empty");
    if (thisDayEventsContainer) {
      thisDayEventsContainer.replaceChildren();
    }
  }
}

const addEventForm = document.querySelector("#add_event_form form");

if (addEventForm) {
  addEventForm.addEventListener("submit", (e) => {
    const isValid = validateEventForm(addEventForm);
    if (!isValid) e.preventDefault();
  });
}

const cancelBtn = document.getElementById("cancel_btn");

if (cancelBtn) {
  cancelBtn.addEventListener("click", (e) => {
    e.preventDefault();
    resetCalendarFormToAddMode();
    restoreRightPanelAfterCancel();
  });
}

document.addEventListener("click", (e) => {
  const menuButton = e.target.closest(".event_menu_btn");
  const actionButton = e.target.closest(".event_action");

  if (menuButton) {
    e.stopPropagation();

    const card = menuButton.closest(".one_event");
    if (!card) return;

    document.querySelectorAll(".one_event.menu-open").forEach((openCard) => {
      if (openCard !== card) {
        openCard.classList.remove("menu-open");
      }
    });

    card.classList.toggle("menu-open");
    return;
  }

  if (actionButton) {
    e.stopPropagation();

    const card = actionButton.closest(".one_event");
    const eventId = card?.dataset?.id;
    const action = actionButton.dataset.action;

    if (card) card.classList.remove("menu-open");

    if (!eventId) return;

    if (action === "delete") {
      const confirmed = window.confirm("Res želiš izbrisati dogodek?");
      if (!confirmed) return;

      fetch("delete_event.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${encodeURIComponent(eventId)}`
      })
        .then((response) => {
          if (response.ok) {
            location.reload();
          } else {
            alert("Brisanje dogodka ni uspelo.");
          }
        })
        .catch(() => {
          alert("Brisanje dogodka ni uspelo.");
        });

      return;
    }

    if (action === "edit") {
      const events = window.eventsByDate[selectedDate] || [];
      const eventData = events.find((eventItem) => String(eventItem.id) === String(eventId));

      if (!eventData) return;

      showDesno("add_event_form");
      switchEventToUpdateMode(eventData.id, window.calMonth, window.calYear);
      clearFormErrors();
      fillEventForm(eventData);
    }

    return;
  }

  document.querySelectorAll(".one_event.menu-open").forEach((openCard) => {
    openCard.classList.remove("menu-open");
  });
});

function switchEventToAddMode(month, year) {
  const form = document.querySelector("#add_event_form form");
  if (!form) return;

  const selectedUserId = window.selectedUserId || 0;
  const eventIdInput = document.getElementById("event_id");
  const submitButton = form.querySelector('button[type="submit"]');
  const title = document.querySelector("#add_event_form h3");

  form.dataset.mode = "add";
  form.action = `add_event.php?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&user_id=${encodeURIComponent(selectedUserId)}`;

  if (eventIdInput) eventIdInput.value = "";
  if (submitButton) submitButton.textContent = "Shrani";
  if (title) title.textContent = "Dodaj dogodek:";
}

function switchEventToUpdateMode(eventId, month, year) {
  const form = document.querySelector("#add_event_form form");
  if (!form) return;

  const selectedUserId = window.selectedUserId || 0;
  const eventIdInput = document.getElementById("event_id");
  const submitButton = form.querySelector('button[type="submit"]');
  const title = document.querySelector("#add_event_form h3");

  form.dataset.mode = "update";
  form.action = `update_event.php?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&user_id=${encodeURIComponent(selectedUserId)}`;

  if (eventIdInput) eventIdInput.value = eventId;
  if (submitButton) submitButton.textContent = "Posodobi";
  if (title) title.textContent = "Uredi dogodek:";
}
