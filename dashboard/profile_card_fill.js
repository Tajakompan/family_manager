document.addEventListener("DOMContentLoaded", async () => {
  const profile_name = document.getElementById("profile_name");
  const profile_role = document.getElementById("profile_role");
  const profile_email = document.getElementById("profile_email");
  const avatar = document.querySelector(".avatar");

  const roleLabels = {
    child: "Otrok",
    adult: "Odrasel",
    parent: "Starš"
  };

  try {
    const res = await fetchJson("../entry/get_user.php");
    if (!res.ok || !res.user) return;

    const user = res.user;
    profile_name.textContent = user.name + " " + user.surname;
    profile_role.textContent = roleLabels[user.user_role_name] ?? user.user_role_name;
    profile_email.textContent = user.email;
    if (avatar) {
      if (user.profile_image) {
        avatar.style.backgroundImage = `url("${user.profile_image}")`;
        avatar.style.backgroundSize = "cover";
        avatar.style.backgroundPosition = "center";
        avatar.style.backgroundRepeat = "no-repeat";
      } else {
        avatar.style.backgroundImage = "";
      }
    }

  } catch (err) {
    console.error("Profile card load failed:", err);
  }
});
