async function fetchJson(url, options = {}) {
  const res = await fetch(url, { credentials: "same-origin", ...options });
  if (!res.ok) throw new Error(url + " HTTP " + res.status);
  return await res.json();
}

function roleLabel(roleId) {
  switch (Number(roleId)) {
    case 1:
      return "Starsev - admin";
    case 2:
      return "Odrasel";
    case 3:
      return "Otrok";
    default:
      return String(roleId ?? "");
  }
}

function text(value) {
  return value ?? "";
}

function confirmDelete(message) {
  return window.confirm(message);
}

const state = {
  family: null,
  users: [],
  selectedUserId: null,
  selectedPointsUserId: null,
};

function setFamilyError(message = "") {
  const familyError = document.getElementById("update_family_error");
  if (!familyError) return;

  familyError.textContent = message;
  familyError.hidden = message === "";
}

function setPasswordError(message = "") {
  const passwordError = document.getElementById("update_user_password_error");
  if (!passwordError) return;

  passwordError.textContent = message;
  passwordError.hidden = message === "";
}

function setPointsError(message = "") {
  const pointsError = document.getElementById("update_points_error");
  if (!pointsError) return;

  pointsError.textContent = message;
  pointsError.hidden = message === "";
}

function getUpdateFamilyErrorMessage(errorCode) {
  switch (errorCode) {
    case "missing_family":
      return "Druzina ne obstaja vec.";
    case "required_fields":
      return "Ime in koda druzine sta obvezna.";
    case "code_taken":
      return "Ta koda druzine je ze zasedena.";
    default:
      return "Posodobitev druzine ni uspela. Poskusi znova.";
  }
}

function getUpdateUserErrorMessage(errorCode) {
  switch (errorCode) {
    case "required_fields":
      return "Vsa polja razen gesla so obvezna.";
    case "invalid_email":
      return "E-mail ni v veljavnem formatu.";
    case "future_birthdate":
      return "Datum rojstva ne more biti v prihodnosti.";
    case "email_taken":
      return "Ta e-mail ze uporablja drug uporabnik.";
    case "password_mismatch":
      return "Gesli se ne ujemata.";
    case "password_too_short":
      return "Geslo mora imeti vsaj 8 znakov.";
    case "missing_user":
      return "Izbrani uporabnik ne obstaja vec.";
    default:
      return "Posodobitev ni uspela. Poskusi znova.";
  }
}

function getUpdatePointsErrorMessage(errorCode) {
  switch (errorCode) {
    case "missing_user":
      return "Izbrani uporabnik ne obstaja vec.";
    case "invalid_points":
      return "Vnesi veljavno stevilo tock 0 ali vec.";
    default:
      return "Posodobitev tock ni uspela. Poskusi znova.";
  }
}

function openOverlay() {
  document.getElementById("add_something_view")?.classList.add("active");
}

function closeOverlay() {
  document.getElementById("add_something_view")?.classList.remove("active");
}

function openUpdateFamilyWindow() {
  openOverlay();
  document.getElementById("update_family_window")?.classList.add("active");
}

function closeUpdateFamilyWindow() {
  document.getElementById("update_family_window")?.classList.remove("active");
}

function openUpdateUserWindow() {
  openOverlay();
  document.getElementById("update_user_window")?.classList.add("active");
}

function closeUpdateUserWindow() {
  document.getElementById("update_user_window")?.classList.remove("active");
}

function openUpdatePointsWindow() {
  openOverlay();
  document.getElementById("update_points_window")?.classList.add("active");
}

function closeUpdatePointsWindow() {
  document.getElementById("update_points_window")?.classList.remove("active");
}

function closeAllWindows() {
  closeUpdateFamilyWindow();
  closeUpdateUserWindow();
  closeUpdatePointsWindow();
  closeOverlay();
}

function getUserById(userId) {
  return state.users.find((user) => Number(user.id) === Number(userId)) ?? null;
}

function prefillUpdateFamilyForm(family) {
  const form = document.getElementById("update_family_form");
  if (!form || !family) return;

  const idInput = form.querySelector('input[name="family_id"]');
  const nameInput = form.querySelector('input[name="name"]');
  const codeInput = form.querySelector('input[name="code"]');

  if (idInput) idInput.value = family.id ?? "";
  if (nameInput) nameInput.value = family.name ?? "";
  if (codeInput) codeInput.value = family.code ?? "";
  setFamilyError("");
}

function prefillUpdateUserForm(user) {
  const form = document.getElementById("update_user_form");
  if (!form || !user) return;

  state.selectedUserId = Number(user.id);

  const idInput = form.querySelector('input[name="user_id"]');
  const nameInput = form.querySelector('input[name="name"]');
  const surnameInput = form.querySelector('input[name="surname"]');
  const emailInput = form.querySelector('input[name="email"]');
  const birthdateInput = form.querySelector('input[name="birthdate"]');
  const roleInput = form.querySelector('select[name="role"]');
  const passwordInput1 = form.querySelector('input[name="password_1"]');
  const passwordInput2 = form.querySelector('input[name="password_2"]');

  if (idInput) idInput.value = user.id ?? "";
  if (nameInput) nameInput.value = user.name ?? "";
  if (surnameInput) surnameInput.value = user.surname ?? "";
  if (emailInput) emailInput.value = user.email ?? "";
  if (birthdateInput) birthdateInput.value = user.birthdate ?? "";
  if (roleInput) roleInput.value = String(user.user_role_id ?? "");
  if (passwordInput1) passwordInput1.value = "";
  if (passwordInput2) passwordInput2.value = "";
  setPasswordError("");
}

