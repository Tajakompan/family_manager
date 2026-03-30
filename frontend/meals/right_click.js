document.addEventListener("DOMContentLoaded", () => {
  const table = document.getElementById("table_1");
  const row_menu = document.getElementById("row_menu"); // Uredi/Izbriši zapis

  let rightClickedMealId = null;

  if (!table || !row_menu) return;

  function hideMenus() {
    row_menu.style.display = "none";
    table.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
    table.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
  }

  function positionMenu(menu, e) {
    menu.style.display = "flex";

    const w = menu.offsetWidth;
    const h = menu.offsetHeight;

    let x = e.clientX;
    let y = e.clientY;

    if (x + w > window.innerWidth) x = window.innerWidth - w - 5;
    if (y + h > window.innerHeight) y = window.innerHeight - h - 5;

    menu.style.left = `${x}px`;
    menu.style.top = `${y}px`;
  }

    function openMealEditFromMeal(meal) {
        const add_something_view = document.getElementById("add_something_view");
        const add_meal_window = document.getElementById("add_meal_window");
        const form = document.getElementById("add_meal_form");
        if (!form || !add_meal_window || !add_something_view) return;

        // title + gumb
        add_meal_window.querySelector(".title").textContent = "Uredi obrok:";
        const submitBtn = document.getElementById("add_new_meal_btn");
        if (submitBtn) submitBtn.textContent = "Posodobi";

        // hidden task_id
        let mealIdInput = form.querySelector('input[name="meal_id"]');
        if (!mealIdInput) {
            mealIdInput = document.createElement("input");
            mealIdInput.type = "hidden";
            mealIdInput.name = "meal_id";
            form.appendChild(mealIdInput);
        }
        mealIdInput.value = String(meal.id ?? "");

        // IMPORTANT: nastavi endpoint za update (ustvari ga na backendu)
        form.action = "update_meal_in_db.php";

        // fill fields
        const nameInput = form.querySelector('input[name="new_meal"]');

        if (nameInput) nameInput.value = meal.name ?? "";

        // odpri modal
        showWindow("add_meal_window");
        add_something_view.classList.add("active");
    }

//DESNI KLIK NA OBROK
// KLIK NA OBROK
table.addEventListener("click", (e) => {
  const chosen = e.target.closest(".has_meal");
  if (!chosen) return;

  e.preventDefault();
  e.stopPropagation();
  hideMenus();

  rightClickedMealId = chosen.dataset.mealId || chosen.closest(".has_meal")?.dataset.mealId;
  if (!rightClickedMealId) return;

  chosen.classList.add("context-active");
  positionMenu(row_menu, e);
});

  // zapiranje
  document.addEventListener("click", hideMenus);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") hideMenus(); });
  window.addEventListener("scroll", hideMenus, { passive: true });
  window.addEventListener("resize", hideMenus);

  // da se ne odpre browser default meni na meniju
  row_menu.addEventListener("contextmenu", (e) => e.preventDefault());

  // klik v meniju naj ne zapre
  row_menu.addEventListener("click", (e) => e.stopPropagation());

  //DELETE
  row_menu.querySelector(".delete")?.addEventListener("click", () => {
    if (!rightClickedMealId) return;
    if (!confirm("Izbrišem ta obrok?")) return;

    fetch("delete_meal.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `meal_id=${encodeURIComponent(rightClickedMealId)}`
    }).then(() => location.reload());
  });

  //EDIT
  row_menu?.querySelector(".edit")?.addEventListener("click", async () => {
    if (!rightClickedMealId) return;

    try {
      const res = await fetch(`get_meal.php?meal_id=${encodeURIComponent(rightClickedMealId)}`);
        const data = await res.json();


      if (!data.ok || !data.meal) {
        alert("Napaka pri nalaganju obroka");
        return;
      }

      openMealEditFromMeal(data.meal);
      hideMenus();

    } catch (err) {
      console.error(err);
      alert("Napaka pri povezavi s strežnikom");
    }
  });
});
