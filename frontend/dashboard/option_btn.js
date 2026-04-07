function fetchProfileJson(url, options = {}) {
  return fetch(url, { credentials: "same-origin", ...options })
    .then((response) => {
      if (!response.ok) return null;
      return response.json().catch(() => null);
    })
    .catch(() => null);
}

function positionMenu(menu, event) {
  menu.style.display = "flex";

  const width = menu.offsetWidth;
  const height = menu.offsetHeight;

  let left = event.clientX;
  let top = event.clientY;

  if (left + width > window.innerWidth) {
    left = window.innerWidth - width - 5;
  }

  if (top + height > window.innerHeight) {
    top = window.innerHeight - height - 5;
  }

  menu.style.left = `${left}px`;
  menu.style.top = `${top}px`;
}

document.addEventListener("DOMContentLoaded", () => {
  const rowMenu = document.getElementById("row_menu");
  const optionBtn = document.getElementById("option_btn");
  const editDataBtn = document.getElementById("edit_data");
  const deleteUserBtn = document.getElementById("delete_user");
  const changeImageBtn = document.getElementById("change_image");

  const overlay = document.getElementById("add_something_view");

  const updateUserWindow = document.getElementById("update_user_window");
  const updateUserForm = document.getElementById("update_user_form");
  const cancelUpdateUserBtn = document.getElementById("cancel_update_user_btn");
  const passwordError = document.getElementById("update_user_password_error");

  const uploadImageWindow = document.getElementById("upload_image_window");
  const cancelUploadImageBtn = document.getElementById("cancel_upload_image_btn");
  const uploadImageError = document.getElementById("upload_image_error");

  const profileName = document.getElementById("profile_name");
  const profileEmail = document.getElementById("profile_email");
  const greetingName = document.querySelector(".title_left h2 span");

  function hideMenus() {
    if (rowMenu) rowMenu.style.display = "none";
  }

  function setPasswordError(message = "") {
    if (!passwordError) return;
    passwordError.textContent = message;
    passwordError.hidden = message === "";
  }

  function setUploadImageError(message = "") {
    if (!uploadImageError) return;
    uploadImageError.textContent = message;
    uploadImageError.hidden = message === "";
  }

  function getUpdateUserErrorMessage(errorCode) {
    if (errorCode === "required_fields") return "Vsa polja razen gesla so obvezna.";
    if (errorCode === "invalid_email") return "E-mail ni v veljavnem formatu.";
    if (errorCode === "future_birthdate") return "Datum rojstva ne more biti v prihodnosti.";
    if (errorCode === "email_taken") return "Ta e-mail ze uporablja drug uporabnik.";
    if (errorCode === "password_mismatch") return "Gesli se ne ujemata.";
    if (errorCode === "password_too_short") return "Geslo mora imeti vsaj 8 znakov.";
    return "Posodobitev ni uspela. Poskusi znova.";
  }

  function getUploadImageErrorMessage(errorCode) {
    if (errorCode === "missing_file") return "Datoteka ni bila izbrana.";
    if (errorCode === "file_too_large") return "Slika je prevelika.";
    if (errorCode === "invalid_type") return "Dovoljene so samo JPG, PNG in WEBP slike.";
    if (errorCode === "read_failed") return "Branje datoteke ni uspelo.";
    if (errorCode === "save_failed") return "Shranjevanje slike ni uspelo.";
    return "Nalaganje slike ni uspelo.";
  }

  function refreshProfileSummary(user) {
    if (!user) return;

    if (profileName) profileName.textContent = `${user.name} ${user.surname}`;
    if (profileEmail) profileEmail.textContent = user.email;
    if (greetingName) greetingName.textContent = user.name;
  }

  function openUpdateUserWindow() {
    overlay?.classList.add("active");
    updateUserWindow?.classList.add("active");
  }

  function closeUpdateUserWindow() {
    updateUserWindow?.classList.remove("active");
    if (!uploadImageWindow?.classList.contains("active")) {
      overlay?.classList.remove("active");
    }
  }

  function openUploadImageWindow() {
    overlay?.classList.add("active");
    uploadImageWindow?.classList.add("active");
  }

  function closeUploadImageWindow() {
    uploadImageWindow?.classList.remove("active");
    if (!updateUserWindow?.classList.contains("active")) {
      overlay?.classList.remove("active");
    }
  }

  async function prefillUpdateUserForm() {
    if (!updateUserForm) return;

    const result = await fetchProfileJson("../entry/get_user.php");
    if (!result || !result.ok || !result.user) return;

    const user = result.user;

    const nameInput = updateUserForm.querySelector('input[name="name"]');
    const surnameInput = updateUserForm.querySelector('input[name="surname"]');
    const emailInput = updateUserForm.querySelector('input[name="email"]');
    const birthdateInput = updateUserForm.querySelector('input[name="birthdate"]');
    const passwordInput1 = updateUserForm.querySelector('input[name="password_1"]');
    const passwordInput2 = updateUserForm.querySelector('input[name="password_2"]');

    if (nameInput) nameInput.value = user.name ?? "";
    if (surnameInput) surnameInput.value = user.surname ?? "";
    if (emailInput) emailInput.value = user.email ?? "";
    if (birthdateInput) birthdateInput.value = user.birthdate ?? "";
    if (passwordInput1) passwordInput1.value = "";
    if (passwordInput2) passwordInput2.value = "";

    setPasswordError("");
  }

  async function restoreUpdateUserErrorState() {
    const params = new URLSearchParams(window.location.search);
    const error = params.get("update_user_error");
    if (!error) return;

    await prefillUpdateUserForm();
    setPasswordError(getUpdateUserErrorMessage(error));
    openUpdateUserWindow();

    params.delete("update_user_error");
    const query = params.toString();
    const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}${window.location.hash}`;
    window.history.replaceState({}, "", nextUrl);
  }

  function restoreUploadImageErrorState() {
    const params = new URLSearchParams(window.location.search);
    const error = params.get("upload_image_error");
    if (!error) return;

    setUploadImageError(getUploadImageErrorMessage(error));
    openUploadImageWindow();

    params.delete("upload_image_error");
    const query = params.toString();
    const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}${window.location.hash}`;
    window.history.replaceState({}, "", nextUrl);
  }

  optionBtn?.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (rowMenu) {
      positionMenu(rowMenu, event);
    }
  });

  rowMenu?.addEventListener("click", (event) => {
    event.stopPropagation();
  });

  document.addEventListener("click", hideMenus);
  window.addEventListener("resize", hideMenus);
  window.addEventListener("scroll", hideMenus, { passive: true });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      hideMenus();
      closeUpdateUserWindow();
      closeUploadImageWindow();
    }
  });

  editDataBtn?.addEventListener("click", async () => {
    hideMenus();
    await prefillUpdateUserForm();
    openUpdateUserWindow();
  });

  cancelUpdateUserBtn?.addEventListener("click", () => {
    closeUpdateUserWindow();
  });

  changeImageBtn?.addEventListener("click", () => {
    hideMenus();
    setUploadImageError("");
    openUploadImageWindow();
  });

  cancelUploadImageBtn?.addEventListener("click", () => {
    setUploadImageError("");
    closeUploadImageWindow();
  });

  overlay?.addEventListener("click", (event) => {
    if (event.target === overlay) {
      closeUpdateUserWindow();
      closeUploadImageWindow();
    }
  });

  updateUserForm?.addEventListener("submit", async (event) => {
    event.preventDefault();
    setPasswordError("");

    const submitButton = updateUserForm.querySelector('button[type="submit"]');
    if(submitButton) submitButton.disabled = true;

    const result = await fetchProfileJson(updateUserForm.action, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new FormData(updateUserForm)
    });

    if (!result || !result.ok) {
      const errorCode = result?.error ?? "";
      setPasswordError(getUpdateUserErrorMessage(errorCode));
      openUpdateUserWindow();

      if (submitButton) submitButton.disabled = false;
      
      return;
    }

    refreshProfileSummary(result.user);
    closeUpdateUserWindow();

    if (submitButton) submitButton.disabled = false;
  });

  deleteUserBtn?.addEventListener("click", () => {
    hideMenus();

    const confirmed = window.confirm(
      "Ali si prepričan, da želiš izbrisati svoj uporabniški račun? S tem ga boš za vedno izgubil, z njim pa tudi vse svoje podatke, ki si jih prispeval v družino."
    );

    if (confirmed) {
      window.location.href = "../entry/delete_app_user.php";
    }
  });

  restoreUpdateUserErrorState();
  restoreUploadImageErrorState();
});
