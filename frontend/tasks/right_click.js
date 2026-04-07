document.addEventListener("DOMContentLoaded", function () {
  const myTasksContainer = document.getElementById("my_tasks_container");
  const otherTasksContainer = document.getElementById("other_tasks_container");
  const container = document.getElementById("big_container");
  const rowMenu = document.getElementById("row_menu");

  let selectedTaskId = "";
  let selectedPin = null;

  if (!myTasksContainer || !otherTasksContainer || !container || !rowMenu) return;

  function hideMenu() {
    rowMenu.style.display = "none";
    selectedTaskId = "";

    if (selectedPin) {
      selectedPin.classList.remove("context-active");
      selectedPin = null;
    }
  }

  function getTaskFormData(task) {
    return {
      id: task.id ?? "",
      name: task.name ?? "",
      details: task.details ?? "",
      date: task.to_do_by ?? "",
      points: task.points ?? 2,
      noDate: String(task.no_date ?? "0") === "1" || !task.to_do_by
    };
  }

  container.addEventListener("contextmenu", function (e) {
      const pin = e.target.closest(".pin");
      if (!pin) return;

      e.preventDefault();
      hideMenu();

      selectedTaskId = pin.dataset.pinId || "";

      if (!selectedTaskId) return;

      selectedPin = pin;
      selectedPin.classList.add("context-active");

      if (typeof positionMenu === "function") {
        positionMenu(rowMenu, e);
      } 
      else {
        rowMenu.style.display = "flex";
        rowMenu.style.left = e.clientX + "px";
        rowMenu.style.top = e.clientY + "px";
      }
  });

  document.addEventListener("click", hideMenu);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        hideMenu();
    }
  });

  window.addEventListener("scroll", hideMenu, { passive: true });
  window.addEventListener("resize", hideMenu);

  rowMenu.addEventListener("click", function (e) {
    e.stopPropagation();
  });

  rowMenu.addEventListener("contextmenu", function (e) {
    e.preventDefault();
  });

  rowMenu.querySelector(".delete")?.addEventListener("click", function () {
    if (!selectedTaskId) return;

    if (!confirm("Ali si prepričan, da želiš izbrisati to opravilo?")) return;
    
    fetch("delete_pin.php", {
      method: "POST",
      headers: {
          "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "task_id=" + encodeURIComponent(selectedTaskId)
    }).then(function () {
      location.reload();
    });
  });

  rowMenu.querySelector(".edit")?.addEventListener("click", async function () {
    if (!selectedTaskId) return;

    try {
      const response = await fetch("get_task.php?task_id=" + encodeURIComponent(selectedTaskId));
      const data = await response.json();

      if (!data.ok || !data.task) {
        alert("Napaka pri nalaganju opravila");
        return;
      }

      hideMenu();
      openTaskWindow("edit", getTaskFormData(data.task));
    } 
    catch (error) {
      console.error(error);
      alert("Napaka pri povezavi s strežnikom");
    }
  });

  rowMenu.querySelector(".details")?.addEventListener("click", async function () {
    if (!selectedTaskId) return;

    try {
      const response = await fetch("get_task.php?task_id=" + encodeURIComponent(selectedTaskId));
      const data = await response.json();

      if (!data.ok || !data.task) {
        alert("Napaka pri nalaganju opravila");
        return;
      }

      hideMenu();
      openDetailsWindow(data.task);
    } 
    catch (error) {
      console.error(error);
      alert("Napaka pri povezavi s strežnikom");
    }
  });
});
