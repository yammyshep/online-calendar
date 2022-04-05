/*
Contains functions pertaining to the event editor window reached by either double clicking an event or creating a new event
 */

function editEventPopup(event) {
    if (event) {
        $("#edit-event-id").val(event.id);
        $("#edit-event-title").val(event.title);
        $("#edit-event-loc").val(event.location);
        $("#edit-event-desc").val(event.description);
        $("#edit-event-date").val(event.date);
        $("#edit-event-create").val(false);
    } else {
        $("#edit-event-id").val(null);
        $("#edit-event-title").val(null);
        $("#edit-event-loc").val(null);
        $("#edit-event-desc").val(null);
        $("#edit-event-date").val(new Date().toISOString().slice(0, 19).replace('T', ' '));
        $("#edit-event-create").val(true);
    }
    $("#edit-event-cal").html("");
    calendars.forEach(function (cal) {
        $("#edit-event-cal").append(new Option(cal.name, cal.id));
    });
    $("#event-editor").addClass("show-window");
}

function createNewEvent() {
    editEventPopup(null);
}

function findEventById(id) {
    var event;
    events.every(function (e) {
        if (e.id == id) {
            event = e;
            return false;
        }
        return true;
    });
    return event;
}

function eventDoubleClicked(eventid) {
    editEventPopup(findEventById(eventid));
}

function saveEventEditor() {
    $("#loader").addClass("show-loader");
    let id = $("#edit-event-id").val();
    let create = $("#edit-event-create").val();
    let cal = $("#edit-event-cal").val();
    let title = $("#edit-event-title").val();
    let loc = $("#edit-event-loc").val();
    let desc = $("#edit-event-desc").val();
    let date = $("#edit-event-date").val();
    let eventdata = {
        event: {
            title: title,
            location: loc,
            description: desc,
            date: date,
            calendar: cal
        }
    };
    if (create !== "true") {
        eventdata.event.id = id;
    }
    $.ajax({
        url: "/api/event.php",
        data: JSON.stringify(eventdata),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        method: "POST",
        success: function (data) {
            $("#loader").removeClass("show-loader");
            $("#event-editor").removeClass("show-window");
            reloadEverything();
        }
    }).fail(function (data) {
        $("#loader").removeClass("show-loader");
        console.log("Failed to update event!");
        console.log(data);
        alert("Failed to update event");
    });
}

function deleteEventInEditor() {
    $("#loader").addClass("show-loader");
    let id = $("#edit-event-id").val();
    $.ajax({
        url: "/api/event.php?id="+id,
        method: "DELETE",
        success: function (data) {
            $("#loader").removeClass("show-loader");
            $("#event-editor").removeClass("show-window");
            reloadEverything();
        }
    }).fail(function (data) {
        $("#loader").removeClass("show-loader");
        console.log("Failed to delete event!");
        console.log(data);
        alert("Failed to delete event");
    });
}