function showWindow(id) {
  const windows = document.querySelectorAll(".window");
  for (let i = 0; i < windows.length; i++) {
    windows[i].classList.remove("active");
  }

  const windowElement = document.getElementById(id);
  if (windowElement) {
    windowElement.classList.add("active");
  }
}

function closeWindows() {
  const windows = document.querySelectorAll(".window");

  for (let i = 0; i < windows.length; i++) {
    windows[i].classList.remove("active");
  }
}

function setTaskFormMode(mode) {
  const taskWindow = document.getElementById("add_task_window");
  const form = document.getElementById("add_task_form");
  const submitButton = document.getElementById("add_new_task_btn");
  const pointsInput = form?.querySelector('input[name="points"]');
  const isChild = window.currentUserRole === "Otrok";

  if (!taskWindow || !form || !submitButton) return;
  
  if (mode === "edit") {
    taskWindow.querySelector(".title").textContent = "Uredi opravilo:";
    submitButton.textContent = "Posodobi";
    form.action = "update_task_in_db.php";
  } else {
    taskWindow.querySelector(".title").textContent = "Ustvari novo opravilo:";
    submitButton.textContent = "Dodaj";
    form.action = "add_task_in_db.php";
  }

  if (pointsInput) {
    if (isChild) {
      pointsInput.value = 2;
      pointsInput.readOnly = true;
      pointsInput.title = "Otrok ne more spreminjati tock opravila.";
    } else {
      pointsInput.readOnly = false;
      pointsInput.title = "";
      if (mode === "add" && pointsInput.value === "") {
        pointsInput.value = 2;
      }
    }
  }
}

function fillTaskForm(task) {
  const form = document.getElementById("add_task_form");
  if (!form) return;

  const taskData = task || {};
  const taskIdInput = form.querySelector('input[name="task_id"]');
  const nameInput = form.querySelector('input[name="new_task"]');
  const detailsInput = form.querySelector('[name="details"]');
  const dateInput = form.querySelector('input[name="to_do_by"]');
  const pointsInput = form.querySelector('input[name="points"]');
  const noDateCheckbox = form.querySelector('input[name="no_date"]');

  if (taskIdInput) taskIdInput.value = taskData.id || "";
  if (nameInput) {
    nameInput.value = taskData.name || "";
    nameInput.classList.remove("red");
  }
  if (detailsInput) detailsInput.value = taskData.details || "";
  if (pointsInput && window.currentUserRole !== "Otrok") pointsInput.value = taskData.points ?? 2;
  if (noDateCheckbox) noDateCheckbox.checked = Boolean(taskData.noDate);
  if (dateInput) {
    if (taskData.noDate) {
      dateInput.value = "";
      dateInput.disabled = true;
    } 
    else {
      dateInput.disabled = false;
      dateInput.value = taskData.date || "";
    }
    dateInput.classList.remove("red");
  }
}

function openTaskWindow(mode, task) {
  const addSomethingView = document.getElementById("add_something_view");

  setTaskFormMode(mode);
  fillTaskForm(task);
  showWindow("add_task_window");

  if (addSomethingView) addSomethingView.classList.add("active");
}

function openDetailsWindow(task) {
  const addSomethingView = document.getElementById("add_something_view");
  const frame = document.getElementById("details_frame");
  const details = frame?.querySelector(".details");
  if (!frame || !details) return;
  
  details.textContent = task?.details?.trim() || "Ni podrobnosti.";
  showWindow("details_frame");

  if (addSomethingView) addSomethingView.classList.add("active");
}

function closeTaskWindows() {
  const addSomethingView = document.getElementById("add_something_view");
  const form = document.getElementById("add_task_form");
  if (form) form.reset();

  closeWindows();

  if (addSomethingView) addSomethingView.classList.remove("active");
  
  setTaskFormMode("add");
  fillTaskForm({
    id: "",
    name: "",
    details: "",
    date: "",
    points: 2,
    noDate: false
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const addTaskButton = document.getElementById("add_task_btn");
  const cancelTaskButton = document.getElementById("cancel_task_btn");
  const addTaskWindow = document.getElementById("add_task_window");
  const detailsFrame = document.getElementById("details_frame");
  const addSomethingView = document.getElementById("add_something_view");

  setTaskFormMode("add");

  if (addTaskButton) {
    addTaskButton.addEventListener("click", function (e) {
      e.preventDefault();
      openTaskWindow("add", {
        id: "",
        name: "",
        details: "",
        date: "",
        points: 2,
        noDate: false
      });
    });
  }

  if (cancelTaskButton) {
    cancelTaskButton.addEventListener("click", function (e) {
      e.preventDefault();
      closeTaskWindows();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeTaskWindows();
  });

  if (addSomethingView) {
    addSomethingView.addEventListener("click", function () {
      closeTaskWindows();
    });
  }

  if (addTaskWindow) {
    addTaskWindow.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  if (detailsFrame) {
    detailsFrame.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }
});
