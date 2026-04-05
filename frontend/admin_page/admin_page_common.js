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

function showError(elementId, message = "") {
  const el = document.getElementById(elementId);
  if (!el) return;

  el.textContent = message;
  el.hidden = message === "";
}

function clearFieldErrors(form) {
  if (!form) return;
  form.querySelectorAll(".red").forEach((el) => el.classList.remove("red"));
}

function markFieldErrors(form, fieldNames) {
  if (!form || !Array.isArray(fieldNames)) return;

  fieldNames.forEach((name) => {
    const field = form.querySelector(`[name="${name}"]`);
    if (field) field.classList.add("red");
  });
}

function getFamilyErrorMessage(errorCode) {
  switch (errorCode) {
    case "missing_family":
      return "Druzina ne obstaja vec.";
    case "required_fields":
      return "Ime in koda druzine sta obvezna.";
    case "code_taken":
      return "Ta koda druzine je ze zasedena.";
    case "forbidden":
      return "To lahko ureja le stars - admin.";
    default:
      return "Posodobitev druzine ni uspela. Poskusi znova.";
  }
}

function getFamilyErrorFields(errorCode) {
  switch (errorCode) {
    case "required_fields":
      return ["name", "code"];
    case "code_taken":
      return ["code"];
    default:
      return [];
  }
}

function getUserErrorMessage(errorCode) {
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
    case "too_many_parents":
      return "Druzina ima lahko najvec dva starsa - admina.";
    case "minor_must_be_child":
      return "Mladoletni uporabnik je lahko le otrok.";
    case "forbidden":
      return "To lahko ureja le stars - admin.";
    default:
      return "Posodobitev ni uspela. Poskusi znova.";
  }
}

function getUserErrorFields(errorCode) {
  switch (errorCode) {
    case "required_fields":
      return ["name", "surname", "email", "birthdate", "role"];
    case "invalid_email":
    case "email_taken":
      return ["email"];
    case "future_birthdate":
      return ["birthdate"];
    case "password_mismatch":
    case "password_too_short":
      return ["password_1", "password_2"];
    case "too_many_parents":
    case "minor_must_be_child":
      return ["role"];
    default:
      return [];
  }
}

function getPointsErrorMessage(errorCode) {
  switch (errorCode) {
    case "missing_user":
      return "Izbrani uporabnik ne obstaja vec.";
    case "invalid_points":
      return "Vnesi veljavno stevilo tock 0 ali vec.";
    case "forbidden":
      return "To lahko ureja le stars - admin.";
    default:
      return "Posodobitev tock ni uspela. Poskusi znova.";
  }
}

function getPointsErrorFields(errorCode) {
  switch (errorCode) {
    case "invalid_points":
      return ["points"];
    default:
      return [];
  }
}

function openWindow(windowId) {
  document.getElementById("add_something_view")?.classList.add("active");
  document.getElementById(windowId)?.classList.add("active");
}

function closeAllWindows() {
  document.getElementById("add_something_view")?.classList.remove("active");
  document.getElementById("update_family_window")?.classList.remove("active");
  document.getElementById("update_user_window")?.classList.remove("active");
  document.getElementById("update_points_window")?.classList.remove("active");
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

  const options = Array.from(roleInput.options);

  const parentOption = options.find((option) =>
    option.textContent.trim() === "Starš - admin"
  );
  const adultOption = options.find((option) =>
    option.textContent.trim() === "Odrasel"
  );
  const childOption = options.find((option) =>
    option.textContent.trim() === "Otrok"
  );

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
