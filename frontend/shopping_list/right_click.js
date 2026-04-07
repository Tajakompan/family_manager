document.addEventListener("DOMContentLoaded", function () {
  const pinsContainer = document.getElementById("pins_container");
  const navMenu = document.getElementById("nav_menu");
  const rowMenu = document.getElementById("row_menu");

  let selectedShopId = "";
  let selectedRowId = "";

  if (!pinsContainer || !navMenu || !rowMenu) return;
  
  function hideMenus() {
    navMenu.style.display = "none";
    rowMenu.style.display = "none";

    selectedShopId = "";
    selectedRowId = "";

    const activeTitles = pinsContainer.querySelectorAll(".context-active");
    const activeRows = pinsContainer.querySelectorAll(".context-active-row");

    for (let i = 0; i < activeTitles.length; i++) {
      activeTitles[i].classList.remove("context-active");
    }

    for (let i = 0; i < activeRows.length; i++) {
      activeRows[i].classList.remove("context-active-row");
    }
  }

  function positionMenu(menu, e) {
    menu.style.display = "flex";

    const menuWidth = menu.offsetWidth;
    const menuHeight = menu.offsetHeight;

    let x = e.clientX;
    let y = e.clientY;

    if (x + menuWidth > window.innerWidth) {
      x = window.innerWidth - menuWidth - 5;
    }

    if (y + menuHeight > window.innerHeight) {
      y = window.innerHeight - menuHeight - 5;
    }

    menu.style.left = x + "px";
    menu.style.top = y + "px";
  }

  pinsContainer.addEventListener("contextmenu", function (e) {
    const row = e.target.closest("tbody tr");

    if (row && row.dataset.rowId) {
      e.preventDefault();
      hideMenus();

      selectedRowId = row.dataset.rowId;
      row.classList.add("context-active-row");
      positionMenu(rowMenu, e);
      return;
    }

    const title = e.target.closest(".shop_name");

    if (!title) return;
    
    e.preventDefault();
    hideMenus();

    selectedShopId = title.dataset.shopId || "";

    if (!selectedShopId) {
      const pin = title.closest(".pin");

      if (pin) selectedShopId = pin.dataset.shopId || "";
    }
    if (!selectedShopId) return;
    
    title.classList.add("context-active");
    positionMenu(navMenu, e);
  });

  document.addEventListener("click", hideMenus);

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      hideMenus();
    }
  });

  window.addEventListener("scroll", hideMenus, { passive: true });
  window.addEventListener("resize", hideMenus);

  navMenu.addEventListener("contextmenu", function (e) {
    e.preventDefault();
  });

  rowMenu.addEventListener("contextmenu", function (e) {
    e.preventDefault();
  });

  navMenu.addEventListener("click", function (e) {
    e.stopPropagation();
  });

  rowMenu.addEventListener("click", function (e) {
    e.stopPropagation();
  });

  const deleteShopButton = navMenu.querySelector(".delete");

  if (deleteShopButton) {
    deleteShopButton.addEventListener("click", function () {
      if (!selectedShopId) return;
      if (!confirm("Ali si prepričan, da želiš izbrisati ta seznam? S tem boš iz seznama odstranil tudi vse izdelke.")) return;

      fetch("delete_shop.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "shop_id=" + encodeURIComponent(selectedShopId)
      }).then(function () {
        location.reload();
      });
    });
  }

  const deleteRowButton = rowMenu.querySelector(".delete");

  if (deleteRowButton) {
    deleteRowButton.addEventListener("click", function () {
        if (!selectedRowId) return;
        if (!confirm("Izbrišem ta zapis?")) return;
        
        fetch("delete_item_from_list.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "id=" + encodeURIComponent(selectedRowId)
        }).then(function () {
          location.reload();
        });
    });
  }
});
