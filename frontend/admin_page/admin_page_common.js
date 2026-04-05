async function fetchJson(url, options = {}) {
  const response = await fetch(url, {
    credentials: "same-origin",
    ...options
  }).catch(() => null);

  if (!response || !response.ok) return null;

  return await response.json().catch(() => null);
}

const state = {
  family: null,
  users: []
};

const familyErrorMessages = {
  missing_family: "Družina ne obstaja več.",
  required_fields: "Ime in koda družine sta obvezna.",
  code_taken: "Ta koda družine je že zasedena.",
  forbidden: "To lahko ureja le starš - admin."
};

const userErrorMessages = {
  required_fields: "Vsa polja razen gesla so obvezna.",
  invalid_email: "E-mail ni v veljavnem formatu.",
  future_birthdate: "Datum rojstva ne more biti v prihodnosti.",
  email_taken: "Ta e-mail ze uporablja drug uporabnik.",
  password_mismatch: "Gesli se ne ujemata.",
  password_too_short: "Geslo mora imeti vsaj 8 znakov.",
  missing_user: "Izbrani uporabnik ne obstaja vec.",
  too_many_parents: "Druzina ima lahko najvec dva starsa - admina.",
  minor_must_be_child: "Mladoletni uporabnik je lahko le otrok.",
  forbidden: "To lahko ureja le stars - admin."
};

const pointsErrorMessages = {
  missing_user: "Izbrani uporabnik ne obstaja vec.",
  invalid_points: "Vnesi veljavno stevilo tock 0 ali vec.",
  forbidden: "To lahko ureja le stars - admin."
};

function showError(elementId, message = "") {
  const el = document.getElementById(elementId);
  if (!el) return;

  el.textContent = message;
  el.hidden = message === "";
}

function clearFieldErrors(form) {
  if (!form) return;

  form.querySelectorAll(".red").forEach((el) => {
    el.classList.remove("red");
  });
}

function markFieldErrors(form, fieldNames) {
  if (!form || !Array.isArray(fieldNames)) return;

  fieldNames.forEach((name) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.classList.add("red");
  });
}

function getFamilyErrorMessage(errorCode) {
  return familyErrorMessages[errorCode] ?? "Posodobitev druzine ni uspela. Poskusi znova.";
}

function getUserErrorMessage(errorCode) {
  return userErrorMessages[errorCode] ?? "Posodobitev ni uspela. Poskusi znova.";
}

function getPointsErrorMessage(errorCode) {
  return pointsErrorMessages[errorCode] ?? "Posodobitev tock ni uspela. Poskusi znova.";
}

function getFamilyErrorFields(errorCode) {
  if (errorCode === "required_fields") return ["name", "code"];
  if (errorCode === "code_taken") return ["code"];
  return [];
}

function getUserErrorFields(errorCode) {
  if (errorCode === "required_fields") return ["name", "surname", "email", "birthdate", "role"];
  if (errorCode === "invalid_email" || errorCode === "email_taken") return ["email"];
  if (errorCode === "future_birthdate") return ["birthdate"];
  if (errorCode === "password_mismatch" || errorCode === "password_too_short") return ["password_1", "password_2"];
  if (errorCode === "too_many_parents" || errorCode === "minor_must_be_child") return ["role"];
  return [];
}

function getPointsErrorFields(errorCode) {
  if (errorCode === "invalid_points") return ["points"];
  return [];
}

function openWindow(windowId) {
  const overlay = document.getElementById("add_something_view");
  const windowEl = document.getElementById(windowId);

  if (overlay) overlay.classList.add("active");
  if (windowEl) windowEl.classList.add("active");
}

function closeAllWindows() {
  const overlay = document.getElementById("add_something_view");
  const familyWindow = document.getElementById("update_family_window");
  const userWindow = document.getElementById("update_user_window");
  const pointsWindow = document.getElementById("update_points_window");

  if (overlay) overlay.classList.remove("active");
  if (familyWindow) familyWindow.classList.remove("active");
  if (userWindow) userWindow.classList.remove("active");
  if (pointsWindow) pointsWindow.classList.remove("active");
}

function calculateAgeFromDate(birthdate) {
  if (!birthdate) return null;

  const birth = new Date(birthdate);
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();

  if (
    today.getMonth() < birth.getMonth() ||
    (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())
  ) {
    age--;
  }

  return age;
}

function syncUpdateUserRoleOptions() {
  const form = document.getElementById("update_user_form");
  if (!form) return;

  const birthdateInput = form.querySelector('input[name="birthdate"]');
  const roleInput = form.querySelector('select[name="role"]');

  if (!birthdateInput || !roleInput) return;

  const parentOption = roleInput.querySelector('option[value="1"]');
  const adultOption = roleInput.querySelector('option[value="2"]');
  const childOption = roleInput.querySelector('option[value="3"]');

  if (!parentOption || !adultOption || !childOption) return;

  parentOption.hidden = false;
  parentOption.disabled = false;
  adultOption.hidden = false;
  adultOption.disabled = false;
  childOption.hidden = false;
  childOption.disabled = false;

  const age = calculateAgeFromDate(birthdateInput.value);
  if (age === null) return;

  if (age < 18) {
    parentOption.hidden = true;
    parentOption.disabled = true;
    adultOption.hidden = true;
    adultOption.disabled = true;
    roleInput.value = childOption.value;
  }
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

  clearFieldErrors(form);
  showError("update_family_error", "");
}

function prefillUpdateUserForm(user) {
  const form = document.getElementById("update_user_form");
  if (!form || !user) return;

  const idInput = form.querySelector('input[name="user_id"]');
  const nameInput = form.querySelector('input[name="name"]');
  const surnameInput = form.querySelector('input[name="surname"]');
  const emailInput = form.querySelector('input[name="email"]');
  const birthdateInput = form.querySelector('input[name="birthdate"]');
  const roleInput = form.querySelector('select[name="role"]');
  const password1Input = form.querySelector('input[name="password_1"]');
  const password2Input = form.querySelector('input[name="password_2"]');

  if (idInput) idInput.value = user.id ?? "";
  if (nameInput) nameInput.value = user.name ?? "";
  if (surnameInput) surnameInput.value = user.surname ?? "";
  if (emailInput) emailInput.value = user.email ?? "";
  if (birthdateInput) birthdateInput.value = user.birthdate ?? "";
  if (roleInput) roleInput.value = String(user.user_role_id ?? "");
  if (password1Input) password1Input.value = "";
  if (password2Input) password2Input.value = "";

  clearFieldErrors(form);
  showError("update_user_password_error", "");
  syncUpdateUserRoleOptions();
}

function prefillUpdatePointsForm(user) {
  const form = document.getElementById("update_points_form");
  if (!form || !user) return;

  const idInput = form.querySelector('input[name="user_id"]');
  const pointsInput = form.querySelector('input[name="points"]');
  const title = document.getElementById("update_points_user_name");

  if (idInput) idInput.value = user.id ?? "";
  if (pointsInput) pointsInput.value = Number(user.user_points ?? 0);
  if (title) title.textContent = user.name ?? "";

  clearFieldErrors(form);
  showError("update_points_error", "");
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

function getUserById(userId) {
  return state.users.find((user) => Number(user.id) === Number(userId)) ?? null;
}
