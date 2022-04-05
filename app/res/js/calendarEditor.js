function editCalendarPopup(calendar) {
    if (calendar) {
        $("#edit-cal-id").val(calendar.id);
        $("#edit-cal-name").val(calendar.name);
        var colorstr = "#"+calendar.color.toString(16).padStart(6, '0');
        $("#edit-cal-color").val(colorstr);
        $("#edit-cal-desc").val(calendar.description);
        $("#edit-event-create").val(false);
    } else {
        $("#edit-cal-id").val(null);
        $("#edit-cal-name").val(null);
        $("#edit-cal-color").val("#000000");
        $("#edit-cal-desc").val(null);
        $("#edit-event-create").val(true);
    }
    $("#calendar-editor").addClass("show-window");
}

function createNewCalendar() {
    editCalendarPopup(null);
}

function deleteCalendarInEditor() {
    $("#loader").addClass("show-loader");
    let id = $("#edit-cal-id").val();
    $.ajax({
        url: "/api/calendar.php?id="+id,
        method: "DELETE",
        success: function () {
            $("#loader").removeClass("show-loader");
            $("#calendar-editor").removeClass("show-window");
            reloadEverything();
        }
    }).fail(function (data) {
        $("#loader").removeClass("show-loader");
        console.log("Failed to delete calendar!");
        console.log(data);
        alert("Failed to delete calendar");
    });
}

function saveCalendarEditor() {
    $("#loader").addClass("show-loader");
    let colorstr = $("#edit-cal-color").val();
    let color = (parseInt(colorstr.substr(1), 16));
    let id = $("#edit-cal-id").val();
    let name = $("#edit-cal-name").val();
    let desc = $("#edit-cal-desc").val();
    let create = $("#edit-event-create").val();
    let caldata = {
        calendar: {
            name: name,
            description: desc,
            color: color
        }
    };
    if (create !== "true") {
        caldata.calendar.id = id;
    }
    $.ajax({
        url: "/api/calendar.php",
        method: "POST",
        data: JSON.stringify(caldata),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function () {
            $("#loader").removeClass("show-loader");
            $("#calendar-editor").removeClass("show-window");
            reloadEverything();
        }
    }).fail(function (data) {
        $("#loader").removeClass("show-loader");
        console.log("Failed to update calendar!");
        console.log(data);
        alert("Failed to update calendar");
    });
}

function findCalendarById(id) {
    var cal;
    calendars.every(function (c)  {
        if (c.id == id) {
            cal = c;
            return false;
        }
        return true;
    });
    return cal;
}

function editCalendar(id) {
    editCalendarPopup(findCalendarById(id));
}
