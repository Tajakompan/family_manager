const new_storage_btn = document.getElementById("add_storage_location");
const new_product_btn = document.getElementById("add_product");
const new_category_btn = document.getElementById("add_category");

const cancel_new_storage_btn = document.getElementById("cancel_new_storage_btn");
const cancel_new_product_btn = document.getElementById("cancel_new_product_btn");
const cancel_category_btn = document.getElementById("cancel_category_btn");

const add_something_view = document.getElementById("add_something_view");

const add_storage_location_window = document.querySelector(".add_storage_location_window");
const add_product_window = document.querySelector(".add_product_window");
const add_category_window = document.querySelector(".add_category_window");


new_storage_btn.addEventListener("click", () =>{
    add_something_view.classList.add("active");
    if(add_product_window.classList.contains("active")) add_product_window.classList.remove("active");
    if(add_category_window.classList.contains("active")) add_category_window.classList.remove("active");
    add_storage_location_window.classList.add("active");
})

new_product_btn.addEventListener("click", () =>{
    add_something_view.classList.add("active");
    if(add_storage_location_window.classList.contains("active")) add_storage_location_window.classList.remove("active");
    if(add_category_window.classList.contains("active")) add_category_window.classList.remove("active");
    add_product_window.classList.add("active");
})

new_category_btn.addEventListener("click", () =>{
    add_something_view.classList.add("active");
    if(add_storage_location_window.classList.contains("active")) add_storage_location_window.classList.remove("active");
    if(add_product_window.classList.contains("active")) add_product_window.classList.remove("active");
    add_category_window.classList.add("active");
})

cancel_new_storage_btn.addEventListener("click", () =>{
    add_something_view.classList.remove("active");
    add_storage_location_input.classList.remove("red");
    add_storage_location_input.placeholder = "";
    add_storage_location_window.classList.remove("active");
})

cancel_new_product_btn.addEventListener("click", () =>{
    add_something_view.classList.remove("active");
    add_product_input.forEach(one => {
        one.classList.remove("red");
        one.placeholder = "";
    })
    add_product_window.classList.remove("active");
    switchToAddMode();
})

cancel_category_btn.addEventListener("click", () =>{
    add_something_view.classList.remove("active");
    add_category_input.classList.remove("red");
    add_category_input.placeholder = "";
    add_category_window.classList.remove("active");
})