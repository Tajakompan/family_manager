async function loadPoints() {
  const tbody = document.getElementById("points_tbody");
  if (!tbody) return;

  const response = await fetch("get_points.php", {
    method: "GET",
    headers: {
        Accept: "application/json"
    },
    credentials: "same-origin"
  });

  if (!response.ok) {
    tbody.innerHTML = "<tr><td colspan='2'>Napaka (" + response.status + ")</td></tr>";
    return;
  }

  const data = await response.json();

  if (!Array.isArray(data) || data.length === 0) {
    tbody.innerHTML = "<tr><td colspan='2'>Ni podatkov</td></tr>";
    return;
  }

  tbody.innerHTML = "";

  for (let i = 0; i < data.length; i++) {
    const row = data[i];
    const tr = document.createElement("tr");
    const nameTd = document.createElement("td");
    const pointsTd = document.createElement("td");

    nameTd.textContent = row.name ?? "";
    pointsTd.textContent = row.points ?? 0;

    tr.appendChild(nameTd);
    tr.appendChild(pointsTd);
    tbody.appendChild(tr);
  }
}

document.addEventListener("DOMContentLoaded", function () {
  loadPoints().catch(function (error) {
    console.error(error);
  });
});
