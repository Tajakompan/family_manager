//oblikovanje izpisa Date
function formatDateSI(isoDate) {
  const [y, m, d] = isoDate.split('-').map(Number);
  const date = new Date(y, m - 1, d);

  return new Intl.DateTimeFormat('sl-SI', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date);
}

//RISANJE DESNE STRANI

//oblikovanje izpisa dneva
const date_and_day = document.getElementById('date_and_day');
const days_in_week = ['Nedelja', 'Ponedeljek', 'Torek', 'Sreda', 'Četrtek', 'Petek', 'Sobota'];

//funkcija, ki v js preverja vpis znakov
function escapeHtml(str) {
    return String(str ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

//polnjenje thisDayEventContainer, ki izpisuje desno stran, seprvi kateri dogodki so ta dan
const thisDayEventsContainer = document.querySelector('.this_day_events');
function renderEventsForDate(dateStr){
    if (!thisDayEventsContainer) return;

    //vzemanje tabele php urejenih dogodkov po datumu
    const eventsByDate = window.eventsByDate || {};
    //vzemanje enega dneva
    const events = eventsByDate[dateStr] || [];

    thisDayEventsContainer.innerHTML = "";

    if (events.length === 0) {
        thisDayEventsContainer.innerHTML = "<div class='one_event'>Ni dogodkov.</div>";
        return;
    }

    // izpiše dogodke za željen dan
    events.forEach(e => {
        const div = document.createElement("div");
        div.className = "one_event";
        div.innerHTML =
            `<div class="event_head">
                <div class="event_title">${escapeHtml(e.name)}</div>
                <button class="event_menu_btn" type="button" aria-label="Meni">⋯</button>
                <div class="event_menu">
                    <button type="button" class="event_action" data-action="edit">Uredi</button>
                    <button type="button" class="event_action danger" data-action="delete">Izbriši</button>
                </div>
            </div>
            <div class="event_body">
                <div><b>Ura: </b>${escapeHtml(e.event_time)}</div>
                <div><b>Lokacija: </b>${escapeHtml(e.location)}</div>
                <div><b>Opis: </b>${escapeHtml(e.description)}</div>
                <div><b>Ustvaril: </b>${escapeHtml(e.user_name)}</div>
                <div><b>Opomnik: </b>${escapeHtml(e.reminder_display)}</div>
            </div>`;
        //doda id na okvirček z informacijami o dogodku
        div.dataset.id = e.id;
        //večjemu okvirju doda noter okvirček dogodka
        thisDayEventsContainer.appendChild(div);
    });
}


//RISANJE KOLEDARJA
let selectedDate = null;

//gre po celi tabeli koledarja, da ikoncam spreminja ozadje
document.querySelectorAll('.dan').forEach(cell => {
    cell.addEventListener('click', () => {
        // če klikneš na že selectan dan, se označba skrije in se pokaže označba to_Select
        if (cell.classList.contains('selected')) {
            cell.classList.remove('selected');
            selectedDate = null;
            cell.classList.add('to_select');
            showView('view-empty');
            
            if (thisDayEventsContainer) thisDayEventsContainer.innerHTML = "";
            return;
        }

        // če imaš selectanega, pa klikneš drugega, se pri prvem select odstrani
        const prevSelected = document.querySelector('.dan.selected');
        if (prevSelected) prevSelected.classList.remove('selected');

        // na drugem se select doda
        cell.classList.add('selected');
        cell.classList.remove('to_select');
        selectedDate = cell.dataset.date;
        showView('view-day-selected');
        const [y, m, d] = cell.dataset.date.split('-').map(Number);
        const dateObj = new Date(y, m - 1, d);
        const dayName = days_in_week[dateObj.getDay()];
        date_and_day.innerHTML = `<div class="datum">${dayName}<br>${formatDateSI(cell.dataset.date)}</div>`;
        //napolni se z dogodki tega dne
        renderEventsForDate(selectedDate);
    });

    //okno obarva na to_select samo, če ni že selectan
    cell.addEventListener('mouseenter', () => {
        if (cell.classList.contains('selected')) 
            return;
        cell.classList.add('to_select');
    });

    //če zapustiš polje, daš stran to_select
    cell.addEventListener('mouseleave', () => {
        cell.classList.remove('to_select');
    });

});

//isto za dodaj dogodek gumb - barvanje s to_select
document.querySelectorAll('.add_event').forEach(btn => {
    btn.addEventListener('mouseenter', () => {
        if (!btn.classList.contains('to_select')) 
            btn.classList.add('to_select');
    });
    btn.addEventListener('mouseleave', () => {
        if (btn.classList.contains('to_select')) 
        btn.classList.remove('to_select');
    });
})

//če pa dejansko klikneš na gumb, pa se spremeni prikaz desnega
document.querySelectorAll('.add_event').forEach(btn => {
    btn.addEventListener('click', () => {
    showDesno('add_event_form');
    switchEventToAddMode(window.calMonth, window.calYear);
    const date_input = document.getElementById('date_input');
    if(date_input && selectedDate)
        date_input.value = selectedDate;
    })
})
