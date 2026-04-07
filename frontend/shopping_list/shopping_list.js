function showWindow(id) {
  const windows = document.querySelectorAll(".window");

  for (let i = 0; i < windows.length; i++) {
    windows[i].classList.remove("active");
  }

  const windowElement = document.getElementById(id);
  if (windowElement) {
    windowElement.classList.add("active");
  }
}

function closeWindows() {
  const windows = document.querySelectorAll(".window");
  for (let i = 0; i < windows.length; i++) {
    windows[i].classList.remove("active");
  }
}

function hideProductError() {
  const productError = document.getElementById("add_product_error");

  if (productError) {
    productError.textContent = "";
    productError.hidden = true;
  }
}

function closeShoppingWindows() {
  const addSomethingView = document.getElementById("add_something_view");
  const addShopForm = document.getElementById("add_shop_form");
  const addProductForm = document.getElementById("add_product_form");

  if (addShopForm) addShopForm.reset();
  if (addProductForm) addProductForm.reset();
  
  hideProductError();
  closeWindows();

  if (addSomethingView) addSomethingView.classList.remove("active");
}

function getProductErrorMessage(errorCode) {
  if (errorCode === "required") return "Vsa polja so obvezna.";
  if (errorCode === "quantity") return "Kvantiteta mora biti vsaj 1.";
  if (errorCode === "necessity") return "Nujnost ni pravilna.";
  if (errorCode === "shop") return "Seznam ni izbran.";
  return "Napaka pri vnosu.";
}

document.addEventListener("DOMContentLoaded", function () {
  const addShopButton = document.getElementById("add_shop_btn");
  const cancelShopButton = document.getElementById("cancel_shop_btn");
  const cancelProductButton = document.getElementById("cancel_product_btn");
  const addShopWindow = document.getElementById("add_shop_window");
  const addProductWindow = document.getElementById("add_product_window");
  const addSomethingView = document.getElementById("add_something_view");
  const addProductForm = document.getElementById("add_product_form");
  const productError = document.getElementById("add_product_error");

  if (addShopButton) {
    addShopButton.addEventListener("click", function (e) {
      e.preventDefault();
      showWindow("add_shop_window");

      if (addSomethingView) {
        addSomethingView.classList.add("active");
      }
    });
  }

  if (cancelShopButton) {
    cancelShopButton.addEventListener("click", function (e) {
      e.preventDefault();
      closeShoppingWindows();
    });
  }

  if (cancelProductButton) {
    cancelProductButton.addEventListener("click", function (e) {
      e.preventDefault();
      closeShoppingWindows();
    });
  }

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeShoppingWindows();
    }
  });

  if (addShopWindow) {
    addShopWindow.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  if (addProductWindow) {
    addProductWindow.addEventListener("click", function (e) {
      e.stopPropagation();
    });
  }

  if (addProductForm) {
    addProductForm.addEventListener("submit", function (e) {
      const productName = addProductForm.product_name.value.trim();
      const productAmount = addProductForm.product_amount.value.trim();
      const productUnit = addProductForm.product_unit.value.trim();
      const productQuantity = Number(addProductForm.product_quantity.value);

      if (productError) {
        productError.textContent = "";
        productError.hidden = true;
      }

      if (productName === "" || productAmount === "" || productUnit === "") {
        e.preventDefault();
        if (productError) {
          productError.textContent = "Vsa polja so obvezna.";
          productError.hidden = false;
        }
        return;
      }

      if (productQuantity <= 0) {
        e.preventDefault();
        if (productError) {
          productError.textContent = "Kvantiteta mora biti vsaj 1.";
          productError.hidden = false;
        }
      }
    });
  }

  const params = new URLSearchParams(window.location.search);
  const productErrorCode = params.get("product_error");
  const shopId = params.get("shop_id");

  if (productErrorCode && productError) {
    showWindow("add_product_window");

    if (addSomethingView) {
      addSomethingView.classList.add("active");
    }

    if (shopId) {
      const shopIdInput = document.getElementById("product_shop_id");
      if (shopIdInput) shopIdInput.value = shopId;
    }

    productError.textContent = getProductErrorMessage(productErrorCode);
    productError.hidden = false;

    params.delete("product_error");
    params.delete("shop_id");

    const newUrl = window.location.pathname + (params.toString() ? "?" + params.toString() : "");
    window.history.replaceState({}, "", newUrl);
  }
});
