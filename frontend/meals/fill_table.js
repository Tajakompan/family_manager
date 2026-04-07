document.addEventListener("DOMContentLoaded", function () {
    const DAYS = ["NED", "PON", "TOR", "SRE", "ČET", "PET", "SOB"];
    const MEAL_COLUMNS = {
        breakfast: 1,
        lunch: 2,
        dinner: 3
    };

    const tbody = document.getElementById("tbody");

    if (!tbody) {
        return;
    }

    function toISODate(date) {
        const newDate = new Date(date);
        newDate.setHours(0, 0, 0, 0);

        const year = newDate.getFullYear();
        const month = String(newDate.getMonth() + 1).padStart(2, "0");
        const day = String(newDate.getDate()).padStart(2, "0");

        return year + "-" + month + "-" + day;
    }

    function createAddImage() {
        const image = document.createElement("img");
        image.src = "../img/add_circle_24dp_3F3F3F_FILL0_wght400_GRAD0_opsz24.svg";
        image.alt = "Dodaj obrok";
        return image;
    }

    function getSlDateText(date) {
        const dayName = DAYS[date.getDay()];
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        return dayName + ", " + day + ". " + month + ". " + year;
    }

    function createDayCell(date) {
        const td = document.createElement("td");
        const text = getSlDateText(date);

        if (date.getDay() === 0 || date.getDay() === 6) {
            const span = document.createElement("span");
            span.className = "weekend";
            span.textContent = text;
            td.appendChild(span);
        } else {
            td.textContent = text;
        }

        return td;
    }

    function createMealButton(dateISO, mealType, mealName, mealId) {
        const button = document.createElement("div");
        button.className = "meal_cell add_meal_btn hover_add";
        button.dataset.date = dateISO;
        button.dataset.mealType = mealType;

        if (mealId !== undefined && mealId !== null && mealId !== "") {
            button.dataset.mealId = mealId;
        }

        if (mealName) {
            button.classList.add("has_meal");
            button.textContent = mealName;
        } else {
            button.classList.add("empty");
            button.appendChild(createAddImage());
        }

        return button;
    }

    function createMealCell(dateISO, mealType) {
        const td = document.createElement("td");
        td.appendChild(createMealButton(dateISO, mealType, "", ""));
        return td;
    }

    function createRow(date) {
        const tr = document.createElement("tr");
        const dateISO = toISODate(date);

        tr.dataset.date = dateISO;
        tr.appendChild(createDayCell(date));
        tr.appendChild(createMealCell(dateISO, "breakfast"));
        tr.appendChild(createMealCell(dateISO, "lunch"));
        tr.appendChild(createMealCell(dateISO, "dinner"));

        return tr;
    }

    function fillEmptyTable() {
        tbody.innerHTML = "";

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        for (let i = 0; i < 10; i++) {
            const date = new Date(today);
            date.setDate(today.getDate() + i);
            tbody.appendChild(createRow(date));
        }
    }

    function showMeals(meals) {
        for (let i = 0; i < meals.length; i++) {
            const meal = meals[i];

            if (!meal.date || !meal.meal_category || !meal.name) {
                continue;
            }

            const tr = tbody.querySelector('tr[data-date="' + meal.date + '"]');

            if (!tr) {
                continue;
            }

            const columnIndex = MEAL_COLUMNS[meal.meal_category];

            if (!columnIndex) {
                continue;
            }

            const cells = tr.getElementsByTagName("td");
            cells[columnIndex].innerHTML = "";
            cells[columnIndex].appendChild(
                createMealButton(meal.date, meal.meal_category, meal.name, meal.id)
            );
        }
    }

    fillEmptyTable();

    fetch("get_meals.php")
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (!Array.isArray(data)) {
                return;
            }

            showMeals(data);
        })
        .catch(function (error) {
            console.log("Napaka pri branju obrokov:", error);
        });
});
