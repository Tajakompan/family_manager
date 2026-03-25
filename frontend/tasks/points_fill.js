async function loadPoints() {
  const tbody = document.getElementById("points_tbody");
  if (!tbody) return;
  const tasksApiBase = `${window.API_URL}/tasks`;

  const res = await fetch(`${tasksApiBase}/get_points.php`, {
    method: "GET",
    headers: { "Accept": "application/json" }
  });

  if (!res.ok) {
    tbody.innerHTML = `<tr><td colspan="2">Napaka (${res.status})</td></tr>`;
    return;
  }

  const data = await res.json();

  if (!Array.isArray(data) || data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="2">Ni podatkov</td></tr>`;
    return;
  }

  tbody.innerHTML = "";

  for (let i = 0; i < data.length; i++) {
    let row = data[i];

    let tr = document.createElement("tr");

    let tdName = document.createElement("td");
    tdName.textContent = row.name ?? "";

    let tdPoints = document.createElement("td");
    tdPoints.textContent = row.points ?? 0;

    tr.appendChild(tdName);
    tr.appendChild(tdPoints);
    tbody.appendChild(tr);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadPoints().catch(err => console.error(err));
});
