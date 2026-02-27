//odpira formo
function showWindow(id) {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
  document.getElementById(id)?.classList.add("active");
}
//zapira formo
function closeWindows() {
  document.querySelectorAll(".window").forEach(w => w.classList.remove("active"));
}

//odpiranje, zapiranje form
document.addEventListener("DOMContentLoaded", () => {
  const add_meal_btn = document.getElementById("add_shop_btn");
  const cancel_meal_btn = document.getElementById("cancel_shop_btn");
  const cancel_product_btn = document.getElementById("cancel_product_btn");
  const add_shop_window = document.getElementById("add_shop_window");
  const add_product_window = document.getElementById("add_product_window");
  const add_something_view = document.getElementById("add_something_view");

  //odpre ADD SHOP
  if (add_shop_btn) {
    add_shop_btn.addEventListener("click", (e) => {
      e.preventDefault();
      showWindow("add_shop_window");
      add_something_view.classList.add("active");
    });
  }

  // zapre ADD SHOP in resetira input
  if (cancel_shop_btn) {
    cancel_shop_btn.addEventListener("click", (e) => {
      e.preventDefault();
      document.getElementById("add_shop_form")?.reset();
      closeWindows();
      add_something_view.classList.remove("active");
    });
  }


  // zapre PRODUCT ADD in resetira
  if (cancel_product_btn) {
    cancel_product_btn.addEventListener("click", (e) => {
      e.preventDefault();
      document.getElementById("add_product_form")?.reset();
      closeWindows();
      add_something_view.classList.remove("active");
    });
  }

  // ESC isto zapre window in resetira
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeWindows();
      add_something_view.classList.remove("active");
      document.getElementById("add_shop_form")?.reset();
      document.getElementById("add_product_form")?.reset();
    }
  });

  // da klik v window ne pobegne
  add_shop_window?.addEventListener("click", (e) => e.stopPropagation());
  add_product_window?.addEventListener("click", (e) => e.stopPropagation());
});
