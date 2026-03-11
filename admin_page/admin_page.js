async function fetchJson(url, options = {}) {
  const res = await fetch(url, { credentials: "same-origin", ...options });
  if (!res.ok) throw new Error(url + " HTTP " + res.status);
  return await res.json();
}

function renderFamilyInfo(family) {
  const familyInfo = document.getElementById("family_info");
  const template = document.getElementById("family_info_template");
  if (!familyInfo || !template) return;

  const table = template.content.querySelector("table")?.cloneNode(true);
  if (!table) return;

  table.dataset.id = family.id ?? 0;

  const cells = table.querySelectorAll("td");
  if (cells[0]) cells[0].textContent = family.name ?? "";
  if (cells[1]) cells[1].textContent = family.code ?? "";
  if (cells[2]) cells[2].textContent = "Izbrisi druzino";

  familyInfo.replaceChildren(table);
}

document.addEventListener("DOMContentLoaded", async () => {
  try {
    const family = await fetchJson("get_family.php");
    renderFamilyInfo(family);
  } catch (err) {
    console.error("admin_page.js load failed:", err);
  }
});
