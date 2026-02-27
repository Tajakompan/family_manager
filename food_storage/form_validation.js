// food_storage forma autofill

const fsNameInp   = document.querySelector('#add_product_form input[name="product_name"]');
const fsAmountInp = document.querySelector('#add_product_form input[name="product_amount"]');
const fsUnitInp   = document.querySelector('#add_product_form input[name="product_unit"]');
const fsCatSel    = document.querySelector('#add_product_form select[name="product_category"]');

let fsT = null;

function fsSetHint(msg, isOk = true){
  if(!fsNameInp) return;

  let el = document.getElementById("fs_product_hint");
  if(!el){
    el = document.createElement("div");
    el.id = "fs_product_hint";
    el.style.marginTop = "6px";
    fsNameInp.insertAdjacentElement("afterend", el);
  }

  el.textContent = msg;
  el.classList.toggle("warn", !isOk);
  el.style.display = msg ? "block" : "none";
}

function fsDebouncedCheck(){
  if(fsT) clearTimeout(fsT);
  fsT = setTimeout(fsCheckName, 250);
}

async function fsCheckName(){
  if(!fsNameInp) return;

  const name = fsNameInp.value.trim();
  if(name.length < 2){
    fsSetHint("");
    return;
  }

  const url = new URL("check_product.php", window.location.href);
  url.searchParams.set("name", name);

  const res = await fetch(url.toString());
  if(!res.ok){
    fsSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  const data = await res.json();
  if(!data.ok){
    fsSetHint("Napaka pri preverjanju izdelka.", false);
    return;
  }

  if(data.exists){

    fsSetHint("Izdelek že obstaja - podatki so bili predizpolnjeni.", true);

    // predizpolni samo prazna polja
    if (fsAmountInp && fsAmountInp.value.trim() === "")
      fsAmountInp.value = data.product.amount ?? "";

    if (fsUnitInp && fsUnitInp.value.trim() === "")
      fsUnitInp.value = data.product.unit ?? "";

    if (fsCatSel && data.product.product_category_id)
      fsCatSel.value = String(data.product.product_category_id);

    const hid = document.querySelector('input[name="product_id_existing"]');
    if(hid) hid.value = data.product.id ?? "";

  } else {
    fsSetHint("Nov izdelek - vnesi podatke.", true);
  }
}

// listenerji
fsNameInp?.addEventListener("input", fsDebouncedCheck);
fsNameInp?.addEventListener("blur", fsCheckName);