function prefillUpdatePointsForm(user) {
  const form = document.getElementById("update_points_form");
  if (!form || !user) return;

  state.selectedPointsUserId = Number(user.id);

  const idInput = form.querySelector('input[name="user_id"]');
  const pointsInput = form.querySelector('input[name="points"]');
  const title = document.getElementById("update_points_user_name");

  if (idInput) idInput.value = user.id ?? "";
  if (pointsInput) pointsInput.value = Number(user.user_points ?? 0);
  if (title) title.textContent = user.name ?? "";
  setPointsError("");
}

function updateFamilyInState(updatedFamily) {
  state.family = { ...state.family, ...updatedFamily };
}

function updateUserInState(updatedUser) {
  state.users = state.users.map((user) => {
    if (Number(user.id) !== Number(updatedUser.id)) return user;
    return { ...user, ...updatedUser };
  });
}

function updateUserPointsInState(userId, points) {
  state.users = state.users.map((user) => {
    if (Number(user.id) !== Number(userId)) return user;
    return { ...user, user_points: Number(points) };
  });
}

function renderFamilyInfo(family) {
  const familyInfo = document.getElementById("family_info");
  const template = document.getElementById("family_info_template");
  if (!familyInfo || !template || !family) return;

  const row = template.content.querySelector("tr")?.cloneNode(true);
  if (!row) return;

  row.dataset.id = family.id ?? 0;

  const cells = row.querySelectorAll("td");
  cells[0].textContent = text(family.name);
  cells[1].textContent = text(family.code);
  cells[2].innerHTML = "<div class='btn'>Uredi</div>";
  cells[3].innerHTML = "<div class='btn warning_btn'>Izbrisi</div>";

  const editButton = cells[2].querySelector(".btn");
  editButton?.addEventListener("click", () => {
    prefillUpdateFamilyForm(family);
    openUpdateFamilyWindow();
  });

  const deleteButton = cells[3].querySelector(".warning_btn");
  deleteButton?.addEventListener("click", () => {
    if (!confirmDelete("Ali si preprican, da zelis izbrisati druzino?")) return;
    window.location.href = "../entry/delete_family.php";
  });

  familyInfo.replaceChildren(row);
}

function createUserRow(user, template) {
  const row = template.content.querySelector("tr")?.cloneNode(true);
  if (!row) return null;

  row.dataset.id = user.id ?? 0;

  const cells = row.querySelectorAll("td");
  cells[0].innerHTML = text(user.name);
  cells[1].innerHTML = text(user.surname);
  cells[2].innerHTML = text(user.birthdate);
  cells[3].innerHTML = text(user.email);
  cells[4].innerHTML = text(user.user_role_name);
  cells[5].innerHTML = "<div class='btn'>Uredi</div>";
  cells[6].innerHTML = "<div class='btn warning_btn'>Izbrisi</div>";

  const editButton = cells[5].querySelector(".btn");
  editButton?.addEventListener("click", () => {
    prefillUpdateUserForm(user);
    openUpdateUserWindow();
  });

  const deleteButton = cells[6].querySelector(".warning_btn");
  deleteButton?.addEventListener("click", () => {
    const userId = encodeURIComponent(user.id ?? 0);
    if (!confirmDelete("Ali si preprican, da zelis izbrisati uporabnika?")) return;
    window.location.href = `../entry/delete_app_user.php?user_id=${userId}`;
  });

  return row;
}

function renderUsersInfo(users) {
  const usersInfo = document.getElementById("users_info");
  const template = document.getElementById("user_info_template");
  if (!usersInfo || !template) return;

  usersInfo.replaceChildren();

  if (!Array.isArray(users) || !users.length) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 7;
    cell.textContent = "Ni uporabnikov.";
    row.appendChild(cell);
    usersInfo.appendChild(row);
    return;
  }

  for (const user of users) {
    const row = createUserRow(user, template);
    if (row) usersInfo.appendChild(row);
  }
}

function createPointsRow(user, template) {
  const row = template.content.querySelector("tr")?.cloneNode(true);
  if (!row) return null;

  row.dataset.id = user.id ?? 0;

  const cells = row.querySelectorAll("td");
  cells[0].textContent = text(user.name);
  cells[1].textContent = String(user.user_points ?? 0);
  cells[2].innerHTML = "<div class='btn'>Uredi</div>";

  const editButton = cells[2].querySelector(".btn");
  editButton?.addEventListener("click", () => {
    prefillUpdatePointsForm(user);
    openUpdatePointsWindow();
  });

  return row;
}

