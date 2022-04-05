/*
Handles fetching and displaying of calendar items
 */

let calendars = [];
let events = [];
let month = new Date();

function loadCalendars(callback) {
    $.get("/api/calendar.php", function (data) {
        calendars = data.calendars;
        populateCalendarList();
        callback();
    });
}

function loadEventsFor(calendar, callback) {
    $.get("/api/event.php?calendarid="+calendar['id'], function (data) {
        data.events.forEach(function (e) { e.calendar = calendar; events.push(e); });
        calendar.events = data.events;
        callback();
    });
}

function loadAll(callback) {
    loadCalendars(function () {
        var complete = 0;
        if (calendars.length === 0) { callback(); return; }
        calendars.forEach(function (cal) { loadEventsFor(cal,function () {
            complete++;
            if (complete >= calendars.length) {
                callback();
            }
        })});
    });
}

function populateCalendarList() {
    document.getElementById("calendar-list").innerHTML = "";
    calendars.forEach(function (cal) {
        var colorstr = "#"+cal.color.toString(16).padStart(6, '0');
        document.getElementById("calendar-list").innerHTML +=
            '<li ondblclick="editCalendar('+cal.id+')" style="background-color: '+colorstr+';"><input id="cal-enable-'+cal.id+'" value='+cal.id+' onchange="onCalendarUpdate()" type="checkbox" checked="true"/>'+cal.name+'</li>';
    });
}

const weekday = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
const monthstr = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
let calgrid = {};

function populateGridForMonth() {
    calgrid = {};
    $("#month-display").html(monthstr[month.getMonth()] + " " + month.getFullYear());
    $(".today").removeClass("today");
    var monthnum = month.getMonth();
    var date = new Date(month.getFullYear(), month.getMonth(), 1);
    var skips = date.getDay();
    date.setDate(date.getDate()-skips);
    for (x = 0; x < 6; x++) {
        for (y = 0; y < 7; y++) {
            calgrid[date] = document.getElementById("calcell-"+x+"-"+y);
            if (date.getMonth() !== monthnum) {
                calgrid[date].classList.add("otherMonth")
            } else {
                calgrid[date].classList.remove("otherMonth")
            }
            var today = new Date();
            if (date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate()) {
                calgrid[date].classList.add("today")
            }
            calgrid[date].innerHTML = "<h3>"+date.getDate()+"</h3>";
            date.setDate(date.getDate()+1);
        }
    }

    events.forEach(function (event) {
        if (!$("#cal-enable-"+event.calendar.id).prop('checked')) { return; }
        eventdate = new Date(event.date);
        grid = calgrid[new Date(eventdate.getFullYear(), eventdate.getMonth(), eventdate.getDate())];
        if (grid) {
            var colorstr = "#"+event.calendar.color.toString(16).padStart(6, '0');
            grid.innerHTML += '<div style="background-color: '+colorstr+';" class="event" onclick="eventDoubleClicked('+event.id+')">'+event.title+'</div>';
        }
    });
}

function onCalendarUpdate() {
    populateGridForMonth();
}

function reloadEverything() {
    calendars = [];
    events = [];
    loadAll(function () {
        populateGridForMonth();
        $("#loader").removeClass("show-loader");
    });
}

function openUserPreferences() {
    $("#user-editor").addClass("show-window");
}

function logout() {
    $.get("/api/logout.php", function () {
        window.location = "/login.php";
    });
}

function prevMonth() {
    month = new Date(month.getFullYear(), month.getMonth()-1, 1);
    populateGridForMonth();
}

function nextMonth() {
    month = new Date(month.getFullYear(), month.getMonth()+1, 1);
    populateGridForMonth();
}

reloadEverything();
setInterval(reloadEverything, 900000); // Every 15 minutes, requery everything