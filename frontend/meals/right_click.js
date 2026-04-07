document.addEventListener("DOMContentLoaded", function () {
  const table = document.getElementById("table_1");
  const rowMenu = document.getElementById("row_menu");

  let selectedMealId = "";
  let selectedMealElement = null;

  if (!table || !rowMenu) return;

  function hideMenu() {
    rowMenu.style.display = "none";
    selectedMealId = "";

    if (selectedMealElement) {
      selectedMealElement.classList.remove("context-active");
      selectedMealElement = null;
    }
  }

  function positionMenu(event) {
    rowMenu.style.display = "flex";

    const menuWidth = rowMenu.offsetWidth;
    const menuHeight = rowMenu.offsetHeight;

    let x = event.clientX;
    let y = event.clientY;

    if (x + menuWidth > window.innerWidth) {
      x = window.innerWidth - menuWidth - 5;
    }

    if (y + menuHeight > window.innerHeight) {
      y = window.innerHeight - menuHeight - 5;
    }

    rowMenu.style.left = x + "px";
    rowMenu.style.top = y + "px";
  }

  table.addEventListener("contextmenu", function (event) {
    const clickedMeal = event.target.closest(".has_meal");

    if (!clickedMeal) return;

    event.preventDefault();
    event.stopPropagation();

    hideMenu();

    selectedMealId = clickedMeal.dataset.mealId || "";
    selectedMealElement = clickedMeal;

    if (!selectedMealId) {
      hideMenu();
      return;
    }

    selectedMealElement.classList.add("context-active");
    positionMenu(event);
  });

  document.addEventListener("click", function () {
    hideMenu();
  });

  document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        hideMenu();
      }
  });

  window.addEventListener("scroll", hideMenu, { passive: true });
  window.addEventListener("resize", hideMenu);

  rowMenu.addEventListener("click", function (event) {
    event.stopPropagation();
  });

  rowMenu.addEventListener("contextmenu", function (event) {
    event.preventDefault();
  });

  const deleteButton = rowMenu.querySelector(".delete");

  if (deleteButton) {
    deleteButton.addEventListener("click", function () {
      if (!selectedMealId) return;
      if (!confirm("Izbrišem ta obrok?")) return;
      
      fetch("delete_meal.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "meal_id=" + encodeURIComponent(selectedMealId)
      }).then(function () {
        location.reload();
      });
    });
  }

  const editButton = rowMenu.querySelector(".edit");
  if(!editButton) return;
  
  editButton.addEventListener("click", function () {
    if (!selectedMealId) return;

    fetch("get_meal.php?meal_id=" + encodeURIComponent(selectedMealId))
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (!data.ok || !data.meal) {
         alert("Napaka pri nalaganju obroka");
          return;
        }

        hideMenu();

        openMealWindow("edit", {
          name: data.meal.name || "",
          date: data.meal.date || "",
          type: data.meal.meal_category || "",
          id: data.meal.id || ""
        });
      })
      .catch(function (error) {
        console.error(error);
        alert("Napaka pri povezavi s strežnikom");
      });
  });
});