function renderPointsInfo(users) {
  const pointsInfo = document.getElementById("points_info");
  const template = document.getElementById("points_info_template");
  if (!pointsInfo || !template) return;

  pointsInfo.replaceChildren();

  const sortedUsers = Array.isArray(users)
    ? [...users].sort((a, b) => {
        const pointsDiff = Number(b.user_points ?? 0) - Number(a.user_points ?? 0);
        if (pointsDiff !== 0) return pointsDiff;
        return String(a.name ?? "").localeCompare(String(b.name ?? ""));
      })
    : [];

  if (!sortedUsers.length) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 3;
    cell.textContent = "Ni podatkov o tockah.";
    row.appendChild(cell);
    pointsInfo.appendChild(row);
    return;
  }

  for (const user of sortedUsers) {
    const row = createPointsRow(user, template);
    if (row) pointsInfo.appendChild(row);
  }
}

async function restoreUpdateUserErrorState() {
  const params = new URLSearchParams(window.location.search);
  const error = params.get("update_user_error");
  const target = params.get("update_user_target");
  if (!error || !target) return;

  const user = getUserById(target);
  if (!user) return;

  prefillUpdateUserForm(user);
  setPasswordError(getUpdateUserErrorMessage(error));
  openUpdateUserWindow();

  params.delete("update_user_error");
  params.delete("update_user_target");
  const query = params.toString();
  const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}${window.location.hash}`;
  window.history.replaceState({}, "", nextUrl);
}

document.addEventListener("DOMContentLoaded", async () => {
  const overlay = document.getElementById("add_something_view");
  const updateFamilyForm = document.getElementById("update_family_form");
  const updateUserForm = document.getElementById("update_user_form");
  const updatePointsForm = document.getElementById("update_points_form");
  const cancelUpdateFamilyBtn = document.getElementById("cancel_update_family_btn");
  const cancelUpdateUserBtn = document.getElementById("cancel_update_user_btn");
  const cancelUpdatePointsBtn = document.getElementById("cancel_update_points_btn");

  try {
    const [family, users] = await Promise.all([
      fetchJson("get_family.php"),
      fetchJson("get_users.php")
    ]);

    state.family = family;
    state.users = Array.isArray(users) ? users : [];

    renderFamilyInfo(state.family);
    renderUsersInfo(state.users);
    renderPointsInfo(state.users);
    await restoreUpdateUserErrorState();
  } catch (err) {
    console.error("admin_page.js load failed:", err);
  }

  cancelUpdateFamilyBtn?.addEventListener("click", () => {
    closeAllWindows();
  });

  cancelUpdateUserBtn?.addEventListener("click", () => {
    closeAllWindows();
  });

  cancelUpdatePointsBtn?.addEventListener("click", () => {
    closeAllWindows();
  });

  overlay?.addEventListener("click", (e) => {
    if (e.target === overlay) {
      closeAllWindows();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeAllWindows();
    }
  });

  updateFamilyForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    setFamilyError("");

    const submitBtn = updateFamilyForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const response = await fetch(updateFamilyForm.action, {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: new FormData(updateFamilyForm)
      });

      const result = await response.json();

      if (!response.ok || !result.ok) {
        setFamilyError(getUpdateFamilyErrorMessage(result.error));
        openUpdateFamilyWindow();
        return;
      }

      updateFamilyInState(result.family);
      renderFamilyInfo(state.family);
      closeAllWindows();
    } catch (err) {
      console.error("Update family failed:", err);
      setFamilyError("Posodobitev druzine ni uspela. Poskusi znova.");
      openUpdateFamilyWindow();
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  updateUserForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    setPasswordError("");

    const submitBtn = updateUserForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const response = await fetch(updateUserForm.action, {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: new FormData(updateUserForm)
      });

      const result = await response.json();

      if (!response.ok || !result.ok) {
        setPasswordError(getUpdateUserErrorMessage(result.error));
        openUpdateUserWindow();
        return;
      }

      updateUserInState(result.user);
      renderUsersInfo(state.users);
      renderPointsInfo(state.users);
      closeAllWindows();
    } catch (err) {
      console.error("Update user failed:", err);
      setPasswordError("Posodobitev ni uspela. Poskusi znova.");
      openUpdateUserWindow();
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });

  updatePointsForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    setPointsError("");

    const submitBtn = updatePointsForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    try {
      const response = await fetch(updatePointsForm.action, {
        method: "POST",
        headers: {
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        },
        body: new FormData(updatePointsForm)
      });

      const result = await response.json();

      if (!response.ok || !result.ok) {
        setPointsError(getUpdatePointsErrorMessage(result.error));
        openUpdatePointsWindow();
        return;
      }

      updateUserPointsInState(result.user.id, result.user.user_points);
      renderPointsInfo(state.users);
      closeAllWindows();
    } catch (err) {
      console.error("Update points failed:", err);
      setPointsError("Posodobitev tock ni uspela. Poskusi znova.");
      openUpdatePointsWindow();
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
});
