function escapeHtml(s) {
  return String(s)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

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
