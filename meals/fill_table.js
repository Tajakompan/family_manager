document.addEventListener("DOMContentLoaded", function () {

    let DAYS = ["NED", "PON", "TOR", "SRE", "ČET", "PET", "SOB"];
    let tbody = document.getElementById("tbody");

    function toISODate(date) {
        let d = new Date(date);
        d.setHours(0, 0, 0, 0);

        let year = d.getFullYear();
        let month = String(d.getMonth() + 1).padStart(2, "0");
        let day = String(d.getDate()).padStart(2, "0");

        return year + "-" + month + "-" + day;
    }

    function formatSlDate(date) {
        let dayName = DAYS[date.getDay()];
        let day = date.getDate();
        let month = date.getMonth() + 1;
        let year = date.getFullYear();
        return dayName + ", " + day + ". " + month + ". " + year;
    }

    function createAddLink(dateISO, mealType) {
        let a = document.createElement("a");
        a.innerHTML = "<span class='hover_add'><span class='material-symbols-outlined'>add_circle</span></span>";
        // prilagodi, kam naj gre
        a.href = "add_meal.php?date=" + encodeURIComponent(dateISO) + "&meal=" + encodeURIComponent(mealType);
        a.className = "add-meal-link";
        return a;
    }

    // =============================
    // 1) Vedno naredimo 14 vrstic
    // =============================
    tbody.innerHTML = "";

    let today = new Date();
    today.setHours(0, 0, 0, 0);

    for (let i = 0; i < 14; i++) {
        let d = new Date(today);
        d.setDate(today.getDate() + i);

        let dateISO = toISODate(d);

        let tr = document.createElement("tr");
        tr.setAttribute("data-date", dateISO);

        // Dan
        let tdDay = document.createElement("td");
        tdDay.textContent = formatSlDate(d);
        tr.appendChild(tdDay);

        // Zajtrk
        let tdB = document.createElement("td");
        tdB.appendChild(createAddLink(dateISO, "breakfast"));
        tr.appendChild(tdB);

        // Kosilo
        let tdL = document.createElement("td");
        tdL.appendChild(createAddLink(dateISO, "lunch"));
        tr.appendChild(tdL);

        // Večerja
        let tdD = document.createElement("td");
        tdD.appendChild(createAddLink(dateISO, "dinner"));
        tr.appendChild(tdD);

        tbody.appendChild(tr);
    }

    // =============================
    // 2) Preberemo obroke iz get_meals.php (POST)
    // =============================
    // get_meals.php bere $_POST["meal_id"], čeprav ga ne rabiš za ta query.
    // Da ne bo notice/undefined, mu pošljemo meal_id=0.
    let formData = new FormData();
    formData.append("meal_id", "0");

    fetch("get_meals.php", {
        method: "POST",
        body: formData
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            // data = [{id, name, meal_category, date}, ...]

            for (let i = 0; i < data.length; i++) {
                let row = data[i];

                let date = row.date;                 // "2026-02-26"
                let category = row.meal_category;    // "breakfast" / "lunch" / "dinner"
                let name = row.name;                 // "kosmiči"

                if (!date || !category || !name) continue;

                let tr = tbody.querySelector('tr[data-date="' + date + '"]');
                if (!tr) continue;

                let cells = tr.getElementsByTagName("td");

                // 0=Dan, 1=Zajtrk, 2=Kosilo, 3=Večerja
                if (category === "breakfast") cells[1].textContent = name;
                if (category === "lunch") cells[2].textContent = name;
                if (category === "dinner") cells[3].textContent = name;
            }
        })
        .catch(function (error) {
            console.log("Napaka pri branju obrokov:", error);
        });

});