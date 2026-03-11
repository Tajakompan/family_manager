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

    const overlay = document.getElementById("add_something_view");
    const update_user_window = document.getElementById("update_user_window");
    const update_user_form = document.getElementById("update_user_form");
    const cancel_update_user_btn = document.getElementById("cancel_update_user_btn");

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
        }
    });

    update_user_form?.addEventListener("submit", () => {
        closeUpdateUserWindow();
    });

    delete_user?.addEventListener("click", () => {
        if (!confirm("Ali si preprican, da zelis izbrisati svoj uporabniski racun? S tem ga bos za vedno izgubil, z njim pa tudi vse svoje podatke, ki si jih prispeval v druzino.")) return;
        fetch("../entry/delete_app_user.php").then(() => location.reload());
    });
});
