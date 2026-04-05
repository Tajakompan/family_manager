function restoreUpdateUserErrorState() {
  const params = new URLSearchParams(window.location.search);
  const error = params.get("update_user_error");
  const target = params.get("update_user_target");

  if (!error || !target) return;

  const user = getUserById(target);
  if (!user) return;

  prefillUpdateUserForm(user);
  showError("update_user_password_error", getErrorMessage(userErrorMessages, error));
  markFieldErrors(
    document.getElementById("update_user_form"),
    getUserErrorFields(error)
  );
  openWindow("update_user_window");

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

  const updateUserBirthdateInput = updateUserForm?.querySelector('input[name="birthdate"]');
  updateUserBirthdateInput?.addEventListener("change", syncUpdateUserRoleOptions);

  const [family, users] = await Promise.all([
    fetchJson("get_family.php"),
    fetchJson("get_users.php")
  ]);


  state.family = family;
  state.users = Array.isArray(users) ? users : [];

  renderFamilyInfo(state.family);
  renderUsersInfo(state.users);
  renderPointsInfo(state.users);
  restoreUpdateUserErrorState();

  document.getElementById("cancel_update_family_btn")?.addEventListener("click", () => {
    closeAllWindows();
  });

  document.getElementById("cancel_update_user_btn")?.addEventListener("click", () => {
    closeAllWindows();
  });

  document.getElementById("cancel_update_points_btn")?.addEventListener("click", () => {
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

    showError("update_family_error", "");
    clearFieldErrors(updateFamilyForm);

    const submitBtn = updateFamilyForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const response = await fetch(updateFamilyForm.action, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new FormData(updateFamilyForm)
    }).catch(() => null);

    if (!response) {
      showError("update_family_error", "Posodobitev druzine ni uspela. Poskusi znova.");
      openWindow("update_family_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    const result = await response.json().catch(() => null);

    if (!result || !response.ok || !result.ok) {
      const errorCode = result?.error ?? "";
      showError("update_family_error", getErrorMessage(familyErrorMessages, errorCode));
      markFieldErrors(updateFamilyForm, getErrorFields("family", errorCode));
      openWindow("update_family_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    updateFamilyInState(result.family);
    renderFamilyInfo(state.family);
    closeAllWindows();

    if (submitBtn) submitBtn.disabled = false;
  });

  updateUserForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    showError("update_user_password_error", "");
    clearFieldErrors(updateUserForm);

    const submitBtn = updateUserForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const response = await fetch(updateUserForm.action, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new FormData(updateUserForm)
    }).catch(() => null);

    if (!response) {
      showError("update_user_password_error", "Posodobitev ni uspela. Poskusi znova.");
      openWindow("update_user_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    const result = await response.json().catch(() => null);

    if (!result || !response.ok || !result.ok) {
      const errorCode = result?.error ?? "";
      showError("update_user_password_error", getErrorMessage(userErrorMessages, errorCode));
      markFieldErrors(updateUserForm, getErrorFields("user", errorCode));
      openWindow("update_user_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    updateUserInState(result.user);
    renderUsersInfo(state.users);
    renderPointsInfo(state.users);
    closeAllWindows();

    if (submitBtn) submitBtn.disabled = false;
  });

  updatePointsForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    showError("update_points_error", "");
    clearFieldErrors(updatePointsForm);

    const submitBtn = updatePointsForm.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const response = await fetch(updatePointsForm.action, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest"
      },
      body: new FormData(updatePointsForm)
    }).catch(() => null);

    if (!response) {
      showError("update_points_error", "Posodobitev tock ni uspela. Poskusi znova.");
      openWindow("update_points_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    const result = await response.json().catch(() => null);

    if (!result || !response.ok || !result.ok) {
      const errorCode = result?.error ?? "";
      showError("update_points_error", getErrorMessage(pointsErrorMessages, errorCode));
      markFieldErrors(updatePointsForm, getErrorFields("points", errorCode));
      openWindow("update_points_window");
      if (submitBtn) submitBtn.disabled = false;
      return;
    }

    updateUserPointsInState(result.user.id, result.user.user_points);
    renderPointsInfo(state.users);
    closeAllWindows();

    if (submitBtn) submitBtn.disabled = false;
  });
});
