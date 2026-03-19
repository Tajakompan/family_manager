const row_menu = document.getElementById("row_menu");

function positionMenu(menu, e) {
    menu.style.display = "flex";

    const w = menu.offsetWidth;
    const h = menu.offsetHeight;

    let x = e.clientX;
    let y = e.clientY;

    if (x + w > window.innerWidth) x = window.innerWidth - w - 5;
    if (y + h > window.innerHeight) y = window.innerHeight - h - 5;

    menu.style.left = `${x}px`;
    menu.style.top = `${y}px`;
}

function hideMenus() {
    if (row_menu) row_menu.style.display = "none";
}

document.addEventListener("DOMContentLoaded", () => {
    const option_btn = document.getElementById("option_btn");
    const edit_data = document.getElementById("edit_data");
    const delete_user = document.getElementById("delete_user");
    const profile_name = document.getElementById("profile_name");
    const profile_email = document.getElementById("profile_email");
    const greeting_name = document.querySelector(".title_left h2 span");

    const overlay = document.getElementById("add_something_view");
    const update_user_window = document.getElementById("update_user_window");
    const update_user_form = document.getElementById("update_user_form");
    const cancel_update_user_btn = document.getElementById("cancel_update_user_btn");
    const password_error = document.getElementById("update_user_password_error");

    function setPasswordError(message = "") {
        if (!password_error) return;

        password_error.textContent = message;
        password_error.hidden = message === "";
    }

    function getUpdateUserErrorMessage(errorCode) {
        switch (errorCode) {
            case "required_fields":
                return "Vsa polja razen gesla so obvezna.";
            case "invalid_email":
                return "E-mail ni v veljavnem formatu.";
            case "future_birthdate":
                return "Datum rojstva ne more biti v prihodnosti.";
            case "email_taken":
                return "Ta e-mail ze uporablja drug uporabnik.";
            case "password_mismatch":
                return "Gesli se ne ujemata.";
            case "password_too_short":
                return "Geslo mora imeti vsaj 8 znakov.";
            default:
                return "Posodobitev ni uspela. Poskusi znova.";
        }
    }

    function refreshProfileSummary(user) {
        if (!user) return;

        if (profile_name) profile_name.textContent = `${user.name} ${user.surname}`;
        if (profile_email) profile_email.textContent = user.email;
        if (greeting_name) greeting_name.textContent = user.name;
    }

    async function prefillUpdateUserForm() {
        if (!update_user_form) return;
        try {
            const res = await fetchJson("../entry/get_user.php");
            if (!res.ok || !res.user) return;

            const { name, surname, email, birthdate } = res.user;
            const nameInput = update_user_form.querySelector('input[name="name"]');
            const surnameInput = update_user_form.querySelector('input[name="surname"]');
            const emailInput = update_user_form.querySelector('input[name="email"]');
            const birthdateInput = update_user_form.querySelector('input[name="birthdate"]');
            const passwordInput1 = update_user_form.querySelector('input[name="password_1"]');
            const passwordInput2 = update_user_form.querySelector('input[name="password_2"]');

            if (nameInput) nameInput.value = name ?? "";
            if (surnameInput) surnameInput.value = surname ?? "";
            if (emailInput) emailInput.value = email ?? "";
            if (birthdateInput) birthdateInput.value = birthdate ?? "";
            if (passwordInput1) passwordInput1.value = "";
            if (passwordInput2) passwordInput2.value = "";
            setPasswordError("");
        } catch (err) {
            console.error("Update profile prefill failed:", err);
        }
    }

    function openUpdateUserWindow() {
        overlay?.classList.add("active");
        update_user_window?.classList.add("active");
    }

    function closeUpdateUserWindow() {
        update_user_window?.classList.remove("active");
        overlay?.classList.remove("active");
    }

    async function restoreUpdateUserErrorState() {
        const params = new URLSearchParams(window.location.search);
        const error = params.get("update_user_error");
        if (!error) return;

        await prefillUpdateUserForm();
        setPasswordError(getUpdateUserErrorMessage(error));
        openUpdateUserWindow();

        params.delete("update_user_error");
        const query = params.toString();
        const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}${window.location.hash}`;
        window.history.replaceState({}, "", nextUrl);
    }

    option_btn?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (row_menu) positionMenu(row_menu, e);
    });

    row_menu?.addEventListener("click", (e) => {
        e.stopPropagation();
    });

    document.addEventListener("click", hideMenus);
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            hideMenus();
            closeUpdateUserWindow();
            closeUploadImageWindow();
        }
    });
    window.addEventListener("scroll", hideMenus, { passive: true });
    window.addEventListener("resize", hideMenus);

    edit_data?.addEventListener("click", async () => {
        hideMenus();
        await prefillUpdateUserForm();
        openUpdateUserWindow();
    });

    cancel_update_user_btn?.addEventListener("click", () => {
        closeUpdateUserWindow();
    });

    overlay?.addEventListener("click", (e) => {
        if (e.target === overlay) {
            closeUpdateUserWindow();
            closeUploadImageWindow();
        }
    });

    update_user_form?.addEventListener("submit", async (e) => {
        e.preventDefault();
        setPasswordError("");

        const submit_btn = update_user_form.querySelector('button[type="submit"]');
        if (submit_btn) submit_btn.disabled = true;

        try {
            const result = await fetchJson(update_user_form.action, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: new FormData(update_user_form)
            });

            if (!result.ok) {
                setPasswordError(getUpdateUserErrorMessage(result.error));
                openUpdateUserWindow();
                return;
            }

            refreshProfileSummary(result.user);
            closeUpdateUserWindow();
        } catch (err) {
            console.error("Update profile failed:", err);
            setPasswordError("Posodobitev ni uspela. Poskusi znova.");
            openUpdateUserWindow();
        } finally {
            if (submit_btn) submit_btn.disabled = false;
        }
    });

    delete_user?.addEventListener("click", () => {
        if (!confirm("Ali si preprican, da zelis izbrisati svoj uporabniski racun? S tem ga bos za vedno izgubil, z njim pa tudi vse svoje podatke, ki si jih prispeval v druzino.")) return;
        fetch("../entry/delete_app_user.php").then(() => location.reload());
    });


    const change_image = document.getElementById("change_image");
    const upload_image_window = document.getElementById("upload_image_window");
    const cancel_upload_image_btn = document.getElementById("cancel_upload_image_btn");
    const upload_image_error = document.getElementById("upload_image_error");

    function setUploadImageError(message = "") {
        if (!upload_image_error) return;

        upload_image_error.textContent = message;
        upload_image_error.hidden = message === "";
    }

    function restoreUploadImageErrorState() {
        const params = new URLSearchParams(window.location.search);
        const error = params.get("upload_image_error");
        if (!error) return;

        setUploadImageError(getUploadImageErrorMessage(error));
        openUploadImageWindow();

        params.delete("upload_image_error");
        const query = params.toString();
        const nextUrl = `${window.location.pathname}${query ? `?${query}` : ""}${window.location.hash}`;
        window.history.replaceState({}, "", nextUrl);
    }



    function openUploadImageWindow() {
        overlay?.classList.add("active");
        upload_image_window?.classList.add("active");
    }

    function closeUploadImageWindow() {
        upload_image_window?.classList.remove("active");
        overlay?.classList.remove("active");
    }

    change_image?.addEventListener("click", () => {
        hideMenus();
        setUploadImageError("");
        openUploadImageWindow();
    });

    cancel_upload_image_btn?.addEventListener("click", () => {
        setUploadImageError("");
        closeUploadImageWindow();
    });
    

    function getUploadImageErrorMessage(errorCode) {
        switch (errorCode) {
            case "missing_file":
                return "Datoteka ni bila izbrana.";
            case "file_too_large":
                return "Slika je prevelika.";
            case "invalid_type":
                return "Dovoljene so samo JPG, PNG in WEBP slike.";
            case "read_failed":
                return "Branje datoteke ni uspelo.";
            case "save_failed":
                return "Shranjevanje slike ni uspelo.";
            default:
                return "Nalaganje slike ni uspelo.";
        }
    }
    restoreUpdateUserErrorState();
    restoreUploadImageErrorState();


});