const myContainer = document.getElementById("my_tasks_container");
const otherContainer = document.getElementById("other_tasks_container");
const temp = document.getElementById("pin_template");

function fmtDate(d) {
  if (!d) return "~ ni časovne omejitve ~";
  return d;
}

function makePin(task, mode) { //mode je my ali other
    const clone = temp.content.cloneNode(true);
    const pin = clone.querySelector(".pin");
    pin.dataset.pinId = task.id ?? 0;
    pin.dataset.task = JSON.stringify(task);

    clone.querySelector(".task_title").textContent = task.name ?? "";
    clone.querySelector(".created_by").textContent = task.created_by ?? "";
    clone.querySelector(".to_do_by").textContent = fmtDate(task.to_do_by);
    clone.querySelector(".points").textContent = task.points ?? "";

    const doers = clone.querySelector(".doers");
    doers.textContent = task.doers || " —";
  
    const btn = clone.querySelector(".action_btn");
    const icon = clone.querySelector(".icon_img");

    if (mode === "my") {
        icon.src = "../img/done_all_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg";
        icon.alt = "Označi kot opravljeno";
        btn.title = "Označi kot opravljeno";
        btn.addEventListener("click", async () => {
            await markDone(task.id);
            await loadAll();
        });
    } else {
        icon.src = "../img/add_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg";
        icon.alt = "Prevzemi opravilo";
        btn.title = "Prevzemi opravilo";
        btn.addEventListener("click", async () => {
            await claimTask(task.id);
            await loadAll();
        });
    }
    return clone;
}

async function fetchJson(url) {
  const res = await fetch(url, { credentials: "same-origin" });
  if (!res.ok) throw new Error(url + " HTTP " + res.status);
  return await res.json();
}

async function loadMyTasks() {
  myContainer.innerHTML = "";
  const tasks = await fetchJson("get_my_tasks.php");

  if (!tasks.length) {
    myContainer.innerHTML = "<p style='color:white;opacity:.8; margin-left: 15px'>Ni mojih opravil.</p>";
    return;
  }

  for (const t of tasks) {
    myContainer.appendChild(makePin(t, "my"));
  }
}

async function loadOtherTasks() {
  otherContainer.innerHTML = "";
  const tasks = await fetchJson("get_other_tasks.php");

  if (!tasks.length) {
    otherContainer.innerHTML = "<p style='color:white;opacity:.8; margin-left: 15px'>Ni ostalih opravil.</p>";
    return;
  }

  for (const t of tasks) {
    otherContainer.appendChild(makePin(t, "other"));
  }
}

async function loadAll() {
  await Promise.all([loadMyTasks(), loadOtherTasks(), loadPoints()]);
}


async function markDone(taskId) {
  const res = await fetch("mark_task_done.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ task_id: taskId }),
    credentials: "same-origin"
  });
  if (!res.ok) throw new Error("mark_task_done.php HTTP " + res.status);
}

async function claimTask(taskId) {
  const res = await fetch("claim_task.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ task_id: taskId }),
    credentials: "same-origin"
  });
  if (!res.ok) throw new Error("claim_task.php HTTP " + res.status);
}

document.addEventListener("DOMContentLoaded", () => {
  loadAll().catch(err => console.error(err));
});

