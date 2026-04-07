function fetchUserCardJson(url) {
  return fetch(url, { credentials: "same-origin" })
    .then((response) => {
      if (!response.ok) return null;
      return response.json().catch(() => null);
    })
    .catch(() => null);
}

document.addEventListener("DOMContentLoaded", async () => {
  const profileName = document.getElementById("profile_name");
  const profileRole = document.getElementById("profile_role");
  const profileEmail = document.getElementById("profile_email");
  const avatar = document.querySelector(".avatar");

  const result = await fetchUserCardJson("../entry/get_user.php");
  if (!result || !result.ok || !result.user) return;

  const user = result.user;

  if (profileName) {
    profileName.textContent = `${user.name} ${user.surname}`;
  }

  if (profileRole) {
    profileRole.textContent = user.user_role_name ?? "";
  }

  if (profileEmail) {
    profileEmail.textContent = user.email ?? "";
  }

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
});
