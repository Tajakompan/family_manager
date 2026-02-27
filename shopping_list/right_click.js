document.addEventListener("DOMContentLoaded", () => {
  const pinsContainer = document.getElementById("pins_container");
  const row_menu = document.getElementById("row_menu"); // Uredi/Izbriši zapis

  let rightClickedShopId = null;
  let rightClickedRowId = null;

  if (!pinsContainer || !nav_menu || !row_menu) return;

  function hideMenus() {
    row_menu.style.display = "none";
    pinsContainer.querySelectorAll(".context-active").forEach(el => el.classList.remove("context-active"));
    pinsContainer.querySelectorAll(".context-active-row").forEach(el => el.classList.remove("context-active-row"));
  }

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

  // ✅ SHOP MENU: SAMO če je desni klik direktno na .shop_name
  pinsContainer.addEventListener("contextmenu", (e) => {
    const title = e.target.closest(".shop_name");
    if (!title) return;

    // če si kliknila na vrstico (tbody tr), naj to NE odpre shop menija
    if (e.target.closest("tbody tr")) return;

    e.preventDefault();
    hideMenus();

    rightClickedShopId = title.dataset.shopId || title.closest(".pin")?.dataset.shopId;
    if (!rightClickedShopId) return;

    title.classList.add("context-active");
    positionMenu(nav_menu, e);
  });

  // ✅ ROW MENU: samo če je desni klik na tbody tr
  pinsContainer.addEventListener("contextmenu", (e) => {
    const row = e.target.closest("tbody tr");
    if (!row || !row.dataset.rowId) return;

    e.preventDefault();
    hideMenus();

    rightClickedRowId = row.dataset.rowId;
    rightClickedShopId = row.dataset.shopId;
    row.classList.add("context-active-row");
    positionMenu(row_menu, e);
  });

  // zapiranje
  document.addEventListener("click", hideMenus);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape") hideMenus(); });
  window.addEventListener("scroll", hideMenus, { passive: true });
  window.addEventListener("resize", hideMenus);

  // da se ne odpre browser default meni na meniju
  nav_menu.addEventListener("contextmenu", (e) => e.preventDefault());
  row_menu.addEventListener("contextmenu", (e) => e.preventDefault());

  // klik v meniju naj ne zapre
  nav_menu.addEventListener("click", (e) => e.stopPropagation());
  row_menu.addEventListener("click", (e) => e.stopPropagation());

  // SHOP delete
  nav_menu.querySelector(".delete")?.addEventListener("click", () => {
    if (!rightClickedShopId) return;
    if (!confirm("Ali si prepričan, da želiš izbrisati ta seznam? S tem boš iz seznama odstranil tudi vse izdelke.")) return;

    fetch("delete_shop.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `shop_id=${encodeURIComponent(rightClickedShopId)}`
    }).then(() => location.reload());
  });

  // ROW delete 

  row_menu.querySelector(".delete")?.addEventListener("click", () => {
    if (!rightClickedRowId) return;
    if (!confirm("Izbrišem ta zapis?")) return;

    fetch("delete_item_from_list.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `id=${encodeURIComponent(rightClickedRowId)}`
    }).then(() => location.reload());
  });
});
