//odpira formo
function showWindow(id) {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
  document.getElementById(id)?.classList.add("active");
}
//zapira formo
function closeWindows() {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
}
//pozicija menija right click contextmenu
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

const my_tasks_container = document.getElementById("my_tasks_container");
const other_tasks_container = document.getElementById("other_tasks_container");
const nav_menu = document.getElementById("nav_menu"); // Izbriši lokacijo
const row_menu = document.getElementById("row_menu"); // Uredi/Izbriši zapis

let rightClickedPinId = null;

function hideMenus() {
  if (nav_menu) nav_menu.style.display = "none";
  if (row_menu) row_menu.style.display = "none";
  my_tasks_container.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
  my_tasks_container.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
  other_tasks_container.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
  other_tasks_container.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
}

//odpiranje, zapiranje form
document.addEventListener("DOMContentLoaded", () => {
  const add_task_btn = document.getElementById("add_task_btn");
  const cancel_task_btn = document.getElementById("cancel_task_btn");
  const add_task_window = document.getElementById("add_task_window");
  const add_something_view = document.getElementById("add_something_view");
  const details_frame = document.getElementById("details_frame");

  
  my_tasks_container.addEventListener("contextmenu", (e) => {
    const pin = e.target.closest(".pin");
    if (!pin) return;

    e.preventDefault();
    hideMenus();

    rightClickedPinId = pin.dataset.pinId;
    if (!rightClickedPinId) return;

    pin.classList.add("context-active");
    if (row_menu) positionMenu(row_menu, e);
  });

  other_tasks_container.addEventListener("contextmenu", (e) => {
    const pin = e.target.closest(".pin");
    if (!pin) return;

    e.preventDefault();
    hideMenus();

   rightClickedPinId = pin.dataset.pinId;
    if (!rightClickedPinId) return;

    pin.classList.add("context-active");
    if (row_menu) positionMenu(row_menu, e);
  });
  // zapiranje menu
  document.addEventListener("click", hideMenus);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") hideMenus(); });
  window.addEventListener("scroll", hideMenus, { passive: true });
  window.addEventListener("resize", hideMenus);

  //odpre ADD TASK
  if (add_task_btn) {
    add_task_btn.addEventListener("click", (e) => {
      e.preventDefault();
      showWindow("add_task_window");
      add_something_view.classList.add("active");
    });
  }

  // zapre ADD TASK in resetira input
  if (cancel_task_btn) {
    cancel_task_btn.addEventListener("click", (e) => {
      e.preventDefault();
      document.getElementById("add_task_form")?.reset();
      closeWindows();
      add_something_view.classList.remove("active");
    });
  }

  // ESC isto zapre window in resetira
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeWindows();
      add_something_view.classList.remove("active");
      document.getElementById("add_task_form")?.reset();
    }
  });

  // da klik v window ne pobegne
  add_task_window?.addEventListener("click", (e) => e.stopPropagation());
  details_frame?.addEventListener("click", (e) => e.stopPropagation());
  

  // klik na overlay = zapri DETAILS
  add_something_view?.addEventListener("click", () => {
    closeWindows();
    add_something_view.classList.remove("active");
  });  

  //IZBRIŠI
  row_menu?.querySelector(".delete")?.addEventListener("click", () => {
    if (!rightClickedPinId) return;

    if (!confirm("Ali si prepričan, da želiš izbrisati to opravilo?")) {
      return;
    }

    fetch("delete_pin.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `task_id=${encodeURIComponent(rightClickedPinId)}`
    })
      .then(() => location.reload());
  });

  //UREDI
  row_menu?.querySelector(".edit")?.addEventListener("click", async () => {
    if (!rightClickedPinId) return;

    try {
      const res = await fetch(`get_task.php?task_id=${encodeURIComponent(rightClickedPinId)}`);
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

  //PODROBNOSTI
  row_menu?.querySelector(".details")?.addEventListener("click", async () => {
    if (!rightClickedPinId) return;

    try {
      const res = await fetch(`get_task.php?task_id=${encodeURIComponent(rightClickedPinId)}`);
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

function openTaskEditFromTask(task) {
  const add_something_view = document.getElementById("add_something_view");
  const add_task_window = document.getElementById("add_task_window");
  const form = document.getElementById("add_task_form");
  if (!form || !add_task_window || !add_something_view) return;

  // title + gumb
  add_task_window.querySelector(".title").textContent = "Uredi opravilo:";
  const submitBtn = document.getElementById("add_new_task_btn");
  if (submitBtn) submitBtn.textContent = "Posodobi";

  // hidden task_id
  let taskIdInput = form.querySelector('input[name="task_id"]');
  if (!taskIdInput) {
    taskIdInput = document.createElement("input");
    taskIdInput.type = "hidden";
    taskIdInput.name = "task_id";
    form.appendChild(taskIdInput);
  }
  taskIdInput.value = String(task.id ?? "");

  // IMPORTANT: nastavi endpoint za update (ustvari ga na backendu)
  form.action = "update_task_in_db.php";

  // fill fields
  const nameInput = form.querySelector('input[name="new_task"]');
  const detailsInput = form.querySelector('[name="details"]');
  const dateInput = form.querySelector('input[name="to_do_by"]');
  const pointsInput = form.querySelector('input[name="points"]');
  const noDateCheckbox = form.querySelector('input[name="no_date"]');

  if (nameInput) nameInput.value = task.name ?? "";
  if (detailsInput) detailsInput.value = task.details ?? "";
  if (pointsInput) pointsInput.value = task.points ?? 2;

  const noDate = String(task.no_date ?? "0") === "1" || !task.to_do_by;
  if (noDateCheckbox) noDateCheckbox.checked = noDate;

  if (dateInput) {
    if (noDate) {
      dateInput.value = "";
      dateInput.disabled = true;
    } else {
      dateInput.disabled = false;
      dateInput.value = task.to_do_by ?? "";
    }
  }

  // odpri modal
  showWindow("add_task_window");
  add_something_view.classList.add("active");
}

function openDetails(task) {
  const frame = document.getElementById("details_frame");
  if (!frame) return;

  const detailsEl = frame.querySelector(".details");

  // varno vstavljanje (NE innerHTML!)
  detailsEl.textContent = task.details?.trim() || "Ni podrobnosti.";

  // odpri kot window
  showWindow("details_frame");

  // če uporabljaš overlay
  document.getElementById("add_something_view")?.classList.add("active");
}