function parseNumber(txt) {
  // podpira "1,5" in "1.5" in "1 200"
  const cleaned = txt.replace(/\s/g, "").replace(",", ".");
  const n = Number(cleaned);
  return Number.isFinite(n) ? n : null;
}

function parseDate(txt) {
  // podpira ISO: 2026-01-30
  if (/^\d{4}-\d{2}-\d{2}/.test(txt)) {
    const t = Date.parse(txt);
    return Number.isNaN(t) ? null : t;
  }

  // podpira: dd.mm.yyyy ali d.m.yyyy (tudi z presledki)
  const m = txt.match(/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})$/);
  if (m) {
    const d = Number(m[1]), mo = Number(m[2]), y = Number(m[3]);
    const t = new Date(y, mo - 1, d).getTime();
    return Number.isNaN(t) ? null : t;
  }

  return null;
}

document.addEventListener("click", (e) => {
  const el = e.target.closest(".sortable");
  if(!el) return;

  const table = el.closest("table");
  const tbody = table?.querySelector("tbody");
  if(!tbody) return;

  const colIndex = Number(el.dataset.col);
  const type = el.dataset.type || "text";

  // asc per tabela+stolpec
  const key = "sortAsc_" + colIndex;
  const asc = table.dataset[key] !== "0";

  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((a, b) => {
    const aTxt = a.children[colIndex]?.innerText.trim() ?? "";
    const bTxt = b.children[colIndex]?.innerText.trim() ?? "";

    let cmp = 0;

    if (type === "number") {
      const A = (typeof parseNumber === "function") ? parseNumber(aTxt) : null;
      const B = (typeof parseNumber === "function") ? parseNumber(bTxt) : null;
      if (A === null && B === null) cmp = 0;
      else if (A === null) cmp = 1;
      else if (B === null) cmp = -1;
      else cmp = A - B;

    } else if (type === "date") {
      const A = (typeof parseDate === "function") ? parseDate(aTxt) : null;
      const B = (typeof parseDate === "function") ? parseDate(bTxt) : null;
      if (A === null && B === null) cmp = 0;
      else if (A === null) cmp = 1;
      else if (B === null) cmp = -1;
      else cmp = A - B;

    } else {
      cmp = aTxt.localeCompare(bTxt, "sl", { sensitivity: "base" });
    }

    return asc ? cmp : -cmp;
  });

  table.dataset[key] = asc ? "0" : "1";
  rows.forEach(r => tbody.appendChild(r));
});
