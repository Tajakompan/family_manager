document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("app_sidebar");
  const toggle = document.getElementById("sidebar_toggle");
  const mobileBreakpoint = window.matchMedia("(max-width: 1100px)");

  if (!sidebar || !toggle) return;

  function closeSidebar() {
    sidebar.classList.remove("sidebar-open");
    document.body.classList.remove("sidebar-mobile-open");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Odpri meni");
  }

  function openSidebar() {
    sidebar.classList.add("sidebar-open");
    document.body.classList.add("sidebar-mobile-open");
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Zapri meni");
  }

  function toggleSidebar() {
    if (sidebar.classList.contains("sidebar-open")) closeSidebar();
    else openSidebar();
  }

  toggle.addEventListener("click", function (e) {
    e.stopPropagation();
    if (!mobileBreakpoint.matches) return;
    toggleSidebar();
  });

  document.addEventListener("click", function (e) {
    if (!mobileBreakpoint.matches) return;
    if (!sidebar.classList.contains("sidebar-open")) return;
    if (sidebar.contains(e.target)) return;
    closeSidebar();
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") closeSidebar();
  });

  sidebar.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", function () {
      if (mobileBreakpoint.matches) closeSidebar();
    });
  });

  window.addEventListener("resize", function () {
    if (!mobileBreakpoint.matches) closeSidebar();
  });
});
