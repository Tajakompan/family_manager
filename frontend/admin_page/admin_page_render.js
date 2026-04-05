function renderFamilyInfo(family) {
  const familyInfo = document.getElementById("family_info");
  const template = document.getElementById("family_info_template");
  if (!familyInfo || !template) return;

  familyInfo.replaceChildren();

  if (!family) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 4;
    cell.textContent = "Ni podatkov o druzini.";
    row.appendChild(cell);
    familyInfo.appendChild(row);
    return;
  }

  const row = template.content.querySelector("tr")?.cloneNode(true);
  if (!row) return;

  const cells = row.querySelectorAll("td");
  cells[0].textContent = family.name ?? "";
  cells[1].textContent = family.code ?? "";
  cells[2].innerHTML = "<div class='btn'>Uredi</div>";
  cells[3].innerHTML = "<div class='btn warning_btn'>Izbrisi</div>";

  const editButton = cells[2].querySelector(".btn");
  editButton?.addEventListener("click", () => {
    prefillUpdateFamilyForm(family);
    openWindow("update_family_window");
  });

  const deleteButton = cells[3].querySelector(".warning_btn");
  deleteButton?.addEventListener("click", () => {
    if (!window.confirm("Ali si preprican, da zelis izbrisati druzino?")) return;
    window.location.href = "../entry/delete_family.php";
  });

  familyInfo.appendChild(row);
}

function renderUsersInfo(users) {
  const usersInfo = document.getElementById("users_info");
  const template = document.getElementById("user_info_template");
  if (!usersInfo || !template) return;

  usersInfo.replaceChildren();

  if (!Array.isArray(users) || users.length === 0) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 7;
    cell.textContent = "Ni uporabnikov.";
    row.appendChild(cell);
    usersInfo.appendChild(row);
    return;
  }

  users.forEach((user) => {
    const row = template.content.querySelector("tr")?.cloneNode(true);
    if (!row) return;

    const cells = row.querySelectorAll("td");
    cells[0].textContent = user.name ?? "";
    cells[1].textContent = user.surname ?? "";
    cells[2].textContent = user.birthdate ?? "";
    cells[3].textContent = user.email ?? "";
    cells[4].textContent = user.user_role_name ?? "";
    cells[5].innerHTML = "<div class='btn'>Uredi</div>";
    cells[6].innerHTML = "<div class='btn warning_btn'>Izbrisi</div>";

    const editButton = cells[5].querySelector(".btn");
    editButton?.addEventListener("click", () => {
      prefillUpdateUserForm(user);
      openWindow("update_user_window");
    });

    const deleteButton = cells[6].querySelector(".warning_btn");
    deleteButton?.addEventListener("click", () => {
      const userId = encodeURIComponent(user.id ?? 0);
      if (!window.confirm("Ali si preprican, da zelis izbrisati uporabnika?")) return;
      window.location.href = `../entry/delete_app_user.php?user_id=${userId}`;
    });

    usersInfo.appendChild(row);
  });
}

function renderPointsInfo(users) {
  const pointsInfo = document.getElementById("points_info");
  const template = document.getElementById("points_info_template");
  if (!pointsInfo || !template) return;

  pointsInfo.replaceChildren();

  const sortedUsers = Array.isArray(users)
    ? [...users].sort((a, b) => {
        const diff = Number(b.user_points ?? 0) - Number(a.user_points ?? 0);
        if (diff !== 0) return diff;
        return String(a.name ?? "").localeCompare(String(b.name ?? ""));
      })
    : [];

  if (sortedUsers.length === 0) {
    const row = document.createElement("tr");
    const cell = document.createElement("td");
    cell.colSpan = 3;
    cell.textContent = "Ni podatkov o tockah.";
    row.appendChild(cell);
    pointsInfo.appendChild(row);
    return;
  }

  sortedUsers.forEach((user) => {
    const row = template.content.querySelector("tr")?.cloneNode(true);
    if (!row) return;

    const cells = row.querySelectorAll("td");
    cells[0].textContent = user.name ?? "";
    cells[1].textContent = String(user.user_points ?? 0);
    cells[2].innerHTML = "<div class='btn'>Uredi</div>";

    const editButton = cells[2].querySelector(".btn");
    editButton?.addEventListener("click", () => {
      prefillUpdatePointsForm(user);
      openWindow("update_points_window");
    });

    pointsInfo.appendChild(row);
  });
}
