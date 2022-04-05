<?php
/*
 * deals with operations regarding events
 * GET retrieves from db, POST creates/updates, DELETE deletes
 */

header("Content-Type: application/json");
session_start();
$out = array();

// Verify user is logged in
if (!$_SESSION['authed']) {
	http_response_code(401);
	$out['success'] = false;
	$out['message'] = "session expired/not authorized";
	echo(json_encode($out));
	exit();
}

// Update session to keepalive
$_SESSION['last_access'] = time();

require_once '../config.php';

// Establish a connection to the database using mysqli
$conn = new mysqli($auth_host, $auth_username, $auth_password, $auth_database);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	// Get calendar if id is specified, otherwise get all visible calendars
	if (isset($_GET['calendarid']) && isset($_GET['id'])) {
		// User wants event with id, from calendar
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE userid=? AND calendarid=? AND id=? AND accesslevel>=1");
		$stmt->bind_param("iii", $_SESSION['user']['id'], $_GET['calendarid'], $_GET['id']);
	} else if (isset($_GET['calendarid'])) {
		// User wants all events from calendar
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE userid=? AND calendarid=? AND accesslevel>=1");
		$stmt->bind_param("ii", $_SESSION['user']['id'], $_GET['calendarid']);
	} else if (isset($_GET['id'])) {
		// User wants event with id
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE userid=? AND id=? AND accesslevel>=1");
		$stmt->bind_param("ii", $_SESSION['user']['id'], $_GET['id']);
	} else {
		// User wants all visible events
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE userid=? AND accesslevel>=1");
		$stmt->bind_param("i", $_SESSION['user']['id']);
	}

	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result($eventid, $eventcalid, $eventtitle, $eventloc, $eventdesc, $eventdate, $eventcreate, $eventaccess);

	//build an array of calendar objects to be passed along as json
	$out['events'] = Array();
	$i = 0;
	while ($stmt->fetch()) {
		$out['events'][$i] = array("id"=>$eventid, "calendar"=>$eventcalid, "title"=>$eventtitle, "location"=>$eventloc, "description"=>$eventdesc, "date"=>$eventdate, "created_at"=>$eventcreate, "accesslevel"=>$eventaccess);
		$i++;
	}

	// build output message
	http_response_code(200);
	$out['success'] = true;
	$out['message'] = sizeof($out['events'])." events retrieved";
	$out['count'] = sizeof($out['events']);
} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
	// Update or create calendars
	$json = file_get_contents('php://input');
	$data = json_decode($json);

	// If user specified an id
	if (isset($data->event->id)) {
		// Check that calendar with id exists
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE id=? AND userid=? AND accesslevel>=3");
		$stmt->bind_param("ii", $data->event->id, $_SESSION['user']['id']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			$stmt->bind_result($eventid, $eventcalid, $eventtitle, $eventloc, $eventdesc, $eventdate, $eventcreate, $eventaccess);
			$stmt->fetch();

			// update values retrieved from database with any differing data provided from user
			if ($data->event->title) { $eventtitle = $data->event->title; }
			if ($data->event->location) { $eventloc = $data->event->location; }
			if ($data->event->description) { $eventdesc = $data->event->description; }
			if ($data->event->date) { $eventdate = $data->event->date; }
			$stmt->close();

			// Prepare update statement
			$stmt = $conn->prepare("UPDATE event SET title=?, location=?, description=?, date=? WHERE id=?");
			$stmt->bind_param("ssssi", $eventtitle, $eventloc, $eventdesc, $eventdate, $eventid);
			$stmt->execute();

			// Verify that database was modified and build output
			if ($stmt->affected_rows != 0) {
				http_response_code(200);
				$out['success'] = true;
				$out['message'] = "event updated";
				$out['event'] = array("id"=>$eventid, "calendar"=>$eventcalid, "title"=>$eventtitle, "location"=>$eventloc, "description"=>$eventdesc, "date"=>$eventdate, "created_at"=>$eventcreate, "accesslevel"=>$eventaccess);
			} else {
				http_response_code(500);
				$out['success'] = false;
				$out['message'] = "database error";
			}
			$conn->close();
			echo(json_encode($out));
			exit();
		}
	}

	// Error if date, title or calendar id are not present
	if (!($data->event->title && $data->event->calendar && $data->event->date)) {
		http_response_code(400);
		$out['success'] = false;
		$out['message'] = "calendar, title and date required";
		$conn->close();
		echo(json_encode($out));
		exit();
	}

	// we either failed to find an event by that id, or id was not specified
	// attempt creation
	$stmt = $conn->prepare("INSERT INTO event (calendar, title, location, description, date) VALUES (?,?,?,?,?)");
	$stmt->bind_param("issss", $data->event->calendar, $data->event->title, $data->event->location, $data->event->description, $data->event->date);
	$stmt->execute();

	// Ensure query did something
	if ($stmt->affected_rows > 0) {
		$eventid = $conn->insert_id;

		$out['success'] = true;
		$out['message'] = "event created";
		$out['event'] = array("id"=>$eventid, "calendar"=>$data->event->calendar, "title"=>$data->event->title, "location"=>$data->event->location, "description"=>$data->event->description, "date"=>$data->event->date);
	} else {
		http_response_code(500);
		$out['success'] = false;
		$out['message'] = "database error";
	}
} else if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
	// Delete calendars
	if (isset($_GET['id'])) {
		// Fetch calendar by id if we have accesslevel 4 (DELETE) or higher
		$stmt = $conn->prepare("SELECT event.*, accesslevel FROM event INNER JOIN calendar_access ON event.calendar=calendar_access.calendarid WHERE userid=? AND id=? AND accesslevel>=4");
		$stmt->bind_param("ii", $_SESSION['user']['id'], $_GET['id']);
		$stmt->execute();
		$stmt->store_result();

		// if nothing came back either the calendar does not exist or the user does not have privilege
		if ($stmt->num_rows == 0) {
			http_response_code(401);
			$out['success'] = false;
			$out['message'] = "not authorized for event";
			$conn->close();
			echo(json_encode($out));
			exit();
		}

		// Pull out data and build output array
		$stmt->bind_result($eventid, $eventcalid, $eventtitle, $eventloc, $eventdesc, $eventdate, $eventcreate, $eventaccess);
		$stmt->fetch();
		$out['event'] = array("id"=>$eventid, "calendar"=>$eventcalid, "title"=>$eventtitle, "location"=>$eventloc, "description"=>$eventdesc, "date"=>$eventdate, "created_at"=>$eventcreate);
		$stmt->close();

		// create and execute delete statements for both event and eventaccess
		$delacc = $conn->prepare("DELETE event_access FROM event_access WHERE eventid=?");
		$delevt = $conn->prepare("DELETE event FROM event WHERE id=?");
		$delacc->bind_param("i", $eventid);
		$delevt->bind_param("i", $eventid);
		$delacc->execute();
		$delevt->execute();

		// Build output
		if ($delevt->affected_rows > 0) {
			$out['success'] = true;
			$out['message'] = "event deleted";
		} else {
			http_response_code(500);
			$out['success'] = false;
			$out['message'] = "database error";
		}
		$delevt->close();
		$delacc->close();
	} else {
		// id was not given
		http_response_code(400);
		$out['success'] = false;
		$out['message'] = "id required";
	}
} else {
	http_response_code(405);
	$out['success'] = false;
	$out['message'] = "method not allowed";
}

$conn->close();
echo(json_encode($out));
