document.addEventListener("DOMContentLoaded", async () => {
  const info = document.getElementById("sidebar_user_info");
  if (!info) return;

  try {
    const res = await fetch("../entry/get_user.php", { credentials: "same-origin" });
    if (!res.ok) return;

    const data = await res.json();
    if (!data.ok || !data.user) return;

    const user = data.user;
    info.textContent = `${user.name} ${user.surname}`;
  } catch (err) {
    console.error("Sidebar user info load failed:", err);
  }
});
