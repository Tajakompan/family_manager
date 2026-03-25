function showWindow(id) {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
  document.getElementById(id)?.classList.add("active");
}

function closeWindows() {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
}

document.addEventListener("DOMContentLoaded", () => {
  const table = document.getElementById("table_1");
  const cancel_meal_btn = document.getElementById("cancel_meal_btn");
  const add_meal_window = document.getElementById("add_meal_window");
  const add_something_view = document.getElementById("add_something_view");

  if (table) {
    table.addEventListener("click", (e) => {
      const plus = e.target.closest(".add_meal_btn");
      if (!plus) return;
      if(plus.classList.contains("has_meal")) return;
      e.preventDefault();
      e.stopPropagation();

      const mealDateInput = document.getElementById("meal_date");
      const mealTypeInput = document.getElementById("meal_type");
      const mealIdInput = document.getElementById("meal_id");

      mealDateInput.value = plus.dataset.date || "";
      mealTypeInput.value = plus.dataset.mealType || "";
      mealIdInput.value = plus.dataset.mealId || "";

      showWindow("add_meal_window");
      add_something_view?.classList.add("active");
    });
  }

  cancel_meal_btn?.addEventListener("click", (e) => {
    e.preventDefault();
    document.getElementById("add_meal_form")?.reset();
    closeWindows();
    add_something_view?.classList.remove("active");
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeWindows();
      add_something_view?.classList.remove("active");
      document.getElementById("add_meal_form")?.reset();
    }
  });

  add_meal_window?.addEventListener("click", (e) => e.stopPropagation());
});