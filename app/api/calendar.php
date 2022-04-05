<?php
/*
 * deals with operations regarding calendars
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

	if (isset($_GET['id'])) {
		$stmt = $conn->prepare("SELECT calendar.*, accesslevel FROM calendar INNER JOIN calendar_access ON calendar.id=calendar_access.calendarid WHERE userid=? AND calendarid=? AND accesslevel>0");
		$stmt->bind_param("ii", $_SESSION['user']['id'], $_GET['id']);
	} else {
		$stmt = $conn->prepare("SELECT calendar.*, accesslevel FROM calendar INNER JOIN calendar_access ON calendar.id=calendar_access.calendarid WHERE userid=? AND accesslevel>0");
		$stmt->bind_param("i", $_SESSION['user']['id']);
	}

	$stmt->execute();
	$stmt->store_result();

	$stmt->bind_result($calid, $calname, $caldesc, $calcolor, $calicon, $calcreate, $calaccess);

	//build an array of calendar objects to be passed along as json
	$out['calendars'] = Array();
	$i = 0;
	while ($stmt->fetch()) {
		$out['calendars'][$i] = array("id"=>$calid, "name"=>$calname, "description"=>$caldesc, "color"=>$calcolor, "icon"=>$calicon, "created_at"=>$calcreate, "accesslevel"=>$calaccess);
		$i++;
	}

	// build output message
	http_response_code(200);
	$out['success'] = true;
	$out['message'] = sizeof($out['calendars'])." calendars retrieved";
	$out['count'] = sizeof($out['calendars']);
} else if ($_SERVER['REQUEST_METHOD'] === "POST") {
	// Update or create calendars
	$json = file_get_contents('php://input');
	$data = json_decode($json);

	// If user specified an id
	if (isset($data->calendar->id)) {
		// Check that calendar with id exists
		$stmt = $conn->prepare("SELECT calendar.* FROM calendar INNER JOIN calendar_access ON calendar.id=calendar_access.calendarid WHERE calendar.id=? AND calendar_access.userid=? AND accesslevel>=3");
		$stmt->bind_param("ii", $data->calendar->id, $_SESSION['user']['id']);
		$stmt->execute();
		$stmt->store_result();
		if ($stmt->num_rows > 0) {
			$stmt->bind_result($rid, $rname, $rdesc, $rcolor, $ricon, $rcreate);
			$stmt->fetch();

			// update values retrieved from database with any differing data provided from user
			if ($data->calendar->name) { $rname = $data->calendar->name; }
			if ($data->calendar->description) { $rdesc = $data->calendar->description; }
			if ($data->calendar->color) { $rcolor = $data->calendar->color; }
			if ($data->calendar->icon) { $ricon = $data->calendar->icon; }
			$stmt->close();

			// Prepare update statement
			$stmt = $conn->prepare("UPDATE calendar SET name=?, description=?, color=?, icon=? WHERE id=?");
			$stmt->bind_param("ssisi", $rname, $rdesc, $rcolor, $ricon, $rid);
			$stmt->execute();

			// Verify that database was modified and build output
			if ($stmt->affected_rows != 0) {
				http_response_code(200);
				$out['success'] = true;
				$out['message'] = "calendar updated";
				$out['calendar'] = array("id"=>$rid, "name"=>$rname, "description"=>$rdesc, "color"=>$rcolor, "icon"=>$ricon, "created_at"=>$rcreate);
				$conn->close();
				echo(json_encode($out));
				exit();
			} else {
				http_response_code(500);
				$out['success'] = false;
				$out['message'] = "database error";
				$conn->close();
				echo(json_encode($out));
				exit();
			}
		}
	}

	// we either failed to find a calendar by that id, or id was not specified
	// attempt creation
	$stmt = $conn->prepare("INSERT INTO calendar (name, description, color, icon) VALUES (?,?,?,?)");
	// color cannot be null, set to zero, then override if user specified
	$color = 0;
	if (isset($data->calendar->color)) { $color = $data->calendar->color; }
	$stmt->bind_param("ssis", $data->calendar->name, $data->calendar->description, $color, $data->calendar->icon);
	$stmt->execute();

	// Ensure query did something
	if ($stmt->affected_rows > 0) {
		// Update calendar access and give user accesslevel 5 (OWNER)
		$calendarid = $conn->insert_id;
		$stmt->close();
		$stmt = $conn->prepare("INSERT INTO calendar_access (calendarid, userid, accesslevel) VALUES (?,?,5)");
		$stmt->bind_param("ii", $calendarid, $_SESSION['user']['id']);
		$stmt->execute();

		$out['success'] = true;
		$out['message'] = "calendar created";
		$out['calendar'] = array("id"=>$calendarid, "name"=>$data->calendar->name, "description"=>$data->calendar->description, "color"=>$color, "icon"=>$data->calendar->icon);
	} else {
		http_response_code(500);
		$out['success'] = false;
		$out['message'] = "database error";
	}
} else if ($_SERVER['REQUEST_METHOD'] === "DELETE") {
	// Delete calendars
	if (isset($_GET['id'])) {
		// Fetch calendar by id if we have accesslevel 4 (DELETE) or higher
		$stmt = $conn->prepare("SELECT * FROM calendar INNER JOIN calendar_access ON calendar.id=calendar_access.calendarid WHERE userid=? AND calendarid=? AND accesslevel>=4");
		$stmt->bind_param("ii", $_SESSION['user']['id'], $_GET['id']);
		$stmt->execute();
		$stmt->store_result();

		// if nothing came back either the calendar does not exist or the user does not have privilege
		if ($stmt->num_rows == 0) {
			http_response_code(401);
			$out['success'] = false;
			$out['message'] = "not authorized for calendar";
			$conn->close();
			echo(json_encode($out));
			exit();
		}

		// Pull out data and build output array
		$stmt->bind_result($rid, $rname, $rdesc, $rcolor, $ricon, $rcreate, $acccalid, $accusrid, $accacclvl);
		$stmt->fetch();
		$out['calendar'] = array("id"=>$rid, "name"=>$rname, "description"=>$rdesc, "color"=>$rcolor, "icon"=>$ricon, "created_at"=>$rcreate);
		$stmt->close();

		// create and execute delete statements for calendar, events and calendaraccess
		$delevt = $conn->prepare("DELETE event FROM event WHERE calendar=?");
		$delacc = $conn->prepare("DELETE calendar_access FROM calendar_access WHERE calendarid=?");
		$delcal = $conn->prepare("DELETE calendar FROM calendar WHERE id=?");
		$delevt->bind_param("i", $rid);
		$delacc->bind_param("i", $rid);
		$delcal->bind_param("i", $rid);
		$delevt->execute();
		$delacc->execute();
		$delcal->execute();

		// Build output
		if ($delcal->affected_rows > 0) {
			$out['success'] = true;
			$out['message'] = "calendar deleted";
		} else {
			http_response_code(500);
			$out['success'] = false;
			$out['message'] = "database error";
		}
		$delcal->close();
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
