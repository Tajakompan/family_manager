async function loadTaskHistory() {
  const container = document.getElementById("task_history_list");
  if (!container) return;

  const response = await fetch("get_task_history.php", {
    method: "GET",
    headers: {
        Accept: "application/json"
    },
    credentials: "same-origin"
  });

  if (!response.ok) {
    container.innerHTML = '<div class="history_empty">Napaka (' + response.status + ")</div>";
    return;
  }

  const data = await response.json();

  if (!Array.isArray(data) || data.length === 0) {
    container.innerHTML = '<div class="history_empty">Ni opravil v zadnjih 7 dneh.</div>';
    return;
  }

  container.innerHTML = "";

  for (let i = 0; i < data.length; i++) {
    const user = data[i];
    const block = document.createElement("div");
    const title = document.createElement("div");
    const list = document.createElement("ul");
    const tasks = user.tasks || [];

    block.className = "history_user";
    title.className = "history_user_name";
    list.className = "history_tasks";

    title.textContent = user.name ?? "";

    for (let j = 0; j < tasks.length; j++) {
      const li = document.createElement("li");
      li.textContent = tasks[j].task_name ?? "";
      list.appendChild(li);
    }

    block.appendChild(title);
    block.appendChild(list);
    container.appendChild(block);
  }
}
