function formatTaskDate(date) {
  if (!date) return "~ ni časovne omejitve ~";

  const [year, month, day] = String(date).split("-");
  if (!year || !month || !day) return date;

  return `${Number(day)}. ${Number(month)}. ${year}`;
}


function createTaskPin(task, mode) {
  const template = document.getElementById("pin_template");
  if (!template) return null;

  const clone = template.content.cloneNode(true);
  const pin = clone.querySelector(".pin");
  const title = clone.querySelector(".task_title");
  const createdBy = clone.querySelector(".created_by");
  const toDoBy = clone.querySelector(".to_do_by");
  const points = clone.querySelector(".points");
  const doers = clone.querySelector(".doers");
  const button = clone.querySelector(".action_btn");
  const icon = clone.querySelector(".icon_img");

  if (pin) pin.dataset.pinId = task.id ?? "";
  if (title) title.textContent = task.name ?? "";
  if (createdBy) createdBy.textContent = task.created_by ?? "";
  if (toDoBy) toDoBy.textContent = formatTaskDate(task.to_do_by);
  if (points) points.textContent = task.points ?? "";
  if (doers) doers.textContent = task.doers || " -";
  if (button && icon) {
    if (mode === "my") {
      icon.src = "../img/done_all_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg";
      icon.alt = "Označi kot opravljeno";
      button.title = "Označi kot opravljeno";

      button.addEventListener("click", async function () {
          await markDone(task.id);
          await loadAll();
      });
    } 
    else {
      icon.src = "../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg";
      icon.alt = "Prevzemi opravilo";
      button.title = "Prevzemi opravilo";

      button.addEventListener("click", async function () {
          await claimTask(task.id);
          await loadAll();
      });
    }
  }

  return clone;
}

async function fetchJson(url) {
  const response = await fetch(url, {
      credentials: "same-origin"
  });

  if (!response.ok) {
      throw new Error(url + " HTTP " + response.status);
  }
  return response.json();
}

async function loadTaskList(containerId, url, mode, emptyMessage) {
  const container = document.getElementById(containerId);
  if (!container) return;

  container.innerHTML = "";

  const tasks = await fetchJson(url);

  if (!Array.isArray(tasks) || tasks.length === 0) {
    container.innerHTML ="<p style='color:white;opacity:.8; margin-left: 15px'>" + emptyMessage + "</p>";
    return;
  }

  for (let i = 0; i < tasks.length; i++) {
      const pin = createTaskPin(tasks[i], mode);
      if (pin) container.appendChild(pin);
  }
}

async function loadMyTasks() {
  await loadTaskList("my_tasks_container", "get_my_tasks.php", "my", "Ni mojih opravil.");
}

async function loadOtherTasks() {
  await loadTaskList("other_tasks_container", "get_other_tasks.php", "other", "Ni ostalih opravil.");
}

async function loadAll() {
  await Promise.all([
      loadMyTasks(),
      loadOtherTasks(),
      loadPoints(),
      loadTaskHistory()
  ]);
}

async function markDone(taskId) {
  const response = await fetch("mark_task_done.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: new URLSearchParams({
        task_id: taskId
    }),
    credentials: "same-origin"
  });

  if (!response.ok) {
    throw new Error("mark_task_done.php HTTP " + response.status);
  }
}

async function claimTask(taskId) {
  const response = await fetch("claim_task.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded"
    },
    body: new URLSearchParams({
        task_id: taskId
    }),
    credentials: "same-origin"
  });

  if (!response.ok) {
    throw new Error("claim_task.php HTTP " + response.status);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  loadAll().catch(function (error) {
    console.error(error);
  });
});
