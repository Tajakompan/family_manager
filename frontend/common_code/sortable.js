function parseNumber(txt) {
  txt = txt.replace(/\s/g, "");
  txt = txt.replace(",", ".");
  
  const number = Number(txt);
  if (Number.isFinite(number)) {
    return number;
  }
  return null;
}


function parseDate(txt) {
  if (/^\d{4}-\d{2}-\d{2}/.test(txt)) {
    const time = Date.parse(txt);
    if (Number.isNaN(time)) {
      return null;
    }
    return time;
  }

  const parts = txt.match(/^(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})$/);
  if (parts) {
    const day = Number(parts[1]);
    const month = Number(parts[2]);
    const year = Number(parts[3]);
    const time = new Date(year, month - 1, day).getTime();
    if (Number.isNaN(time)) {
      return null;
    }
    return time;
  }
  return null;
}


function compareValues(type, aTxt, bTxt, aSortValue, bSortValue) {
  if (type === "number") {
    const aNumber = parseNumber(aTxt);
    const bNumber = parseNumber(bTxt);

    if (aNumber === null && bNumber === null) return 0;
    if (aNumber === null) return 1;
    if (bNumber === null) return -1;

    return aNumber - bNumber;
  }

  if (type === "date") {
    const aDate = parseDate(aTxt);
    const bDate = parseDate(bTxt);

    if (aDate === null && bDate === null) return 0;
    if (aDate === null) return 1;
    if (bDate === null) return -1;

    return aDate - bDate;
  }

  if (type === "necessity") {
    const aNumber = Number(aSortValue || 0);
    const bNumber = Number(bSortValue || 0);

    return aNumber - bNumber;
  }

  return aTxt.localeCompare(bTxt, "sl", { sensitivity: "base" });
}

document.addEventListener("click", (e) => {
  const sortButton = e.target.closest(".sortable");
  if (!sortButton) return;

  const table = sortButton.closest("table");
  const tbody = table?.querySelector("tbody");
  if (!table || !tbody) return;

  const colIndex = Number(sortButton.dataset.col);
  const type = sortButton.dataset.type || "text";
  const key = "sortAsc_" + colIndex;
  const ascending = table.dataset[key] !== "0";
  const rows = Array.from(tbody.querySelectorAll("tr"));

  rows.sort((rowA, rowB) => {
    const cellA = rowA.children[colIndex];
    const cellB = rowB.children[colIndex];

    const textA = cellA?.innerText.trim() ?? "";
    const textB = cellB?.innerText.trim() ?? "";

    const sortValueA = cellA?.dataset.sortValue ?? "";
    const sortValueB = cellB?.dataset.sortValue ?? "";

    const result = compareValues(type, textA, textB, sortValueA, sortValueB);

    if (ascending) return result;
    return -result;
  });

  table.dataset[key] = ascending ? "0" : "1";

  rows.forEach((row) => {
    tbody.appendChild(row);
  });
});


