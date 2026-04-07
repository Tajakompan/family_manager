function hideAllWindows() {
    const windows = document.querySelectorAll(".window");

    for (let i = 0; i < windows.length; i++) {
        windows[i].classList.remove("active");
    }
}

function setMealWindowMode(mode) {
    const mealWindowTitle = document.querySelector("#add_meal_window .title");
    const submitButton = document.getElementById("add_new_meal_btn");
    const form = document.getElementById("add_meal_form");

    if (!mealWindowTitle || !submitButton || !form) {
        return;
    }

    if (mode === "edit") {
        mealWindowTitle.textContent = "Uredi obrok:";
        submitButton.textContent = "Posodobi";
        form.action = "update_meal_in_db.php";
    } else {
        mealWindowTitle.textContent = "Dodaj obrok:";
        submitButton.textContent = "Dodaj";
        form.action = "add_meal_in_db.php";
    }
}

function fillMealForm(data) {
    const nameInput = document.querySelector('#add_meal_form input[name="new_meal"]');
    const mealDateInput = document.getElementById("meal_date");
    const mealTypeInput = document.getElementById("meal_type");
    const mealIdInput = document.getElementById("meal_id");

    const mealData = data || {};

    if (nameInput) nameInput.value = mealData.name || "";
    if (mealDateInput) mealDateInput.value = mealData.date || "";
    if (mealTypeInput) mealTypeInput.value = mealData.type || "";
    if (mealIdInput) mealIdInput.value = mealData.id || "";
}

function openMealWindow(mode, data) {
    const addSomethingView = document.getElementById("add_something_view");
    const addMealWindow = document.getElementById("add_meal_window");

    if (!addSomethingView || !addMealWindow) return;

    setMealWindowMode(mode);
    fillMealForm(data);

    hideAllWindows();
    addMealWindow.classList.add("active");
    addSomethingView.classList.add("active");
}

function closeMealWindow() {
    const form = document.getElementById("add_meal_form");
    const addSomethingView = document.getElementById("add_something_view");

    if (form) form.reset();
    
    hideAllWindows();

    if (addSomethingView) addSomethingView.classList.remove("active");

    setMealWindowMode("add");
    fillMealForm({});
}

document.addEventListener("DOMContentLoaded", function () {
  const table = document.getElementById("table_1");
  const cancelMealButton = document.getElementById("cancel_meal_btn");
  const addMealWindow = document.getElementById("add_meal_window");

  if (table) {
      table.addEventListener("click", function (e) {
          const clickedButton = e.target.closest(".add_meal_btn");

          if (!clickedButton) {
              return;
          }

          if (clickedButton.classList.contains("has_meal")) {
              return;
          }

          e.preventDefault();
          e.stopPropagation();

          openMealWindow("add", {
              date: clickedButton.dataset.date || "",
              type: clickedButton.dataset.mealType || "",
              id: ""
          });
      });
  }

  if (cancelMealButton) {
    cancelMealButton.addEventListener("click", function (e) {
      e.preventDefault();
      closeMealWindow();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeMealWindow();
    }
  });

  if (addMealWindow) {
    addMealWindow.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }
});
