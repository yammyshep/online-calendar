/*
Periodically sends requests to a php endpoint that refreshes the session object so long as the page remains open
 */

function refreshSession() {
    $.get("/api/keepalive.php").fail(function () {
        window.location = "/login.php";
    });
}

setInterval(refreshSession, 60000);