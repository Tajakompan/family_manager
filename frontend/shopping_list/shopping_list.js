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
  const add_shop_btn = document.getElementById("add_shop_btn");
  const cancel_shop_btn = document.getElementById("cancel_shop_btn");
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

  const add_product_form = document.getElementById("add_product_form");
  const product_error = document.getElementById("add_product_error");

if (add_product_form) {
  add_product_form.addEventListener("submit", function (e) {
    const product_name = add_product_form.product_name.value.trim();
    const product_amount = add_product_form.product_amount.value.trim();
    const product_unit = add_product_form.product_unit.value.trim();
    const product_quantity = Number(add_product_form.product_quantity.value);

    product_error.innerHTML = "";
    product_error.hidden = true;

    if (product_name === "" || product_amount === "" || product_unit === "") {
      e.preventDefault();
      product_error.innerHTML = "Vsa polja so obvezna.";
      product_error.hidden = false;
      return;
    }

    if (product_quantity <= 0) {
      e.preventDefault();
      product_error.innerHTML = "Kvantiteta mora biti vsaj 1.";
      product_error.hidden = false;
      return;
    }
  });
}

const params = new URLSearchParams(window.location.search);
const product_error_code = params.get("product_error");
const shop_id = params.get("shop_id");

if (product_error_code && product_error) {
  showWindow("add_product_window");
  document.getElementById("add_something_view")?.classList.add("active");

  if (shop_id) {
    document.getElementById("product_shop_id").value = shop_id;
  }

  if (product_error_code === "required") {
    product_error.innerHTML = "Vsa polja so obvezna.";
  } else if (product_error_code === "quantity") {
    product_error.innerHTML = "Kvantiteta mora biti vsaj 1.";
  } else if (product_error_code === "necessity") {
    product_error.innerHTML = "Nujnost ni pravilna.";
  } else if (product_error_code === "shop") {
    product_error.innerHTML = "Seznam ni izbran.";
  } else {
    product_error.innerHTML = "Napaka pri vnosu.";
  }

  product_error.hidden = false;

  params.delete("product_error");
  params.delete("shop_id");
  const new_url = `${window.location.pathname}${params.toString() ? "?" + params.toString() : ""}`;
  window.history.replaceState({}, "", new_url);
}

});



