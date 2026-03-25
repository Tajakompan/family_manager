//odpiranje, zapiranje form
document.addEventListener("DOMContentLoaded", () => {
  const add_task_btn = document.getElementById("add_task_btn");
  const cancel_task_btn = document.getElementById("cancel_task_btn");
  const add_task_window = document.getElementById("add_task_window");
  const add_something_view = document.getElementById("add_something_view");
  const details_frame = document.getElementById("details_frame");

  //handler za DODAJ OPRAVILO gumb

  //odpre ADD TASK
  if (add_task_btn) {
    add_task_btn.addEventListener("click", (e) => {
      e.preventDefault();
      showWindow("add_task_window");
      add_something_view.classList.add("active");
    });
  }

  //zapre ADD TASK in resetira input
  if (cancel_task_btn) {
    cancel_task_btn.addEventListener("click", (e) => {
      e.preventDefault();
      document.getElementById("add_task_form")?.reset();
      closeWindows();
      add_something_view.classList.remove("active");
    });
  }

  //ESC isto zapre window in resetira
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeWindows();
      add_something_view.classList.remove("active");
      document.getElementById("add_task_form")?.reset();
    }
  });

  // klik izven windowa tudi zapre
  add_something_view?.addEventListener("click", () => {
    closeWindows();
    add_something_view.classList.remove("active");
  });  

  // da klik v window ne pobegne
  add_task_window?.addEventListener("click", (e) => e.stopPropagation());
  details_frame?.addEventListener("click", (e) => e.stopPropagation());
  
  const pointsInput = document.querySelector('#add_task_form input[name="points"]');
  const isChild = window.currentUserRole === "Otrok";

  if (pointsInput && isChild) {
    pointsInput.value = 2;
    pointsInput.readOnly = true;
    pointsInput.title = "Otrok ne more spreminjati tock opravila.";
  }

});


