document.addEventListener("DOMContentLoaded", function () {

    var form = document.getElementById("add_task_form");
    if (!form) return;

    var nameInput = form.querySelector('input[name="new_task"]');
    var dateInput = form.querySelector('input[name="to_do_by"]');
    var noDateCheckbox = form.querySelector('input[name="no_date"]');

    form.addEventListener("submit", function (event) {

        var isValid = true;

        // odstrani stare napake
        nameInput.classList.remove("red");
        dateInput.classList.remove("red");

        var nameValue = nameInput.value.trim();
        var dateValue = dateInput.value;
        var noDateChecked = noDateCheckbox.checked;

        // 1. Ime je obvezno
        if (nameValue === "") {
            nameInput.classList.add("red");
            isValid = false;
        }

        // 2. Obvezen je datum ALI checkbox
        if (!dateValue && !noDateChecked) {
            dateInput.classList.add("red");
            isValid = false;
        }

        // če validacija ni uspešna, prepreči submit
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Če uporabnik klikne checkbox, onemogočimo datum
    noDateCheckbox.addEventListener("change", function () {

        if (noDateCheckbox.checked) {
            dateInput.value = "";
            dateInput.disabled = true;
            dateInput.classList.remove("red");
        } else {
            dateInput.disabled = false;
        }
    });

});
