//MENJANJE POGLEDOV DESNE STRANI
//menja view za obliko prikaza desne strani, v okviru za informacije z gumbom add_event
function showView(id){
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
//menja prikaz med okvirom za informaije z gumbom add_event in okvirom s formo brez tega gumba
function showDesno(id){
    document.querySelectorAll('.desno').forEach(v => v.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}


//VALIDACIJA FORME
const add_event_form = document.querySelector('#add_event_form form');
if (add_event_form) {
  add_event_form.addEventListener('submit', (e) => {
    let ok = true;
    //preverja nujen vpis
    const name = add_event_form.querySelector('input[name="name"]');
    const date = add_event_form.querySelector('input[name="date"]');
    const time = add_event_form.querySelector('input[name="time"]');
    const whole_day = add_event_form.querySelector('input[name="whole_day"]');

    // name
    if (!name || name.value.trim() === "") {
      document.getElementById("error_name").innerHTML = "Obvezen vpis naziva dogodka!";
      ok = false;
    } else {
      document.getElementById("error_name").innerHTML = "";
    }

    // date
    if (!date || date.value.trim() === "") {
      document.getElementById("error_date").innerHTML = "Obvezen vpis datuma dogodka!";
      ok = false;
    } else {
      document.getElementById("error_date").innerHTML = "";
    }

    // time oziroma whole_day
    const wholeDayChecked = whole_day ? whole_day.checked : false;

    if (!wholeDayChecked) {
      if (!time || time.value.trim() === "") {
        document.getElementById("error_time").innerHTML = "Obvezen vpis ure dogodka!";
        ok = false;
      } else {
        document.getElementById("error_time").innerHTML = "";
      }
    } else {
      document.getElementById("error_time").innerHTML = "";
    }

    if (!ok) e.preventDefault();
  });
}

//ZAPIRANJE FORME
const cancel_btn = document.getElementById("cancel_btn");
if (cancel_btn) {
  cancel_btn.addEventListener("click", (e) => {
    e.preventDefault();

    // vrni formo v ADD mode - da ni slučajno edit
    switchEventToAddMode(window.calMonth, window.calYear);

    // počisti errorje za validacijo vpisa
    document.getElementById("error_name").innerHTML = "";
    document.getElementById("error_date").innerHTML = "";
    document.getElementById("error_time").innerHTML = "";

    showDesno('podrobnosti_dneva');

    if (selectedDate) {
      showView('view-day-selected');
      renderEventsForDate(selectedDate);
    } else {
      showView('view-empty');
      if (thisDayEventsContainer) thisDayEventsContainer.innerHTML = "";
    }
  });
}


// MENU ... za dodatne možnsti
document.addEventListener("click", (e) => {
  //najde klik
  const btn = e.target.closest(".event_menu_btn");
  const actionBtn = e.target.closest(".event_action");

  // klik na ⋯
  if (btn) {
    e.stopPropagation();
    const card = btn.closest(".one_event");
    // zapri ostale odprte menije v tem viewju
    document.querySelectorAll(".one_event.menu-open").forEach(x => {
      if (x !== card) x.classList.remove("menu-open");
    });
    card.classList.toggle("menu-open");
    return;
  }

  // klik na akcijo v meniju - kar želiš da naredi
  if (actionBtn) {
    e.stopPropagation();
    const card = actionBtn.closest(".one_event");
    const eventId = card?.dataset?.id;
    //pogleda, kater je pritisnjen
    const action = actionBtn.dataset.action;
    //ugasne meni
    card.classList.remove("menu-open");

    if (!eventId) return;

    if (action === "delete") {
        if (!confirm("Res želiš izbrisati dogodek?")) return;
        //pošlje v delete_event.php s potrebnimi atributi za poizvedbo
        fetch("delete_event.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(eventId)}`
        })
        .then(() => location.reload());
    } 
    else if (action === "edit") {
        const arr = window.eventsByDate[selectedDate] || [];
        //V arrayu arr sepravi desni prikaz dogodkov, najde pravega(id) in ga shrani v ev
        const ev = arr.find(x => String(x.id) === String(eventId));
        if (!ev) return;

        showDesno('add_event_form');
        switchEventToUpdateMode(ev.id, window.calMonth, window.calYear);

        //napolni formo z enakimi informacijami
        const form = document.querySelector('#add_event_form form');
        form.querySelector('input[name="name"]').value = ev.name || "";
        form.querySelector('input[name="date"]').value = selectedDate;

        const wholeDayCb = form.querySelector('input[name="whole_day"]');
        const timeInp = form.querySelector('input[name="time"]');

        if (wholeDayCb) wholeDayCb.checked = (ev.whole_day === 1);
        if (timeInp) {
          timeInp.value = (ev.whole_day === 0) ? (ev.event_time_raw || "") : "";
        }

        form.querySelector('input[name="location"]').value = (ev.location && ev.location !== "/") ? ev.location : "";
        form.querySelector('input[name="description"]').value = (ev.description && ev.description !== "/") ? ev.description : "";
        form.querySelector('input[name="reminder"]').value = ev.reminder_input || "";
        form.querySelector('input[name="just_for_creator"]').checked = (ev.just_for_creator === 1);
    }
  }

  // klik kjerkoli drugje zapre vse menije
  document.querySelectorAll(".one_event.menu-open").forEach(x => x.classList.remove("menu-open"));
});

//forma namenjena dodajanju dogodka
function switchEventToAddMode(month, year) {
  const form = document.querySelector('#add_event_form form');
  if (!form) return;

  const selectedUserId = window.selectedUserId || 0;

  form.dataset.mode = "add";
  form.action = `add_event.php?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&user_id=${encodeURIComponent(selectedUserId)}`;

  const hid = document.getElementById("event_id");
  if (hid) hid.value = "";

  form.querySelector('button[type="submit"]').textContent = "Shrani";
  const h3 = document.querySelector('#add_event_form h3');
  if (h3) h3.textContent = "Dodaj dogodek:";
}


//forma namenjena updejtu dogodka
function switchEventToUpdateMode(eventId, month, year) {
  const form = document.querySelector('#add_event_form form');
  if (!form) return;

  const selectedUserId = window.selectedUserId || 0;

  form.dataset.mode = "update";
  form.action = `update_event.php?month=${encodeURIComponent(month)}&year=${encodeURIComponent(year)}&user_id=${encodeURIComponent(selectedUserId)}`;

  const hid = document.getElementById("event_id");
  if (hid) hid.value = eventId;

  form.querySelector('button[type="submit"]').textContent = "Posodobi";
  const h3 = document.querySelector('#add_event_form h3');
  if (h3) h3.textContent = "Uredi dogodek:";
}

