<?php
	/*
	 * Jacob Buelow
	 * CS248-001 Web Development
	 * Final Project 9
	 * Interactive Calendar
	 */

session_start();
if (!$_SESSION['authed']) {
    // User is not signed in or session expired
    header("Location: /login.php");
}
?>
<!DOCTYPE html>
<html>
    <title>Online Calendar</title>
	<link rel="stylesheet" href="/res/css/styles.css">
    <script src="/res/js/jquery-3.6.0.min.js"></script>
    <script src="/res/js/modalCommon.js"></script>
    <script src="/res/js/keepAlive.js"></script>
    <script src="/res/js/calendar.js"></script>
    <script src="/res/js/eventEditor.js"></script>
    <script src="/res/js/calendarEditor.js"></script>
    <script src="/res/js/users.js"></script>
<head>
</head>
<body>
	<header>
        <table class="header">
            <tr>
                <td class="headerleft">
                    <div id="logozone">
                        <img src="/res/img/icon.svg" alt="logo">
                        Calendar
                    </div>
                </td>
                <td class="header-mid">
                    <table>
                        <tr>
                            <td><div class="arrow" onclick="prevMonth()">&#10094;</div></td>
                            <td id="month-display"></td>
                            <td><div class="arrow rightarr" onclick="nextMonth()">&#10095;</div></td>
                        </tr>
                    </table>
                </td>
                <td class="headerright">
                    <div id="profilezone" onclick="openUserPreferences()">
                        <?=$_SESSION['user']['email'] ?>
                        <img src="https://www.gravatar.com/avatar/<?=md5(strtolower(trim($_SESSION['user']['email']))); ?>" alt="avatar">
                    </div>
                </td>
            </tr>
        </table>
	</header>
	<section>
		<nav>
            <h3>My Calendars</h3>
            <ul id="calendar-list"></ul>
            <button type="button" onclick="createNewEvent()">Create Event</button>
            <button type="button" onclick="createNewCalendar()">Create Calendar</button>
		</nav>
		<article>
			<table class="calendar">
				<tr>
					<th><h2>Sunday</h2></th>
					<th><h2>Monday</h2></th>
					<th><h2>Tuesday</h2></th>
					<th><h2>Wednesday</h2></th>
					<th><h2>Thursday</h2></th>
					<th><h2>Friday</h2></th>
					<th><h2>Saturday</h2></th>
				</tr>
<?php
// Generate the 6 row, 7 col grid for a calendar with corresponding ids
for ($x = 0; $x < 6; $x++) {
	echo '<tr>';
	for ($y = 0; $y < 7; $y++) {
?>
					<td id="calcell-<?=$x ?>-<?=$y ?>"></td>
<?php
	}
	echo '</tr>';
}
?>
			</table>
		</article>
	</section>
    <div id="event-editor" class="modal-window editor">
        <div>
            <div class="modal-close" onclick="onModalClose('event-editor')">&times;</div>
            <h1>Edit Event</h1>
            <input type="hidden" id="edit-event-id">
            <input type="hidden" id="edit-event-create">
            <table>
                <tr><td><label for="edit-event-cal">Calendar:</label></td><td><select id="edit-event-cal"></select></td></tr>
                <tr><td><label for="edit-event-title">Title:</label></td><td><input id="edit-event-title"/></td></tr>
                <tr><td><label for="edit-event-date">Date:</label></td><td><input id="edit-event-date" type="datetime-local"></td></tr>
                <tr><td><label for="edit-event-loc">Location:</label></td><td><input id="edit-event-loc"/></td></tr>
                <tr><td colspan="2"><textarea id="edit-event-desc"></textarea></td></tr>
            </table>
            <div class="modal-delete" onclick="deleteEventInEditor()">Delete</div>
            <div class="modal-save" onclick="saveEventEditor()">Save</div>
        </div>
    </div>
    <div id="calendar-editor" class="modal-window editor">
        <div>
            <div class="modal-close" onclick="onModalClose('calendar-editor')">&times;</div>
            <h1>Edit Calendar</h1>
            <input type="hidden" id="edit-cal-id">
            <input type="hidden" id="edit-cal-create">
            <table>
                <tr><td><label for="edit-cal-name">Name:</label></td><td><input id="edit-cal-name"/></td></tr>
                <tr><td><label for="edit-cal-color">Color:</label></td><td><input type="color" id="edit-cal-color" class="colorpicker"></td></tr>
                <tr><td colspan="2"><textarea id="edit-cal-desc"></textarea></td></tr>
            </table>
            <div class="modal-delete" onclick="deleteCalendarInEditor()">Delete</div>
            <div class="modal-save" onclick="saveCalendarEditor()">Save</div>
        </div>
    </div>
    <div id="user-editor" class="modal-window editor">
        <div>
            <div class="modal-close" onclick="onModalClose('user-editor')">&times;</div>
            <h1>User Preferences</h1>
            <h2>Signed in as <?=$_SESSION['user']['email'] ?></h2>
            <div id="user-editor-error" class="errorholder"></div>
            <input type="hidden" id="edit-user-id" value="<?=$_SESSION['user']['id'] ?>">
            <table>
                <tr>
                    <td>
                        <label for="edit-user-email-1">Change email:</label><br>
                        <input id="edit-user-email-1" placeholder="<?=$_SESSION['user']['email'] ?>">
                    </td>
                    <td>
                        <label for="edit-user-pass-1">Change password:</label><br>
                        <input id="edit-user-pass-1" type="password" placeholder="************">
                    </td>
                </tr>
                <tr>
                    <td>
                        <input id="edit-user-email-2" placeholder="Confirm email">
                    </td>
                    <td>
                        <input id="edit-user-pass-2" type="password" placeholder="Confirm password">
                    </td>
                </tr>
                <tr>
                    <td>
                        <button type="button" onclick="updateEmail()">Change email</button>
                    </td>
                    <td>
                        <button type="button" onclick="updatePassword()">Change password</button>
                    </td>
                </tr>
            </table>
            <div class="modal-delete logout" onclick="logout()">Log Out</div>
        </div>
    </div>
    <div class="loader show-loader" id="loader">
        <div>
            <img src="/res/img/spinner.svg">
        </div>
    </div>
</body>
</html>
