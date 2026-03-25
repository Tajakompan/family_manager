async function loadTaskHistory() {
  const container = document.getElementById("task_history_list");
  if (!container) return;

  const res = await fetch("get_task_history.php", {
    method: "GET",
    headers: { "Accept": "application/json" },
    credentials: "same-origin"
  });

  if (!res.ok) {
    container.innerHTML = `<div class="history_empty">Napaka (${res.status})</div>`;
    return;
  }

  const data = await res.json();

  if (!Array.isArray(data) || data.length === 0) {
    container.innerHTML = `<div class="history_empty">Ni opravil v zadnjih 7 dneh.</div>`;
    return;
  }

  container.innerHTML = "";

  for (const user of data) {
    const block = document.createElement("div");
    block.className = "history_user";

    const title = document.createElement("div");
    title.className = "history_user_name";
    title.textContent = user.name ?? "";

    const list = document.createElement("ul");
    list.className = "history_tasks";

    for (const task of user.tasks ?? []) {
      const li = document.createElement("li");
      li.textContent = task.task_name ?? "";
      list.appendChild(li);
    }

    block.appendChild(title);
    block.appendChild(list);
    container.appendChild(block);
  }
}
