document.addEventListener("DOMContentLoaded", function () {

    let form = document.getElementById("add_task_form");
    if (!form) return;

    let nameInput = form.querySelector('input[name="new_task"]');
    let dateInput = form.querySelector('input[name="to_do_by"]');
    let noDateCheckbox = form.querySelector('input[name="no_date"]');

    form.addEventListener("submit", function (event) {
        let isValid = true;

        nameInput.classList.remove("red");
        dateInput.classList.remove("red");

        let nameValue = nameInput.value.trim();
        let dateValue = dateInput.value;
        let noDateChecked = noDateCheckbox.checked;

        //ime obvezno
        if (nameValue === "") {
            nameInput.classList.add("red");
            isValid = false;
        }
        //datum ali no_date obvezno
        if (!dateValue && !noDateChecked) {
            dateInput.classList.add("red");
            isValid = false;
        }
        // če forma ni uspešna, prekini submit
        if (!isValid) 
            event.preventDefault();
    });

    //če damo no_date, preprečimo datum
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
