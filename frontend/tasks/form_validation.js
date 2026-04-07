document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("add_task_form");
    if (!form) return;

    const nameInput = form.querySelector('input[name="new_task"]');
    const dateInput = form.querySelector('input[name="to_do_by"]');
    const noDateCheckbox = form.querySelector('input[name="no_date"]');

    function updateDateState() {
        if (!dateInput || !noDateCheckbox) return;

        if (noDateCheckbox.checked) {
            dateInput.value = "";
            dateInput.disabled = true;
            dateInput.classList.remove("red");
        } 
        else {
            dateInput.disabled = false;
        }
    }

    form.addEventListener("submit", function (event) {
        let isValid = true;

        if (nameInput) {
            nameInput.classList.remove("red");

            if (nameInput.value.trim() === "") {
                nameInput.classList.add("red");
                isValid = false;
            }
        }

        if (dateInput) {
            dateInput.classList.remove("red");

            if (!noDateCheckbox.checked && !dateInput.value) {
                dateInput.classList.add("red");
                isValid = false;
            }
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    if (noDateCheckbox) {
        noDateCheckbox.addEventListener("change", updateDateState);
    }

    updateDateState();
});
