// --- SHOPPING LIST: auto-fill amount + unit po product_name (enako kot ti dela drugje) ---

const slNameInp   = document.querySelector('#add_product_form input[name="product_name"]');
const slAmountInp = document.querySelector('#add_product_form input[name="product_amount"]');
const slUnitInp   = document.querySelector('#add_product_form input[name="product_unit"]');

let slT = null;

function slSetHint(msg, isOk=true){
  if(!slNameInp) return;

  let el = document.getElementById("sl_product_hint");
  if(!el){
    el = document.createElement("div");
    el.id = "sl_product_hint";
    el.style.marginTop = "6px";
    slNameInp.insertAdjacentElement("afterend", el);
  }
  el.textContent = msg;
  el.classList.toggle("warn", !isOk);
  el.style.display = msg ? "block" : "none";
}

function slDebouncedCheck(){
  if(slT) clearTimeout(slT);
  slT = setTimeout(slCheckName, 250);
}

async function slCheckName(){
  if(!slNameInp) return;

  const name = slNameInp.value.trim();
  if(name.length < 2){
    slSetHint("");
    return;
  }

  // IMPORTANT: pot mora biti pravilna glede na shopping_list.php!
  // Če je check_product.php v ISTI mapi kot shopping_list.php, pusti "check_product.php".
  // Če je v drugi mapi, popravi npr. "../food/check_product.php"
  const url = new URL("../food_storage/check_product.php", window.location.href);
  url.searchParams.set("name", name);

  const res = await fetch(url.toString());
  if(!res.ok){
    slSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  const data = await res.json();
  if(!data.ok){
    slSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  if(data.exists){
    // predizpolni samo, če je prazno (da ne prepiše uporabnice)
    if (slAmountInp && slAmountInp.value.trim() === "") slAmountInp.value = data.product.amount ?? "";
    if (slUnitInp && slUnitInp.value.trim() === "") slUnitInp.value = data.product.unit ?? "";

  }
}

// listenerji (isto kot pri tebi)
slNameInp?.addEventListener("input", slDebouncedCheck);
slNameInp?.addEventListener("blur", slCheckName);



