document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.querySelector('#add_product_form input[name="product_name"]');
  const amountInput = document.querySelector('#add_product_form input[name="product_amount"]');
  const unitInput = document.querySelector('#add_product_form input[name="product_unit"]');

  let timer = null;

  if (!nameInput) return;

  function setHint(message, isOk) {
    let hint = document.getElementById("sl_product_hint");

    if (!hint) {
      hint = document.createElement("div");
      hint.id = "sl_product_hint";
      hint.style.marginTop = "6px";
      nameInput.insertAdjacentElement("afterend", hint);
    }

    hint.textContent = message;
    hint.classList.toggle("warn", !isOk);
    hint.style.display = message ? "block" : "none";
  }

  async function checkName() {
    const name = nameInput.value.trim();

    if (name.length < 2) {
      setHint("", true);
      return;
    }

    const url = new URL("../food_storage/check_product.php", window.location.href);
    url.searchParams.set("name", name);

    try {
      const response = await fetch(url.toString());

      if (!response.ok) {
        setHint("Napaka pri preverjanju izdelka.", false);
        return;
      }

      const data = await response.json();

      if (!data.ok) {
        setHint("Napaka pri preverjanju izdelka.", false);
        return;
      }

      if (data.exists) {
        if (amountInput && amountInput.value.trim() === "") {
            amountInput.value = data.product.amount ?? "";
        }

        if (unitInput && unitInput.value.trim() === "") {
          unitInput.value = data.product.unit ?? "";
        }
      }
      setHint("", true);
    } 
    catch (error) {
      setHint("Napaka pri preverjanju izdelka.", false);
    }
  }

  function debouncedCheck() {
      if (timer) {
        clearTimeout(timer);
      }
      timer = setTimeout(checkName, 250);
  }

  nameInput.addEventListener("input", debouncedCheck);
  nameInput.addEventListener("blur", checkName);
});
