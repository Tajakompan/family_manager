const my_tasks_container = document.getElementById("my_tasks_container");
const other_tasks_container = document.getElementById("other_tasks_container");
const container = document.getElementById("big_container");
const row_menu = document.getElementById("row_menu"); 
const add_something_view = document.getElementById("add_something_view");
const tasksApiBase = `${window.API_URL}/tasks`;

let rightClickedPinId = null;

function hideMenus() {
  if (row_menu) row_menu.style.display = "none";
  my_tasks_container.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
  my_tasks_container.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
  other_tasks_container.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
  other_tasks_container.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
}

document.addEventListener("DOMContentLoaded", () => {
  container.addEventListener("contextmenu", (e) => {
    const pin = e.target.closest(".pin");
    if (!pin) return;

    e.preventDefault();
    hideMenus();

    rightClickedPinId = pin.dataset.pinId;
    if (!rightClickedPinId) return;

    pin.classList.add("context-active");
    if (row_menu) positionMenu(row_menu, e);
  });

  //zapiranje menija
  document.addEventListener("click", hideMenus);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") hideMenus(); });
  window.addEventListener("scroll", hideMenus, { passive: true });
  window.addEventListener("resize", hideMenus);

  //IZBRIŠI - v meniju
  row_menu?.querySelector(".delete")?.addEventListener("click", () => {
    if (!rightClickedPinId) return;

    if (!confirm("Ali si prepričan, da želiš izbrisati to opravilo?")) {
      return;
    }

    fetch(`${tasksApiBase}/delete_pin.php`, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `task_id=${encodeURIComponent(rightClickedPinId)}`
    })
      .then(() => location.reload());
  });

  //UREDI - v meniju
  row_menu?.querySelector(".edit")?.addEventListener("click", async () => {
    if (!rightClickedPinId) return;

    try {
      const res = await fetch(`${tasksApiBase}/get_task.php?task_id=${encodeURIComponent(rightClickedPinId)}`);
      const data = await res.json();

      if (!data.ok || !data.task) {
        alert("Napaka pri nalaganju opravila");
        return;
      }

      openTaskEditFromTask(data.task);
      hideMenus();

    } catch (err) {
      console.error(err);
      alert("Napaka pri povezavi s strežnikom");
    }
  });

  //PODROBNOSTI - v meniju
  row_menu?.querySelector(".details")?.addEventListener("click", async () => {
    if (!rightClickedPinId) return;

    try {
      const res = await fetch(`${tasksApiBase}/get_task.php?task_id=${encodeURIComponent(rightClickedPinId)}`);
      const data = await res.json();

      if (!data.ok || !data.task) {
        alert("Napaka pri nalaganju opravila");
        return;
      }
      openDetails(data.task);
      hideMenus();

    } catch (err) {
      console.error(err);
      alert("Napaka pri povezavi s strežnikom");
    }
  });
});  

//izpolni podatke v formo
function openTaskEditFromTask(task) {
  const add_something_view = document.getElementById("add_something_view");
  const add_task_window = document.getElementById("add_task_window");
  const form = document.getElementById("add_task_form");
  if (!form || !add_task_window || !add_something_view) return;

  //drug naslov in drug gumb
  add_task_window.querySelector(".title").textContent = "Uredi opravilo:";
  const submitBtn = document.getElementById("add_new_task_btn");
  submitBtn.textContent = "Posodobi";

  //hidden task id set
  let taskIdInput = form.querySelector('input[name="task_id"]');
  taskIdInput.value = String(task.id ?? "");

  //spremeni cilj forme
  form.action = "update_task_in_db.php";

  form.querySelector('input[name="new_task"]').value = task.name ?? "";
  form.querySelector('[name="details"]').value = task.details ?? "";
  const pointsInput = form.querySelector('input[name="points"]');
  if (pointsInput) {
    pointsInput.value = task.points ?? 2;
    if (window.currentUserRole === "Otrok") {
      pointsInput.readOnly = true;
      pointsInput.title = "Otrok ne more spreminjati tock opravila.";
    } else {
      pointsInput.readOnly = false;
      pointsInput.title = "";
    }
  }


  const dateInput = form.querySelector('input[name="to_do_by"]');
  const noDateCheckbox = form.querySelector('input[name="no_date"]');

  noDateCheckbox.checked = String(task.no_date ?? "0") === "1" || !task.to_do_by;

  if (dateInput) {
    if (noDateCheckbox.checked) {
      dateInput.value = "";
      dateInput.disabled = true;
    } else {
      dateInput.disabled = false;
      dateInput.value = task.to_do_by ?? "";
    }
  }

  showWindow("add_task_window");
  add_something_view.classList.add("active");
}

//odpre podrobnosti
function openDetails(task) {
  const frame = document.getElementById("details_frame");
  if (!frame) return;

  const details = frame.querySelector(".details");
  details.textContent = task.details?.trim() || "Ni podrobnosti.";

  showWindow("details_frame");
  add_something_view.classList.add("active");
}


